<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Venue;
use App\Helper\Helper;
use App\Models\VenueMedia;
use App\Models\VenueDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Venue\VenueRequest;

class VenueManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $venues = Venue::latest('id')->get();

            return DataTables::of($venues)
                ->addIndexColumn()
                // ->addColumn('title', fn($item) => $item->title)
                ->addColumn('title', function ($item) {
                    return strlen($item->title) > 15 ? substr($item->title, 0, 15) . '...' : $item->title;
                })
                ->addColumn('capacity', fn($item) => $item->capacity)
                ->addColumn('location', function ($item) {
                    return strlen($item->location) > 20 ? substr($item->location, 0, 20) . '...' : $item->location;
                })

                ->addColumn('service_start_time', fn($item) => optional($item->service_start_time)->format('h:i A'))
                ->addColumn('service_end_time', fn($item) => optional($item->service_end_time)->format('h:i A'))


                ->addColumn('ticket_price', fn($item) => $item->ticket_price)
                ->addColumn('phone', fn($item) => $item->phone ?? '---')
                ->addColumn('email', fn($item) => $item->email ?? '---')

                ->addColumn('status', function ($item) {
                    $checked = $item->status == 1 ? 'checked' : '';

                    return '<div class="form-check form-switch" style="display: flex; justify-content: center; align-items: center;">
                             <input onclick="showStatusChangeAlert(' . $item->id . ')"
                    type="checkbox"
                    class="form-check-input"
                    role="switch"
                    style="cursor: pointer; width: 40px; height: 20px;"
                    ' . $checked . '>
                         </div>';
                })


                ->addColumn('action', function ($item) {
                    return '<div class="d-flex justify-content-start align-items-center gap-1">
                   <button type="button"
                            class="btn btn-primary btn-sm editVenue"
                            data-id="' . $item->id . '">
                        <i class="fe fe-edit"></i>
                    </button>

                    <button type="button" onclick="showDeleteConfirm(' . $item->id . ')" class="btn btn-danger btn-sm">
                        <i class="fe fe-trash"></i>
                    </button>
                </div>';
                })

                ->rawColumns(['title', 'status', 'action'])
                ->make();
        }

        return view("backend.layouts.venue.index");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VenueRequest $request)
    {
        $validated_data = $request->validated();
        DB::beginTransaction();

        try {
            // 1. Create Venue (main table)
            $venue = Venue::create($validated_data);

            // 2. Add Venue Details (description & features)
            VenueDetail::create([
                'venue_id'    => $venue->id,
                'description' => $request->description,
                'features'    => $request->features,
            ]);

            // 3. Add Venue Media (optional)
            // Upload Images (if any)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::uploadImage($image, 'venue/images');
                    VenueMedia::create([
                        'venue_id'  => $venue->id,
                        'image_url' => $imagePath
                    ]);
                }
            }

            // Upload Videos (if any)
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $videoPath = Helper::fileUpload($video, 'venue/videos', 'venue-video-' . Str::random(8));
                    VenueMedia::create([
                        'venue_id'  => $venue->id,
                        'video_url' => $videoPath,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Venue added successfully!',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $venue = Venue::with(['detail', 'media'])->find($id);

            if (!$venue) {
                return response()->json(['success' => false, 'message' => 'Venue not found.'], 404);
            }

            return response()->json(['success' => true, 'data' => $venue]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch venue. ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VenueRequest $request, $id)
    {
        $validated_data = $request->validated();
        DB::beginTransaction();

        try {
            $venue = Venue::where('id', $id)
                ->first();

            if (!$venue) {
                return back()->with('t-error', 'Venue not found.');
            }

            // Update venue
            $venue->update($validated_data);

            // Update venue details
            $venue->detail()->updateOrCreate(
                ['venue_id' => $venue->id],
                [
                    'description' => $request->description,
                    'features'    => $request->features,
                ]
            );

            // Replace Media if New Files Uploaded
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = $venue->media()->whereNotNull('image_url')->get();
                foreach ($oldImages as $img) {
                    Helper::deleteImage($img->image_url);
                    $img->delete();
                }

                // Upload new images
                foreach ($request->file('images') as $image) {
                    $imagePath = Helper::uploadImage($image, 'venue/images');
                    VenueMedia::create([
                        'venue_id'   => $venue->id,
                        'image_url'  => $imagePath,
                    ]);
                }
            }

            if ($request->hasFile('videos')) {
                // Delete old videos
                $oldVideos = $venue->media()->whereNotNull('video_url')->get();
                foreach ($oldVideos as $vid) {
                    Helper::deleteFile($vid->video_url);
                    $vid->delete();
                }

                // Upload new videos
                foreach ($request->file('videos') as $video) {
                    $videoPath = Helper::fileUpload($video, 'venue/videos', 'venue-video-' . Str::random(8));
                    VenueMedia::create([
                        'venue_id'   => $venue->id,
                        'video_url'  => $videoPath,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Venue added successfully!',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating the venue.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $venue = Venue::with(['detail', 'media'])->where('id', $id)->first();

            // Delete media files from storage
            foreach ($venue->media as $media) {
                if ($media->image_url) {
                    Helper::deleteFile($media->image_url);
                }
                if ($media->video_url) {
                    Helper::deleteFile($media->video_url);
                }
            }

            // Delete related records
            $venue->media()->delete();
            $venue->detail()->delete();
            $venue->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venue deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete venue. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function status($id)
    {
        $venue = Venue::find($id);

        if (!$venue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found.',
            ]);
        }

        // Toggle status
        $venue->status = $venue->status == 0 ? 1 : 0;
        $venue->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status Changed successfully!',
        ]);
    }
}
