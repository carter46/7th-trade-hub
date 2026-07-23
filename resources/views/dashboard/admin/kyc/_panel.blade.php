<x-dashboard.table :empty="$submissions->isEmpty()" empty-title="No KYC submissions" empty-description="Submissions matching this tab will appear here." empty-icon="kyc" striped>
    <x-slot:head>
        <x-dashboard.th>User</x-dashboard.th>
        <x-dashboard.th>Level</x-dashboard.th>
        <x-dashboard.th>Status</x-dashboard.th>
        <x-dashboard.th>Submitted</x-dashboard.th>
        <x-dashboard.th>Actions</x-dashboard.th>
    </x-slot:head>
    @foreach ($submissions as $s)
        <tr>
            <x-dashboard.td>{{ $s->user->email }}</x-dashboard.td>
            <x-dashboard.td>Level {{ $s->level_requested }}</x-dashboard.td>
            <x-dashboard.td><x-dashboard.badge :status="$s->status" /></x-dashboard.td>
            <x-dashboard.td class="text-xs text-text-muted">{{ $s->created_at->format('M j, Y') }}</x-dashboard.td>
            <x-dashboard.td>
                <x-dashboard.row-actions>
                    @if ($s->status === 'pending')
                        <x-dashboard.menu-item type="button" variant="success" @click="$dispatch('open-modal', 'approve-kyc-{{ $s->id }}')">Approve</x-dashboard.menu-item>
                        <x-dashboard.menu-item type="button" variant="danger" @click="$dispatch('open-modal', 'reject-kyc-{{ $s->id }}')">Reject</x-dashboard.menu-item>
                    @else
                        <x-dashboard.menu-item type="button" @click="$dispatch('open-modal', 'return-kyc-{{ $s->id }}')">Return to pending</x-dashboard.menu-item>
                        <x-dashboard.menu-item type="button" @click="$dispatch('open-modal', 'override-kyc-{{ $s->id }}')">Override level</x-dashboard.menu-item>
                    @endif
                </x-dashboard.row-actions>

                @if ($s->status === 'pending')
                    <x-dashboard.modal name="approve-kyc-{{ $s->id }}" title="Approve KYC?" confirm-label="Approve" :form-action="route('admin.kyc.approve', $s)">
                        Grant level {{ $s->level_requested }} to {{ $s->user->email }}.
                    </x-dashboard.modal>
                    <x-dashboard.modal name="reject-kyc-{{ $s->id }}" title="Reject KYC?" variant="danger" confirm-label="Reject" :form-action="route('admin.kyc.reject', $s)">
                        The user will need to resubmit documentation.
                    </x-dashboard.modal>
                @else
                    <x-dashboard.modal name="return-kyc-{{ $s->id }}" title="Return to pending?" confirm-label="Return" :form-action="route('admin.kyc.return-pending', $s)">
                        Move this submission back to the pending queue.
                    </x-dashboard.modal>
                    <x-dashboard.modal name="override-kyc-{{ $s->id }}" title="Override KYC level" confirm-label="Save" :form-action="route('admin.kyc.override', $s)">
                        <x-dashboard.input name="kyc_level" type="number" label="KYC level" :value="$s->level_granted ?? $s->level_requested" min="0" max="3" required />
                        <x-dashboard.textarea name="notes" label="Notes" :rows="2" />
                    </x-dashboard.modal>
                @endif
            </x-dashboard.td>
        </tr>
    @endforeach
</x-dashboard.table>

<x-dashboard.pagination :paginator="$submissions" />
