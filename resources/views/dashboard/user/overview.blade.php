@extends('layouts.dashboard-user')

@section('title', 'Dashboard')

@section('content')
<section data-purpose="welcome-section">
    <h1 class="text-3xl font-bold text-white">Welcome back, {{ auth()->user()->name ?? 'User' }}</h1>
    <p class="text-slate-400 mt-1">Here's what's happening with your account today.</p>
</section>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" data-purpose="stats-widgets">
    <div class="glass-card rounded-2xl p-6 bg-gradient-to-br from-primary/20 to-transparent">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-400 text-sm font-medium">Total Balance</span>
            <div class="p-2 bg-primary/10 rounded-lg text-primary">
                <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-white mb-1">${{ number_format($balanceUsd ?? 0, 2) }}</div>
        <div class="text-xs text-green-400 flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">trending_up</span>
            <span>{{ $balanceChangeLabel ?? '+0% from last month' }}</span>
        </div>
    </div>
    <div class="glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-400 text-sm font-medium">Crypto Assets</span>
            <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                <span class="material-symbols-outlined text-xl">show_chart</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-white mb-1">{{ number_format($cryptoBtc ?? 0, 2) }} BTC</div>
        <div class="text-xs text-slate-400">≈ ${{ number_format(($cryptoBtc ?? 0) * 45800, 2) }} USD</div>
    </div>
    <div class="glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-400 text-sm font-medium">Active Orders</span>
            <div class="p-2 bg-yellow-500/10 rounded-lg text-yellow-500">
                <span class="material-symbols-outlined text-xl">shopping_bag</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-white mb-1">{{ $activeOrdersCount ?? 0 }}</div>
        <div class="text-xs text-slate-400">{{ $ordersAwaitingLabel ?? 'All caught up' }}</div>
    </div>
    <div class="glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <span class="text-slate-400 text-sm font-medium">New Messages</span>
            <div class="p-2 bg-purple-500/10 rounded-lg text-purple-500">
                <span class="material-symbols-outlined text-xl">chat_bubble</span>
            </div>
        </div>
        <div class="text-2xl font-bold text-white mb-1">{{ $messagesCount ?? 0 }}</div>
        <div class="text-xs text-purple-400 font-medium">Check inbox</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 glass-card rounded-3xl p-8 flex flex-col" data-purpose="activity-chart">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-xl font-bold text-white">Trading Activity</h2>
                <p class="text-slate-400 text-sm">Real-time market analysis</p>
            </div>
            <select class="bg-slate-800 border-none text-slate-300 text-sm rounded-lg focus:ring-primary">
                <option>Last 7 Days</option>
                <option>Last 30 Days</option>
                <option>Last 6 Months</option>
            </select>
        </div>
        <div class="flex-1 w-full min-h-[300px] relative">
            <canvas class="w-full h-full" id="activityChart"></canvas>
        </div>
    </div>
    <div class="glass-card rounded-3xl p-8" data-purpose="recommended-services">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white">Recommended</h2>
            <a href="{{ route('marketplace') }}" class="text-primary text-sm hover:underline">View All</a>
        </div>
        <div class="space-y-4">
            @forelse($recommendedListings ?? collect() as $listing)
                <a href="{{ route('marketplace') }}" class="block p-4 bg-slate-800/40 rounded-2xl hover:bg-slate-800/60 transition-colors cursor-pointer border border-transparent hover:border-slate-700">
                    <div class="flex items-start gap-4">
                        @php
                            $iconMap = ['code' => 'code', 'image' => 'image', 'document' => 'description'];
                            $icon = $iconMap[$listing->category ?? ''] ?? 'store';
                            $colorMap = ['code' => 'bg-blue-600/20 text-blue-500', 'image' => 'bg-pink-600/20 text-pink-500', 'document' => 'bg-green-600/20 text-green-500'];
                            $colorClass = $colorMap[$listing->category ?? ''] ?? 'bg-slate-600/20 text-slate-400';
                        @endphp
                        <div class="w-12 h-12 {{ $colorClass }} rounded-xl flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">{{ $icon }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-white">{{ $listing->title }}</h3>
                            <p class="text-xs text-slate-400 mt-1 line-clamp-1">{{ $listing->description ?? '' }}</p>
                            <div class="mt-2 text-primary font-bold text-sm">${{ number_format($listing->price, 2) }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-slate-400 text-sm">No recommended listings yet. Run <code class="text-slate-500">php artisan db:seed</code> to load demo data.</p>
            @endforelse
        </div>
    </div>
</div>

<section class="glass-card rounded-3xl overflow-hidden" data-purpose="recent-orders-table">
    <div class="p-8 border-b border-slate-800 flex items-center justify-between">
        <h2 class="text-xl font-bold text-white">Recent Transactions</h2>
        <button class="bg-slate-800 hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Download CSV</button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-900/50">
                <tr>
                    <th class="px-8 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Transaction ID</th>
                    <th class="px-8 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Asset / Service</th>
                    <th class="px-8 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
                    <th class="px-8 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Amount</th>
                    <th class="px-8 py-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($transactions ?? [] as $tx)
                    @php
                        $statusClass = match($tx->status) {
                            'completed' => 'bg-green-500/10 text-green-500',
                            'processing' => 'bg-yellow-500/10 text-yellow-500',
                            'cancelled', 'failed' => 'bg-red-500/10 text-red-500',
                            default => 'bg-slate-500/10 text-slate-400',
                        };
                        $icon = str_contains(strtolower($tx->label ?? ''), 'bitcoin') || ($tx->asset_type === 'BTC') ? 'payments' : 'dns';
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-8 py-4 text-sm font-medium text-slate-300">#{{ $tx->reference }}</td>
                        <td class="px-8 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-orange-500/20 rounded-full flex items-center justify-center text-orange-500">
                                    <span class="material-symbols-outlined text-sm">{{ $icon }}</span>
                                </div>
                                <span class="text-sm text-white">{{ $tx->label }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-4 text-sm text-slate-400">{{ $tx->created_at->format('M j, Y, H:i') }}</td>
                        <td class="px-8 py-4 text-sm font-bold text-white">{{ $tx->currency === 'USD' ? '$' . number_format($tx->amount, 2) : number_format($tx->amount, 4) . ' ' . ($tx->asset_type ?? $tx->currency) }}</td>
                        <td class="px-8 py-4">
                            <span class="px-3 py-1 {{ $statusClass }} rounded-full text-xs font-medium">{{ ucfirst($tx->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-8 text-center text-slate-400 text-sm">No transactions yet. Run <code class="text-slate-500">php artisan db:seed</code> to load demo data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('activityChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    function resizeCanvas() {
        canvas.width = canvas.parentElement.clientWidth;
        canvas.height = canvas.parentElement.clientHeight;
        drawChart();
    }
    function drawChart() {
        const w = canvas.width, h = canvas.height, padding = 40;
        ctx.clearRect(0, 0, w, h);
        const points = [h-100, h-120, h-80, h-180, h-140, h-220, h-200];
        const stepX = (w - (padding * 2)) / (points.length - 1);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
        ctx.lineWidth = 1;
        for (let i = 0; i < 5; i++) {
            const y = padding + (i * (h - padding * 2) / 4);
            ctx.beginPath();
            ctx.moveTo(padding, y);
            ctx.lineTo(w - padding, y);
            ctx.stroke();
        }
        ctx.beginPath();
        ctx.moveTo(padding, points[0]);
        ctx.lineWidth = 4;
        ctx.strokeStyle = '#16a34a';
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        for (let i = 1; i < points.length; i++) ctx.lineTo(padding + (i * stepX), points[i]);
        ctx.stroke();
        const gradient = ctx.createLinearGradient(0, 0, 0, h);
        gradient.addColorStop(0, 'rgba(22, 163, 74, 0.3)');
        gradient.addColorStop(1, 'rgba(22, 163, 74, 0)');
        ctx.lineTo(padding + ((points.length - 1) * stepX), h - padding);
        ctx.lineTo(padding, h - padding);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();
        points.forEach((p, i) => {
            ctx.beginPath();
            ctx.arc(padding + (i * stepX), p, 5, 0, Math.PI * 2);
            ctx.fillStyle = '#16a34a';
            ctx.fill();
            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 2;
            ctx.stroke();
        });
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
});
</script>
@endpush
@endsection
