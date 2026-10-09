@extends('layouts.app')

@section('content')
<header><div><h1>Agent entry tracking</h1></div></header>
<div class="dashboard-content">
    <section class="season-panel">
        <div class="panel-title hotel-to-title"><div><h2>Progress by hotel and TO</h2></div><label class="hotel-progress-filter"><input type="search" placeholder="Search hotel" aria-label="Search hotel progress" data-hotel-progress-filter></label></div>
        <div class="hotel-to-tabs" role="tablist">
            @foreach($platforms->groupBy('group') as $group => $groupPlatforms)
                <button type="button" class="{{ $loop->first ? 'active' : '' }}" data-hotel-to-group="{{ $group }}">{{ $group === 'PLATFORM' ? 'Platform' : ucfirst(strtolower($group)) }}</button>
            @endforeach
        </div>
        @foreach($platforms->groupBy('group') as $group => $groupPlatforms)
            <div class="panel hotel-to-group-panel {{ $loop->first ? '' : 'to-hidden' }}" data-hotel-to-group-panel="{{ $group }}">
                <div class="hotel-to-group-header"><span>HOTEL</span><span>TO ENTRY PROGRESS</span></div>
                @foreach($hotelsBySeason as $hotelContracts)
                    <article class="hotel-to-progress">
                        <div class="hotel-progress-name"><b>{{ $hotelContracts->first()->hotel->name }}</b><small>{{ $hotelContracts->pluck('season.name')->join(' · ') }}</small></div>
                        <div class="hotel-to-platforms">
                            @foreach($groupPlatforms as $platform)
                                @php($platformTasks = $hotelContracts->flatMap(fn($contract) => $contract->tasks->where('platform_id', $platform->id)->where('is_active', true)))
                                @php($total = $platformTasks->count())
                                @php($done = $platformTasks->where('status', 'COMPLETED')->count())
                                @php($percentage = $total ? round($done * 100 / $total) : 0)
                                <div class="hotel-to-platform-row"><b>{{ $platform->label }}</b><div class="hotel-progress-track"><i style="width:{{ $percentage }}%"></i></div><strong>{{ $percentage }}%</strong><small>{{ $done }}/{{ $total }}</small></div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        @endforeach
    </section>

    <section class="panel platform-panel">
        <div class="panel-title"><div><h2>Progress by TO and contract type</h2><p class="muted">Year contracts are included in both Winter and Summer.</p></div></div>
        @foreach($platforms->groupBy('group') as $group => $groupPlatforms)
            <section class="to-group-progress"><h3>{{ $group === 'PLATFORM' ? 'PLATFORM' : ucfirst(strtolower($group)) }}</h3><div class="to-group-rows">
                @foreach($groupPlatforms as $platform)
                    <div class="to-progress-row"><b class="to-short-name">{{ $platform->label }}</b>@foreach($platform->byType as $type)<div class="to-type-progress {{ strtolower($type->label) }}"><span>{{ $type->label }}</span><div class="bar"><i style="width:{{ $type->percent }}%"></i></div><b>{{ $type->percent }}%</b><small>{{ $type->done }}/{{ $type->total }}</small></div>@endforeach</div>
                @endforeach
            </div></section>
        @endforeach
    </section>
</div>
@endsection

@push('scripts')
<style>
.hotel-progress-filter input{width:300px;height:36px;border:1px solid #d6e0eb;border-radius:8px;background:#fff;padding:8px 12px;font:14px 'DM Sans';color:#24324a}.hotel-to-tabs{display:flex;gap:9px;margin-bottom:14px}.hotel-to-tabs button{border:1px solid #d6e0eb;border-radius:8px;background:#fff;color:#59708f;padding:11px 24px;font:700 13px 'DM Sans';cursor:pointer}.hotel-to-tabs button.active{background:#16877c;border-color:#16877c;color:#fff}.hotel-to-group-panel{padding:0;overflow:hidden}.hotel-to-group-header{display:grid;grid-template-columns:240px 1fr;gap:18px;padding:13px 18px;background:#f7f9fc;color:#667894;font-size:10px;font-weight:700;letter-spacing:1px}.hotel-to-progress{display:grid;grid-template-columns:240px 1fr;gap:18px;padding:17px 18px;border-top:1px solid #e7edf4}.hotel-to-platforms{display:grid;gap:9px}.hotel-to-platform-row{display:grid;grid-template-columns:78px 1fr 36px 38px;gap:9px;align-items:center}.hotel-to-platform-row>b{font-size:12px;color:#274c77}.hotel-to-platform-row strong{font-size:12px}.hotel-to-platform-row small{color:#8290a4;font-size:11px}.to-hidden{display:none!important}@media(max-width:760px){.hotel-progress-filter input{width:100%}.hotel-to-group-header{display:none}.hotel-to-progress{grid-template-columns:1fr}.hotel-to-platform-row{grid-template-columns:62px 1fr 32px 32px}.hotel-to-tabs button{padding:10px 18px}}
.platform-panel .muted{margin:5px 0 0}.to-group-progress{display:grid;grid-template-columns:135px 1fr;gap:20px;padding:20px 0;border-top:1px solid #e7edf4}.to-group-progress h3{align-self:center;margin:0;font:800 20px Manrope;letter-spacing:.8px;color:#17304e}.to-group-rows{display:grid;gap:11px}.to-progress-row{display:grid;grid-template-columns:92px repeat(2,minmax(260px,1fr));gap:16px;align-items:center}.to-short-name{font-size:12px;color:#274c77}.to-type-progress{display:grid;grid-template-columns:56px 1fr 33px 35px;gap:7px;align-items:center;font-size:12px}.to-type-progress>span{font-weight:700}.to-type-progress.winter>span{color:#356fb5}.to-type-progress.summer>span{color:#b44747}.to-type-progress b{font-size:12px}.to-type-progress small{color:#8290a4}@media(max-width:900px){.to-group-progress{grid-template-columns:1fr}.to-progress-row{grid-template-columns:70px 1fr}.to-type-progress{grid-column:2}.to-group-progress h3{font-size:17px}}@media(max-width:600px){.to-progress-row{grid-template-columns:1fr}.to-type-progress{grid-column:auto;grid-template-columns:50px 1fr 30px 30px}}
</style>
<script>
document.querySelectorAll('[data-hotel-to-group]').forEach(button => button.addEventListener('click', () => {
    document.querySelectorAll('[data-hotel-to-group]').forEach(item => item.classList.toggle('active', item === button));
    document.querySelectorAll('[data-hotel-to-group-panel]').forEach(panel => panel.classList.toggle('to-hidden', panel.dataset.hotelToGroupPanel !== button.dataset.hotelToGroup));
}));
document.querySelector('[data-hotel-progress-filter]')?.addEventListener('input', event => {
    const term = event.target.value.trim().toLowerCase();
    document.querySelectorAll('.hotel-to-progress').forEach(row => {
        const hotelName = row.querySelector('.hotel-progress-name b')?.textContent.toLowerCase() ?? '';
        row.style.display = hotelName.includes(term) ? '' : 'none';
    });
});
</script>
@endpush
