@extends('layouts.dashboard-admin')

@section('title', 'Listing: ' . $listing->title)

@section('content')
<x-layout.page
    :title="$listing->title"
    subtitle="Marketplace listing details"
    width="full"
    :breadcrumb="[
        ['Admin', route('admin')],
        ['Listings', route('admin.listings')],
        [$listing->title, null],
    ]"
>
    <x-slot:actions>
        <div class="flex items-center gap-2">
            @if($listing->status === 'published')
                <x-dashboard.button 
                    tag="a" 
                    href="{{ route('marketplace.show', $listing->slug) }}" 
                    variant="secondary"
                    target="_blank"
                >
                    View Public Page
                </x-dashboard.button>
            @endif
            
            @if(in_array($listing->status, ['pending_review', 'draft']))
                <form method="POST" action="{{ route('admin.listings.approve', $listing) }}" class="inline">
                    @csrf
                    <x-dashboard.button type="submit" variant="primary">
                        Approve & Publish
                    </x-dashboard.button>
                </form>
            @endif
        </div>
    </x-slot:actions>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <x-dashboard.card>
            <div class="text-sm text-text-muted mb-1">Status</div>
            @php
                $statusMap = [
                    'published' => ['label' => 'Published', 'status' => 'active'],
                    'pending_review' => ['label' => 'Pending Review', 'status' => 'warning'],
                    'draft' => ['label' => 'Draft', 'status' => 'default'],
                    'suspended' => ['label' => 'Suspended', 'status' => 'danger'],
                    'rejected' => ['label' => 'Rejected', 'status' => 'danger'],
                    'archived' => ['label' => 'Archived', 'status' => 'default'],
                ];
                $statusInfo = $statusMap[$listing->status] ?? ['label' => ucfirst($listing->status), 'status' => 'default'];
            @endphp
            <x-dashboard.badge :status="$statusInfo['status']" size="lg">
                {{ $statusInfo['label'] }}
            </x-dashboard.badge>
        </x-dashboard.card>

        <x-dashboard.card>
            <div class="text-sm text-text-muted mb-1">Price</div>
            <div class="text-2xl font-bold text-text-primary">₦{{ number_format($listing->price, 2) }}</div>
        </x-dashboard.card>

        <x-dashboard.card>
            <div class="text-sm text-text-muted mb-1">Orders</div>
            <div class="text-2xl font-bold text-text-primary">{{ $listing->orders->count() }}</div>
        </x-dashboard.card>

        <x-dashboard.card>
            <div class="text-sm text-text-muted mb-1">Reviews</div>
            <div class="text-2xl font-bold text-text-primary">{{ $listing->reviews->count() }}</div>
        </x-dashboard.card>
    </div>

    <div x-data="{ currentTab: '{{ $tab }}' }">
        <div class="border-b border-border-default mb-6">
            <nav class="-mb-px flex gap-6 overflow-x-auto" role="tablist">
                <a 
                    href="{{ route('admin.listings.show', ['listing' => $listing, 'tab' => 'overview']) }}"
                    @click.prevent="currentTab = 'overview'"
                    :class="currentTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border-default'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors whitespace-nowrap"
                >
                    Overview
                </a>
                <a 
                    href="{{ route('admin.listings.show', ['listing' => $listing, 'tab' => 'seller']) }}"
                    @click.prevent="currentTab = 'seller'"
                    :class="currentTab === 'seller' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border-default'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors whitespace-nowrap"
                >
                    Seller
                </a>
                <a 
                    href="{{ route('admin.listings.show', ['listing' => $listing, 'tab' => 'moderation']) }}"
                    @click.prevent="currentTab = 'moderation'"
                    :class="currentTab === 'moderation' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border-default'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors whitespace-nowrap"
                >
                    Moderation
                </a>
                <a 
                    href="{{ route('admin.listings.show', ['listing' => $listing, 'tab' => 'reviews']) }}"
                    @click.prevent="currentTab = 'reviews'"
                    :class="currentTab === 'reviews' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border-default'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors whitespace-nowrap"
                >
                    Reviews ({{ $listing->reviews->count() }})
                </a>
                <a 
                    href="{{ route('admin.listings.show', ['listing' => $listing, 'tab' => 'history']) }}"
                    @click.prevent="currentTab = 'history'"
                    :class="currentTab === 'history' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border-default'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors whitespace-nowrap"
                >
                    History
                </a>
                <a 
                    href="{{ route('admin.listings.show', ['listing' => $listing, 'tab' => 'audit']) }}"
                    @click.prevent="currentTab = 'audit'"
                    :class="currentTab === 'audit' ? 'border-primary text-primary' : 'border-transparent text-text-muted hover:text-text-primary hover:border-border-default'"
                    class="border-b-2 pb-3 text-sm font-medium transition-colors whitespace-nowrap"
                >
                    Audit Log
                </a>
            </nav>
        </div>

        <div x-show="currentTab === 'overview'">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <x-dashboard.card>
                        <h3 class="text-lg font-semibold mb-4">Listing Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm text-text-muted mb-1">Title</div>
                                <div class="text-text-primary font-medium">{{ $listing->title }}</div>
                            </div>

                            <div>
                                <div class="text-sm text-text-muted mb-1">Slug</div>
                                <div class="text-text-primary font-mono text-sm">{{ $listing->slug }}</div>
                            </div>

                            <div>
                                <div class="text-sm text-text-muted mb-1">Description</div>
                                <div class="text-text-primary prose prose-sm max-w-none">
                                    {!! nl2br(e($listing->description)) !!}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-text-muted mb-1">Category</div>
                                    <div class="text-text-primary">
                                        {{ $listing->marketplaceProduct?->category?->name ?? $listing->listingCategory?->name ?? '—' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm text-text-muted mb-1">Product</div>
                                    <div class="text-text-primary">
                                        {{ $listing->marketplaceProduct?->name ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-text-muted mb-1">Active</div>
                                    <x-dashboard.badge :status="$listing->is_active ? 'active' : 'default'">
                                        {{ $listing->is_active ? 'Yes' : 'No' }}
                                    </x-dashboard.badge>
                                </div>

                                <div>
                                    <div class="text-sm text-text-muted mb-1">Featured</div>
                                    <x-dashboard.badge :status="$listing->featured ? 'warning' : 'default'">
                                        {{ $listing->featured ? 'Yes' : 'No' }}
                                    </x-dashboard.badge>
                                </div>
                            </div>
                        </div>
                    </x-dashboard.card>
                </div>

                <div class="space-y-6">
                    <x-dashboard.card>
                        <h3 class="text-lg font-semibold mb-4">Metadata</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <div class="text-text-muted mb-1">Created</div>
                                <div class="text-text-primary">{{ $listing->created_at->format('M j, Y g:i A') }}</div>
                            </div>
                            <div>
                                <div class="text-text-muted mb-1">Last Updated</div>
                                <div class="text-text-primary">{{ $listing->updated_at->format('M j, Y g:i A') }}</div>
                            </div>
                            @if($listing->deleted_at)
                                <div>
                                    <div class="text-text-muted mb-1">Deleted</div>
                                    <div class="text-danger">{{ $listing->deleted_at->format('M j, Y g:i A') }}</div>
                                </div>
                            @endif
                        </div>
                    </x-dashboard.card>
                </div>
            </div>
        </div>

        <div x-show="currentTab === 'seller'">
            <x-dashboard.card>
                @if($listing->user)
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl font-bold">
                            {{ substr($listing->user->name, 0, 2) }}
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-text-primary">{{ $listing->user->name }}</h3>
                            <div class="text-sm text-text-muted mb-3">{{ $listing->user->email }}</div>
                            
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <div class="text-sm text-text-muted mb-1">Total Listings</div>
                                    <div class="text-text-primary font-medium">{{ $listing->user->listings()->count() }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-text-muted mb-1">Member Since</div>
                                    <div class="text-text-primary font-medium">{{ $listing->user->created_at->format('M Y') }}</div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-dashboard.button 
                                    tag="a" 
                                    href="{{ route('admin.users.show', $listing->user) }}" 
                                    variant="secondary"
                                >
                                    View User Profile
                                </x-dashboard.button>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-text-muted">No seller associated with this listing.</p>
                @endif
            </x-dashboard.card>
        </div>

        <div x-show="currentTab === 'moderation'">
            <div class="space-y-6">
                @if(in_array($listing->status, ['pending_review', 'draft']) || $listing->hasPendingVersion())
                    <x-dashboard.card>
                        <h3 class="text-lg font-semibold mb-4">Review Actions</h3>
                        @if($listing->status === 'published' && $listing->hasPendingVersion())
                            <p class="text-sm text-text-muted mb-4">This live listing has a pending revision awaiting review.</p>
                        @endif
                        
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.listings.approve', $listing) }}" class="flex-1">
                                @csrf
                                <x-dashboard.button type="submit" variant="primary" class="w-full">
                                    Approve & Publish
                                </x-dashboard.button>
                            </form>

                            <form method="POST" action="{{ route('admin.listings.reject', $listing) }}" class="flex-1" x-data="{ notes: '' }">
                                @csrf
                                <x-dashboard.button 
                                    type="button" 
                                    variant="danger" 
                                    class="w-full"
                                    @click="if(confirm('Add rejection notes?')) { notes = prompt('Rejection notes:'); if(notes !== null) $el.closest('form').submit(); }"
                                >
                                    Reject
                                </x-dashboard.button>
                                <input type="hidden" name="notes" x-model="notes">
                            </form>
                        </div>
                    </x-dashboard.card>
                @endif

                <x-dashboard.card>
                    <h3 class="text-lg font-semibold mb-4">Moderation Actions</h3>
                    
                    <div class="space-y-3">
                        @if($listing->canBeSuspended())
                            <form method="POST" action="{{ route('admin.listings.suspend', $listing) }}">
                                @csrf
                                <x-dashboard.button type="submit" variant="warning" class="w-full">
                                    Suspend Listing
                                </x-dashboard.button>
                            </form>
                        @endif

                        @if(in_array($listing->status, ['suspended', 'rejected', 'archived']) || $listing->trashed())
                            <form method="POST" action="{{ route('admin.listings.restore', $listing) }}">
                                @csrf
                                <x-dashboard.button type="submit" variant="primary" class="w-full">
                                    {{ $listing->status === 'rejected' ? 'Return to Draft' : 'Restore Listing' }}
                                </x-dashboard.button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.listings.feature', $listing) }}">
                            @csrf
                            <x-dashboard.button type="submit" variant="secondary" class="w-full">
                                {{ $listing->featured ? 'Unfeature' : 'Feature' }} Listing
                            </x-dashboard.button>
                        </form>

                        <form method="POST" action="{{ route('admin.listings.duplicate', $listing) }}">
                            @csrf
                            <x-dashboard.button type="submit" variant="secondary" class="w-full">
                                Duplicate as Draft
                            </x-dashboard.button>
                        </form>

                        @if(in_array($listing->status, ['suspended', 'rejected', 'archived']))
                            <form 
                                method="POST" 
                                action="{{ route('admin.listings.destroy', $listing) }}"
                                onsubmit="return confirm('Delete this listing? It will be soft-deleted and removable from trash later.');"
                            >
                                @csrf
                                @method('DELETE')
                                <x-dashboard.button type="submit" variant="danger" class="w-full">
                                    Delete Listing
                                </x-dashboard.button>
                            </form>
                        @endif
                    </div>
                </x-dashboard.card>
            </div>
        </div>

        <div x-show="currentTab === 'reviews'">
            <x-dashboard.card>
                @if($listing->reviews->isEmpty())
                    <p class="text-text-muted">No reviews yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($listing->reviews as $review)
                            <div class="border-b border-border-default pb-4 last:border-0 last:pb-0">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <div class="font-medium text-text-primary">{{ $review->user->name }}</div>
                                        <div class="text-sm text-text-muted">{{ $review->created_at->format('M j, Y') }}</div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <x-ui.icon name="star" class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-500 fill-yellow-500' : 'text-gray-300' }}" />
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-text-secondary text-sm">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dashboard.card>
        </div>

        <div x-show="currentTab === 'history'">
            <x-dashboard.card>
                <h3 class="text-lg font-semibold mb-4">Version History</h3>
                
                @if($listing->versions->isEmpty())
                    <p class="text-text-muted">No version history available.</p>
                @else
                    <div class="space-y-3">
                        @foreach($listing->versions->sortByDesc('version_number') as $version)
                            <div class="border-l-4 {{ $version->status === 'approved' ? 'border-green-500' : ($version->status === 'rejected' ? 'border-red-500' : 'border-gray-300') }} pl-4 py-2">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="font-medium text-text-primary">Version {{ $version->version_number }}</div>
                                    <x-dashboard.badge :status="$version->status === 'approved' ? 'active' : ($version->status === 'rejected' ? 'danger' : 'default')">
                                        {{ ucfirst($version->status) }}
                                    </x-dashboard.badge>
                                </div>
                                <div class="text-sm text-text-muted">
                                    @if($version->submitted_at)
                                        Submitted {{ $version->submitted_at->format('M j, Y g:i A') }}
                                    @else
                                        Created {{ $version->created_at->format('M j, Y g:i A') }}
                                    @endif
                                </div>
                                @if($version->reviewed_at)
                                    <div class="text-sm text-text-muted">
                                        Reviewed {{ $version->reviewed_at->format('M j, Y g:i A') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dashboard.card>
        </div>

        <div x-show="currentTab === 'audit'">
            <x-dashboard.card>
                <h3 class="text-lg font-semibold mb-4">Audit Log</h3>
                
                @if(empty($auditLogs) || (is_countable($auditLogs) && count($auditLogs) === 0))
                    <p class="text-text-muted">No audit logs available.</p>
                @else
                    <div class="space-y-2">
                        @foreach($auditLogs as $log)
                            <div class="text-sm border-b border-border-default pb-2 last:border-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-medium text-text-primary">{{ $log->admin?->name ?? 'System' }}</span>
                                        <span class="text-text-muted">{{ $log->action }}</span>
                                    </div>
                                    <div class="text-text-muted">{{ $log->created_at->format('M j, Y g:i A') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dashboard.card>
        </div>
    </div>
</x-layout.page>
@endsection
