<div class="space-y-4">
    @forelse($listings as $listing)
        @include('partials.marketplace.listing-card', ['listing' => $listing])
    @empty
        <x-ui.empty
            icon="listings"
            title="No listings match your filters"
            description="Try a different search or clear filters to see more results."
        />
    @endforelse
</div>
@if($listings->hasPages())
    <div class="mt-8 marketplace-pagination">
        <x-ui.pagination :paginator="$listings" />
    </div>
@endif
