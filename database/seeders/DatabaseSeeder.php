<?php

namespace Database\Seeders;

use App\Models\{Contract, ContractTask, Hotel, Platform, Season, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin=User::firstOrCreate(['username'=>'admin'], ['name'=>'Administrator','email'=>'admin@agence.test','password'=>Hash::make('password'),'role'=>'ADMIN']);
        User::firstOrCreate(['username'=>'contracting'], ['name'=>'Contracting','email'=>'contracting@agence.test','password'=>Hash::make('password'),'role'=>'CONTRACTING']);
        $najiba=User::firstOrCreate(['username'=>'najiba'], ['name'=>'Najiba','email'=>'najiba@agence.test','password'=>Hash::make('password'),'role'=>'ENTRY_MANAGER']);
        $haifa=User::firstOrCreate(['username'=>'haifa'], ['name'=>'Haifa','email'=>'haifa@agence.test','password'=>Hash::make('password'),'role'=>'ENTRY_MANAGER']);
        $amal=User::firstOrCreate(['username'=>'amal'], ['name'=>'Amal','email'=>'amal@agence.test','password'=>Hash::make('password'),'role'=>'ENTRY_MANAGER']);
        $wafa=User::firstOrCreate(['username'=>'wafa'], ['name'=>'Wafa','email'=>'wafa@agence.test','password'=>Hash::make('password'),'role'=>'ENTRY_MANAGER']);
        $platforms=collect([['Sejour','SEJOUR',$najiba],['Jumbo','JUMBO',$haifa],['Dnata','DNATA',$haifa],['Sunhotels','SUNHOTELS',null],['Sunquest','SUNQUEST',$amal],['Paximum','PAXIMUM',$najiba],['Peak','PEAK',$wafa]])->map(function($p){$x=Platform::firstOrCreate(['code'=>$p[1]], ['name'=>$p[0]]);if($p[2])$x->users()->syncWithoutDetaching([$p[2]->id]);return $x;});
        $winter=Season::firstOrCreate(['code'=>'W26'], ['name'=>'Winter 2026/2027','start_date'=>'2026-11-01','end_date'=>'2027-04-30']);
        $summer=Season::firstOrCreate(['code'=>'S27'], ['name'=>'Summer 2027','start_date'=>'2027-05-01','end_date'=>'2027-10-31']);
        foreach ([['El Ksar','Tunis',$winter,4],['Golf Résidence','Sousse',$winter,3],['Riadh Palms','Sousse',$summer,5]] as [$name,$destination,$season,$done]) {
            $hotel=Hotel::firstOrCreate(['name'=>$name], ['destination'=>$destination]); $contract=Contract::firstOrCreate(['hotel_id'=>$hotel->id,'season_id'=>$season->id], ['received_date'=>now()->subDays(3),'purchase_contract_received'=>true]);
            foreach($platforms as $index=>$platform) { $assignee=$platform->users()->first(); ContractTask::firstOrCreate(['contract_id'=>$contract->id,'platform_id'=>$platform->id], ['assigned_user_id'=>$assignee?->id,'status'=>$index<$done?'COMPLETED':'PENDING','completed_at'=>$index<$done?now()->subDay():null,'completed_by'=>$index<$done?$assignee?->id:null]); }
        }
    }
}
