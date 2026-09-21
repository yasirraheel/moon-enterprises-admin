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
        Schema::table('users', function (Blueprint $table) {
            // Drop all unnecessary columns
            $table->dropColumn([
                'name',
                'bio', 
                'countries_id',
                'email',
                'type_account',
                'website',
                'twitter',
                'facebook',
                'google',
                'authorized_to_upload',
                'instagram',
                'bank',
                'author_exclusive',
                'stripe_connect_id',
                'completed_stripe_onboarding',
                'stripe_id',
                'pm_type',
                'pm_last_four',
                'downloads',
                'trial_ends_at'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the columns if needed to rollback
            $table->string('name')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedBigInteger('countries_id')->nullable();
            $table->string('email')->nullable();
            $table->string('type_account')->nullable();
            $table->string('website')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('google')->nullable();
            $table->string('authorized_to_upload')->nullable();
            $table->string('instagram')->nullable();
            $table->string('bank')->nullable();
            $table->string('author_exclusive')->nullable();
            $table->string('stripe_connect_id')->nullable();
            $table->boolean('completed_stripe_onboarding')->default(false);
            $table->string('stripe_id')->nullable();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four')->nullable();
            $table->integer('downloads')->default(0);
            $table->timestamp('trial_ends_at')->nullable();
        });
    }
};
