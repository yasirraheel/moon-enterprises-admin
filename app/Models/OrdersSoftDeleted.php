<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersSoftDeleted extends Model
{
    protected $table = 'orders_soft_deleted';
    protected $guarded = [];
    public $timestamps = true;

    protected $fillable = [
        'original_id',
        'user_id',
        'username',
        'user_phone',
        'game_name',
        'bond_name',
        'rttp',
        'first',
        'second',
        'status',
        'n_p',
        'deleted_at',
        'original_created_at',
        'original_updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Restore this soft deleted order back to the main orders table
     */
    public function restore()
    {
        $orderData = $this->toArray();
        unset($orderData['id'], $orderData['original_id'], $orderData['deleted_at'],
              $orderData['original_created_at'], $orderData['original_updated_at'],
              $orderData['created_at'], $orderData['updated_at']);

        // Create new order in main table
        $restoredOrder = Orders::create($orderData);

        // Delete from soft deleted table
        $this->delete();

        return $restoredOrder;
    }
}
