<?php

namespace App\Services\Analytics\Providers;

use App\Contracts\Analytics\HeatmapProviderInterface;
use App\Models\AnalyticsProvider;

class MicrosoftClarityProvider implements HeatmapProviderInterface
{
    public function isEnabled(): bool
    {
        return (bool) $this->config()?->enabled
            && filled($this->projectId());
    }

    public function script(): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $projectId = e($this->projectId());

        return <<<HTML
<script type="text/javascript">
(function(c,l,a,r,i,t,y){
    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
})(window, document, "clarity", "script", "{$projectId}");
</script>
HTML;
    }

    /**
     * @return array{ok: bool, message: string, details?: array<string, mixed>}
     */
    public function connectionTest(): array
    {
        return $this->connectionTestFromInput([
            'project_id' => $this->projectId(),
        ]);
    }

    /**
     * @param  array{project_id?: string|null}  $input
     * @return array{ok: bool, message: string, details?: array<string, mixed>}
     */
    public function connectionTestFromInput(array $input): array
    {
        $projectId = $input['project_id'] ?? null;

        if (blank($projectId)) {
            return ['ok' => false, 'message' => 'Project ID is required.'];
        }

        if (! preg_match('/^[a-z0-9]+$/i', (string) $projectId)) {
            return ['ok' => false, 'message' => 'Project ID must be alphanumeric.'];
        }

        return [
            'ok' => true,
            'message' => 'Project ID format is valid.',
            'details' => ['project_id' => $projectId],
        ];
    }

    private function config(): AnalyticsProvider
    {
        return AnalyticsProvider::forProvider(AnalyticsProvider::PROVIDER_MICROSOFT_CLARITY);
    }

    private function projectId(): ?string
    {
        $id = $this->config()->credential('project_id');

        return is_string($id) && $id !== '' ? $id : null;
    }
}
