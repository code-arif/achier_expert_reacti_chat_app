<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class TestimonialController extends Controller
{
    public function index(Request $request) {

       
        if ($request->ajax()) {
            $data = Testimonial::with('user')->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function ($data) {
                    return $data->user->name;
                })
                ->addColumn('user_avatar', function ($data) {
                    return '<img src="' . asset($data->user->avatar) . '" alt="image" width="50px" height="50px" style="margin-left:20px;">';

                })

                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="statusChange(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })    

                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="btn btn-danger text-white" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div>';
                })
                ->rawColumns(['user_name','user_avatar','status', 'action', 'status'])
                ->make(true);
        }
        return view('backend.layouts.testimonial.index');
    }

    public function status(int $id): JsonResponse {
        $data = Testimonial::findOrFail($id);
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Status changed successfully!',
        ]);
    }
    

    public function destroy(int $id): JsonResponse {
        $data = Testimonial::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'FAQ not found.',
            ], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ deleted successfully!',
        ], 200);
    }
}