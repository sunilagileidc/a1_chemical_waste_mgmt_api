<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    use HasFactory;
    public $table = 'questions_file_upload';

    protected $fillable = [
        'id',
        'answer_id',
        'doc_name',
        'doc_date',
        'doc_url',
        'doc_type',
        'created_by',
        'updated_by',
    ];


}
