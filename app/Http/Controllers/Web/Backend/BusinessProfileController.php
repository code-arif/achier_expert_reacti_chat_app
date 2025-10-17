<?php

namespace App\Http\Controllers\Web\Backend;

use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;

class BusinessProfileController extends Controller
{
    public function pendingProfiles(Request $request)
    {
        if ($request->ajax()) {
            $data = BusinessProfile::with('user', 'establishment')
                ->where('status', 'pending')
                ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function ($data) {
                    return $data->user ? $data->user->f_name . ' ' . $data->user->l_name : 'N/A';
                })
                ->addColumn('user_email', function ($data) {
                    return $data->user ? $data->user->email : 'N/A';
                })
                ->addColumn('establishment_name', function ($data) {
                    return $data->establishment ? $data->establishment->title : 'N/A';
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: green; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('cancel_status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: red; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showCancelRequestAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<a href="' . route('admin.business_profile.show', ['id' => Crypt::encryptString($data->id)]) . '" class="btn btn-primary fs-14 text-white view-icn" title="View">
                                <i class="fa fa-eye"></i>
                            </a>';
                })
                ->rawColumns(['status', 'cancel_status', 'action', 'user_name', 'user_email', 'establishment_name'])
                ->make(true);
        }
        return view("backend.layouts.business_profile.index");
    }

    public function approveProfile($id)
    {
        try {
            $profile = BusinessProfile::findOrFail($id);
            $profile->status = 'approved';
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Business Profile Approved Successfully.',
                'data' => $profile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function cancelProfile($id)
    {
        try {
            $profile = BusinessProfile::findOrFail($id);
            $profile->status = 'cancelled';
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Business Profile Approval Cancelled Successfully.',
                'data' => $profile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function profileDetails(Request $request)
    {
        try {
            $id = Crypt::decryptString($request->id);
            $profile = BusinessProfile::with('user', 'establishment')->findOrFail($id);

            if (!$profile) {
                return redirect()->back()->with('t-error', 'Business Profile not found.');
            }

            return view('backend.layouts.business_profile.view', compact('profile'));
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }
}
