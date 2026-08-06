<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $loans = DB::table('loans')
            ->where('installments', 1)
            ->where('emi', '>', 0)
            ->whereRaw('amount != emi')
            ->get(['id', 'amount', 'emi']);

        foreach ($loans as $loan) {
            $installments = (int) ceil($loan->amount / $loan->emi);
            DB::table('loans')
                ->where('id', $loan->id)
                ->update(['installments' => $installments]);
        }
    }

    public function down(): void
    {
        // no rollback: this migration backfills existing loan records
    }
};
