<?php

namespace Pterodactyl\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Pterodactyl\Models\TopupRequest;

/**
 * Simple rejection email sent to the user when their top-up is rejected.
 */
class TopupRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TopupRequest $topup,
        public string $username,
        public string $package,
        public ?string $note,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Top-Up Request Rejected',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        $username = htmlspecialchars($this->username);
        $package = htmlspecialchars($this->package);
        $note = htmlspecialchars($this->note ?: 'Please contact support for details.');

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
</td></tr>
<tr><td style="padding:32px;text-align:center;">
<div style="font-size:48px;margin-bottom:12px;">⚠️</div>
<div style="font-size:22px;font-weight:bold;color:#f5d76e;margin-bottom:8px;">Top-Up Rejected</div>
<p style="font-size:15px;color:#d7dce6;line-height:1.6;">Hi <strong>{$username}</strong>,<br><br>Your top-up request for <strong>{$package}</strong> was not approved.<br><br><span style="color:#9aa3b8;">Reason: {$note}</span></p>
<p style="font-size:14px;color:#9aa3b8;margin-top:20px;">Please try again or contact us for help.</p>
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
