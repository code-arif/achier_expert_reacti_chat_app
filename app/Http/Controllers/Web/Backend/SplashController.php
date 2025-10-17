<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\Splash;
use Illuminate\View\View;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class SplashController extends Controller
{
    use ApiResponse;

    public function Splash()
    {
        try {
            $data = Splash::first();

            if (!$data) {
                return $this->success([], 'Splash Data not found', 200);
            }

            return $this->success($data, 'Splash Data successfully retrieved', 200);
        } catch (Exception $e) {

            Log::error($e->getMessage());
            return $this->error($e->getMessage(), 'Error while fetching Splash Data', 500);
        }
    }

    public function index(): View
    {
        $splash = Splash::first();
        return view('backend.layouts.splash.index', compact('splash'));
    }


    public function createOrUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
        ]);


        $splash = Splash::first();

        if ($splash) {

            $splash->update($request->all());
            $message = 'Splash updated successfully!';
        } else {

            Splash::create($request->all());
            $message = 'Splash created successfully!';
        }

        return redirect()->route('admin.splash.index')->with('status', $message);
    }



    public function destroy(Splash $splash): RedirectResponse
    {
        $splash->delete();

        return redirect()->route('admin.splash.index')->with('status', 'Splash deleted successfully!');
    }
}
