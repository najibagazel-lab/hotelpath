<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('seasons')
            ->join('hotels', 'hotels.id', '=', 'seasons.hotel_id')
            ->orderBy('seasons.id')
            ->get([
                'seasons.id',
                'seasons.contract_type',
                'seasons.start_date',
                'seasons.end_date',
                'hotels.name as hotel_name',
            ])
            ->each(function (object $season): void {
                $startDate = Carbon::parse($season->start_date);
                $endDate = Carbon::parse($season->end_date);
                $summerStart = Carbon::create($endDate->year, 4, 1);
                $winterEnd = Carbon::create($endDate->year, 4, 30);

                $type = match (true) {
                    $endDate->lte($winterEnd) => 'WINTER',
                    $startDate->lt($summerStart) && $endDate->gt($winterEnd) => 'YEAR',
                    default => 'SUMMER',
                };

                if ($season->contract_type === $type) {
                    return;
                }

                DB::table('seasons')->where('id', $season->id)->update([
                    'contract_type' => $type,
                    'name' => ucfirst(strtolower($type)).' '.$startDate->year.'/'.$endDate->year.' - '.$season->hotel_name,
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // This data correction cannot safely infer each contract's former type.
    }
};
