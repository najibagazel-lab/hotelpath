<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Contract;
use App\Models\ContractTask;
use App\Models\Hotel;
use App\Models\Platform;
use App\Models\Season;
use App\Models\User;
use App\Notifications\ContractReceivedNotification;
use App\Support\SimpleXlsx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ContractingController extends Controller
{
    /**
     * Dates remain hotel-specific. This catches only unambiguous Winter,
     * Summer, and near annual periods selected with the wrong type.
     */
    private function validateContractType(array $data): void
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $days = $startDate->diffInDays($endDate);
        $expectedType = match (true) {
            $days >= 300 => 'YEAR',
            in_array($startDate->month, [11, 12, 1, 2, 3], true) => 'WINTER',
            in_array($startDate->month, [4, 5, 6, 7, 8, 9, 10], true) => 'SUMMER',
        };

        if ($expectedType !== null && $data['contract_type'] !== $expectedType) {
            throw ValidationException::withMessages([
                'contract_type' => 'Contract type is incorrect: this period is '.ucfirst(strtolower($expectedType)).'.',
            ]);
        }
    }

    private function authorizeContracting(): void
    {
        abort_unless(in_array(auth()->user()->role, ['ADMIN', 'MANAGER', 'CONTRACTING'], true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeContracting();

        return $this->renderEntries($request, false);
    }

    public function export(Request $request)
    {
        $this->authorizeContracting();
        $hotels = Hotel::where('active', true)
            ->when($request->filled('hotel'), fn ($query) => $query->where('name', 'like', '%'.$request->hotel.'%'))
            ->when($request->filled('region'), fn ($query) => $query->where('destination', $request->region))
            ->get();
        $seasons = Season::with('hotel')->current()->whereNotNull('hotel_id')
            ->whereIn('hotel_id', $hotels->pluck('id'))
            ->when($request->filled('contract_type'), fn ($query) => $query->where('contract_type', $request->contract_type))
            ->orderBy('hotel_id')->orderBy('start_date')->get();
        $contracts = Contract::whereIn('hotel_id', $hotels->pluck('id'))
            ->whereIn('season_id', $seasons->pluck('id'))->get()
            ->keyBy(fn ($contract) => $contract->hotel_id.'-'.$contract->season_id);
        $status = $request->input('receipt_status');
        $rows = $seasons->map(function ($season) use ($contracts) {
            $contract = $contracts->get($season->hotel_id.'-'.$season->id);
            return [$season->hotel->name, $season->hotel->destination, ucfirst(strtolower($season->contract_type)), $season->start_date->format('d/m/Y'), $season->end_date->format('d/m/Y'), $contract?->purchase_contract_received ? 'Received' : 'Not received', $contract?->received_date?->format('d/m/Y') ?? ''];
        })->filter(fn ($row) => $status === 'received' ? $row[5] === 'Received' : ($status === 'not_received' ? $row[5] === 'Not received' : true));

        $suffix = $status === 'received' ? 'received' : ($status === 'not_received' ? 'not-received' : 'all');
        return SimpleXlsx::download('contracting-'.$suffix.'-'.now()->format('Y-m-d').'.xlsx', ['Hotel', 'Region', 'Contract type', 'Start date', 'End date', 'Receipt status', 'Received date'], $rows);
    }

    public function history(Request $request)
    {
        $this->authorizeContracting();

        return $this->renderEntries($request, true);
    }

    public function toConfiguration(Request $request)
    {
        $this->authorizeContracting();

        $contracts = Contract::with(['hotel', 'season', 'tasks.platform'])
            ->where('purchase_contract_received', true)
            ->whereHas('season', fn ($season) => $season->current())
            ->when($request->filled('hotel'), fn ($query) => $query->whereHas('hotel', fn ($hotel) => $hotel->where('name', 'like', '%'.$request->hotel.'%')))
            ->orderBy('hotel_id')->orderBy('season_id')->get();
        $platforms = Platform::where('active', true)
            ->orderByRaw("CASE platform_group WHEN 'SEJOUR' THEN 1 WHEN 'PLATFORM' THEN 2 ELSE 3 END")
            ->orderBy('label')->get();
        $selectedPlatform = $platforms->firstWhere('id', (int) $request->input('platform')) ?? $platforms->first();

        return view('contracting.to-configuration', compact('contracts', 'platforms', 'selectedPlatform'));
    }

    public function updateToConfiguration(Request $request, Platform $platform)
    {
        $this->authorizeContracting();
        abort_unless($platform->active, 404);

        $data = $request->validate([
            'contract_ids' => 'nullable|array',
            'contract_ids.*' => 'integer|exists:contracts,id',
        ]);
        $selectedIds = collect($data['contract_ids'] ?? [])->map(fn ($id) => (int) $id);
        $currentContracts = Contract::where('purchase_contract_received', true)
            ->whereHas('season', fn ($season) => $season->current())
            ->get();

        DB::transaction(function () use ($platform, $selectedIds, $currentContracts) {
            $owner = $platform->users()->where('active', true)->first();
            $currentContracts->each(function ($contract) use ($platform, $owner, $selectedIds) {
                $task = ContractTask::firstOrCreate(
                    ['contract_id' => $contract->id, 'platform_id' => $platform->id],
                    ['assigned_user_id' => $owner?->id, 'is_active' => true]
                );
                $task->update(['is_active' => $selectedIds->contains($contract->id)]);
            });
        });

        return redirect()->route('contracting.to-configuration', ['platform' => $platform->id])
            ->with('success', 'TO hotel configuration saved. Only selected hotels are included in progress.');
    }

    private function renderEntries(Request $request, bool $history)
    {

        $regions = Hotel::where('active', true)->whereNotNull('destination')->distinct()->orderBy('destination')->pluck('destination');
        $hotels = Hotel::where('active', true)
            ->when($request->filled('hotel'), fn ($query) => $query->where('name', 'like', '%'.$request->hotel.'%'))
            ->when($request->filled('region'), fn ($query) => $query->where('destination', $request->region))
            ->orderBy('name')
            ->get();
        $seasons = Season::with('hotel')
            ->whereNotNull('hotel_id')
            ->when($history, fn ($query) => $query->where('active', true)->whereDate('end_date', '<', today()), fn ($query) => $query->current())
            ->when($request->filled('contract_type'), fn ($query) => $query->where('contract_type', $request->contract_type))
            ->orderBy('hotel_id')->orderBy('start_date')
            ->get();
        $contracts = Contract::whereIn('hotel_id', $hotels->pluck('id'))
            ->whereIn('season_id', $seasons->pluck('id'))
            ->get()
            ->keyBy(fn ($contract) => $contract->hotel_id.'-'.$contract->season_id);

        $entries = $hotels->flatMap(function ($hotel) use ($seasons, $contracts) {
            $hotelSeasons = $seasons->where('hotel_id', $hotel->id);
            return $hotelSeasons->map(fn ($season) => (object) ['hotel' => $hotel, 'season' => $season, 'contract' => $contracts->get($hotel->id.'-'.$season->id)]);
        });
        if ($request->input('receipt_status') === 'received') {
            $entries = $entries->filter(fn ($entry) => (bool) $entry->contract?->purchase_contract_received);
        } elseif ($request->input('receipt_status') === 'not_received') {
            $entries = $entries->filter(fn ($entry) => ! (bool) $entry->contract?->purchase_contract_received);
        }

        $customPeriods = Season::with('hotel')->whereNotNull('hotel_id')->where('active', true)->orderBy('hotel_id')->orderBy('start_date')->get();
        $periodTypes = ['WINTER', 'SUMMER', 'YEAR'];
        $activePeriods = Season::current()->whereNotNull('hotel_id')->get(['id', 'contract_type']);
        $receivedContracts = Contract::where('purchase_contract_received', true)->whereIn('season_id', $activePeriods->pluck('id'))->get(['season_id']);
        $winterPeriods = $activePeriods->whereIn('contract_type', ['WINTER', 'YEAR']);
        $summerPeriods = $activePeriods->whereIn('contract_type', ['SUMMER', 'YEAR']);
        $winterTotal = $winterPeriods->count();
        $summerTotal = $summerPeriods->count();
        $yearTotal = $activePeriods->where('contract_type', 'YEAR')->count();
        $winterReceived = $receivedContracts->whereIn('season_id', $winterPeriods->pluck('id'))->count();
        $summerReceived = $receivedContracts->whereIn('season_id', $summerPeriods->pluck('id'))->count();
        $winterNotReceived = $winterTotal - $winterReceived;
        $summerNotReceived = $summerTotal - $summerReceived;

        return view('contracting.index', compact('entries', 'history', 'hotels', 'customPeriods', 'regions', 'periodTypes', 'winterReceived', 'summerReceived', 'winterTotal', 'summerTotal', 'yearTotal', 'winterNotReceived', 'summerNotReceived'));
    }

    public function entries(Request $request)
    {
        $this->authorizeContracting();
        $hotels = Hotel::where('active', true)
            ->when($request->filled('hotel'), fn ($query) => $query->where('name', 'like', '%'.$request->hotel.'%'))
            ->orderBy('name')->get();
        $seasons = Season::with('hotel')->current()->whereNotNull('hotel_id')
            ->when($request->filled('contract_type'), fn ($query) => $query->where('contract_type', $request->contract_type))
            ->orderBy('start_date')->get();
        $contracts = Contract::whereIn('hotel_id', $hotels->pluck('id'))->whereIn('season_id', $seasons->pluck('id'))
            ->get()->keyBy(fn ($contract) => $contract->hotel_id.'-'.$contract->season_id);

        return response()->json($seasons->whereIn('hotel_id', $hotels->pluck('id'))->map(fn ($season) => [
            'hotelId' => $season->hotel_id,
            'hotelName' => $season->hotel->name,
            'seasonId' => $season->id,
            'seasonLabel' => $season->name,
            'seasonStartDate' => $season->start_date->toDateString(),
            'seasonEndDate' => $season->end_date->toDateString(),
            'purchaseContractReceived' => (bool) optional($contracts->get($season->hotel_id.'-'.$season->id))->purchase_contract_received,
            'contractType' => $season->contract_type,
        ])->values());
    }

    public function storeHotel(Request $request)
    {
        $this->authorizeContracting();
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'destination' => 'required|string|max:120',
            'contract_type' => 'required|in:WINTER,SUMMER,YEAR',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $this->validateContractType($data);
        $existingHotel = Hotel::where('name', trim($data['name']))->first();

        if ($existingHotel && Season::where('hotel_id', $existingHotel->id)->where('active', true)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'This hotel already has an active contract period. Use Add period to add another period.',
            ]);
        }

        DB::transaction(function () use ($data, $existingHotel) {
            $hotel = $existingHotel ?: Hotel::create(['name' => trim($data['name']), 'destination' => $data['destination'], 'active' => true]);
            if ($existingHotel) {
                $hotel->update(['destination' => $data['destination'], 'active' => true]);
            }
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $periodName = ucfirst(strtolower($data['contract_type'])).' '.$startDate->year.'/'.$endDate->year;

            Season::create([
                'hotel_id' => $hotel->id,
                'contract_type' => $data['contract_type'],
                'name' => $periodName.' - '.$hotel->name,
                'code' => 'HOTEL-'.$hotel->id.'-'.strtoupper(uniqid()),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'active' => true,
            ]);
        });

        return $request->expectsJson()
            ? response()->json(['message' => 'Hotel and first contract period added.'], 201)
            : back()->with('success', 'Hotel and first contract period added.');
    }

    public function storePeriod(Request $request)
    {
        $this->authorizeContracting();
        $data = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'contract_type' => 'required|in:WINTER,SUMMER,YEAR',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $this->validateContractType($data);

        $hotel = Hotel::findOrFail($data['hotel_id']);
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $periodName = ucfirst(strtolower($data['contract_type'])).' '.$startDate->year.'/'.$endDate->year;

        Season::create([
            'hotel_id' => $data['hotel_id'],
            'contract_type' => $data['contract_type'],
            'name' => $periodName.' - '.$hotel->name,
            'code' => 'HOTEL-'.$data['hotel_id'].'-'.strtoupper(uniqid()),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'active' => true,
        ]);

        return back()->with('success', 'Period added for this hotel only.');
    }

    public function destroyPeriod(Season $season)
    {
        $this->authorizeContracting();
        abort_unless($season->hotel_id !== null, 403);

        if ($season->contracts()->exists()) {
            $season->update(['active' => false]);
            return back()->with('success', 'Period removed from active follow-up. Existing contracts and tasks were preserved.');
        }

        $season->delete();
        return back()->with('success', 'Period deleted.');
    }

    public function updatePeriod(Request $request, Season $season)
    {
        $this->authorizeContracting();
        abort_unless($season->hotel_id !== null, 403);

        $data = $request->validate([
            'contract_type' => 'required|in:WINTER,SUMMER,YEAR',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $this->validateContractType($data);

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);
        $periodName = ucfirst(strtolower($data['contract_type'])).' '.$startDate->year.'/'.$endDate->year;
        $season->update([
            'contract_type' => $data['contract_type'],
            'name' => $periodName.' - '.$season->hotel->name,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        return back()->with('success', 'Period updated.');
    }

    public function updateEntry(Request $request, Hotel $hotel, Season $season)
    {
        $this->authorizeContracting();
        // Older contracts can still reference a legacy global season. New periods
        // are always hotel-specific, but keeping this compatibility avoids losing
        // access to existing contract records.
        abort_unless($hotel->active && $season->active && ($season->hotel_id === null || $season->hotel_id === $hotel->id), 404);
        $received = $request->boolean('purchase_contract_received');

        DB::transaction(function () use ($hotel, $season, $received) {
            $contract = Contract::firstOrNew(['hotel_id' => $hotel->id, 'season_id' => $season->id]);
            $wasReceived = (bool) $contract->purchase_contract_received;
            $contract->fill([
                'purchase_contract_received' => $received,
                'received_date' => $received ? now() : $contract->received_date,
                'contracting_user_id' => auth()->id(),
                'contracting_checked_at' => now(),
                'created_by' => $contract->created_by ?: auth()->id(),
            ]);
            $contract->save();

            if ($received) {
                foreach (Platform::where('active', true)->get() as $platform) {
                    $owner = $platform->users()->where('active', true)->first();
                    ContractTask::firstOrCreate(
                        ['contract_id' => $contract->id, 'platform_id' => $platform->id],
                        ['assigned_user_id' => $owner?->id]
                    );
                }

                if (! $wasReceived) {
                    $recipients = User::where('active', true)
                        ->whereIn('id', $contract->tasks()->whereNotNull('assigned_user_id')->pluck('assigned_user_id')->unique())
                        ->get();

                    Notification::send($recipients, new ContractReceivedNotification($contract->loadMissing('hotel', 'season')));
                }
            } elseif ($wasReceived) {
                // Keep the original message as an auditable correction instead
                // of removing it from the agent's notification history.
                DB::table('notifications')
                    ->where('type', ContractReceivedNotification::class)
                    ->orderBy('created_at')
                    ->get()
                    ->each(function ($notification) use ($contract) {
                        $data = json_decode($notification->data, true) ?: [];

                        if ((int) ($data['contract_id'] ?? 0) !== $contract->id) {
                            return;
                        }

                        $data['cancelled'] = true;
                        $data['title'] = 'Contract cancelled';
                        $data['message'] = "The receipt for \"{$contract->hotel->name}\" was cancelled.";

                        DB::table('notifications')->where('id', $notification->id)->update([
                            'data' => json_encode($data),
                            'read_at' => null,
                            'updated_at' => now(),
                        ]);
                    });
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'contract_id' => $contract->id,
                'action' => $received ? 'CONTRACTING_CONTRACT_RECEIVED' : 'CONTRACTING_CONTRACT_UNCHECKED',
                'details' => "{$hotel->name} — {$season->name}",
            ]);
        });

        $message = $received ? 'Purchase contract received. Tasks were created.' : 'Purchase contract marked as not received. Existing tasks were kept.';
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'purchaseContractReceived' => $received])
            : back()->with('success', $message);
    }

    public function stats(Request $request)
    {
        $this->authorizeContracting();
        abort_unless(auth()->user()->role !== 'CONTRACTING', 403);
        $regions = Hotel::where('active', true)->whereNotNull('destination')->distinct()->orderBy('destination')->pluck('destination');
        $hotels = Hotel::where('active', true)
            ->when($request->filled('region'), fn ($query) => $query->where('destination', $request->region))
            ->orderBy('name')
            ->get();
        $seasons = Season::with('hotel')->current()->whereNotNull('hotel_id')
            ->whereIn('hotel_id', $hotels->pluck('id'))->orderBy('start_date')->get();
        $contracts = Contract::whereIn('season_id', $seasons->pluck('id'))->get();
        $total = $seasons->count();
        $received = $contracts->where('purchase_contract_received', true)->count();
        $winterPeriods = $seasons->whereIn('contract_type', ['WINTER', 'YEAR']);
        $summerPeriods = $seasons->whereIn('contract_type', ['SUMMER', 'YEAR']);
        $yearPeriods = $seasons->where('contract_type', 'YEAR');
        $winterTotal = $winterPeriods->count();
        $summerTotal = $summerPeriods->count();
        $yearTotal = $yearPeriods->count();
        $winterReceived = $contracts->whereIn('season_id', $winterPeriods->pluck('id'))->where('purchase_contract_received', true)->count();
        $summerReceived = $contracts->whereIn('season_id', $summerPeriods->pluck('id'))->where('purchase_contract_received', true)->count();
        $byType = collect([
            'WINTER' => ['WINTER', 'YEAR'],
            'SUMMER' => ['SUMMER', 'YEAR'],
        ])->map(function ($includedTypes, $type) use ($seasons, $contracts) {
            $typePeriods = $seasons->whereIn('contract_type', $includedTypes);
            $typeContracts = $contracts->whereIn('season_id', $typePeriods->pluck('id'));
            $done = $typeContracts->where('purchase_contract_received', true)->count();
            return (object) ['type' => $type, 'done' => $done, 'total' => $typePeriods->count(), 'percent' => $typePeriods->count() ? round($done * 100 / $typePeriods->count()) : 0];
        });
        $logs = ActivityLog::with('user')->whereIn('action', ['CONTRACTING_CONTRACT_RECEIVED', 'CONTRACTING_CONTRACT_UNCHECKED'])->latest('created_at')->take(20)->get();
        if (request()->expectsJson()) {
            return response()->json([
                'total' => $total,
                'received' => $received,
                'pending' => $total - $received,
                'percent' => $total ? round($received * 100 / $total) : 0,
                'byType' => $byType->map(fn ($item) => ['type' => $item->type, 'done' => $item->done, 'total' => $item->total, 'percent' => $item->percent]),
            ]);
        }

        return view('contracting.stats', compact('total', 'received', 'byType', 'logs', 'regions', 'winterTotal', 'summerTotal', 'yearTotal', 'winterReceived', 'summerReceived'));
    }
}
