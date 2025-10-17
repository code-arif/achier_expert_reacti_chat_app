<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use Exception;
use App\Models\CMS;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\CmsManageRequest;

class HomeController extends Controller
{
    use ApiResponse;

    /**
     * show hero section
     **/
    public function hero()
    {
        $data = CMS::where('page', 'home-page')->where('section', 'hero')->first();
        return view("backend.layouts.cms.home-page.hero", compact("data"));
    }

    /**
     * update hero section
     **/
    public function updateHero(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home-page')
                ->where('section', 'hero')
                ->first();

            // handle image
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/hero');
                $validated_data['image'] = $image_path;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'home-page',
                    'section' => 'hero',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Hero content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * show upcoming events section
     **/
    public function upcomingEvents()
    {
        $data = CMS::where('page', 'home-page')->where('section', 'upcoming-event')->first();
        return view("backend.layouts.cms.home-page.event", compact("data"));
    }


    /**
     * update update upcoming section
     **/
    public function updateUpcomingEvents(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home-page')
                ->where('section', 'upcoming-event')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home-page',
                    'section' => 'upcoming-event',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Event content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * show popular vanue section
     **/
    public function popularVenues()
    {
        $data = CMS::where('page', 'home-page')->where('section', 'popular-vanue')->first();
        return view("backend.layouts.cms.home-page.vanue", compact("data"));
    }

    /**
     * update popular vanue section
     **/
    public function updatePopularVenues(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home-page')
                ->where('section', 'popular-vanue')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home-page',
                    'section' => 'popular-vanue',
                ],
                $validated_data
            );

            return back()->with('t-success', 'Vanue content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }

    /**
     * show app download section
     **/
    public function appDownload()
    {
        $data = CMS::where('page', 'home-page')->where('section', 'app-download')->first();
        return view("backend.layouts.cms.home-page.app-download", compact("data"));
    }

    /**
     * update app download section
     **/
    public function updateAppDownload(CmsManageRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            $existing = CMS::where('page', 'home-page')
                ->where('section', 'app-download')
                ->first();

            // handle image
            if ($request->hasFile('image')) {
                if ($existing && $existing->image) {
                    Helper::deleteImage($existing->image);
                }

                $image_path = Helper::uploadImage($request->file('image'), 'cms/app-download');
                $validated_data['image'] = $image_path;
            }

            CMS::updateOrCreate(
                [
                    'page' => 'home-page',
                    'section' => 'app-download',
                ],
                $validated_data
            );

            return back()->with('t-success', 'App download content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
