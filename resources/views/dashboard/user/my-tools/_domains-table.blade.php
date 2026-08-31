<x-dashboard.table
    :empty="$domains->isEmpty()"
    empty-title="No domains yet"
    empty-description="When you register a domain through our catalog, it appears here."
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
            $ns = $domain->nameserverList();
        @endphp
        <tr>
            <x-dashboard.td class="font-medium text-text-primary">{{ $domain->fqdn }}</x-dashboard.td>
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
                <x-dashboard.button :href="route('dashboard.my-domains.show', $domain)" size="sm">Manage</x-dashboard.button>
            </x-dashboard.td>
        </tr>
    @endforeach
</x-dashboard.table>
<x-dashboard.pagination :paginator="$domains" />
