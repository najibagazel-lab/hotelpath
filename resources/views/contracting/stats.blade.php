@extends('layouts.app')

@section('content')
<header><div><p class="eyebrow">ADMINISTRATOR</p><h1>Contracting follow-up</h1></div></header>

<form class="stats-filter panel" method="GET">
    <label>Region<select name="region" onchange="this.form.submit()"><option value="">All regions</option>@foreach($regions as $region)<option value="{{ $region }}" @selected(request('region') === $region)>{{ $region }}</option>@endforeach</select></label>
</form>

<section class="metrics" style="grid-template-columns:repeat(3,minmax(0,1fr))">
    <article><span>WINTER CONTRACTS</span><strong>{{ $winterTotal }}</strong><small>{{ $winterReceived }} received · {{ $winterTotal - $winterReceived }} not received</small></article>
    <article><span>SUMMER CONTRACTS</span><strong>{{ $summerTotal }}</strong><small>{{ $summerReceived }} received · {{ $summerTotal - $summerReceived }} not received</small></article>
    <article><span>YEAR CONTRACTS</span><strong>{{ $yearTotal }}</strong><small>Included in Winter and Summer totals</small></article>
</section>

<section class="panel platform-panel">
    <div class="panel-title"><div><h2>Progress by contract type</h2></div></div>
    @foreach($byType as $item)
        <div class="platform-season-row">
            <div class="platform-name"><b>{{ ucfirst(strtolower($item->type)) }}</b><small>Includes annual contracts</small></div>
            <div class="season-progresses"><div class="season-platform-progress"><span>Contracts received</span><div class="bar"><i style="width:{{ $item->percent }}%"></i></div><b>{{ $item->percent }}%</b><small>{{ $item->done }}/{{ $item->total }}</small></div></div>
        </div>
    @endforeach
</section>

<section class="panel contracting-log">
    <div class="panel-title"><div><h2>Recent Contracting activity</h2></div></div>
    <table><thead><tr><th>Action</th><th>User</th><th>Details</th><th>Date</th></tr></thead><tbody>
        @forelse($logs as $log)
            <tr><td>{{ str_replace('CONTRACTING_CONTRACT_', '', $log->action) }}</td><td>{{ $log->user?->name ?? 'Deleted user' }}</td><td>{{ $log->details }}</td><td>{{ $log->created_at->format('d/m/y H:i') }}</td></tr>
        @empty
            <tr><td colspan="4" class="empty">No Contracting activity yet.</td></tr>
        @endforelse
    </tbody></table>
</section>
@endsection
