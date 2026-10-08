<?php

namespace Pterodactyl\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Test email to verify Gmail SMTP settings work.
 */
class TopupTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Aurex Email Notifications Working',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:32px;background-color:#0a0e1a;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
<div style="max-width:500px;background-color:#1a2138;border:1px solid #c9a227;border-radius:12px;padding:32px;text-align:center;">
<div style="font-size:24px;font-weight:bold;color:#f5d76e;">👑 AUREX PANEL</div>
<div style="font-size:40px;margin:16px 0;">✅</div>
<div style="font-size:18px;font-weight:bold;color:#ffffff;">Email notifications are working!</div>
<p style="font-size:14px;color:#9aa3b8;">You will receive alerts here for new top-up requests. 🔔</p>
</div>
</td></tr></table>
</body>
</html>
HTML,
        );
    }
}
