<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IntegrationDocs
{
    private const ROOT = 'docs/integrations';

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['md', 'yaml', 'php', 'example'];

    /**
     * @return list<array{path: string, title: string, group: string}>
     */
    public function navigation(): array
    {
        return [
            ['path' => 'README', 'title' => 'Overview', 'group' => 'Start'],
            ['path' => 'MERCHANT-GUIDE', 'title' => 'Merchant guide', 'group' => 'Start'],
            ['path' => 'CONSUMER-PHP', 'title' => 'PHP consumer notes', 'group' => 'Start'],
            ['path' => 'ENDPOINTS-REFERENCE', 'title' => 'Endpoint reference', 'group' => 'Start'],
            ['path' => 'PROTOCOL-v1', 'title' => 'Protocol v1', 'group' => 'Reference'],
            ['path' => 'OVERVIEW', 'title' => 'Architecture', 'group' => 'Reference'],
            ['path' => 'ERRORS', 'title' => 'Error codes', 'group' => 'Reference'],
            ['path' => 'openapi.yaml', 'title' => 'OpenAPI (YAML)', 'group' => 'Reference'],
            ['path' => 'OPERATOR', 'title' => 'Operator guide', 'group' => 'Hub operators'],
            ['path' => 'checklists/MERCHANT-GO-LIVE', 'title' => 'Go-live checklist', 'group' => 'Checklists'],
            ['path' => 'checklists/SECURITY', 'title' => 'Security checklist', 'group' => 'Checklists'],
            ['path' => 'samples/README', 'title' => 'Samples index', 'group' => 'Samples'],
            ['path' => 'samples/SMOKE-TEST', 'title' => 'Smoke test', 'group' => 'Samples'],
            ['path' => 'samples/env.example', 'title' => 'Env sample', 'group' => 'Samples'],
            ['path' => 'samples/php/protocol-v1-verify.php', 'title' => 'PHP: HMAC verify', 'group' => 'Samples'],
            ['path' => 'samples/php/consume-validate.php', 'title' => 'PHP: Token validate', 'group' => 'Samples'],
            ['path' => 'samples/php/poll-subscription.php', 'title' => 'PHP: Poll subscription', 'group' => 'Samples'],
            ['path' => 'samples/php/sync-admin-credentials.php', 'title' => 'PHP: Sync admin credentials', 'group' => 'Samples'],
            ['path' => 'samples/laravel/README', 'title' => 'Laravel sketch', 'group' => 'Samples'],
            ['path' => 'CHANGELOG', 'title' => 'Changelog', 'group' => 'Reference'],
        ];
    }

    /**
     * @return array{
     *     path: string,
     *     filename: string,
     *     extension: string,
     *     title: string,
     *     content: string,
     *     html: string|null,
     *     language: string|null
     * }|null
     */
    public function resolve(string $path): ?array
    {
        $filename = $this->normalizeFilename($path);
        if ($filename === null) {
            return null;
        }

        $absolute = $this->absolutePath($filename);
        if ($absolute === null || ! is_file($absolute)) {
            return null;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $content = (string) file_get_contents($absolute);
        $title = $this->titleFromContent($content, $filename);

        if ($extension === 'md') {
            return [
                'path' => $this->publicPath($filename),
                'filename' => $filename,
                'extension' => $extension,
                'title' => $title,
                'content' => $content,
                'html' => Str::markdown($this->rewriteMarkdownLinks($content)),
                'language' => null,
            ];
        }

        return [
            'path' => $this->publicPath($filename),
            'filename' => $filename,
            'extension' => $extension,
            'title' => $title,
            'content' => $content,
            'html' => null,
            'language' => match ($extension) {
                'yaml' => 'yaml',
                'php' => 'php',
                default => 'text',
            },
        ];
    }

    public function rawDownload(string $path): ?BinaryFileResponse
    {
        $filename = $this->normalizeFilename($path);
        if ($filename === null) {
            return null;
        }

        $absolute = $this->absolutePath($filename);
        if ($absolute === null || ! is_file($absolute)) {
            return null;
        }

        return response()->download($absolute, basename($filename), [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function normalizeFilename(string $path): ?string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = trim($path, '/');

        if ($path === '' || strtolower($path) === 'readme') {
            $path = 'README.md';
        } elseif (! str_contains($path, '.')) {
            $path .= '.md';
        }

        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            return null;
        }

        return $path;
    }

    private function absolutePath(string $filename): ?string
    {
        $root = realpath(base_path(self::ROOT));
        if ($root === false) {
            return null;
        }

        $candidate = base_path(self::ROOT.'/'.$filename);
        $resolved = realpath($candidate);
        if ($resolved === false || ! str_starts_with($resolved, $root.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $resolved;
    }

    private function publicPath(string $filename): string
    {
        if (preg_match('/\.md$/i', $filename) === 1) {
            $path = preg_replace('/\.md$/i', '', $filename) ?? $filename;

            return $path === 'README' ? 'README' : $path;
        }

        return $filename;
    }

    private function titleFromContent(string $content, string $filename): string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches) === 1) {
            return trim($matches[1]);
        }

        return basename($filename);
    }

    private function rewriteMarkdownLinks(string $content): string
    {
        return (string) preg_replace_callback(
            '/\]\(([^)]+)\)/',
            function (array $matches): string {
                $target = $matches[1];
                if ($target === '' || str_contains($target, '://') || str_starts_with($target, '#')) {
                    return ']('.$target.')';
                }

                [$linkPath, $fragment] = array_pad(explode('#', $target, 2), 2, null);
                if ($linkPath === null || $linkPath === '') {
                    return ']('.$target.')';
                }

                $linkPath = $this->resolveRelativeDocPath($linkPath);

                if (! str_ends_with(strtolower($linkPath), '.md') && ! str_contains($linkPath, '.')) {
                    $linkPath .= '.md';
                }

                $publicPath = $this->publicPath($linkPath);
                $url = $publicPath === 'README'
                    ? route('developers.integrations.index')
                    : route('developers.integrations.show', ['path' => $publicPath]);

                return ']('.$url.($fragment ? '#'.$fragment : '').')';
            },
            $content
        );
    }

    private function resolveRelativeDocPath(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        while (str_starts_with($path, './')) {
            $path = substr($path, 2);
        }

        while (str_starts_with($path, '../')) {
            $path = substr($path, 3);
        }

        return $path;
    }
}
