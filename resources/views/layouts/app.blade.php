<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ config('app.name') }}</title><link rel="stylesheet" href="{{ asset('css/app.css') }}"><link rel="stylesheet" href="{{ asset('css/sidebar-active.css') }}"><link rel="stylesheet" href="{{ asset('css/responsive-layout.css') }}"></head>
<body><aside><div class="brand">{{ \Illuminate\Support\Str::before(config('app.name'), 'Path') }}<span>{{ \Illuminate\Support\Str::after(config('app.name'), 'Hotel') }}</span></div>@if(auth()->user()->role === 'ENTRY_MANAGER')<p class="label">MY WORKSPACE</p>@elseif(auth()->user()->role === 'CONTRACTING')<p class="label">CONTRACTING</p>@endif
@if(auth()->user()->role === 'ENTRY_MANAGER')
<a class="{{ request()->routeIs('tasks.index') ? 'active' : '' }}" href="{{ route('tasks.index') }}">My tasks</a><a class="notification-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}" href="{{ route('notifications.index') }}">Notifications @if($unread = auth()->user()->unreadNotifications()->count())<span class="notification-count">{{ $unread }}</span>@endif</a><a class="{{ request()->routeIs('tasks.history') ? 'active' : '' }}" href="{{ route('tasks.history') }}">History</a>
@elseif(auth()->user()->role === 'CONTRACTING')
<a class="{{ request()->routeIs('contracting.index') ? 'active' : '' }}" href="{{ route('contracting.index') }}">My contract follow-up</a><a class="{{ request()->routeIs('contracting.to-configuration') ? 'active' : '' }}" href="{{ route('contracting.to-configuration') }}">TO configuration</a><a class="{{ request()->routeIs('contracting.history') ? 'active' : '' }}" href="{{ route('contracting.history') }}">History</a>
@else
<a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Agent entry tracking</a><a class="{{ request()->routeIs('contracting.stats') ? 'active' : '' }}" href="{{ route('contracting.stats') }}">Contracting Suivi</a><a class="{{ request()->routeIs('contracting.index') ? 'active' : '' }}" href="{{ route('contracting.index') }}">Contract receipt follow-up</a><a class="{{ request()->routeIs('contracting.to-configuration') ? 'active' : '' }}" href="{{ route('contracting.to-configuration') }}">TO configuration</a><a class="{{ request()->routeIs('contracting.history') ? 'active' : '' }}" href="{{ route('contracting.history') }}">Contracting history</a><a class="{{ request()->routeIs('access.*') ? 'active' : '' }}" href="{{ route('access.index') }}">Employee access</a>
@endif
<div class="profile"><div class="profile-identity"><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->role === 'ENTRY_MANAGER' ? 'Data Entry Agent' : (auth()->user()->role === 'CONTRACTING' ? 'Contracting' : 'Administrator') }}</small></div><div class="profile-actions"><a class="change-password-link" href="{{ route('password.edit') }}">Change password</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout">Sign out</button></form></div></div></aside><main>@if(session('success'))<div class="flash">{{ session('success') }}</div>@endif @if($errors->any() && !(request()->routeIs('contracting.index') && old('form_context')))<div class="flash error">{{ $errors->first() }}</div>@endif @yield('content')</main><div class="notification-toasts" aria-live="polite" aria-atomic="true"></div><script>
(() => {
  const container = document.querySelector('.notification-toasts');
  const displayed = new Set();
  const show = notification => {
    if (displayed.has(notification.id)) return;
    displayed.add(notification.id);
    const toast = document.createElement('article');
    toast.className = 'notification-toast';
    toast.innerHTML = `<button type="button" aria-label="Close">×</button><b>${notification.title}</b><p>${notification.message}</p>`;
    toast.querySelector('button').onclick = () => toast.remove();
    container.append(toast);
    fetch(`/notifications/${notification.id}/read`, {method:'PATCH', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}});
    setTimeout(() => toast.remove(), 10000);
  };
  const checkNotifications = () => fetch('{{ route('notifications.unread') }}', {headers:{Accept:'application/json'}})
    .then(response => response.ok ? response.json() : [])
    .then(items => items.forEach(show))
    .catch(() => {});
  checkNotifications();
  setInterval(checkNotifications, 10000);
})();
</script>@stack('scripts')</body></html>
