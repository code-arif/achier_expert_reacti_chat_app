<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class EstablismentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Establishment::all();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white delete-icn" title="Edit">
                                    <i class="fe fe-edit"></i>
                                </a>
                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white delete-icn" title="Delete">
                                    <i class="fe fe-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make();
        }
        return view("backend.layouts.establishment.index");
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required|unique:establishments,title',
        ]);

        try {
            Establishment::create($validate);
            session()->put('t-success', 'Establishment created successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.establishment.index')->with('success', 'Establishment created successfully');
    }

    public function edit(Establishment $establishment, $id)
    {
        $establishment = Establishment::find($id);

        if(!$establishment){
            return response()->json([
               'success' => false,
               'message' => 'Establishment not found.',
            ], 404);
        }

        return response()->json($establishment);
    }

    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'title' => 'required',
        ]);

        try {
            $establishment = Establishment::findOrFail($id);
            $establishment->update($validate);
            session()->put('t-success', 'Establishment updated successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.establishment.index');
    }

    public function destroy(string $id)
    {
        $data = Establishment::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Establishment not found.',
            ], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Establishment deleted successfully!',
        ], 200);
    }

    public function status(int $id): JsonResponse
    {
        $data = Establishment::find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Establishment not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Status Changed successfully!',
        ]);
    }
}
