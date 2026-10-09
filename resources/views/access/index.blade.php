@extends('layouts.app')

@section('content')
@php($sejourPlatforms = $platforms->where('platform_group', 'SEJOUR'))
@php($platformTOs = $platforms->where('platform_group', 'PLATFORM'))
<header><div><h1>Employee access</h1></div></header>

<section class="panel new-employee">
    <h2>Add employee</h2>
    <form method="POST" action="{{ route('access.store') }}">
        @csrf
        <div class="employee-form"><label>Full name<input name="name" required></label><label>Username<input name="username" required></label><label>Password<input name="password" type="password" required></label><label>Role<select name="role" required><option value="ENTRY_MANAGER">Data Entry Agent</option><option value="CONTRACTING">Contracting</option></select></label></div>
        <fieldset><legend>TO access <small>(Data Entry Agent only)</small></legend>
            <div class="access-checks platform-access">@foreach($platformTOs as $platform)<label><input type="checkbox" name="platform_ids[]" value="{{ $platform->id }}"> {{ $platform->label ?? $platform->name }}</label>@endforeach<label class="sejour-access"><input type="checkbox" name="sejour_access" value="1"><span><b>Sejour</b><small>Access to all {{ $sejourPlatforms->count() }} TOs</small></span></label></div>
        </fieldset>
        <button class="button" type="submit">Add employee</button>
    </form>
</section>

<section class="access-list">
@foreach($users as $user)
    @php($sejourAssigned = $sejourPlatforms->filter(fn($platform) => $user->platforms->contains('id', $platform->id))->count())
    <form method="POST" action="{{ route('access.update', $user) }}" class="panel access-card">
        @csrf @method('PUT')
        <div><h2>{{ $user->name }}</h2><p class="muted">{{ $user->role === 'CONTRACTING' ? 'Contracting' : 'Data Entry Agent' }} · Username: <b>{{ $user->username }}</b></p></div>
        @if($user->role === 'ENTRY_MANAGER')
            <div class="access-checks platform-access">@foreach($platformTOs as $platform)<label><input type="checkbox" name="platform_ids[]" value="{{ $platform->id }}" {{ $user->platforms->contains('id', $platform->id) ? 'checked' : '' }}> {{ $platform->label ?? $platform->name }}</label>@endforeach<label class="sejour-access"><input type="checkbox" name="sejour_access" value="1" {{ $sejourAssigned === $sejourPlatforms->count() && $sejourPlatforms->isNotEmpty() ? 'checked' : '' }}><span><b>Sejour</b><small>{{ $sejourAssigned }}/{{ $sejourPlatforms->count() }} TOs accessible</small></span></label></div>
        @endif
        <label class="password-change">New password <input type="password" name="password" placeholder="Leave empty to keep current password"></label><button class="button" type="submit">Save access</button>
    </form>
@endforeach
</section>
@endsection

@push('scripts')
<style>
.platform-access{grid-template-rows:repeat(4,auto);grid-auto-flow:column;align-items:start;padding-top:2px}.platform-access .sejour-access{display:flex;align-items:flex-start;gap:6px;cursor:pointer}.sejour-access input{margin-top:2px}.sejour-access b,.sejour-access small{display:block}.sejour-access b{color:#167b70;font-size:14px}.sejour-access small{margin-top:3px;color:#7185a0;font-size:12px}
</style>
@endpush
