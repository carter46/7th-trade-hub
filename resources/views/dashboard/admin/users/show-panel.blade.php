<div class="space-y-4">
    @if ($activeTab === 'overview')
        <div class="grid gap-4 md:grid-cols-3">
            <x-dashboard.stats-card label="Orders" :value="number_format($orderCount ?? 0)" icon="orders" />
            <x-dashboard.stats-card label="Listings" :value="number_format($listingCount ?? 0)" icon="listings" />
            <x-dashboard.stats-card label="Tickets" :value="number_format($ticketCount ?? 0)" icon="support" />
        </div>
        <x-dashboard.card>
            <h3 class="mb-3 text-sm font-semibold text-text-primary">Wallet</h3>
            <p class="text-text-secondary">
                @if ($wallet ?? null)
                    Balance: ₦{{ number_format($wallet->balance ?? 0, 2) }}
                @else
                    No wallet provisioned.
                @endif
            </p>
        </x-dashboard.card>
        <x-dashboard.card>
            <h3 class="mb-3 text-sm font-semibold text-text-primary">Recent transactions</h3>
            @forelse (($recentTransactions ?? collect()) as $tx)
                <div class="flex items-center justify-between border-b border-border-default py-2 text-sm last:border-0">
                    <span class="font-mono text-xs">{{ $tx->reference ?? $tx->id }}</span>
                    <span>{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</span>
                    <x-dashboard.badge :status="$tx->status" />
                </div>
            @empty
                <p class="text-sm text-text-muted">No transactions.</p>
            @endforelse
        </x-dashboard.card>
    @elseif ($activeTab === 'wallet')
        <x-dashboard.card>
            @if ($wallet ?? null)
                <dl class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-text-muted">Balance</dt>
                        <dd class="text-lg font-semibold text-text-primary">₦{{ number_format($wallet->balance ?? 0, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-text-muted">Status</dt>
                        <dd class="text-text-primary">{{ $wallet->status ?? 'active' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-text-muted">No wallet.</p>
            @endif
        </x-dashboard.card>
    @elseif ($activeTab === 'transactions')
        <x-dashboard.table :empty="($transactions ?? collect())->isEmpty()" empty-title="No transactions" striped>
            <x-slot:head>
                <x-dashboard.th>Reference</x-dashboard.th>
                <x-dashboard.th>Amount</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
                <x-dashboard.th>Date</x-dashboard.th>
            </x-slot:head>
            @foreach ($transactions as $tx)
                <tr>
                    <x-dashboard.td class="font-mono text-xs">{{ $tx->reference ?? $tx->id }}</x-dashboard.td>
                    <x-dashboard.td>{{ number_format($tx->amount, 2) }} {{ $tx->currency }}</x-dashboard.td>
                    <x-dashboard.td><x-dashboard.badge :status="$tx->status" /></x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $tx->created_at->format('j M Y') }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
        <x-dashboard.pagination :paginator="$transactions" />
    @elseif ($activeTab === 'orders')
        <x-dashboard.table :empty="($orders ?? collect())->isEmpty()" empty-title="No orders" striped>
            <x-slot:head>
                <x-dashboard.th>ID</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
                <x-dashboard.th>Date</x-dashboard.th>
            </x-slot:head>
            @foreach ($orders as $order)
                <tr>
                    <x-dashboard.td>#{{ $order->id }}</x-dashboard.td>
                    <x-dashboard.td><x-dashboard.badge :status="$order->status" /></x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $order->created_at->format('j M Y') }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
        <x-dashboard.pagination :paginator="$orders" />
    @elseif ($activeTab === 'tools')
        @if (session('fresh_tool_credentials'))
            @php $creds = session('fresh_tool_credentials'); @endphp
            <x-dashboard.card class="border-primary/40 bg-primary/5">
                <h3 class="text-sm font-semibold text-text-primary">Provisioning credentials (copy now)</h3>
                <p class="mt-1 text-xs text-text-muted">These secrets are shown once. Give them to the site developer for config.php / .env.</p>
                <dl class="mt-3 space-y-2 font-mono text-xs">
                    <div><dt class="text-text-muted">Integration ID</dt><dd>{{ $creds['integration_id'] }}</dd></div>
                    <div><dt class="text-text-muted">Client ID</dt><dd>{{ $creds['client_id'] }}</dd></div>
                    <div><dt class="text-text-muted">Client Secret</dt><dd>{{ $creds['client_secret'] }}</dd></div>
                    <div><dt class="text-text-muted">Webhook Secret</dt><dd>{{ $creds['webhook_secret'] }}</dd></div>
                    <div><dt class="text-text-muted">Webhook URL</dt><dd>{{ $creds['webhook_url'] }}</dd></div>
                </dl>
                @if (isset($creds['connection_message']))
                    <p class="mt-3 text-sm {{ ($creds['connection_ok'] ?? false) ? 'text-emerald-600' : 'text-red-600' }}">{{ $creds['connection_message'] }}</p>
                @endif
            </x-dashboard.card>
        @endif
        <x-dashboard.table :empty="($tools ?? collect())->isEmpty()" empty-title="No tools" empty-description="Purchased website packages appear here after checkout." striped>
            <x-slot:head>
                <x-dashboard.th>Tool</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
                <x-dashboard.th>Expires</x-dashboard.th>
                <x-dashboard.th>Actions</x-dashboard.th>
            </x-slot:head>
            @foreach (($tools ?? collect()) as $tool)
                <tr>
                    <x-dashboard.td>
                        <div class="font-medium text-text-primary">{{ $tool->resolvedDisplayName() }}</div>
                        <div class="text-xs text-text-muted">#{{ $tool->id }} · {{ $tool->public_id }}</div>
                    </x-dashboard.td>
                    <x-dashboard.td>
                        <x-dashboard.badge :status="$tool->status->value" />
                        @if ($tool->isExpiringSoon())
                            <span class="ml-1 text-xs text-amber-600">Expiring soon</span>
                        @endif
                    </x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $tool->expires_at?->format('j M Y') ?? '—' }}</x-dashboard.td>
                    <x-dashboard.td class="space-x-2">
                        @php $isPendingSetup = $tool->status->value === 'pending_setup'; @endphp
                        <x-dashboard.button
                            type="button"
                            size="sm"
                            variant="secondary"
                            x-on:click="$dispatch('open-modal', 'setup-tool-{{ $tool->id }}')"
                        >
                            {{ $isPendingSetup ? 'Setup' : 'Reconfigure' }}
                        </x-dashboard.button>
                        @if ($tool->site_url)
                            <form method="POST" action="{{ route('admin.users.tools.check', [$user, $tool]) }}" class="inline">
                                @csrf
                                <x-dashboard.button type="submit" size="sm" variant="secondary">Check connection</x-dashboard.button>
                            </form>
                        @endif
                        <x-dashboard.modal name="setup-tool-{{ $tool->id }}" :show="false" maxWidth="lg">
                            @php
                                $toolFormAction = $isPendingSetup
                                    ? route('admin.users.tools.setup', [$user, $tool])
                                    : route('admin.users.tools.reconfigure', [$user, $tool]);
                            @endphp
                            <form method="POST" action="{{ $toolFormAction }}" class="space-y-4 p-1">
                                @csrf
                                <h3 class="text-lg font-semibold text-text-primary">
                                    {{ $isPendingSetup ? 'Setup' : 'Reconfigure' }} {{ $tool->resolvedDisplayName() }}
                                </h3>
                                <x-dashboard.input name="site_url" label="Website URL" type="url" :value="old('site_url', $tool->site_url)" required />
                                <x-dashboard.input name="admin_login_url" label="Admin login URL" type="url" :value="old('admin_login_url', $tool->admin_login_url)" required />
                                <x-dashboard.input name="admin_email" label="Admin email" type="email" :value="old('admin_email', $tool->admin_email)" required />
                                <x-dashboard.input
                                    name="admin_password"
                                    label="Admin password"
                                    type="text"
                                    :required="$isPendingSetup"
                                    autocomplete="off"
                                />
                                @if ($isPendingSetup)
                                    <p class="text-xs text-text-muted">Saving starts the subscription clock and generates unique provisioning API keys for this customer site (never demo credentials).</p>
                                @else
                                    <p class="text-xs text-text-muted">Reconfigure updates URLs and admin identity. It does <strong>not</strong> extend the paid subscription. Leave password blank to keep the current one.</p>
                                @endif
                                <div class="flex justify-end gap-2">
                                    <x-dashboard.button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'setup-tool-{{ $tool->id }}')">Cancel</x-dashboard.button>
                                    <x-dashboard.button type="submit">{{ $isPendingSetup ? 'Save & generate keys' : 'Save reconfiguration' }}</x-dashboard.button>
                                </div>
                            </form>
                        </x-dashboard.modal>
                        @if (! $isPendingSetup && $tool->integration)
                            <form method="POST" action="{{ route('admin.users.tools.rotate', [$user, $tool]) }}" class="inline" onsubmit="return confirm('Rotate API credentials? The merchant site env must be updated.');">
                                @csrf
                                <x-dashboard.button type="submit" size="sm" variant="secondary">Rotate keys</x-dashboard.button>
                            </form>
                        @endif
                    </x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
        @if (($tools ?? null) instanceof \Illuminate\Contracts\Pagination\Paginator)
            <x-dashboard.pagination :paginator="$tools" />
        @endif
    @elseif ($activeTab === 'listings')
        <x-dashboard.table :empty="($listings ?? collect())->isEmpty()" empty-title="No listings" striped>
            <x-slot:head>
                <x-dashboard.th>Title</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
                <x-dashboard.th>Date</x-dashboard.th>
            </x-slot:head>
            @foreach ($listings as $listing)
                <tr>
                    <x-dashboard.td>{{ $listing->title ?? ('Listing #'.$listing->id) }}</x-dashboard.td>
                    <x-dashboard.td><x-dashboard.badge :status="$listing->status ?? ($listing->is_active ? 'active' : 'inactive')" /></x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $listing->created_at->format('j M Y') }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
        <x-dashboard.pagination :paginator="$listings" />
    @elseif ($activeTab === 'escrows')
        <x-dashboard.table :empty="($escrows ?? collect())->isEmpty()" empty-title="No escrows" striped>
            <x-slot:head>
                <x-dashboard.th>ID</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
                <x-dashboard.th>Date</x-dashboard.th>
            </x-slot:head>
            @foreach ($escrows as $escrow)
                <tr>
                    <x-dashboard.td>#{{ $escrow->id }}</x-dashboard.td>
                    <x-dashboard.td><x-dashboard.badge :status="$escrow->status" /></x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $escrow->created_at->format('j M Y') }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
        <x-dashboard.pagination :paginator="$escrows" />
    @elseif ($activeTab === 'tickets')
        <x-dashboard.table :empty="($tickets ?? collect())->isEmpty()" empty-title="No tickets" striped>
            <x-slot:head>
                <x-dashboard.th>Subject</x-dashboard.th>
                <x-dashboard.th>Status</x-dashboard.th>
                <x-dashboard.th>Date</x-dashboard.th>
            </x-slot:head>
            @foreach ($tickets as $ticket)
                <tr>
                    <x-dashboard.td>{{ $ticket->subject ?? ('Ticket #'.$ticket->id) }}</x-dashboard.td>
                    <x-dashboard.td><x-dashboard.badge :status="$ticket->status" /></x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $ticket->created_at->format('j M Y') }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
        <x-dashboard.pagination :paginator="$tickets" />
    @elseif ($activeTab === 'activity')
        <x-dashboard.table :empty="($activity ?? collect())->isEmpty()" empty-title="No activity" striped>
            <x-slot:head>
                <x-dashboard.th>Action</x-dashboard.th>
                <x-dashboard.th>Date</x-dashboard.th>
            </x-slot:head>
            @foreach ($activity as $row)
                <tr>
                    <x-dashboard.td>{{ $row->action ?? $row->event ?? '—' }}</x-dashboard.td>
                    <x-dashboard.td class="text-xs text-text-muted">{{ $row->created_at->format('j M Y H:i') }}</x-dashboard.td>
                </tr>
            @endforeach
        </x-dashboard.table>
        <x-dashboard.pagination :paginator="$activity" />
    @elseif ($activeTab === 'security')
        <x-dashboard.card class="space-y-2 text-sm text-text-secondary">
            <p>Suspended: {{ $user->is_suspended ? 'Yes' : 'No' }}</p>
            <p>Anonymized: {{ $user->anonymized_at ? $user->anonymized_at->format('j M Y H:i') : 'No' }}</p>
            <p>Email verified: {{ $user->email_verified_at ? 'Yes' : 'No' }}</p>
            <p>KYC level: {{ $user->kyc_level }}</p>
        </x-dashboard.card>
    @endif
</div>
