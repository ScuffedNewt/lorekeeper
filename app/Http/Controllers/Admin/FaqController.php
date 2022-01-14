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
        return view('admin.faq.faq_categories', [
            'categories' => FaqCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

        
    /**********************************************************************************************

        FAQ 

    **********************************************************************************************/

    /**
     * Shows the faq category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFaqIndex(Request $request)
    {
        $query = FaqQuestion::query();
        $data = $request->only(['faq_category_id', 'name']);
        if(isset($data['faq_category_id']) && $data['faq_category_id'] != 'none')
            $query->where('faq_category_id', $data['faq_category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.faq.index', [
            'questions' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + FaqCategory::orderBy('name', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

}
