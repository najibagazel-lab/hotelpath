<?php
namespace App\Http\Controllers;
use App\Models\{Contract, ContractTask, Hotel, Platform, Season};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\SimpleXlsx;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role === 'CONTRACTING') {
            return redirect()->route('contracting.index');
        }
        if (auth()->user()->role === 'ENTRY_MANAGER') {
            return $this->myTasks($request);
        }
        $contracts = Contract::with(['hotel','season','tasks.platform','tasks.assignee'])
            ->where('purchase_contract_received', true)
            ->whereHas('season', fn ($season) => $season->current())
            ->whereHas('tasks', fn ($task) => $task->where('is_active', true))
            ->latest('updated_at')
            ->get();
        $seasons = Season::where('active', true)->whereNull('hotel_id')->orderBy('start_date')->get();
        $tasks = ContractTask::where('is_active', true)->whereHas('contract', fn ($contract) => $contract
            ->where('purchase_contract_received', true)
            ->whereHas('season', fn ($season) => $season->current()));
        $totalTasks = (clone $tasks)->count();
        $completed = (clone $tasks)->where('status', 'COMPLETED')->count();
        $platforms = Platform::where('active', true)
            ->orderByRaw("CASE platform_group WHEN 'SEJOUR' THEN 1 WHEN 'PLATFORM' THEN 2 ELSE 3 END")
            ->orderBy('label')
            ->get()
            ->map(function ($platform) {
            $query = ContractTask::where('platform_id', $platform->id)->where('is_active', true)
                ->whereHas('contract', fn ($contract) => $contract
                    ->where('purchase_contract_received', true)
                    ->whereHas('season', fn ($season) => $season->current()));
            $total = (clone $query)->count();
            $done = (clone $query)->where('status', 'COMPLETED')->count();
            $byType = collect([
                ['label' => 'Winter', 'types' => ['WINTER', 'YEAR']],
                ['label' => 'Summer', 'types' => ['SUMMER', 'YEAR']],
            ])->map(function ($type) use ($platform) {
                $query = ContractTask::where('platform_id', $platform->id)->where('is_active', true)
                    ->whereHas('contract', fn ($contract) => $contract->where('purchase_contract_received', true)
                        ->whereHas('season', fn ($season) => $season->current()->whereIn('contract_type', $type['types'])));
                $typeTotal = (clone $query)->count();
                $typeDone = (clone $query)->where('status', 'COMPLETED')->count();
                return (object)['label' => $type['label'], 'total' => $typeTotal, 'done' => $typeDone, 'percent' => $typeTotal ? round($typeDone * 100 / $typeTotal) : 0];
            });
            return (object)['id'=>$platform->id,'name'=>$platform->name,'label'=>$platform->label ?? $platform->name,'group'=>$platform->platform_group ?? 'OTHER','total'=>$total,'done'=>$done,'pending'=>$total-$done,'percent'=>$total ? round($done*100/$total) : 0, 'byType' => $byType];
        });
        // La matrice est volontairement construite Hôtel × Saison : chaque cellule
        // avance uniquement à partir des tâches cochées pour ce contrat précis.
        $hotelsBySeason = $contracts->groupBy('hotel_id');
        return view('dashboard', compact('contracts','platforms','totalTasks','completed','seasons','hotelsBySeason'));
    }

    public function myTasks(Request $request)
    {
        $platforms = Platform::where('active', true)
            ->orderByRaw("CASE platform_group WHEN 'SEJOUR' THEN 1 WHEN 'PLATFORM' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE label WHEN 'DNA' THEN 1 WHEN 'JMT' THEN 2 WHEN 'PEAK' THEN 3 WHEN 'SUN' THEN 4 WHEN 'SQ' THEN 5 WHEN 'PAXIMUM' THEN 6 WHEN 'HEY TRIP' THEN 7 WHEN 'AURUM' THEN 8 ELSE 99 END")
            ->get();
        // Use the same active-period rule as Contract receipt follow-up.
        $contracts = Contract::with(['hotel', 'season', 'tasks.platform'])
            ->where('purchase_contract_received', true)
            ->whereHas('season', fn ($season) => $season->current())
            ->whereHas('tasks', fn ($task) => $task->where('is_active', true)->where('assigned_user_id', auth()->id()))
            ->when($request->filled('hotel'), fn ($query) => $query->whereHas('hotel', fn ($hotel) => $hotel->where('name', 'like', '%'.$request->hotel.'%')))
            ->orderBy('season_id')->orderBy('hotel_id')->get();
        $allowedPlatformIds = auth()->user()->platforms()->pluck('platforms.id')->all();
        $allowedPlatformGroups = $platforms->whereIn('id', $allowedPlatformIds)->pluck('platform_group');
        $defaultPlatformGroup = $allowedPlatformGroups->contains('SEJOUR') ? 'SEJOUR' : 'PLATFORM';
        $toProgress = $platforms->whereIn('id', $allowedPlatformIds)->map(function ($platform) {
            $tasks = ContractTask::where('platform_id', $platform->id)->where('is_active', true)
                ->where('assigned_user_id', auth()->id())
                ->whereHas('contract', fn ($contract) => $contract
                    ->where('purchase_contract_received', true)
                    ->whereHas('season', fn ($season) => $season->current()));
            $winterTasks = (clone $tasks)->whereHas('contract.season', fn ($season) => $season->whereIn('contract_type', ['WINTER', 'YEAR']));
            $summerTasks = (clone $tasks)->whereHas('contract.season', fn ($season) => $season->whereIn('contract_type', ['SUMMER', 'YEAR']));

            return (object) [
                'label' => $platform->label ?? $platform->name,
                'group' => $platform->platform_group ?? 'OTHER',
                'winterReceived' => (clone $winterTasks)->count(),
                'winterEntered' => (clone $winterTasks)->where('status', 'COMPLETED')->count(),
                'summerReceived' => (clone $summerTasks)->count(),
                'summerEntered' => (clone $summerTasks)->where('status', 'COMPLETED')->count(),
            ];
        });
        $hotels = Hotel::where('active', true)->orderBy('name')->get();
        $seasons = Season::where('active', true)->whereNull('hotel_id')->orderBy('start_date')->get();
        // Winter 2026/2027 and Summer 2027 belong to one campaign (2027).
        // The next campaign groups Winter 2027/2028 with Summer 2028.
        $seasonGroups = $contracts->groupBy(function ($contract) {
            preg_match_all('/\d{4}/', $contract->season->name, $years);
            return (int) (end($years[0]) ?: $contract->season->end_date->year);
        })->sortKeys();
        return view('tasks.index', compact('contracts', 'platforms', 'allowedPlatformIds', 'hotels', 'seasons', 'seasonGroups', 'defaultPlatformGroup', 'toProgress'));
    }

    public function exportMyTasks(Request $request)
    {
        abort_unless(auth()->user()->role === 'ENTRY_MANAGER', 403);
        $tasks = ContractTask::with(['contract.hotel', 'contract.season', 'platform'])
            ->where('assigned_user_id', auth()->id())
            ->where('is_active', true)
            ->whereHas('contract', fn ($contract) => $contract->where('purchase_contract_received', true)
                ->whereHas('season', fn ($season) => $season->current())
                ->when($request->filled('hotel'), fn ($query) => $query->whereHas('hotel', fn ($hotel) => $hotel->where('name', 'like', '%'.$request->hotel.'%'))))
            ->orderBy('platform_id')->orderBy('contract_id')->get();
        $rows = $tasks->map(fn ($task) => [$task->contract->hotel->name, ucfirst(strtolower($task->contract->season->contract_type)), $task->contract->season->start_date->format('d/m/Y'), $task->contract->season->end_date->format('d/m/Y'), $task->platform->label ?? $task->platform->name, ucfirst(strtolower($task->status)), $task->completed_at?->format('d/m/Y H:i') ?? '']);

        return SimpleXlsx::download('my-task-status-'.now()->format('Y-m-d').'.xlsx', ['Hotel', 'Contract type', 'Start date', 'End date', 'TO', 'Entry status', 'Completed at'], $rows);
    }

    public function history()
    {
        $platforms = Platform::where('active', true)->orderBy('name')->get();
        $contracts = Contract::with(['hotel', 'season', 'tasks.platform'])
            ->whereHas('season', fn ($query) => $query->whereDate('end_date', '<', today()))
            ->orderByDesc('season_id')->orderBy('hotel_id')->get();
        return view('tasks.history', compact('contracts', 'platforms'));
    }
}
