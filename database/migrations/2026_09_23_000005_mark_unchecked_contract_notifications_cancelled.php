<?php

use App\Models\Contract;
use App\Notifications\ContractReceivedNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('notifications')->where('type', ContractReceivedNotification::class)->orderBy('created_at')->get()
            ->each(function ($notification) {
                $data = json_decode($notification->data, true) ?: [];
                $contract = Contract::find($data['contract_id'] ?? null);

                if (! $contract || $contract->purchase_contract_received) {
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

    public function down(): void
    {
        // Notification history is intentionally preserved.
    }
};
