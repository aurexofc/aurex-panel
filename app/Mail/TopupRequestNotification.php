<?php

namespace Pterodactyl\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Pterodactyl\Models\TopupRequest;

/**
 * Beautiful VIP-styled email sent to the admin when a user
 * submits a manual coin top-up request.
 */
class TopupRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public TopupRequest $topup;

    public string $methodLabel;

    public string $reviewUrl;

    public function __construct(TopupRequest $topup)
    {
        $topup->loadMissing(['user:id,username,email', 'package:id,name']);
        $this->topup = $topup;
        $this->methodLabel = (string) (config("aurex.topup.methods.{$topup->method}.label") ?? ucfirst($topup->method));
        $this->reviewUrl = rtrim((string) config('app.url'), '/') . '/admin/aurex/topups';
    }

    public function envelope(): Envelope
    {
        $username = $this->topup->user?->username ?? 'user';
        $coins = number_format((int) $this->topup->coins);

        return new Envelope(
            subject: "🔔 New Top-Up Request — {$username} ({$coins} coins)",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    /**
     * VIP gold/dark HTML email with inline CSS for client compatibility.
     */
    private function buildHtml(): string
    {
        $t = $this->topup;
        $username = htmlspecialchars($t->user?->username ?? '—');
        $package = htmlspecialchars($t->package?->name ?? '—');
        $coins = number_format((int) $t->coins);
        $amount = \Pterodactyl\Services\CurrencyService::format((int) $t->price);
        $method = htmlspecialchars($this->methodLabel);
        $ref = htmlspecialchars($t->transaction_ref);
        $whatsapp = htmlspecialchars($t->whatsapp ?? '—');
        $time = $t->created_at ? $t->created_at->format('d M Y, h:i A') : '—';
        $url = htmlspecialchars($this->reviewUrl);

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background-color:#0a0e1a;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#0a0e1a;padding:32px 16px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#1a2138;border:1px solid #c9a227;border-radius:12px;overflow:hidden;">
<tr><td style="background:linear-gradient(135deg,#c9a227,#f5d76e);padding:24px;text-align:center;">
<div style="font-size:28px;font-weight:bold;color:#0a0e1a;">👑 AUREX PANEL</div>
<div style="font-size:14px;color:#0a0e1a;opacity:0.8;margin-top:4px;">VIP Coin Top-Up Alert</div>
</td></tr>
<tr><td style="padding:28px;">
<div style="font-size:20px;font-weight:bold;color:#f5d76e;margin-bottom:4px;">🔔 New Top-Up Request</div>
<div style="font-size:13px;color:#9aa3b8;margin-bottom:20px;">{$time}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#9aa3b8;font-size:14px;">👤 User</td><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#ffffff;font-size:14px;font-weight:bold;text-align:right;">{$username}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#9aa3b8;font-size:14px;">📦 Package</td><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#ffffff;font-size:14px;font-weight:bold;text-align:right;">{$package}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#9aa3b8;font-size:14px;">🪙 Coins</td><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#f5d76e;font-size:18px;font-weight:bold;text-align:right;">{$coins}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#9aa3b8;font-size:14px;">💰 Amount</td><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#ffffff;font-size:14px;font-weight:bold;text-align:right;">{$amount}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#9aa3b8;font-size:14px;">💳 Method</td><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#ffffff;font-size:14px;font-weight:bold;text-align:right;">{$method}</td></tr>
<tr><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#9aa3b8;font-size:14px;">🔢 Transaction Ref</td><td style="padding:10px 0;border-bottom:1px solid #2a3350;color:#ffffff;font-size:14px;font-weight:bold;text-align:right;">{$ref}</td></tr>
<tr><td style="padding:10px 0;color:#9aa3b8;font-size:14px;">📱 User WhatsApp</td><td style="padding:10px 0;color:#ffffff;font-size:14px;font-weight:bold;text-align:right;">{$whatsapp}</td></tr>
</table>
<div style="text-align:center;margin-top:28px;">
<a href="{$url}" style="display:inline-block;background:linear-gradient(135deg,#c9a227,#f5d76e);color:#0a0e1a;font-size:16px;font-weight:bold;text-decoration:none;padding:14px 42px;border-radius:8px;">✅ Review Request</a>
</div>
<p style="color:#9aa3b8;font-size:12px;text-align:center;margin-top:20px;">Verify the payment in your Easypaisa / JazzCash / bank app,<br>then approve or reject from the admin panel.</p>
</td></tr>
<tr><td style="padding:16px;text-align:center;border-top:1px solid #2a3350;">
<div style="font-size:12px;color:#5a6478;">© 2026 Aurex Panel — VIP Gaming Servers</div>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
