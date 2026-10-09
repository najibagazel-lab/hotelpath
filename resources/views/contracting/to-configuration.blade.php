@extends('layouts.app')

@section('content')
<div class="to-configuration-page">
    <header><div><p class="eyebrow">CONTRACTING</p><h1>TO hotel configuration</h1><p class="muted">Select a TO, then choose the hotel contracts that work with it.</p></div></header>
    <form method="GET" class="panel to-picker"><label>TO<select name="platform" onchange="this.form.submit()">@foreach($platforms->groupBy('platform_group') as $group => $groupPlatforms)<optgroup label="{{ $group === 'PLATFORM' ? 'Platform' : ucfirst(strtolower($group)) }}">@foreach($groupPlatforms as $platform)<option value="{{ $platform->id }}" @selected($selectedPlatform?->id === $platform->id)>{{ $platform->label ?? $platform->name }}</option>@endforeach</optgroup>@endforeach</select></label></form>
    @if($selectedPlatform)
        @php($selectedContractIds = $contracts->filter(fn ($contract) => (bool) $contract->tasks->firstWhere('platform_id', $selectedPlatform->id)?->is_active)->pluck('id')->all())
        <section class="panel to-hotel-panel">
            <div class="to-hotel-heading"><div><h2>{{ $selectedPlatform->label ?? $selectedPlatform->name }}</h2><p>Hotels and periods that work with this TO.</p></div><span data-selected-count>{{ count($selectedContractIds) }} hotel contract{{ count($selectedContractIds) === 1 ? '' : 's' }} selected</span></div>
            <form method="POST" action="{{ route('contracting.to-configuration.update', $selectedPlatform) }}">@csrf @method('PATCH')
                <div class="to-hotel-help"><b>Checked:</b> this hotel contract is shown to {{ $selectedPlatform->label ?? $selectedPlatform->name }} and counted in its progress. <b>Unchecked:</b> it is excluded only from this TO.</div>
                <div class="to-list-tools"><input type="search" placeholder="Search hotel" data-to-hotel-search><button type="button" data-select-all-hotels>Select all</button><button type="button" data-clear-all-hotels>Remove all</button></div>
                <div class="to-hotel-list">@forelse($contracts as $contract)@php($task = $contract->tasks->firstWhere('platform_id', $selectedPlatform->id))<label class="to-hotel-row" data-to-hotel-row><input type="checkbox" name="contract_ids[]" value="{{ $contract->id }}" @checked($task?->is_active)><span class="to-hotel-check"></span><span><b>{{ $contract->hotel->name }}</b><small>{{ ucfirst(strtolower($contract->season->contract_type)) }} · {{ $contract->season->start_date->format('d/m/y') }} → {{ $contract->season->end_date->format('d/m/y') }}</small></span></label>@empty<p class="empty">No current received contracts.</p>@endforelse</div>
                <button class="button" type="submit">Save hotel configuration</button>
            </form>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<style>
.to-configuration-page{max-width:980px;margin:36px auto}.to-picker{padding:18px;margin:22px 0}.to-picker label{display:block;font-size:13px;font-weight:700;color:#33415a}.to-picker select{display:block;width:100%;margin-top:7px;padding:11px;border:1px solid #dbe2ec;border-radius:7px;background:#fff;font:14px 'DM Sans',sans-serif}.to-hotel-panel{padding:24px}.to-hotel-heading{display:flex;justify-content:space-between;gap:18px;padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid #e6edf4}.to-hotel-heading h2{margin:0;font-size:20px}.to-hotel-heading p{margin:6px 0 0;color:#7185a0;font-size:13px}.to-hotel-heading>span{height:max-content;padding:7px 10px;border-radius:16px;background:#edf8f6;color:#16877c;font-size:12px;font-weight:700}.to-hotel-help{margin-bottom:14px;color:#58708d;font-size:13px;line-height:1.5}.to-hotel-help b{color:#294e7c}.to-list-tools{display:flex;gap:8px;margin-bottom:12px}.to-list-tools input{flex:1;padding:9px 11px;border:1px solid #d7e1ec;border-radius:6px;font:13px 'DM Sans'}.to-list-tools button{padding:7px 11px;border:1px solid #d7e1ec;border-radius:6px;background:#fff;color:#426181;font:700 12px 'DM Sans';cursor:pointer}.to-list-tools button:last-child{color:#b44747}.to-hotel-list{max-height:440px;overflow:auto;margin-bottom:20px;border:1px solid #dfe7ef;border-radius:8px}.to-hotel-row{display:flex;align-items:center;gap:12px;padding:13px 15px;border-bottom:1px solid #e8eef4;cursor:pointer}.to-hotel-row:last-child{border-bottom:0}.to-hotel-row input{position:absolute;opacity:0}.to-hotel-check{width:18px;height:18px;border:1px solid #cbd8e6;border-radius:4px;background:#fff;flex:none}.to-hotel-row input:checked+.to-hotel-check{border-color:#1bb7a1;background:#1bb7a1}.to-hotel-row input:checked+.to-hotel-check::after{content:'✓';display:block;color:#fff;font-size:14px;font-weight:800;line-height:17px;text-align:center}.to-hotel-row b,.to-hotel-row small{display:block}.to-hotel-row b{color:#1e3555;font-size:14px}.to-hotel-row small{margin-top:4px;color:#7185a0;font-size:12px}@media(max-width:700px){.to-configuration-page{margin:24px 16px}.to-hotel-heading{align-items:flex-start;flex-direction:column}.to-list-tools{flex-wrap:wrap}.to-list-tools input{width:100%;flex-basis:100%}}
</style>
<script>
const hotelChecks = [...document.querySelectorAll('.to-hotel-row input')];
const updateSelectedCount = () => { const count = hotelChecks.filter(input => input.checked).length; document.querySelector('[data-selected-count]').textContent = `${count} hotel contract${count === 1 ? '' : 's'} selected`; };
document.querySelector('[data-select-all-hotels]')?.addEventListener('click', () => { hotelChecks.forEach(input => input.checked = true); updateSelectedCount(); });
document.querySelector('[data-clear-all-hotels]')?.addEventListener('click', () => { hotelChecks.forEach(input => input.checked = false); updateSelectedCount(); });
hotelChecks.forEach(input => input.addEventListener('change', updateSelectedCount));
document.querySelector('[data-to-hotel-search]')?.addEventListener('input', event => { const term = event.target.value.toLowerCase(); document.querySelectorAll('[data-to-hotel-row]').forEach(row => row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none'); });
</script>
@endpush
