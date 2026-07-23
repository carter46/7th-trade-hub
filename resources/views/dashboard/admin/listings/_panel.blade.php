@include('dashboard.admin.listings._table', ['listings' => $listings])

<div class="mt-4">
    <x-dashboard.pagination :paginator="$listings" />
</div>
