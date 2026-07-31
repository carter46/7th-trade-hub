<?php

namespace App\Services\Communications\LiveChat;

use App\Models\IntegrationProvider;
use App\Models\SystemSetting;

class LiveChatManager
{
    /**
     * @return array{provider: string, enabled: bool, label: string, credentials: array<string, mixed>, key_set: bool}
     */
    public function resolved(): array
    {
        $active = $this->activeProvider();

        return [
            'provider' => $active['provider'],
            'enabled' => $active['enabled'],
            'label' => match ($active['provider']) {
                'smartsupp' => 'Smartsupp',
                'jivo' => 'JivoChat',
                'chatway' => 'Chatway',
                default => 'Live chat',
            },
            'credentials' => $active['credentials'],
            'key_set' => $active['key_set'],
        ];
    }

    /**
     * Read from integration_providers only (encrypted credentials).
     * One-time migrate from legacy SystemSetting plaintext keys if IP empty.
     *
     * @return array{provider: string, enabled: bool, credentials: array<string, mixed>, key_set: bool}
     */
    private function activeProvider(): array
    {
        $this->migrateLegacySecretsOnce();

        foreach ([IntegrationProvider::SMARTSUPP, IntegrationProvider::JIVO, IntegrationProvider::CHATWAY] as $name) {
            $row = IntegrationProvider::forProvider($name);
            if (! $row->enabled) {
                continue;
            }
            $creds = $row->credentials ?? [];
            if ($name === IntegrationProvider::SMARTSUPP && filled($creds['key'] ?? null)) {
                return ['provider' => 'smartsupp', 'enabled' => true, 'credentials' => $creds, 'key_set' => true];
            }
            if ($name === IntegrationProvider::JIVO && filled($creds['widget_id'] ?? null)) {
                return ['provider' => 'jivo', 'enabled' => true, 'credentials' => $creds, 'key_set' => true];
            }
            if ($name === IntegrationProvider::CHATWAY && filled($creds['widget_id'] ?? null)) {
                return ['provider' => 'chatway', 'enabled' => true, 'credentials' => $creds, 'key_set' => true];
            }
        }

        return ['provider' => 'none', 'enabled' => false, 'credentials' => [], 'key_set' => false];
    }

    /**
     * Persist live chat to integration_providers only.
     * Clears legacy plaintext SystemSetting secret keys.
     */
    public function save(string $provider, ?string $smartsuppKey, ?string $jivoWidgetId, ?string $chatwayWidgetId = null): void
    {
        foreach ([IntegrationProvider::SMARTSUPP, IntegrationProvider::JIVO, IntegrationProvider::CHATWAY] as $name) {
            $row = IntegrationProvider::forProvider($name);
            $row->enabled = false;
            $row->save();
        }

        if ($provider === 'smartsupp') {
            $row = IntegrationProvider::forProvider(IntegrationProvider::SMARTSUPP);
            $existing = (string) ($row->credential('key') ?? '');
            $key = filled($smartsuppKey) ? (string) $smartsuppKey : $existing;
            $row->enabled = filled($key);
            if (filled($smartsuppKey)) {
                $row->mergeCredentials(['key' => $key]);
            }
            $row->status = $row->enabled ? 'connected' : 'idle';
            $row->save();
        } elseif ($provider === 'jivo') {
            $row = IntegrationProvider::forProvider(IntegrationProvider::JIVO);
            $existing = (string) ($row->credential('widget_id') ?? '');
            $id = filled($jivoWidgetId) ? (string) $jivoWidgetId : $existing;
            $row->enabled = filled($id);
            if (filled($jivoWidgetId)) {
                $row->mergeCredentials(['widget_id' => $id]);
            }
            $row->status = $row->enabled ? 'connected' : 'idle';
            $row->save();
        } elseif ($provider === 'chatway') {
            $row = IntegrationProvider::forProvider(IntegrationProvider::CHATWAY);
            $existing = (string) ($row->credential('widget_id') ?? '');
            $id = filled($chatwayWidgetId) ? (string) $chatwayWidgetId : $existing;
            $row->enabled = filled($id);
            if (filled($chatwayWidgetId)) {
                $row->mergeCredentials(['widget_id' => $id]);
            }
            $row->status = $row->enabled ? 'connected' : 'idle';
            $row->save();
        }

        // Provider name only (not secrets) for ops visibility.
        SystemSetting::set('live_chat_provider', $provider);
        // Purge legacy plaintext secrets.
        SystemSetting::set('smartsupp_key', '');
        SystemSetting::set('jivo_widget_id', '');
    }

    /**
     * Import plaintext SystemSetting chat secrets into IP once, then clear them.
     */
    private function migrateLegacySecretsOnce(): void
    {
        $smartsuppKey = trim((string) SystemSetting::get('smartsupp_key', ''));
        $jivoId = trim((string) SystemSetting::get('jivo_widget_id', ''));
        $legacyProvider = strtolower(trim((string) SystemSetting::get('live_chat_provider', 'none')));

        if ($smartsuppKey === '' && $jivoId === '') {
            return;
        }

        if ($smartsuppKey !== '') {
            $row = IntegrationProvider::forProvider(IntegrationProvider::SMARTSUPP);
            if (! filled($row->credential('key'))) {
                $row->mergeCredentials(['key' => $smartsuppKey]);
                $row->enabled = $legacyProvider === 'smartsupp' || $row->enabled;
                $row->status = $row->enabled ? 'connected' : 'idle';
                $row->save();
            }
        }

        if ($jivoId !== '') {
            $row = IntegrationProvider::forProvider(IntegrationProvider::JIVO);
            if (! filled($row->credential('widget_id'))) {
                $row->mergeCredentials(['widget_id' => $jivoId]);
                $row->enabled = $legacyProvider === 'jivo' || $row->enabled;
                $row->status = $row->enabled ? 'connected' : 'idle';
                $row->save();
            }
        }

        SystemSetting::set('smartsupp_key', '');
        SystemSetting::set('jivo_widget_id', '');
    }
}
