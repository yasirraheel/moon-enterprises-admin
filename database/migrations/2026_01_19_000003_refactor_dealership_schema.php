<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('dealership_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->enum('dealer_status', ['na', 'pending', 'approved', 'rejected'])->default('na')->after('status');
            $table->decimal('dealer_commission', 5, 2)->default(0)->after('dealer_status');
        });

        // Migrate data from is_dealer to dealer_status if is_dealer exists
        if (Schema::hasColumn('users', 'is_dealer')) {
            \DB::table('users')->where('is_dealer', 1)->update(['dealer_status' => 'approved']);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_dealer');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dealer_status', 'dealer_commission']);
            $table->boolean('is_dealer')->default(false);
        });

        Schema::create('dealership_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('date')->useCurrent();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
