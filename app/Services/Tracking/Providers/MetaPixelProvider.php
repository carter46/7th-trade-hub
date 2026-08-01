<?php

namespace App\Services\Tracking\Providers;

use App\Models\IntegrationProvider;

class MetaPixelProvider
{
    public function isEnabled(): bool
    {
        return (bool) $this->config()->enabled
            && filled($this->pixelId());
    }

    public function headScript(): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $id = e($this->pixelId());

        return <<<HTML
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$id}');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={$id}&ev=PageView&noscript=1"
/></noscript>
HTML;
    }

    /**
     * @param  array{pixel_id?: string|null}  $input
     * @return array{ok: bool, message: string, details?: array<string, mixed>}
     */
    public function connectionTestFromInput(array $input): array
    {
        $pixelId = trim((string) ($input['pixel_id'] ?? ''));

        if ($pixelId === '') {
            return ['ok' => false, 'message' => 'Pixel ID is required.'];
        }

        if (! preg_match('/^\d{5,20}$/', $pixelId)) {
            return ['ok' => false, 'message' => 'Pixel ID must be 5–20 digits.'];
        }

        return [
            'ok' => true,
            'message' => 'Pixel ID format is valid.',
            'details' => ['pixel_id' => $pixelId],
        ];
    }

    public function pixelId(): ?string
    {
        $id = $this->config()->credential('pixel_id');

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function config(): IntegrationProvider
    {
        return IntegrationProvider::forProvider(IntegrationProvider::META_PIXEL);
    }
}
