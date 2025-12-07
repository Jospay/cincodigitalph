<?php

// app/Models/DetailUser.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailUser extends Model
{
    use HasFactory;

    // Note the snake_case table name for your migration
    protected $table = 'detail_user';

    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'mobile_number',
        'account_type',
        'qrcode_name',
        'qrcode_img',
        'verification_account',
        'status',
    ];
}
