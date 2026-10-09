@extends('layouts.app')

@section('content')
<div class="employee-page">
    <div class="employee-intro">
        <div><p class="eyebrow">2026/2027 CONTRACT ENTRY</p><h1>My tasks</h1></div><a class="button" href="{{ route('tasks.export', request()->query()) }}">Export my status</a>
    </div>

    @forelse($seasonGroups as $campaignYear => $campaignContracts)
        <section class="season-task-group" data-active-group="{{ $defaultPlatformGroup }}">
            <div class="season-group-title"><div class="platform-tabs" role="tablist"><button class="{{ $defaultPlatformGroup === 'SEJOUR' ? 'active' : '' }}" type="button" data-platform-tab="SEJOUR">Sejour</button><button class="{{ $defaultPlatformGroup === 'PLATFORM' ? 'active' : '' }}" type="button" data-platform-tab="PLATFORM">Platform</button></div><form class="task-hotel-filter" method="GET"><input name="hotel" value="{{ request('hotel') }}" placeholder="Search hotel" aria-label="Search hotel"></form></div>
            @if($loop->first)<section class="to-progress"><div class="to-progress-title"><b>TO entry progress</b><small>Entered contracts / received contracts · Year is included in Winter and Summer</small></div><div class="to-progress-list">@foreach($toProgress as $progress)<article class="to-progress-card" data-progress-group="{{ $progress->group }}"><span>{{ $progress->label }}</span><div class="to-progress-season"><b>Winter</b><strong>{{ $progress->winterEntered }}<i>/</i>{{ $progress->winterReceived }}</strong></div><div class="to-progress-season"><b>Summer</b><strong>{{ $progress->summerEntered }}<i>/</i>{{ $progress->summerReceived }}</strong></div><small>entered / received</small></article>@endforeach</div></section>@endif
            @php($platformGroups = $platforms->groupBy(fn ($platform) => $platform->platform_group ?? 'OTHER'))
            <div class="employee-board grouped-platform-board" data-active-group="{{ $defaultPlatformGroup }}"><table><thead><tr><th rowspan="2" class="hotel-column">Hotel</th><th rowspan="2" class="season-column">Season</th>@foreach($platformGroups as $group => $groupPlatforms)<th class="platform-group-title" data-platform-group="{{ $group }}" colspan="{{ $groupPlatforms->count() }}">{{ $group === 'PLATFORM' ? 'Platform' : ucfirst(strtolower($group)) }}</th>@endforeach</tr><tr>@foreach($platformGroups as $group => $groupPlatforms) @foreach($groupPlatforms as $platform)<th class="platform-column-title" data-platform-group="{{ $group }}">{{ $platform->label ?? $platform->name }}</th>@endforeach @endforeach</tr></thead><tbody>
                @foreach($campaignContracts->groupBy('hotel_id') as $hotelContracts)
                    @foreach($hotelContracts as $contract)
                        <tr>
                            @if($loop->first)<td rowspan="{{ $hotelContracts->count() }}" class="grouped-hotel-name">{{ $contract->hotel->name }}</td>@endif
                            @php(preg_match('/^(Winter|Summer|Year)/i', $contract->season->name, $seasonMatch))
                            @php($seasonType = $contract->season->contract_type ?? strtoupper($seasonMatch[1] ?? 'PERIOD'))
                            <td class="task-season season-{{ strtolower($seasonType) }}"><b>{{ ucfirst(strtolower($seasonType)) }}</b><small>{{ $contract->season->start_date->format('d/m/y') }} &rarr; {{ $contract->season->end_date->format('d/m/y') }}</small></td>
                            @foreach($platformGroups as $group => $groupPlatforms)
                            @foreach($groupPlatforms as $platform)
                                @php($task = $contract->tasks->where('is_active', true)->firstWhere('platform_id', $platform->id))
                                @php($allowed = in_array($platform->id, $allowedPlatformIds) && $task && $task->assigned_user_id === auth()->id())
                                <td class="employee-check" data-platform-group="{{ $group }}">
                                    @if($task && $allowed)
                                        <form method="POST" action="{{ route('tasks.update', $task) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $task->status === 'COMPLETED' ? 'PENDING' : 'COMPLETED' }}"><button class="matrix-check {{ $task->status === 'COMPLETED' ? 'checked' : '' }}" aria-label="Update {{ $platform->name }}">{{ $task->status === 'COMPLETED' ? '✓' : '' }}</button></form>
                                    @elseif($task)
                                        <span class="matrix-check locked {{ $task->status === 'COMPLETED' ? 'checked' : '' }}">{{ $task->status === 'COMPLETED' ? '✓' : '' }}</span>
                                    @else
                                        <span class="matrix-check locked unavailable">—</span>
                                    @endif
                                </td>
                            @endforeach
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody></table></div>
        </section>
    @empty
        <div class="empty">No active contracts.</div>
    @endforelse
