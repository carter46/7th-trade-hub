<x-dashboard.table
    :empty="$domains->isEmpty()"
    empty-title="No domains yet"
    empty-description="Registered and connected domains for your websites appear here."
    empty-icon="listings"
    striped
>
    <x-slot:head>
        <x-dashboard.th>Domain</x-dashboard.th>
        <x-dashboard.th>Status</x-dashboard.th>
        <x-dashboard.th>Nameservers</x-dashboard.th>
        <x-dashboard.th></x-dashboard.th>
    </x-slot:head>
    @foreach ($domains as $domain)
        @php
            $ns = $domain->nameservers ?? [];
        @endphp
        <tr>
            <x-dashboard.td class="font-medium text-text-primary">
                <span>{{ $domain->fqdn }}</span>
                @if(($domain->kind ?? '') === 'connection')
                    <span class="mt-0.5 block text-xs font-normal text-text-muted">Connected domain</span>
                @endif
            </x-dashboard.td>
            <x-dashboard.td>
                <x-dashboard.badge :status="$domain->status" />
            </x-dashboard.td>
            <x-dashboard.td class="text-xs text-text-secondary">
                @if ($ns !== [])
                    @foreach (array_slice($ns, 0, 2) as $host)
                        <div>{{ $host }}</div>
                    @endforeach
                    @if (count($ns) > 2)
                        <div class="text-text-muted">+{{ count($ns) - 2 }} more</div>
                    @endif
                @else
                    <span class="text-text-muted">Not synced yet</span>
                @endif
            </x-dashboard.td>
            <x-dashboard.td>
                <x-dashboard.button :href="$domain->manage_url" size="sm">Manage</x-dashboard.button>
            </x-dashboard.td>
        </tr>
    @endforeach
</x-dashboard.table>
