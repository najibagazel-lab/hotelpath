<?php

use App\Models\Season;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Season::with('hotel')->where('contract_type', 'WINTER')->get()->each(function (Season $season) {
            $startDate = Carbon::parse($season->start_date);

            if (! in_array($startDate->month, [4, 5, 6, 7, 8, 9, 10], true)) {
                return;
            }

            $endDate = Carbon::parse($season->end_date);
            $season->update([
                'contract_type' => 'SUMMER',
                'name' => 'Summer '.$startDate->year.'/'.$endDate->year.' - '.$season->hotel->name,
            ]);
        });
    }

    public function down(): void
    {
        // Incorrect Winter labels must not be restored.
    }
};
