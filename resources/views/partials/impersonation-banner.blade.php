<div
    class="sticky top-0 z-50 border-b border-amber-600/40 bg-amber-500 px-4 py-2.5 text-center text-sm font-semibold text-slate-900"
    role="status"
>
    <span class="uppercase tracking-wide">You are impersonating {{ auth()->user()?->name }}</span>
    @if ($impersonatorName ?? null)
        <span class="font-normal opacity-80"> (Admin: {{ $impersonatorName }})</span>
    @endif
    <form method="POST" action="{{ route('impersonation.leave') }}" class="mt-1 inline-block sm:ml-3 sm:mt-0">
        @csrf
        <button type="submit" class="underline underline-offset-2 hover:no-underline focus-ring rounded">
            Return to Admin
        </button>
    </form>
</div>
