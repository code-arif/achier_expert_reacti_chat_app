<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\CmsManageRequest;

class NewsletterController extends Controller
{
    /**
     * show newsletter section
     **/
    public function index()
    {
        $data = CMS::where('page', 'newsletter-page')->where('section', 'newsletter')->first();
        return view("backend.layouts.cms.newsletter.index", compact("data"));
    }

    /**
     * update newsletter section
     **/
    public function update(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'newsletter-page')
                ->where('section', 'newsletter')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'newsletter-page',
                    'section' => 'newsletter',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Newsletter content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