</div>

<div class="hotel-modal" data-hotel-modal aria-hidden="true"><div class="modal-card"><button type="button" class="modal-close" data-close-hotel aria-label="Close">×</button><h2>Add hotel</h2><form method="POST" action="{{ route('contracts.store') }}">@csrf<div class="modal-grid"><label>Existing hotel<select name="hotel_id"><option value="">Select existing hotel</option>@foreach($hotels as $hotel)<option value="{{ $hotel->id }}">{{ $hotel->name }}</option>@endforeach</select></label><label>New hotel name<input name="hotel_name" placeholder="e.g. Iberostar Selection"></label><label>Season<div class="inline-season"><select name="season_names[]" required><option value="">Select season</option>@foreach($seasons as $season)<option value="{{ $season->name }}">{{ $season->name }}</option>@endforeach</select><button type="button" data-add-season>+</button></div></label><label>Received date<input name="received_date" type="date" value="{{ now()->format('Y-m-d') }}" required></label></div><button class="button" type="submit">Add hotel</button></form></div></div>
<div class="hotel-modal" data-delete-modal aria-hidden="true"><div class="modal-card delete-modal"><button type="button" class="modal-close" data-close-delete aria-label="Close">×</button><h2>Delete hotel contract</h2><p class="muted">Choose the hotel and season to remove.</p><div class="delete-list">@foreach($contracts as $contract)<div><span><b>{{ $contract->hotel->name }}</b><small>{{ $contract->season->name }}</small></span><form method="POST" action="{{ route('contracts.destroy', $contract) }}" onsubmit="return confirm('Delete this hotel contract?')">@csrf @method('DELETE')<button class="trash-button" title="Delete">Delete</button></form></div>@endforeach</div></div></div>
@endsection

