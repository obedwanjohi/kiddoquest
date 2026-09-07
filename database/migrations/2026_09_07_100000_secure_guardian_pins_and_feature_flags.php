<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1. Guarantees the guardian columns the app relies on (parent_pin, pin_changed_at,
     *    enable_devotional, enable_songs_hub) — the toggles were previously created at
     *    runtime from a controller.
     * 2. Hashes every plaintext parent PIN in place. Rows that already hold a hash are
     *    left alone, so the migration is safe to re-run.
     */
    public function up(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            if (! Schema::hasColumn('guardians', 'parent_pin')) {
                $table->string('parent_pin')->nullable()->after('password');
            }
            if (! Schema::hasColumn('guardians', 'pin_changed_at')) {
                $table->timestamp('pin_changed_at')->nullable()->after('parent_pin');
            }
            if (! Schema::hasColumn('guardians', 'enable_devotional')) {
                $table->boolean('enable_devotional')->default(true);
            }
            if (! Schema::hasColumn('guardians', 'enable_songs_hub')) {
                $table->boolean('enable_songs_hub')->default(true);
            }
        });

        $default = (string) config('plans.default_parent_pin', '1234');

        DB::table('guardians')
            ->select('id', 'parent_pin')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($default) {
                foreach ($rows as $row) {
                    $pin = (string) ($row->parent_pin ?? '');

                    if ($pin !== '' && Hash::isHashed($pin)) {
                        continue;
                    }

                    DB::table('guardians')
                        ->where('id', $row->id)
                        ->update(['parent_pin' => Hash::make($pin !== '' ? $pin : $default)]);
                }
            });
    }

    public function down(): void
    {
        // Irreversible on purpose: hashes cannot be turned back into PINs.
    }
};
