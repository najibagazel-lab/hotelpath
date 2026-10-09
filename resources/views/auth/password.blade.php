@extends('layouts.app')

@section('content')
<div class="password-page">
    <header><div><h1>Change password</h1><p class="muted">Choose a new password for your account.</p></div></header>
    <form class="panel password-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')
        <label>Current password<input type="password" name="current_password" required autofocus></label>
        @error('current_password')<p class="error">{{ $message }}</p>@enderror
        <label>New password<input type="password" name="password" required></label>
        @error('password')<p class="error">{{ $message }}</p>@enderror
        <label>Confirm new password<input type="password" name="password_confirmation" required></label>
        <button class="button" type="submit">Change password</button>
    </form>
</div>
@endsection
