<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('fcm_notification_settings')->insert([
            'notification_type' => 'dealer_request',
            'label' => 'Dealer Ship Request',
            'description' => 'When user submit a dealership request.',
            'is_enabled' => true,
            // 'created_at' and 'updated_at' will be null if not set, but timestamps are nullable usually or default.
            // Based on previous migration, timestamps were not explicitly set in the array, but let's check if the table has default current_timestamp.
            // The previous migration used $table->timestamps(), which creates nullable columns without default.
            // So better to include them if I want them set.
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('fcm_notification_settings')->where('notification_type', 'dealer_request')->delete();
    }
};
