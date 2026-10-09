@extends('layouts.app')

@section('content')
<header><div><p class="eyebrow">MY WORKSPACE</p><h1>Notifications</h1></div></header>
<section class="panel notifications-panel">
    @forelse($notifications as $notification)
        @php($cancelled = (bool) ($notification->data['cancelled'] ?? false))
        <article class="notification-item {{ is_null($notification->read_at) ? 'unread' : '' }} {{ $cancelled ? 'cancelled' : '' }}">
            <div class="notification-dot"></div>
            <div><b>{{ $notification->data['title'] ?? 'Notification' }}</b>@if($cancelled)<span class="notification-status">Cancelled</span>@endif<p>{{ $notification->data['message'] ?? '' }}</p><small>{{ $notification->created_at->diffForHumans() }}</small></div>
        </article>
    @empty
        <p class="muted">No notifications yet.</p>
    @endforelse
</section>
@endsection

@push('scripts')
<style>
.notifications-panel{max-width:780px}.notification-item{display:flex;gap:13px;padding:16px 0;border-bottom:1px solid #e7edf4}.notification-item:last-child{border-bottom:0}.notification-item b{font:700 15px Manrope}.notification-item p{margin:5px 0;color:#52647f}.notification-item small{color:#8491a3}.notification-dot{width:9px;height:9px;flex:none;margin-top:6px;border-radius:50%;background:transparent}.notification-item.unread .notification-dot{background:#2dbca5}.notification-item.cancelled{opacity:.74}.notification-item.cancelled .notification-dot{background:#d86161}.notification-status{float:right;background:#fbe9e8;color:#b44747;border-radius:20px;padding:3px 9px;font-size:11px;font-weight:700}
</style>
<script>
// When an alert arrives while this page is open, reload once so the inbox
// immediately includes the same notification.
const toastArea = document.querySelector('.notification-toasts');
new MutationObserver(records => {
    if (records.some(record => record.addedNodes.length)) window.location.reload();
}).observe(toastArea, {childList: true});
</script>
@endpush
