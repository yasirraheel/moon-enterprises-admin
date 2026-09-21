<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_exported')->default(false)->after('n_p');
            $table->decimal('cut_first', 10, 2)->nullable()->after('is_exported');
            $table->decimal('cut_second', 10, 2)->nullable()->after('cut_first');
            $table->timestamp('exported_at')->nullable()->after('cut_second');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_exported', 'cut_first', 'cut_second', 'exported_at']);
        });
    }
};
