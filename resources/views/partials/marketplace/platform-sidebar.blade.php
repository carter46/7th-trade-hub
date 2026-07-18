@php
    $sidebarLinks = [
        ['label' => 'Our Services', 'href' => route('services'), 'desc' => 'Browse all platform categories'],
        ['label' => 'Website Templates', 'href' => route('website-listings'), 'desc' => 'Ready-to-launch designs'],
        ['label' => 'Document Templates', 'href' => route('templates'), 'desc' => 'Contracts, HR & legal docs'],
        ['label' => 'Website Packages', 'href' => route('services.segment', 'website_package'), 'desc' => 'Hosted packages with demos'],
        ['label' => 'Escrow Service', 'href' => route('services.segment', 'escrow_service'), 'desc' => 'Protected digital deals'],
        ['label' => 'VPN Services', 'href' => route('services.segment', 'vpn'), 'desc' => 'Residential & business plans'],
        ['label' => 'Email Services', 'href' => route('services.segment', 'email'), 'desc' => 'Business mailboxes'],
        ['label' => 'Virtual Numbers', 'href' => route('services.segment', 'virtual_phone'), 'desc' => 'Local & international lines'],
        ['label' => 'Domains', 'href' => route('services.segment', 'domain'), 'desc' => 'Register or transfer'],
    ];
@endphp
<aside class="space-y-4">
    <div class="rounded-xl border border-white/10 bg-slate-900/60 overflow-hidden">
        <div class="px-4 py-3 border-b border-white/10">
            <h2 class="text-sm font-bold font-display text-white uppercase tracking-wide">Explore the platform</h2>
            <p class="text-xs text-slate-400 mt-0.5">Platform services sold by 7th Trade Hub</p>
        </div>
        <ul class="divide-y divide-white/5">
            @foreach($sidebarLinks as $link)
                <li>
                    <a href="{{ $link['href'] }}" class="flex items-start gap-3 px-4 py-3 hover:bg-white/5 transition-colors group">
                        <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-accent group-hover:scale-110 transition-transform"></span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-white group-hover:text-accent transition-colors">{{ $link['label'] }}</span>
                            <span class="block text-xs text-slate-500 mt-0.5">{{ $link['desc'] }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</aside>
