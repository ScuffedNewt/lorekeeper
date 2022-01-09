<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Auth;

use App\Models\Faq\FaqCategory;
use App\Models\Faq\FaqQuestion;
use App\Models\Faq\FaqTag;

use App\Services\FaqService;

use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Faq Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of faq categories and faqs.
    |
    */

    /**********************************************************************************************

        FAQ CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the faq category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCategoryIndex()
    {
        return view('admin.faqs.faq_categories', [
            'categories' => FaqCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

}
