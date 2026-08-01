<?php

namespace App\Services\Auth\Identity;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

/**
 * Verifies Google Identity Services ID tokens using Google's JWKS.
 * Does not require google/apiclient — Client ID alone is sufficient for GIS sign-in.
 */
class GoogleIdTokenVerifier
{
    private const CERTS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    private const ISSUERS = [
        'accounts.google.com',
        'https://accounts.google.com',
    ];

    public function verify(string $idToken, string $clientId): array
    {
        if ($clientId === '') {
            throw new InvalidArgumentException('Google Client ID is not configured.');
        }

        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Invalid Google ID token format.');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = $this->decodeJson($headerB64);
        $payload = $this->decodeJson($payloadB64);

        $alg = $header['alg'] ?? null;
        $kid = $header['kid'] ?? null;
        if ($alg !== 'RS256' || ! is_string($kid) || $kid === '') {
            throw new InvalidArgumentException('Unsupported Google ID token algorithm.');
        }

        $pem = $this->publicKeyPemForKid($kid);
        $signed = $headerB64.'.'.$payloadB64;
        $signature = $this->base64UrlDecode($signatureB64);

        $ok = openssl_verify($signed, $signature, $pem, OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            throw new InvalidArgumentException('Google ID token signature is invalid.');
        }

        $exp = (int) ($payload['exp'] ?? 0);
        // Allow a small clock skew window.
        if ($exp < (time() - 60)) {
            throw new InvalidArgumentException('Google ID token has expired.');
        }

        $aud = $payload['aud'] ?? null;
        if (is_array($aud)) {
            if (! in_array($clientId, $aud, true)) {
                throw new InvalidArgumentException('Google ID token audience mismatch.');
            }
        } elseif ($aud !== $clientId) {
            throw new InvalidArgumentException('Google ID token audience mismatch.');
        }

        $iss = (string) ($payload['iss'] ?? '');
        if (! in_array($iss, self::ISSUERS, true)) {
            throw new InvalidArgumentException('Google ID token issuer is invalid.');
        }

        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        if ($email === '') {
            throw new InvalidArgumentException('Google ID token is missing an email address.');
        }

        if (empty($payload['email_verified']) || ($payload['email_verified'] !== true && $payload['email_verified'] !== 'true')) {
            throw new InvalidArgumentException('Google email address is not verified.');
        }

        $sub = (string) ($payload['sub'] ?? '');
        if ($sub === '') {
            throw new InvalidArgumentException('Google ID token is missing subject.');
        }

        return $payload;
    }

    /**
     * Lightweight config check used by Admin → Test.
     */
    public function testConfiguration(string $clientId): array
    {
        if ($clientId === '') {
            return ['ok' => false, 'message' => 'Client ID is required.'];
        }

        try {
            $keys = $this->fetchCerts(force: true);
            $count = count($keys['keys'] ?? []);

            return [
                'ok' => $count > 0,
                'message' => $count > 0
                    ? "Google Identity configuration looks valid. Fetched {$count} signing keys."
                    : 'Could not load Google signing keys.',
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Failed to reach Google certs: '.$e->getMessage()];
        }
    }

    private function publicKeyPemForKid(string $kid): string
    {
        $certs = $this->fetchCerts();
        foreach ($certs['keys'] ?? [] as $jwk) {
            if (($jwk['kid'] ?? null) === $kid) {
                return $this->jwkToPem($jwk);
            }
        }

        // Key rotation — refresh once.
        $certs = $this->fetchCerts(force: true);
        foreach ($certs['keys'] ?? [] as $jwk) {
            if (($jwk['kid'] ?? null) === $kid) {
                return $this->jwkToPem($jwk);
            }
        }

        throw new InvalidArgumentException('Google signing key not found for this token.');
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchCerts(bool $force = false): array
    {
        $cacheKey = 'google_identity_jwks';

        if (! $force) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $response = Http::timeout(10)->get(self::CERTS_URL);
        if (! $response->successful()) {
            throw new RuntimeException('HTTP '.$response->status());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Invalid JWKS payload.');
        }

        Cache::put($cacheKey, $json, now()->addHours(6));

        return $json;
    }

    /**
     * @param  array<string, mixed>  $jwk
     */
    private function jwkToPem(array $jwk): string
    {
        $n = $this->base64UrlDecode((string) ($jwk['n'] ?? ''));
        $e = $this->base64UrlDecode((string) ($jwk['e'] ?? ''));

        if ($n === '' || $e === '') {
            throw new RuntimeException('Incomplete JWK.');
        }

        $modulus = $this->encodeAsn1Integer($n);
        $exponent = $this->encodeAsn1Integer($e);
        $rsaPublicKey = $this->encodeAsn1Sequence($modulus.$exponent);
        $algorithmIdentifier = hex2bin('300d06092a864886f70d0101010500');
        $subjectPublicKey = chr(0).$rsaPublicKey;
        $bitString = chr(0x03).$this->encodeAsn1Length(strlen($subjectPublicKey)).$subjectPublicKey;
        $spki = $this->encodeAsn1Sequence($algorithmIdentifier.$bitString);

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($spki), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    private function encodeAsn1Integer(string $bytes): string
    {
        if ($bytes === '' || (ord($bytes[0]) & 0x80)) {
            $bytes = "\x00".$bytes;
        }

        return "\x02".$this->encodeAsn1Length(strlen($bytes)).$bytes;
    }

    private function encodeAsn1Sequence(string $contents): string
    {
        return "\x30".$this->encodeAsn1Length(strlen($contents)).$contents;
    }

    private function encodeAsn1Length(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        $bin = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($bin)).$bin;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $base64Url): array
    {
        $json = $this->base64UrlDecode($base64Url);
        $data = json_decode($json, true);
        if (! is_array($data)) {
            throw new InvalidArgumentException('Invalid Google ID token JSON.');
        }

        return $data;
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid base64 in Google ID token.');
        }

        return $decoded;
    }
}
