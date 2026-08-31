<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DomainConnection;
use App\Models\DomainRegistration;
use App\Services\Domains\DomainConnectionService;
use App\Services\Domains\DomainNameserverService;
use App\Services\Domains\Exceptions\DomainBusinessException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class MyDomainsController extends Controller
{
    public function __construct(
        private DomainNameserverService $nameservers,
        private DomainConnectionService $connections,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('dashboard.my-tools.domains');
    }

    public function show(Request $request, DomainRegistration $registration): View
    {
        $this->authorizeRegistration($request, $registration);

        $registration->load('order');

        return view('dashboard.user.my-domains.show', [
            'registration' => $registration,
            'canManageNameservers' => $registration->isRegistered(),
            'showChangeForm' => $request->boolean('change'),
            'platformDefaultNameservers' => $this->nameservers->defaultNameservers(),
        ]);
    }

    public function showConnection(Request $request, DomainConnection $connection): View
    {
        $this->authorizeConnection($request, $connection);

        return view('dashboard.user.my-domains.connection-show', [
            'connection' => $connection,
        ]);
    }

    public function checkConnection(Request $request, DomainConnection $connection): RedirectResponse
    {
        $this->authorizeConnection($request, $connection);

        try {
            $result = $this->connections->checkStatus($connection);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('error', 'Unable to check nameserver status right now. Please try again shortly.');
        }

        return redirect()
            ->route('dashboard.my-domains.connections.show', $connection)
            ->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function updateNameservers(Request $request, DomainRegistration $registration): RedirectResponse
    {
        $this->authorizeRegistration($request, $registration);

        $data = $request->validate([
            'nameserver_1' => ['required', 'string', 'max:253'],
            'nameserver_2' => ['required', 'string', 'max:253'],
            'nameserver_3' => ['nullable', 'string', 'max:253'],
            'nameserver_4' => ['nullable', 'string', 'max:253'],
        ]);

        try {
            $this->nameservers->updateForCustomer($registration, [
                $data['nameserver_1'],
                $data['nameserver_2'],
                $data['nameserver_3'] ?? '',
                $data['nameserver_4'] ?? '',
            ], $request->user());
        } catch (InvalidArgumentException|DomainBusinessException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->withInput()->with('error', 'Unable to update nameservers. Please try again or contact support.');
        }

        return redirect()
            ->route('dashboard.my-domains.show', $registration)
            ->with('status', 'Nameservers updated successfully. Changes may take up to 24–48 hours to propagate globally.');
    }

    public function applyDefaults(Request $request, DomainRegistration $registration): RedirectResponse
    {
        $this->authorizeRegistration($request, $registration);

        try {
            $this->nameservers->applyPlatformDefaults($registration, $request->user());
        } catch (DomainBusinessException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('error', 'Unable to apply platform default nameservers. Please try again or contact support.');
        }

        return redirect()
            ->route('dashboard.my-domains.show', $registration)
            ->with('status', 'Platform default nameservers applied.');
    }

    public function syncFromRegistrar(Request $request, DomainRegistration $registration): RedirectResponse
    {
        $this->authorizeRegistration($request, $registration);

        try {
            $this->nameservers->syncFromProvider($registration);
        } catch (DomainBusinessException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('error', 'Unable to refresh nameservers from the registrar.');
        }

        return redirect()
            ->route('dashboard.my-domains.show', $registration)
            ->with('status', 'Nameservers refreshed from the registrar.');
    }

    private function authorizeRegistration(Request $request, DomainRegistration $registration): void
    {
        $registration->loadMissing('order');
        abort_unless($registration->order && $registration->order->user_id === $request->user()->id, 404);
    }

    private function authorizeConnection(Request $request, DomainConnection $connection): void
    {
        abort_unless($connection->user_id === $request->user()->id, 404);
    }
}
