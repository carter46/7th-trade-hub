<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainConnection;
use App\Models\DomainRegistration;
use App\Models\User;
use App\Modules\Admin\Services\AuditLogService;
use App\Services\Domains\DomainConnectionService;
use App\Services\Domains\DomainRegistrationFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class UserDomainAdminController extends Controller
{
    public function __construct(
        private DomainRegistrationFulfillmentService $domainFulfillment,
        private DomainConnectionService $domainConnections,
        private AuditLogService $audit,
    ) {}

    public function showRegistration(User $user, DomainRegistration $registration): View
    {
        $this->ensureMember($user);
        $this->assertRegistrationBelongsToUser($user, $registration);

        $registration->load(['order', 'orderItem', 'domainQuote']);

        return view('dashboard.admin.users.domains.registration-show', [
            'user' => $user,
            'registration' => $registration,
        ]);
    }

    public function approveRegistration(Request $request, User $user, DomainRegistration $registration): RedirectResponse
    {
        $this->ensureMember($user);
        $this->assertRegistrationBelongsToUser($user, $registration);

        $data = $request->validate([
            'provider_reference' => ['nullable', 'string', 'max:191'],
            'nameservers' => ['nullable', 'string', 'max:1000'],
        ]);

        $nameservers = null;
        if (filled($data['nameservers'] ?? null)) {
            $nameservers = preg_split('/[\s,;]+/', (string) $data['nameservers'], -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        try {
            [$updated, $alreadyRegistered] = $this->domainFulfillment->markManualRegistered(
                $registration,
                $data['provider_reference'] ?? null,
                $nameservers,
                $request->user()?->id,
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! $alreadyRegistered) {
            $this->audit->log(
                $request->user()?->id,
                'domains.manual_registered',
                $updated,
                null,
                ['status' => $updated->status, 'fqdn' => $updated->fqdn, 'order_id' => $updated->order_id],
                $request->ip(),
            );
        }

        return redirect()
            ->route('admin.users.domains.registrations.show', [$user, $updated])
            ->with('status', $alreadyRegistered
                ? $updated->fqdn.' was already registered.'
                : 'Approved and marked '.$updated->fqdn.' as registered.');
    }

    public function rejectRegistration(Request $request, User $user, DomainRegistration $registration): RedirectResponse
    {
        $this->ensureMember($user);
        $this->assertRegistrationBelongsToUser($user, $registration);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        try {
            $updated = $this->domainFulfillment->rejectManualRegistration(
                $registration,
                $data['reason'],
                $request->user()?->id,
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $updated->loadMissing('order.user');

        $this->audit->log(
            $request->user()?->id,
            'domains.manual_rejected',
            $updated,
            null,
            [
                'status' => $updated->status,
                'fqdn' => $updated->fqdn,
                'reason' => $data['reason'],
                'mail_sent' => (bool) $updated->getAttribute('_reject_mail_sent'),
                'mail_error' => $updated->getAttribute('_reject_mail_error'),
            ],
            $request->ip(),
        );

        $status = 'Rejected '.$updated->fqdn.'. The customer can submit a free replacement.';
        if ($updated->getAttribute('_reject_mail_sent')) {
            $customerEmail = $updated->order?->user?->email
                ?? $user->email
                ?? 'the customer';
            $status .= ' Rejection email sent to '.$customerEmail.'.';
        } else {
            $mailError = $updated->getAttribute('_reject_mail_error') ?: 'unknown error';
            $status .= ' Warning: rejection email was NOT sent ('.$mailError.'). Check storage/logs.';
        }

        return redirect()
            ->route('admin.users.domains.registrations.show', [$user, $updated])
            ->with('status', $status);
    }

    public function showConnection(User $user, DomainConnection $connection): View
    {
        $this->ensureMember($user);
        abort_unless((int) $connection->user_id === (int) $user->id, 404);

        $connection->load(['order', 'orderItem', 'userTool']);

        return view('dashboard.admin.users.domains.connection-show', [
            'user' => $user,
            'connection' => $connection,
        ]);
    }

    public function approveConnection(Request $request, User $user, DomainConnection $connection): RedirectResponse
    {
        $this->ensureMember($user);
        abort_unless((int) $connection->user_id === (int) $user->id, 404);

        if ($connection->verification_status === DomainConnection::STATUS_VERIFIED) {
            return redirect()
                ->route('admin.users.domains.connections.show', [$user, $connection])
                ->with('status', 'Domain is already verified.');
        }

        $previousStatus = $connection->verification_status;

        try {
            $this->domainConnections->approveManually($connection);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('error', 'Unable to approve domain connection.');
        }

        $this->audit->log(
            $request->user()?->id,
            'admin.domain_connection.manual_approve',
            $connection->fresh(),
            ['verification_status' => $previousStatus],
            ['verification_status' => DomainConnection::STATUS_VERIFIED],
            $request->ip(),
        );

        return redirect()
            ->route('admin.users.domains.connections.show', [$user, $connection])
            ->with('status', 'Domain connection approved.');
    }

    private function ensureMember(User $user): void
    {
        abort_if($user->isAnonymized(), 404);
        abort_unless($user->hasRole('user') && ! $user->hasRole('admin'), 404);
    }

    private function assertRegistrationBelongsToUser(User $user, DomainRegistration $registration): void
    {
        $registration->loadMissing('order');
        abort_unless((int) $registration->order?->user_id === (int) $user->id, 404);
    }
}
