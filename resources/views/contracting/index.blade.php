@extends('layouts.app')

@section('content')
<div class="contracting-page">
    <div class="employee-intro">
        <div><p class="eyebrow">CONTRACTING</p><h1>{{ $history ? 'History' : 'Contract receipt follow-up' }}</h1></div>
        @unless($history)<div class="contracting-actions"><a class="button" href="{{ route('contracting.export', request()->query()) }}">Export Excel</a><div class="period-actions-menu"><button class="button" type="button" data-toggle-period-actions>Manage periods</button><div class="period-actions-list" data-period-actions-list><button type="button" data-open-hotel>+ Add hotel</button><button type="button" data-open-period>+ Add period</button><button type="button" data-open-edit-period>Modify period</button><button type="button" class="danger-action" data-open-delete-period>Delete period</button></div></div></div>@endunless
    </div>

    @unless($history)
        <section class="metrics received-summary">
            <article><span>WINTER CONTRACTS</span><strong>{{ $winterTotal }}</strong><small>{{ $winterReceived }} received · {{ $winterNotReceived }} not received</small></article>
            <article><span>SUMMER CONTRACTS</span><strong>{{ $summerTotal }}</strong><small>{{ $summerReceived }} received · {{ $summerNotReceived }} not received</small></article>
            <article><span>YEAR CONTRACTS</span><strong>{{ $yearTotal }}</strong><small>Included in Winter and Summer totals</small></article>
        </section>
    @endunless

    <form class="contracting-filters panel" method="GET">
        <input name="hotel" value="{{ request('hotel') }}" placeholder="Search hotel" autocomplete="off" oninput="clearTimeout(window.hotelSearchTimer); window.hotelSearchTimer = setTimeout(() => this.form.submit(), 450)">
        <select name="region" onchange="this.form.submit()"><option value="">All regions</option>@foreach($regions as $region)<option value="{{ $region }}" @selected(request('region') === $region)>{{ $region }}</option>@endforeach</select>
        <select name="contract_type" onchange="this.form.submit()">
            <option value="">All contract types</option>
            @foreach($periodTypes as $periodType)
                <option value="{{ $periodType }}" @selected(request('contract_type') === $periodType)>{{ ucfirst(strtolower($periodType)) }}</option>
            @endforeach
        </select>
        <select name="receipt_status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="received" @selected(request('receipt_status') === 'received')>Received</option>
            <option value="not_received" @selected(request('receipt_status') === 'not_received')>Not received</option>
        </select>
    </form>

    <section class="employee-board contracting-board">
        <table>
            <thead><tr><th>Hotel</th><th>Period</th><th>Contract type</th><th>Purchase contract received</th></tr></thead>
            <tbody>
                @if($entries->isEmpty())
                    <tr><td colspan="4" class="empty">No hotel found.</td></tr>
                @else
                    @foreach($entries->groupBy('hotel.id') as $hotelEntries)
                        @foreach($hotelEntries as $entry)
                            <tr>
                                @if($loop->first)
                                    <td rowspan="{{ $hotelEntries->count() }}" class="contracting-hotel-name">{{ $entry->hotel->name }}</td>
                                @endif
                                <td>{{ $entry->season->start_date->format('d/m/y') }} → {{ $entry->season->end_date->format('d/m/y') }}</td>
                                <td>{{ ucfirst(strtolower($entry->season->contract_type)) }}</td>
                                <td class="employee-check">
                                    @if($history)
                                        <span class="matrix-check locked {{ $entry->contract?->purchase_contract_received ? 'checked' : '' }}">{{ $entry->contract?->purchase_contract_received ? '✓' : '' }}</span>
                                    @else
                                    <form method="POST" action="{{ route('contracting.entries.update', [$entry->hotel, $entry->season]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="purchase_contract_received" value="{{ $entry->contract?->purchase_contract_received ? '0' : '1' }}">
                                        <button class="matrix-check {{ $entry->contract?->purchase_contract_received ? 'checked' : '' }}" aria-label="Toggle purchase contract received">{{ $entry->contract?->purchase_contract_received ? '✓' : '' }}</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </section>
</div>

