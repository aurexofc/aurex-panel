@extends('layouts.admin')

@section('title')
    Aurex Coin Top-Ups
@endsection

@section('content-header')
    <h1>Coin Top-Ups<small>Manual payments via Easypaisa, JazzCash, USDT & Binance.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.aurex.index') }}">Aurex</a></li>
        <li class="active">Top-Ups</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="{{ request('tab', 'requests') === 'requests' ? 'active' : '' }}">
                    <a href="{{ route('admin.aurex.topups', ['tab' => 'requests']) }}">
                        Requests
                        @if($counts['pending'] > 0)
                            <span class="badge bg-yellow">{{ $counts['pending'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="{{ request('tab') === 'packages' ? 'active' : '' }}">
                    <a href="{{ route('admin.aurex.topups', ['tab' => 'packages']) }}">Packages</a>
                </li>
                <li class="{{ request('tab') === 'settings' ? 'active' : '' }}">
                    <a href="{{ route('admin.aurex.topups', ['tab' => 'settings']) }}">Settings</a>
                </li>
            </ul>
            <div class="tab-content">
                @if(request('tab', 'requests') === 'requests')
                    <div class="btn-group" style="margin-bottom:15px">
                        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $k => $label)
                            <a href="{{ route('admin.aurex.topups', ['status' => $k]) }}"
                               class="btn btn-sm {{ $status === $k ? 'btn-primary' : 'btn-default' }}">
                                {{ $label }} <span class="badge">{{ $counts[$k] }}</span>
                            </a>
                        @endforeach
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th><th>User</th><th>Package</th><th>Coins</th>
                                    <th>Amount</th><th>Method</th><th>Transaction Ref</th>
                                    <th>Date</th><th>Status</th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $r)
                                    <tr>
                                        <td>{{ $r->id }}</td>
                                        <td>{{ $r->user->username }}<br><small class="text-muted">{{ $r->user->email }}</small></td>
                                        <td>{{ $r->package?->name ?? '—' }}</td>
                                        <td><strong class="text-yellow">{{ number_format($r->coins) }}</strong></td>
                                        <td>{{ \Pterodactyl\Services\CurrencyService::format($r->price) }}</td>
                                        <td><span class="label label-info">{{ ucfirst($r->method) }}</span></td>
                                        <td><code>{{ $r->transaction_ref }}</code>
                                            @if($r->whatsapp)
                                                <br><small><i class="fa fa-whatsapp" style="color:#25d366;"></i> {{ $r->whatsapp }}</small>
                                            @endif
                                            @if($r->email)
                                                <br><small><i class="fa fa-envelope"></i> {{ $r->email }}</small>
                                            @endif
                                        </td>
                                        <td><small>{{ $r->created_at->format('d M Y H:i') }}</small></td>
                                        <td>
                                            @if($r->status === 'pending')
                                                <span class="label label-warning">Pending</span>
                                            @elseif($r->status === 'approved')
                                                <span class="label label-success">Approved</span>
                                            @else
                                                <span class="label label-danger">Rejected</span>
                                            @endif
                                            @if($r->admin_note)
                                                <br><small class="text-muted">{{ $r->admin_note }}</small>
                                            @endif
                                        </td>
                                        <td class="text-right" style="white-space:nowrap">
                                            @if($r->status === 'pending')
                                                <form action="{{ route('admin.aurex.topups.approve', ['topup' => $r->id]) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    <button class="btn btn-xs btn-success" onclick="return confirm('Approve and credit {{ $r->coins }} coins to {{ $r->user->username }}?')">
                                                        <i class="fa fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                <button class="btn btn-xs btn-danger" data-toggle="modal" data-target="#reject-{{ $r->id }}">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                                <div class="modal fade" id="reject-{{ $r->id }}" tabindex="-1">
                                                    <div class="modal-dialog modal-sm">
                                                        <form action="{{ route('admin.aurex.topups.reject', ['topup' => $r->id]) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-content">
                                                                <div class="modal-header"><h4 class="modal-title">Reject request #{{ $r->id }}</h4></div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <label>Reason (shown to user)</label>
                                                                        <input type="text" name="admin_note" class="form-control" maxlength="500" placeholder="e.g. Payment not received">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center text-muted">No {{ $status }} requests.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $requests->links() }}

                @elseif(request('tab') === 'packages')
                    <div class="row">
                        <div class="col-md-5">
                            <div class="box box-primary">
                                <div class="box-header with-border"><h3 class="box-title">New Package</h3></div>
                                <form action="{{ route('admin.aurex.topups.packages.store') }}" method="POST">
                                    @csrf
                                    <div class="box-body">
                                        <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required maxlength="100" placeholder="e.g. Starter Pack"></div>
                                        <div class="row">
                                            <div class="col-xs-6"><div class="form-group"><label>Coins</label><input type="number" name="coins" class="form-control" required min="1" max="1000000"></div></div>
                                            <div class="col-xs-6"><div class="form-group"><label>Price ({{ \Pterodactyl\Services\CurrencyService::symbol() }})</label><input type="number" name="price" class="form-control" required min="1" max="1000000"></div></div>
                                        </div>
                                        <div class="form-group"><label>Sort order</label><input type="number" name="sort_order" class="form-control" min="0" value="0"></div>
                                    </div>
                                    <div class="box-footer"><button class="btn btn-primary">Create Package</button></div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-7">
                            @php $cur = \Pterodactyl\Services\CurrencyService::symbol(); @endphp
                            <table class="table table-hover">
                                <thead><tr><th>Name</th><th>Coins</th><th>Price ({{ $cur }})</th><th>Status</th><th></th></tr></thead>
                                <tbody>
                                    @foreach(\Pterodactyl\Models\TopupPackage::orderBy('sort_order')->get() as $p)
                                        <tr>
                                            <form action="{{ route('admin.aurex.topups.packages.update', ['package' => $p->id]) }}" method="POST">
                                                @csrf @method('PUT')
                                                <td><input type="text" name="name" class="form-control input-sm" value="{{ $p->name }}" required maxlength="100" style="min-width:120px"></td>
                                                <td><input type="number" name="coins" class="form-control input-sm" value="{{ $p->coins }}" required min="1" max="1000000" style="width:90px"></td>
                                                <td>
                                                    <div class="input-group" style="width:130px">
                                                        <span class="input-group-addon">{{ $cur }}</span>
                                                        <input type="number" name="price" class="form-control input-sm" value="{{ $p->price }}" required min="1" max="1000000">
                                                    </div>
                                                    <input type="hidden" name="sort_order" value="{{ $p->sort_order }}">
                                                </td>
                                                <td>{!! $p->active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Hidden</span>' !!}</td>
                                                <td class="text-right" style="white-space:nowrap">
                                                    <button class="btn btn-xs btn-primary" title="Save changes"><i class="fa fa-check"></i></button>
                                            </form>
                                                <form action="{{ route('admin.aurex.topups.packages.toggle', ['package' => $p->id]) }}" method="POST" style="display:inline">@csrf<button class="btn btn-xs btn-default">{{ $p->active ? 'Hide' : 'Show' }}</button></form>
                                                <form action="{{ route('admin.aurex.topups.packages.delete', ['package' => $p->id]) }}" method="POST" style="display:inline">@csrf @method('DELETE')<button class="btn btn-xs btn-danger" onclick="return confirm('Delete this package?')">Delete</button></form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p class="help-block"><i class="fa fa-info-circle"></i> Edit any field inline and click <i class="fa fa-check"></i> to save.</p>
                        </div>
                    </div>

                @elseif(request('tab') === 'settings')
                    <form action="{{ route('admin.aurex.topups.settings') }}" method="POST">
                        @csrf
                                        <input type="hidden" name="section" value="topup">
                        <div class="box box-warning">
                            <div class="box-header with-border"><h3 class="box-title">Top-Up Settings</h3></div>
                            <div class="box-body">
                                <div class="form-group">
                                    <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;">
                                        <input type="checkbox" name="topup_enabled" value="1" {{ !empty($topup['enabled']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#d4a017;cursor:pointer;flex:0 0 auto;margin:0 8px;">
                                        <strong>Top-ups enabled</strong>
                                    </label>
                                </div>
                                <p class="help-block">Your receiving accounts. Leave a method empty to hide it from users.</p>
                                <div class="form-group">
                                    <label><i class="fa fa-money"></i> Currency</label>
                                    <select name="topup_currency" class="form-control">
                                        @foreach(\Pterodactyl\Services\CurrencyService::CURRENCIES as $code => $c)
                                            <option value="{{ $code }}" {{ ($topup['currency'] ?? 'PKR') === $code ? 'selected' : '' }}>{{ $code }} ({{ $c['symbol'] }}) — {{ $c['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <p class="help-block">All package prices will show in this currency.</p>
                                </div>
                                @foreach(['easypaisa' => 'Easypaisa number', 'jazzcash' => 'JazzCash number', 'usdt' => 'USDT wallet address (TRC20)', 'binance' => 'Binance Pay ID'] as $key => $label)
                                    <div class="form-group">
                                        <label>{{ $label }}</label>
                                        <input type="text" name="{{ $key }}_account" class="form-control" maxlength="200" value="{{ $topup['methods'][$key]['account'] ?? '' }}" placeholder="Leave empty to disable {{ $label }}">
                                    </div>
                                @endforeach
                            </div>
                            <div class="box-footer"><button class="btn btn-success"><i class="fa fa-check"></i> Save Settings</button></div>
                        </div>
                    </form>

                    <div class="box box-success" style="border-top-color:#25d366;">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-whatsapp" style="color:#25d366;"></i> WhatsApp Notifications <span class="label label-success" style="background:#25d366;">FREE</span></h3>
                        </div>
                        <form action="{{ route('admin.aurex.topups.settings') }}" method="POST">
                            @csrf
                                        <input type="hidden" name="section" value="whatsapp">
                            <div class="box-body">
                                <div class="form-group">
                                    <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;">
                                        <input type="checkbox" name="notify_enabled" value="1" {{ !empty($topup['notifications']['enabled']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#25d366;cursor:pointer;flex:0 0 auto;margin:0 8px;">
                                        <strong>WhatsApp notifications enabled</strong>
                                    </label>
                                    <p class="help-block">Get an instant WhatsApp message when a user submits a top-up request. Users also get notified on approve/reject.</p>
                                </div>
                                @php $waProvider = $topup['notifications']['provider'] ?? 'wasphere'; @endphp
                                <div class="form-group">
                                    <label>Provider</label>
                                    <select name="notify_provider" id="wa_provider" class="form-control">
                                        <option value="wasphere" {{ $waProvider === 'wasphere' ? 'selected' : '' }}>WaSphere (self-hosted — recommended, messages anyone)</option>
                                        <option value="callmebot" {{ $waProvider === 'callmebot' ? 'selected' : '' }}>CallMeBot (free — admin alerts only)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Your WhatsApp number (admin)</label>
                                    <input type="text" name="notify_admin_phone" class="form-control" maxlength="20" value="{{ $topup['notifications']['admin_phone'] ?? '' }}" placeholder="923001234567">
                                    <p class="help-block">Country code + number, no + sign. Example: 923001234567</p>
                                </div>
                                <div id="wa_wasphere_fields" style="{{ $waProvider === 'wasphere' ? '' : 'display:none;' }}">
                                    <div class="form-group">
                                        <label>WaSphere API URL</label>
                                        <input type="text" name="notify_wasphere_url" class="form-control" maxlength="200" value="{{ $topup['notifications']['wasphere_url'] ?? '' }}" placeholder="http://YOUR-VPS-IP:3001">
                                        <p class="help-block">Your WaSphere WA Server URL (port 3001). Example: http://203.161.39.29:3001</p>
                                    </div>
                                    <div class="form-group">
                                        <label>WaSphere API key</label>
                                        <input type="password" name="notify_wasphere_key" class="form-control" maxlength="200" value="" placeholder="{{ !empty($topup['notifications']['wasphere_key']) ? '•••••••• (saved — enter new to replace)' : 'Paste your WaSphere API key' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>WaSphere session name</label>
                                        <input type="text" name="notify_wasphere_session" class="form-control" maxlength="100" value="{{ $topup['notifications']['wasphere_session'] ?? '' }}" placeholder="aurex">
                                        <p class="help-block">The session name you created in the WaSphere dashboard.</p>
                                    </div>
                                    <div class="alert alert-info" style="border-radius:8px;">
                                        <strong><i class="fa fa-info-circle"></i> WaSphere setup (one time, ~5 min):</strong>
                                        <ol style="margin:8px 0 0 18px;padding:0;">
                                            <li>On your VPS run: <code>sudo bash wasphere-install.sh</code></li>
                                            <li>Open <code>http://YOUR-VPS-IP:3004</code>, register admin</li>
                                            <li>Settings → WA Server → URL <code>http://wa-server:3001</code> + your WA_TOKEN</li>
                                            <li>Sessions → New session → scan QR with a <strong>spare</strong> WhatsApp number</li>
                                            <li>Create an API key → paste it above with the session name</li>
                                        </ol>
                                    </div>
                                </div>
                                <div id="wa_callmebot_fields" style="{{ $waProvider === 'callmebot' ? '' : 'display:none;' }}">
                                    <div class="form-group">
                                        <label>CallMeBot API key</label>
                                        <input type="text" name="notify_apikey" class="form-control" maxlength="100" value="{{ $topup['notifications']['apikey'] ?? '' }}" placeholder="Paste your CallMeBot API key">
                                    </div>
                                    <div class="alert alert-warning" style="border-radius:8px;">
                                        <strong><i class="fa fa-exclamation-triangle"></i> Limitation:</strong> CallMeBot's free plan only sends to <strong>your own number</strong>. User approve/reject notifications won't work — admin alerts only.
                                        <ol style="margin:8px 0 0 18px;padding:0;">
                                            <li>On WhatsApp, send <code>I allow callmebot to send me messages</code> to <strong>+34 623 78 64 49</strong></li>
                                            <li>CallMeBot will reply with your free <strong>API key</strong></li>
                                            <li>Paste the key above along with your WhatsApp number and save</li>
                                        </ol>
                                    </div>
                                </div>
                                <script>
                                document.getElementById('wa_provider').addEventListener('change', function() {
                                    var isWa = this.value === 'wasphere';
                                    document.getElementById('wa_wasphere_fields').style.display = isWa ? '' : 'none';
                                    document.getElementById('wa_callmebot_fields').style.display = isWa ? 'none' : '';
                                });
                                </script>
                            </div>
                            <div class="box-footer">
                                <button class="btn btn-success"><i class="fa fa-check"></i> Save</button>
                            </div>
                        </form>
                        <div class="box-footer" style="border-top:1px solid #f4f4f4;">
                            <form action="{{ route('admin.aurex.topups.notify_test') }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-default"><i class="fa fa-paper-plane" style="color:#25d366;"></i> Send Test Message</button>
                            </form>
                            <span class="help-block" style="display:inline;margin-left:10px;">Sends a test WhatsApp to your number.</span>
                        </div>
                    </div>

                    <div class="box box-warning" style="border-top-color:#c9a227;">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-envelope" style="color:#c9a227;"></i> Email Notifications <span class="label label-warning" style="background:#c9a227;">FREE</span></h3>
                        </div>
                        <form action="{{ route('admin.aurex.topups.settings') }}" method="POST">
                            @csrf
                                        <input type="hidden" name="section" value="email">
                            <div class="box-body">
                                <div class="form-group">
                                    <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:10px 0;">
                                        <input type="checkbox" name="email_enabled" value="1" {{ !empty($topup['email_notifications']['enabled']) ? 'checked' : '' }} style="width:24px;height:24px;transform:scale(1.6);-webkit-transform:scale(1.6);accent-color:#c9a227;cursor:pointer;flex:0 0 auto;margin:0 8px;">
                                        <strong>Email notifications enabled</strong>
                                    </label>
                                    <p class="help-block">Get an instant stylish email when a user submits a top-up request. Users also get notified on approve/reject.</p>
                                </div>
                                <div class="form-group">
                                    <label>Notification email (where alerts are sent)</label>
                                    <input type="email" name="email_to" class="form-control" maxlength="200" value="{{ $topup['email_notifications']['to'] ?? 'asifofc.dev@gmail.com' }}" placeholder="asifofc.dev@gmail.com">
                                </div>
                                <div class="form-group">
                                    <label>Gmail address (sender)</label>
                                    <input type="text" name="email_smtp_user" class="form-control" maxlength="200" value="{{ $topup['email_notifications']['smtp_user'] ?? '' }}" placeholder="your@gmail.com">
                                    <p class="help-block">The Gmail account used to send the emails.</p>
                                </div>
                                <div class="form-group">
                                    <label>Gmail App Password</label>
                                    <input type="password" name="email_smtp_pass" class="form-control" maxlength="200" value="" placeholder="{{ !empty($topup['email_notifications']['smtp_pass']) ? '•••••••• (saved — leave blank to keep)' : 'Paste your 16-character App Password' }}">
                                    <p class="help-block">Leave blank to keep the saved password.</p>
                                </div>
                                <div class="alert alert-info" style="border-radius:8px;">
                                    <strong><i class="fa fa-info-circle"></i> Free setup (2 minutes):</strong>
                                    <ol style="margin:8px 0 0 18px;padding:0;">
                                        <li>Go to <strong>myaccount.google.com/security</strong> and enable <strong>2-Step Verification</strong></li>
                                        <li>Go to <strong>myaccount.google.com/apppasswords</strong></li>
                                        <li>Generate a password for <strong>"Mail"</strong> and paste it above</li>
                                        <li>Enter your Gmail address and save</li>
                                    </ol>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button class="btn btn-warning"><i class="fa fa-check"></i> Save</button>
                            </div>
                        </form>
                        <div class="box-footer" style="border-top:1px solid #f4f4f4;">
                            <form action="{{ route('admin.aurex.topups.email_test') }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-default"><i class="fa fa-paper-plane" style="color:#c9a227;"></i> Send Test Email</button>
                            </form>
                            <span class="help-block" style="display:inline;margin-left:10px;">Sends a test email to your notification address.</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
