<?php

namespace App\Services\Tracking\Providers;

use App\Models\IntegrationProvider;

class GoogleTagManagerProvider
{
    public function isEnabled(): bool
    {
        return (bool) $this->config()->enabled
            && filled($this->containerId());
    }

    public function headScript(): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $id = e($this->containerId());

        return <<<HTML
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$id}');</script>
HTML;
    }

    public function bodyNoscript(): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $id = e($this->containerId());

        return <<<HTML
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$id}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
HTML;
    }

    /**
     * @param  array{container_id?: string|null}  $input
     * @return array{ok: bool, message: string, details?: array<string, mixed>}
     */
    public function connectionTestFromInput(array $input): array
    {
        $containerId = trim((string) ($input['container_id'] ?? ''));

        if ($containerId === '') {
            return ['ok' => false, 'message' => 'Container ID is required.'];
        }

        if (! preg_match('/^GTM-[A-Z0-9]+$/i', $containerId)) {
            return ['ok' => false, 'message' => 'Container ID must match the GTM-XXXXXXX format.'];
        }

        return [
            'ok' => true,
            'message' => 'Container ID format is valid.',
            'details' => ['container_id' => strtoupper($containerId)],
        ];
    }

    public function containerId(): ?string
    {
        $id = $this->config()->credential('container_id');

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function config(): IntegrationProvider
    {
        return IntegrationProvider::forProvider(IntegrationProvider::GOOGLE_TAG_MANAGER);
    }
}
