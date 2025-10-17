<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use Exception;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Helper\Helper;

class SocialSettingController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = SocialMedia::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('social_media_icon', function ($data) {
                    if (empty($data->social_media_icon)) {
                        return ' --- ';
                    }
                    $url = asset($data->social_media_icon);
                    $image = '<img src="' . $url . '" width="50px">';
                    return $image;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('admin.social_media.edit', $data->id) . '" class="btn btn-primary text-white" title="Edit">
                              <i class="bi bi-pencil"></i>
                            </a>
                          </div>';
                })
                ->rawColumns(['social_media_icon', 'action'])
                ->make(true);
        }
        return view('backend.layouts.socialmedia.index');
    }

    public function edit($id)
    {
        $socialMedia = SocialMedia::find($id);
        return view('backend.layouts.socialmedia.edit', compact('socialMedia'));
    }

    public function update(Request $request, $id)
    {
        $socialMedia = SocialMedia::find($id);

        $request->validate([
            'profile_link' => 'nullable|url',
            'social_media_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240'
        ]);

        try {
            if ($request->hasFile('social_media_icon')) {
                
                if (!empty($socialMedia->social_media_icon) && file_exists($socialMedia->social_media_icon)) {
                    unlink($socialMedia->social_media_icon);
                }
                

                // Upload the new icon
                $iconPath = Helper::uploadImage($request->file('social_media_icon'), 'social_media');
                if ($iconPath === null) {
                    throw new Exception('Failed to upload image.');
                }

                $socialMedia->social_media_icon = $iconPath;
            }

            $socialMedia->update($request->only('profile_link'));

            session()->put('t-success', 'Social Media updated successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.social_media.index');
    }

    public function destroy($id)
    {
        try {
            $socialMedia = SocialMedia::findOrFail($id);


            if($socialMedia->social_media_icon)
            {
                Helper::deleteImage(public_path($socialMedia->social_media_icon));
            }

            $socialMedia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Social Media deleted successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
