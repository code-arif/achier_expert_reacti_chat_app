<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Faq;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {
            $data = Faq::latest();

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
                              <a href="' . route('admin.faq.edit', ['id' => $data->id]) . '" class="btn btn-primary text-white" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="btn btn-danger text-white" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div>';
                })
                ->rawColumns(['status', 'action', 'status'])
                ->make(true);
        }
        return view('backend.layouts.faq.index');
    }

    public function create() {
        return view('backend.layouts.faq.create');
    }

    public function store(Request $request) {
        $validate = $request->validate([
            'question' => 'required',
            'answer' => 'required',
            'faq_type' => 'nullable',
        ]);

        try {
            Faq::create($validate);
            session()->put('t-success', 'FAQ created successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.faq.index')->with('success', 'FAQ created successfully');
    }

    public function edit($id) {
        $data = Faq::findOrFail($id);
        return view('backend.layouts.faq.edit', compact('data'));
    }

    public function update(Request $request, int $id) {
        $validate = $request->validate([
            'question' => 'required',
            'answer' => 'required',
            'faq_type' => 'nullable',
        ]);

        try {
            $faq = Faq::findOrFail($id);
            $faq->update($validate);
            session()->put('t-success', 'FAQ updated successfully');
        } catch (Exception $e) {
            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.faq.index');
    }

    public function status(int $id): JsonResponse {
        $data = Faq::findOrFail($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'FAQ not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 'success',
            'message' => 'FAQ Status Changed successfully!',
        ]);
    }

    public function destroy(int $id): JsonResponse {
        $data = Faq::findOrFail($id);
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
