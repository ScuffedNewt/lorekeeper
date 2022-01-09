<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use Settings;

use App\Models\Faq\FaqCategory;
use App\Models\Faq\FaqTag;
use App\Models\Faq\FaqQuestion;
use App\Models\Faq\FaqQuestionTag;

class FaqController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FAQ Controller
    |--------------------------------------------------------------------------
    |
    | Displays lists of users and characters.
    |
    */

    /**
     * Gets the index of the FAQ page.
     * 
     * @return \Illuminate\View\View
     */
    public function getIndex(Request $request)
    {
        $query = FaqQuestion::with('tags', 'category')->where('status', 'answered');

        $query->orderBy('created_at', 'desc');

        return view('browse.faq.faq_index', [
            'questions' => $query->paginate(30)->appends($request->query()),
        ]);
    }

    /**
     * Shows the submit question page.
     * 
     * @return \Illuminate\View\View
     */
    public function getQuestionSubmit()
    {
        return view('browse.faq.faq_submit_question', [
            'question' => new FaqQuestion(),
        ]);
    }

}
