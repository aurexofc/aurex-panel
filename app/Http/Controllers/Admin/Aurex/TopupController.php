<?php

namespace Pterodactyl\Http\Controllers\Admin\Aurex;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\TopupPackage;
use Pterodactyl\Models\TopupRequest;
use Pterodactyl\Services\EmailNotificationService;
use Pterodactyl\Services\WhatsAppNotificationService;

class TopupController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
        protected ViewFactory $view,
        protected SettingsRepositoryInterface $settings,
    ) {
    }

    /**
     * Pending + recent top-up requests.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');
        $query = TopupRequest::query()->with(['user:id,username,email', 'package:id,name'])->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(25)->withQueryString();
        $counts = [
            'pending' => TopupRequest::where('status', 'pending')->count(),
            'approved' => TopupRequest::where('status', 'approved')->count(),
            'rejected' => TopupRequest::where('status', 'rejected')->count(),
        ];

        return $this->view->make('admin.aurex.topups', [
            'requests' => $requests,
            'counts' => $counts,
            'status' => $status,
            'topup' => config('aurex.topup'),
        ]);
    }

    /**
     * Approve a request: credit coins to the user.
     */
    public function approve(TopupRequest $topup): RedirectResponse
    {
        if ($topup->status !== TopupRequest::STATUS_PENDING) {
            $this->alert->danger('This request has already been reviewed.')->flash();

            return redirect()->route('admin.aurex.topups');
        }

        $user = $topup->user;
        $user->awardCoins($topup->coins, 'topup', [
            'topup_id' => $topup->id,
            'method' => $topup->method,
            'transaction_ref' => $topup->transaction_ref,
            'price' => $topup->price,
        ]);

        $topup->update([
            'status' => TopupRequest::STATUS_APPROVED,
            'reviewed_by' => auth()->user()->id,
            'reviewed_at' => now(),
        ]);

        // Notify the user on WhatsApp — never break the approval flow.
        try {
            app(WhatsAppNotificationService::class)->notifyUserApproved($topup);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('User approval notification failed: ' . $e->getMessage());
        }

        // Notify the user by email — never break the approval flow.
        try {
            app(EmailNotificationService::class)->sendApprovalEmail($topup);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('User approval email failed: ' . $e->getMessage());
        }

        $this->alert->success("Approved! {$topup->coins} coins credited to {$user->username}.")->flash();

        return redirect()->route('admin.aurex.topups');
    }

    /**
     * Reject a request with an optional note.
     */
    public function reject(Request $request, TopupRequest $topup): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        if ($topup->status !== TopupRequest::STATUS_PENDING) {
            $this->alert->danger('This request has already been reviewed.')->flash();

            return redirect()->route('admin.aurex.topups');
        }

        $topup->update([
            'status' => TopupRequest::STATUS_REJECTED,
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => auth()->user()->id,
            'reviewed_at' => now(),
        ]);

        // Notify the user on WhatsApp — never break the rejection flow.
        try {
            app(WhatsAppNotificationService::class)->notifyUserRejected($topup, $data['admin_note'] ?? null);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('User rejection notification failed: ' . $e->getMessage());
        }

        // Notify the user by email — never break the rejection flow.
        try {
            app(EmailNotificationService::class)->sendRejectionEmail($topup, $data['admin_note'] ?? null);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('User rejection email failed: ' . $e->getMessage());
        }

        $this->alert->warning('Request rejected.')->flash();

        return redirect()->route('admin.aurex.topups');
    }

    /**
     * Save top-up settings (enabled + payment account details).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $section = $request->input('section', 'all');

        // Only update the section that was submitted — the Settings tab has
        // three separate forms (topup / whatsapp / email) posting to this route.
        if ($section === 'all' || $section === 'topup') {
            $data = $request->validate([
                'topup_enabled' => 'sometimes|boolean',
                'topup_currency' => 'nullable|string|in:PKR,USD,EUR,GBP,INR,IDR,AED,SAR,TRY,BDT',
                'easypaisa_account' => 'nullable|string|max:100',
                'jazzcash_account' => 'nullable|string|max:100',
                'usdt_account' => 'nullable|string|max:200',
                'binance_account' => 'nullable|string|max:200',
            ]);

            $this->settings->set('settings::aurex:topup:enabled', $request->boolean('topup_enabled') ? '1' : '0');
            if (!empty($data['topup_currency'])) {
                $this->settings->set('settings::aurex:topup:currency', $data['topup_currency']);
            }
            foreach (['easypaisa', 'jazzcash', 'usdt', 'binance'] as $m) {
                $this->settings->set(
                    "settings::aurex:topup:methods:{$m}:account",
                    (string) ($data["{$m}_account"] ?? '')
                );
            }
        }

        if ($section === 'all' || $section === 'whatsapp') {
            $data = $request->validate([
                'notify_enabled' => 'sometimes|boolean',
                'notify_provider' => 'nullable|in:wasphere,callmebot',
                'notify_admin_phone' => 'nullable|string|max:20',
                'notify_apikey' => 'nullable|string|max:100',
                'notify_wasphere_url' => 'nullable|string|max:200',
                'notify_wasphere_key' => 'nullable|string|max:200',
                'notify_wasphere_session' => 'nullable|string|max:100',
            ]);

            $this->settings->set('settings::aurex:topup:notifications:enabled', $request->boolean('notify_enabled') ? '1' : '0');
            $this->settings->set('settings::aurex:topup:notifications:provider', (string) ($data['notify_provider'] ?? 'wasphere'));
            $this->settings->set(
                'settings::aurex:topup:notifications:admin_phone',
                WhatsAppNotificationService::normalizePhone((string) ($data['notify_admin_phone'] ?? ''))
            );
            $this->settings->set('settings::aurex:topup:notifications:apikey', (string) ($data['notify_apikey'] ?? ''));
            $this->settings->set('settings::aurex:topup:notifications:wasphere_url', rtrim((string) ($data['notify_wasphere_url'] ?? ''), '/'));
            // Only overwrite the API key if a new one was entered.
            if (!empty($data['notify_wasphere_key'])) {
                $this->settings->set('settings::aurex:topup:notifications:wasphere_key', (string) $data['notify_wasphere_key']);
            }
            $this->settings->set('settings::aurex:topup:notifications:wasphere_session', (string) ($data['notify_wasphere_session'] ?? ''));
        }

        if ($section === 'all' || $section === 'email') {
            $data = $request->validate([
                'email_enabled' => 'sometimes|boolean',
                'email_to' => 'nullable|email|max:200',
                'email_smtp_user' => 'nullable|string|max:200',
                'email_smtp_pass' => 'nullable|string|max:200',
            ]);

            $this->settings->set('settings::aurex:topup:email_notifications:enabled', $request->boolean('email_enabled') ? '1' : '0');
            $this->settings->set('settings::aurex:topup:email_notifications:to', (string) ($data['email_to'] ?? ''));
            $this->settings->set('settings::aurex:topup:email_notifications:smtp_user', (string) ($data['email_smtp_user'] ?? ''));
            // Only overwrite the App Password if a new one was entered.
            if (!empty($data['email_smtp_pass'])) {
                $this->settings->set('settings::aurex:topup:email_notifications:smtp_pass', (string) $data['email_smtp_pass']);
            }
        }

        $this->alert->success('Top-up settings saved.')->flash();

        return redirect()->route('admin.aurex.topups', ['tab' => 'settings']);
    }

    /**
     * Send a test WhatsApp message to the admin number.
     */
    public function testNotification(): RedirectResponse
    {
        $service = app(WhatsAppNotificationService::class);
        $phone = (string) config('aurex.topup.notifications.admin_phone');

        if (empty($phone) || empty(config('aurex.topup.notifications.apikey'))) {
            $this->alert->danger('Enter your WhatsApp number and CallMeBot API key first.')->flash();

            return redirect()->route('admin.aurex.topups', ['tab' => 'settings']);
        }

        $ok = $service->send($phone, implode("\n", [
            '👑 *AUREX PANEL* 👑',
            '━━━━━━━━━━━━━━━',
            '✅ WhatsApp notifications are working!',
            'You will receive alerts here for new top-up requests. 🔔',
        ]));

        if ($ok) {
            $this->alert->success('Test message sent! Check your WhatsApp.')->flash();
        } else {
            $this->alert->danger('Test failed. Check the number format (923001234567) and API key.')->flash();
        }

        return redirect()->route('admin.aurex.topups', ['tab' => 'settings']);
    }

    /**
     * Send a test email to the admin address.
     */
    public function testEmail(): RedirectResponse
    {
        $service = app(EmailNotificationService::class);

        if (empty(config('aurex.topup.email_notifications.to'))
            || empty(config('aurex.topup.email_notifications.smtp_user'))
            || empty(config('aurex.topup.email_notifications.smtp_pass'))
        ) {
            $this->alert->danger('Enter the notification email, Gmail address and App Password first.')->flash();

            return redirect()->route('admin.aurex.topups', ['tab' => 'settings']);
        }

        $ok = $service->sendTestEmail();

        if ($ok) {
            $this->alert->success('Test email sent! Check your inbox.')->flash();
        } else {
            $this->alert->danger('Test failed. Check the Gmail address and App Password (myaccount.google.com/apppasswords).')->flash();
        }

        return redirect()->route('admin.aurex.topups', ['tab' => 'settings']);
    }

    /**
     * Create a coin package.
     */
    public function storePackage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'coins' => 'required|integer|min:1|max:1000000',
            'price' => 'required|integer|min:1|max:1000000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        TopupPackage::create([
            'name' => $data['name'],
            'coins' => $data['coins'],
            'price' => $data['price'],
            'sort_order' => $data['sort_order'] ?? 0,
            'active' => true,
        ]);

        $this->alert->success('Package created.')->flash();

        return redirect()->route('admin.aurex.topups', ['tab' => 'packages']);
    }

    /**
     * Update a package's name, coins or price.
     */
    public function updatePackage(Request $request, TopupPackage $package): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'coins' => 'required|integer|min:1|max:1000000',
            'price' => 'required|integer|min:1|max:1000000',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $package->update([
            'name' => $data['name'],
            'coins' => $data['coins'],
            'price' => $data['price'],
            'sort_order' => $data['sort_order'] ?? $package->sort_order,
        ]);

        $this->alert->success('Package updated.')->flash();

        return redirect()->route('admin.aurex.topups', ['tab' => 'packages']);
    }

    /**
     * Toggle a package active/inactive.
     */
    public function togglePackage(TopupPackage $package): RedirectResponse
    {
        $package->update(['active' => !$package->active]);

        return redirect()->route('admin.aurex.topups', ['tab' => 'packages']);
    }

    /**
     * Delete a package.
     */
    public function destroyPackage(TopupPackage $package): RedirectResponse
    {
        $package->delete();
        $this->alert->success('Package deleted.')->flash();

        return redirect()->route('admin.aurex.topups', ['tab' => 'packages']);
    }
}