@push('scripts')
<style>.task-season.season-winter b{color:#356fb5}.task-season.season-winter small{color:#5d87bd}.task-season.season-summer b,.task-season.season-summer small{color:#b44747}.task-season.season-year b{color:#16877c}.task-season.season-year small{color:#4e918a}.task-season.season-period b{color:#6d7890}.task-season.season-period small{color:#8290a4}</style>
<style>
.season-group-title{gap:9px;flex-wrap:wrap}.platform-tabs{display:flex;gap:8px}.platform-tabs button,.task-actions .button,.task-actions .delete-hotel-action{height:34px;border:1px solid #d6e0eb;border-radius:7px;padding:7px 14px;font:700 12px 'DM Sans';cursor:pointer}.platform-tabs button{background:#fff;color:#59708f}.platform-tabs button.active,.task-actions .button{background:#16877c;border-color:#16877c;color:#fff}.task-actions{display:flex;gap:8px}.task-actions .button{box-shadow:none}.task-actions .delete-hotel-action{background:#fff;color:#59708f}.platform-hidden{display:none!important}.task-hotel-filter{flex:1;max-width:330px;margin-left:auto}.task-hotel-filter input{width:100%;height:34px;border:1px solid #d6e0eb;border-radius:7px;background:#fff;padding:7px 12px;color:#24324a;font:14px 'DM Sans'}
.grouped-platform-board[data-active-group="SEJOUR"] [data-platform-group="PLATFORM"],.grouped-platform-board[data-active-group="PLATFORM"] [data-platform-group="SEJOUR"]{display:none!important}
.to-progress{margin:0 0 14px;padding:15px 17px;border:1px solid #dfe7f0;border-radius:12px;background:#fff}.to-progress-title{display:flex;align-items:baseline;gap:9px;margin-bottom:11px}.to-progress-title b{font:700 14px Manrope}.to-progress-title small,.to-progress-card small{color:#7b8ca5;font-size:11px}.to-progress-list{display:flex;gap:10px;flex-wrap:wrap}.to-progress-card{min-width:154px;padding:10px 12px;border-radius:8px;background:#f7f9fc;border:1px solid #e6edf4}.to-progress-card>span,.to-progress-card>small{display:block}.to-progress-card>span{margin-bottom:7px;font-size:10px;font-weight:700;letter-spacing:.8px;color:#52709a}.to-progress-season{display:flex;align-items:baseline;justify-content:space-between;gap:12px}.to-progress-season b{font-size:11px}.to-progress-season:first-of-type b{color:#356fb5}.to-progress-season:nth-of-type(2) b{color:#b44747}.to-progress-season strong{font:800 17px Manrope;color:#17304e}.to-progress-season strong i{font-style:normal;color:#9aa8ba;padding:0 2px}.to-progress-card>small{margin-top:5px}.season-task-group[data-active-group="SEJOUR"] [data-progress-group="PLATFORM"],.season-task-group[data-active-group="PLATFORM"] [data-progress-group="SEJOUR"]{display:none!important}
.grouped-platform-board{overflow-x:auto;border:1px solid #dfe7f0;border-radius:12px;background:#fff}.grouped-platform-board table{width:100%;min-width:0;table-layout:fixed;border-collapse:separate;border-spacing:0}.grouped-platform-board th{white-space:normal}.grouped-platform-board thead tr:first-child th{height:42px;background:#f8fafc;border-bottom:0;color:#294e7c;font-size:11px;letter-spacing:1px}.grouped-platform-board thead tr:nth-child(2) th{height:44px;background:#f8fafc;color:#526f9b;font-size:10px;letter-spacing:.8px;border-top:1px solid #e8eef5}.grouped-platform-board .hotel-column{width:18%;text-align:left;padding-left:16px}.grouped-platform-board .season-column{width:14%;text-align:left}.grouped-platform-board .platform-group-title{text-align:center;border-left:2px solid #dce6f1}.grouped-platform-board .platform-column-title{width:4.85%;padding:8px 2px;text-align:center}.grouped-platform-board tbody td{height:58px}.grouped-platform-board tbody .employee-check{padding:8px 2px}.grouped-platform-board .task-season b,.grouped-platform-board .task-season small{display:block}.grouped-platform-board .task-season b{font-size:14px}.grouped-platform-board .task-season small{margin-top:4px;color:#7185a4;font-size:12px}.grouped-platform-board .matrix-check.checked{font-size:0}.grouped-platform-board .matrix-check.checked::after{content:"\2713";font-size:15px}.grouped-platform-board thead tr:nth-child(2) th:nth-child(9){border-left:2px solid #dce6f1}@media(max-width:760px){.grouped-platform-board table{min-width:980px}.grouped-platform-board .hotel-column{width:210px}.grouped-platform-board .season-column{width:160px}.grouped-platform-board .platform-column-title{width:55px}}
</style>
<style>
/* Keep the two fixed headings on the same first header line as “Sejour”. */
.grouped-platform-board .hotel-column,.grouped-platform-board .season-column{vertical-align:top;padding-top:15px;text-align:center}.grouped-platform-board .hotel-column{padding-left:0}
.grouped-platform-board td.task-season{text-align:center}
.grouped-platform-board td.task-season{padding-left:8px;padding-right:8px}.grouped-platform-board .task-season small{white-space:nowrap}
.grouped-platform-board thead tr:nth-child(2) th:first-child{border-left:2px solid #dce6f1}
</style>
<script>
const modal=document.querySelector('[data-hotel-modal]'),deleteModal=document.querySelector('[data-delete-modal]');
document.querySelector('[data-open-hotel]')?.addEventListener('click',()=>modal?.classList.add('open'));
document.querySelector('[data-close-hotel]')?.addEventListener('click',()=>modal?.classList.remove('open'));
document.querySelector('[data-open-delete]')?.addEventListener('click',()=>deleteModal?.classList.add('open'));
document.querySelector('[data-close-delete]')?.addEventListener('click',()=>deleteModal?.classList.remove('open'));
[modal,deleteModal].filter(Boolean).forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open')}));
document.querySelector('[data-add-season]')?.addEventListener('click',()=>{const value=prompt('New season name (example: Winter 2028/2029)');if(!value?.trim())return;document.querySelector('.inline-season select').add(new Option(value.trim(),value.trim(),true,true));});
document.querySelectorAll('.season-task-group').forEach(section => {
  const board = section.querySelector('.grouped-platform-board');
  const setGroup = group => {
    board.dataset.activeGroup = group;
    section.dataset.activeGroup = group;
    board.querySelectorAll('[data-platform-group]').forEach(cell => cell.classList.toggle('platform-hidden', cell.dataset.platformGroup !== group));
    section.querySelectorAll('[data-platform-tab]').forEach(button => button.classList.toggle('active', button.dataset.platformTab === group));
  };
  section.querySelectorAll('[data-platform-tab]').forEach(button => button.onclick = () => setGroup(button.dataset.platformTab));
  setGroup(board.dataset.activeGroup);
});
document.querySelectorAll('.task-hotel-filter input').forEach(input => {
  let timer;
  input.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => input.form.submit(), 300); });
});
</script>
@endpush
