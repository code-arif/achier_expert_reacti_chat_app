<?php

namespace App\Http\Controllers\Web\Backend\Settings;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SocialController extends Controller {
    /**
     * Display mail settings page.
     *
     * @return View
     */
    public function index(): View {
        $settings = [
            'google_client_id'    => env('GOOGLE_CLIENT_ID', ''),
            'google_client_secret'=> env('GOOGLE_CLIENT_SECRET', ''),
            'google_redirect_url' => env('GOOGLE_REDIRECT_URL', '')
        ];

        return view('backend.layouts.settings.social_settings', compact('settings'));
    }

    /**
     * Update mail settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse {
        $request->validate([
            'google_client_id'            => 'nullable|string',
            'google_client_secret'         => 'nullable|string',
            'google_redirect_url' => 'nullable|string'
        ]);

        try {
            $envContent = File::get(base_path('.env'));
            $lineBreak  = "\n";
            $envContent = preg_replace([
                '/GOOGLE_CLIENT_ID=(.*)\s*/',
                '/GOOGLE_CLIENT_SECRET=(.*)\s*/',
                '/GOOGLE_REDIRECT_URL=(.*)\s*/'
            ], [
                'GOOGLE_CLIENT_ID=' . $request->google_client_id.$lineBreak,
                'GOOGLE_CLIENT_SECRET=' . $request->google_client_secret.$lineBreak,
                'GOOGLE_REDIRECT_URL=' . $request->google_redirect_url.$lineBreak
            ], $envContent);

            File::put(base_path('.env'), $envContent);

            return back()->with('t-success', 'Updated successfully');
        } catch (Exception) {
            return back()->with('t-error', 'Failed to update');
        }
    }
}
