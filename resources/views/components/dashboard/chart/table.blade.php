@props([
    'title' => null,
    'columns' => [],
    'rows' => [],
])

<x-dashboard.card variant="solid" {{ $attributes }}>
    @if ($title)
        <h3 class="text-base font-semibold text-text-primary mb-4">{{ $title }}</h3>
    @endif

    @if (empty($rows))
        <x-dashboard.empty title="No data available." />
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-border-subtle text-left text-text-muted">
                        @foreach ($columns as $column)
                            <th class="py-2 pr-4 font-semibold">{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="border-b border-border-subtle/60 last:border-0">
                            @foreach ($row as $cell)
                                <td class="py-2 pr-4 text-text-secondary">{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-dashboard.card>