@php($hotelFormError = $errors->any() && old('form_context') === 'hotel')
@php($periodFormError = $errors->any() && old('form_context') === 'period')
@unless($history)<div class="hotel-modal {{ $hotelFormError ? 'open' : '' }}" data-hotel-modal aria-hidden="{{ $hotelFormError ? 'false' : 'true' }}">
    <div class="modal-card">
        <button type="button" class="modal-close" data-close-hotel>×</button>
        <h2>Add hotel</h2>
        <form method="POST" action="{{ route('contracting.hotels.store') }}">
            @csrf
            <input type="hidden" name="form_context" value="hotel">
            @if($errors->any() && old('form_context') === 'hotel')<p class="error">{{ $errors->first() }}</p>@endif
            <label class="modal-field">Hotel name<input name="name" value="{{ old('name') }}" required autofocus></label>
            <label class="modal-field">Region<select name="destination" required><option value="">Select region</option>@foreach($regions as $region)<option value="{{ $region }}">{{ $region }}</option>@endforeach</select></label>
            <label class="modal-field">Contract type<select name="contract_type" required><option value="">Select type</option><option value="WINTER">Winter</option><option value="SUMMER">Summer</option><option value="YEAR">Year</option></select></label>
            <div class="modal-grid"><label>Start date<input name="start_date" type="date" required></label><label>End date<input name="end_date" type="date" required></label></div>
            <button class="button" type="submit">Add hotel</button>
        </form>
    </div>
</div>
<div class="hotel-modal {{ $periodFormError ? 'open' : '' }}" data-period-modal aria-hidden="{{ $periodFormError ? 'false' : 'true' }}">
    <div class="modal-card">
        <button type="button" class="modal-close" data-close-period>×</button>
        <h2>Add period for one hotel</h2>
        <form method="POST" action="{{ route('contracting.periods.store') }}">
            @csrf
            <input type="hidden" name="form_context" value="period">
            @if($errors->any() && old('form_context') === 'period')<p class="error">{{ $errors->first() }}</p>@endif
            <label class="modal-field">Hotel<select name="hotel_id" required><option value="">Select hotel</option>@foreach($hotels as $hotel)<option value="{{ $hotel->id }}" @selected(old('hotel_id') == $hotel->id)>{{ $hotel->name }}</option>@endforeach</select></label>
            <label class="modal-field">Contract type<select name="contract_type" required><option value="">Select type</option><option value="WINTER" @selected(old('contract_type') === 'WINTER')>Winter</option><option value="SUMMER" @selected(old('contract_type') === 'SUMMER')>Summer</option><option value="YEAR" @selected(old('contract_type') === 'YEAR')>Year</option></select></label>
            <div class="modal-grid"><label>Start date<input name="start_date" type="date" value="{{ old('start_date') }}" required></label><label>End date<input name="end_date" type="date" value="{{ old('end_date') }}" required></label></div>
            <button class="button" type="submit">Add period</button>
        </form>
    </div>
</div>
<div class="hotel-modal" data-delete-period-modal aria-hidden="true">
    <div class="modal-card delete-modal">
        <button type="button" class="modal-close" data-close-delete-period>×</button>
        <h2>Delete period</h2>
        <p class="muted">Choose a hotel period to remove.</p>
        <input type="search" placeholder="Search hotel" autocomplete="off" data-delete-period-search style="width:100%;margin:12px 0;padding:11px;border:1px solid #dbe2ec;border-radius:7px;font:14px 'DM Sans',sans-serif">
        <div class="delete-list">
            @forelse($customPeriods as $period)
                <div><span><b>{{ $period->hotel->name }}</b><small>{{ $period->start_date->format('d/m/y') }} → {{ $period->end_date->format('d/m/y') }}</small></span><form method="POST" action="{{ route('contracting.periods.destroy', $period) }}" onsubmit="return confirm('Delete this hotel period?')">@csrf @method('DELETE')<button class="trash-button" title="Delete">Delete</button></form></div>
            @empty
                <p class="empty">No private hotel period to delete.</p>
            @endforelse
        </div>
    </div>
