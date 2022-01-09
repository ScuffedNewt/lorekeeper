<?php

namespace App\Models\Faq;

use App\Models\Model;

class FaqCategory extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'faq_categories';
    
}
