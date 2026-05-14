<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionContacts extends Model
{
    use HasFactory;

    public $table = 'institution_contacts';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [

        'institution_id'
        , 'user_id'
        , 'name'
        , 'email'
        , 'status'
        , 'created_by'
        , 'updated_by',
    ];
    public function contact_user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

}