</div>
<div class="hotel-modal" data-edit-period-modal aria-hidden="true">
    <div class="modal-card">
        <button type="button" class="modal-close" data-close-edit-period>×</button>
        <h2>Modify period</h2>
        <form method="POST" data-edit-period-form data-action-template="{{ url('/contracting/periods/__SEASON__') }}">
            @csrf @method('PATCH')
            <label class="modal-field">Period<select data-edit-period-select required><option value="">Select a period</option>@foreach($customPeriods as $period)<option value="{{ $period->id }}" data-type="{{ $period->contract_type }}" data-start="{{ $period->start_date->format('Y-m-d') }}" data-end="{{ $period->end_date->format('Y-m-d') }}">{{ $period->hotel->name }} — {{ ucfirst(strtolower($period->contract_type)) }} ({{ $period->start_date->format('d/m/y') }} → {{ $period->end_date->format('d/m/y') }})</option>@endforeach</select></label>
            <label class="modal-field">Contract type<select name="contract_type" data-edit-period-type required><option value="WINTER">Winter</option><option value="SUMMER">Summer</option><option value="YEAR">Year</option></select></label>
            <div class="modal-grid"><label>Start date<input name="start_date" data-edit-period-start type="date" required></label><label>End date<input name="end_date" data-edit-period-end type="date" required></label></div>
            <button class="button" type="submit">Save changes</button>
        </form>
    </div>
</div>
@endunless
@endsection

@unless($history)
<style>.matrix-check.checked{font-size:0}.matrix-check.checked::after{content:"\2713";font-size:15px}.received-summary{grid-template-columns:repeat(3,190px);justify-content:start;gap:10px;margin-bottom:14px}.received-summary article{min-height:0;padding:12px 14px;border-radius:9px}.received-summary strong{margin:9px 0 4px;font-size:31px}.received-summary small{font-size:13px}@media(max-width:700px){.received-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}</style>
@push('scripts')
<script>
const modal = document.querySelector('[data-hotel-modal]');
const periodModal = document.querySelector('[data-period-modal]');
const deletePeriodModal = document.querySelector('[data-delete-period-modal]');
const editPeriodModal = document.querySelector('[data-edit-period-modal]');
const periodActionsMenu = document.querySelector('[data-period-actions-list]');
document.querySelector('[data-toggle-period-actions]').onclick = () => periodActionsMenu.classList.toggle('open');
document.querySelector('[data-open-hotel]').onclick = () => modal.classList.add('open');
document.querySelector('[data-close-hotel]').onclick = () => modal.classList.remove('open');
document.querySelector('[data-open-period]').onclick = () => periodModal.classList.add('open');
document.querySelector('[data-close-period]').onclick = () => periodModal.classList.remove('open');
document.querySelector('[data-open-delete-period]').onclick = () => deletePeriodModal.classList.add('open');
document.querySelector('[data-close-delete-period]').onclick = () => deletePeriodModal.classList.remove('open');
document.querySelector('[data-open-edit-period]').onclick = () => editPeriodModal.classList.add('open');
document.querySelector('[data-close-edit-period]').onclick = () => editPeriodModal.classList.remove('open');
modal.onclick = event => { if (event.target === modal) modal.classList.remove('open'); };
periodModal.onclick = event => { if (event.target === periodModal) periodModal.classList.remove('open'); };
deletePeriodModal.onclick = event => { if (event.target === deletePeriodModal) deletePeriodModal.classList.remove('open'); };
editPeriodModal.onclick = event => { if (event.target === editPeriodModal) editPeriodModal.classList.remove('open'); };
const editPeriodSelect = document.querySelector('[data-edit-period-select]');
const deletePeriodSearch = document.querySelector('[data-delete-period-search]');
deletePeriodSearch.oninput = () => {
    const search = deletePeriodSearch.value.trim().toLowerCase();
    document.querySelectorAll('[data-delete-period-modal] .delete-list > div').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none';
    });
};
editPeriodSelect.onchange = () => {
    const option = editPeriodSelect.options[editPeriodSelect.selectedIndex];
    const form = document.querySelector('[data-edit-period-form]');
    form.action = form.dataset.actionTemplate.replace('__SEASON__', option.value);
    document.querySelector('[data-edit-period-type]').value = option.dataset.type || 'WINTER';
    document.querySelector('[data-edit-period-start]').value = option.dataset.start || '';
    document.querySelector('[data-edit-period-end]').value = option.dataset.end || '';
};
document.addEventListener('click', event => { if (!event.target.closest('.period-actions-menu')) periodActionsMenu.classList.remove('open'); });
</script>
@endpush
@endunless
