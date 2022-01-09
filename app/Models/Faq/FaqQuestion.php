<?php

namespace App\Models\Faq;

use App\Models\Model;

class FaqQuestion extends Model
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
    protected $table = 'faq_questions';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the category that the question belongs to.
     */
    public function category() 
    {
        return $this->belongsTo('App\Models\Faq\FaqCategory', 'faq_category_id');
    }

    /**
     * Get the tags that belong to the question.
     */
    public function tags()
    {
        return $this->hasMant('App\Models\Faq\FaqTag', 'question_id');
    }
}
