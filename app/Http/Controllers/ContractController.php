<?php
namespace App\Http\Controllers;
use App\Models\{ActivityLog, Contract, ContractTask, Hotel, Platform, Season};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    public function create(){ return view('contracts.create',['hotels'=>Hotel::where('active',true)->orderBy('name')->get(),'seasons'=>Season::where('active', true)->whereNull('hotel_id')->orderBy('start_date')->get()]); }
    public function store(Request $request){
        $data=$request->validate(['hotel_id'=>'nullable|exists:hotels,id','hotel_name'=>'nullable|string|max:120|required_without:hotel_id','destination'=>'nullable|string|max:120','season_names'=>'required|array|min:1','season_names.*'=>'required|string|max:100','received_date'=>'required|date','start_date'=>'nullable|date','end_date'=>'nullable|date','notes'=>'nullable|string']);
        $contract=DB::transaction(function() use($data){
            if (empty($data['hotel_id'])) {
                $hotel = Hotel::firstOrCreate(['name' => $data['hotel_name']], ['destination' => $data['destination'] ?? null]);
                $data['hotel_id'] = $hotel->id;
            }
            foreach ($data['season_names'] as $seasonName) {
                preg_match_all('/\d{4}/', $seasonName, $years);
                $seasonEndYear = (int) (end($years[0]) ?: now()->year);
                $season = Season::firstOrCreate(
                    ['name' => trim($seasonName)],
                    ['code' => Str::upper(Str::slug($seasonName)), 'start_date' => now()->setYear($seasonEndYear)->startOfYear(), 'end_date' => now()->setYear($seasonEndYear)->endOfYear(), 'active' => true]
                );
                $contract = Contract::firstOrCreate(
                    ['hotel_id' => $data['hotel_id'], 'season_id' => $season->id],
                    ['received_date' => $data['received_date'], 'purchase_contract_received' => true, 'notes' => $data['notes'] ?? null, 'created_by' => auth()->id()]
                );
                foreach(Platform::where('active', true)->get() as $platform){
                    $owner=$platform->users()->where('active',true)->first();
                    ContractTask::firstOrCreate(['contract_id'=>$contract->id,'platform_id'=>$platform->id], ['assigned_user_id'=>$owner?->id]);
                }
                ActivityLog::create(['user_id'=>auth()->id(),'contract_id'=>$contract->id,'action'=>'CONTRACT_CREATED','details'=>'Contract and platform tasks created automatically']);
            }
            return $contract;
        });
        return redirect()->route('tasks.index')->with('success','Hotel added. Tasks are now available for assigned platforms.');
    }
    public function updateTask(Request $request, ContractTask $task){
        // Même un administrateur ne coche pas à la place d'un responsable :
        // la traçabilité reste donc fiable et chaque tâche est personnelle.
        abort_unless($task->is_active && $task->assigned_user_id === auth()->id(), 403);
        $status=$request->validate(['status'=>'required|in:PENDING,IN_PROGRESS,COMPLETED,BLOCKED'])['status'];
        $task->update(['status'=>$status,'completed_at'=>$status==='COMPLETED'?now():null,'completed_by'=>$status==='COMPLETED'?auth()->id():null]);
        ActivityLog::create(['user_id'=>auth()->id(),'contract_id'=>$task->contract_id,'platform_id'=>$task->platform_id,'action'=>'STATUT_MODIFIE','details'=>"Statut défini sur {$status}"]);
        return back()->with('success','Statut mis à jour.');
    }

    public function destroy(Contract $contract)
    {
        $hotelName = $contract->hotel->name;
        $season = $contract->season;
        $contract->delete();
        // If this was the final contract for the season, remove the season too.
        // It will consequently disappear from every dashboard progress indicator.
        if ($season->contracts()->doesntExist()) {
            $season->delete();
        }
        return back()->with('success', "{$hotelName} contract deleted.");
    }
}
