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
    protected $table = 'faq_question_tags';

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
    
    /**
     * Get the question that the tag belongs to.
     */
    public function question()
    {
        return $this->belongsTo('App\Models\Faq\FaqQuestion', 'question_id');
    }

    /**
     * Get the tags that belong to the question.
     */
    public function tags()
    {
        return $this->hasMant('App\Models\Faq\FaqTag', 'question_id');
    }
}
