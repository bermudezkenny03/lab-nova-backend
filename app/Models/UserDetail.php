<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['gender_type_id', 'birthdate', 'address', 'addon_address', 'notes', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function genderType()
    {
        return $this->belongsTo(GenderType::class, 'gender_type_id');
    }

    public static function createUserDetail($validated, $userId)
    {
        $userDetailData = array_filter($validated, fn($key) => in_array($key, (new self)->getFillable()), ARRAY_FILTER_USE_KEY);
        $userDetailData['user_id'] = $userId;

        return self::create($userDetailData);
    }

    public static function updateUserDetail($validated, $user)
    {
        if ($user->userDetail) {
            $user->userDetail->update(array_filter($validated, fn($key) => in_array($key, (new self)->getFillable()), ARRAY_FILTER_USE_KEY));
        }
    }
}
