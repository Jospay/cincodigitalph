<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    // By default, Laravel looks for 'users'. If you changed the table name, update it here.
    protected $table = 'users';

    protected $fillable = [
        'team_name',
        'total_payment',
        'additional_shirt_count',
        'country',
        'region',
        'province',
        'city',
        'barangay',
        'postal_code',
        'paymongo_checkout_session_id',
        'transaction_status'
    ];

    // Define the relationship to DetailUser
    public function details()
    {
        return $this->hasMany(DetailUser::class);
    }
}
