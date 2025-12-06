<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Models\PrivecyAndTerms;
use App\Http\Controllers\Controller;

class PrivacyController extends Controller
{
    public function termsAndCondition()
    {
        $terms = PrivecyAndTerms::first();
        return view('backend.layouts.privacy.index', compact('terms'));
    }

    public function termsAndConditionUpdate(Request $request)
    {
        $request->validate([
            'description' => 'required',
        ]);

        // Find existing record with type 'terms'
        $terms = PrivecyAndTerms::where('type', 'terms')->first();

        if ($terms) {
            // Update existing record
            $terms->description = $request->description;
        } else {
            // Create new record
            $terms = new PrivecyAndTerms();
            $terms->type = 'terms';
            $terms->description = $request->description;
        }

        $terms->save();

        return redirect()->back()->with('success', 'Terms and Conditions updated successfully.');
    }


    public function privacyPolicy()
    {
        $privacy = PrivecyAndTerms::where('type', 'privacy')->first();
        return view('backend.layouts.privacyandterms.privacy_policy', compact('privacy'));
    }

    public function privacyPolicyUpdate(Request $request)
    {
        $request->validate([
            'description' => 'required',
        ]);

        // Find existing record with type 'privacy'
        $privacy = PrivecyAndTerms::where('type', 'privacy')->first();

        if ($privacy) {
            // Update existing record
            $privacy->description = $request->description;
        } else {
            // Create new record
            $privacy = new PrivecyAndTerms();
            $privacy->type = 'privacy';
            $privacy->description = $request->description;
        }

        $privacy->save();

        return redirect()->back()->with('success', 'Privacy Policy updated successfully.');
    }

    /**
     * show why desi carouel update page
     */
    public function whyDesiCarousel()
    {
        $why_desi_carousel = PrivecyAndTerms::where('type', 'why_desi_carousel')->first();
        return view('backend.layouts.privacyandterms.why_desi_carousel', compact('why_desi_carousel'));
    }


    /**
     * update why desi carousel
     */
    public function whyDesiCarouselUpdate(Request $request)
    {

        $request->validate([
            'description' => 'required',
        ]);

        // Find existing record with type 'privacy'
        $why_desi_carousel = PrivecyAndTerms::where('type', 'why_desi_carousel')->first();

        if ($why_desi_carousel) {
            // Update existing record
            $why_desi_carousel->description = $request->description;
        } else {
            // Create new record
            $why_desi_carousel = new PrivecyAndTerms();
            $why_desi_carousel->type = 'why_desi_carousel';
            $why_desi_carousel->description = $request->description;
        }

        $why_desi_carousel->save();

        return redirect()->back()->with('success', 'Privacy Policy updated successfully.');
    }

    // trust and service


     public function trustSefty()
    {
        $trust_and_sefty = PrivecyAndTerms::where('type', 'trust&service')->first();
        return view('backend.layouts.privacyandterms.trust_sefty', compact('trust_and_sefty'));
    }

     public function trustAndService(Request $request)
    {

        $request->validate([
            'description' => 'required',
        ]);

        // Find existing record with type 'privacy'
        $trust_and_service = PrivecyAndTerms::where('type', 'trust&service')->first();

        if ($trust_and_service) {
            // Update existing record
            $trust_and_service->description = $request->description;
        } else {
            // Create new record
            $trust_and_service = new PrivecyAndTerms();
            $trust_and_service->type = 'trust&service';
            $trust_and_service->description = $request->description;
        }

        $trust_and_service->save();

        return redirect()->back()->with('success', 'Privacy Policy updated successfully.');
    }
}
