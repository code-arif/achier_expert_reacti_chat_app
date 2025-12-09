<?php

namespace App\Http\Controllers\Api;

use App\Models\DynamicPage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PrivacyController extends Controller
{
    public function index()
    {
        $data = DynamicPage::where('page_slug', 'privacy-policy')->first();
        return response()->json([
            'status' => true,
            'message' => 'Privacy and Terms fetched successfully',
            'data' => $data
        ]);
    }
}
