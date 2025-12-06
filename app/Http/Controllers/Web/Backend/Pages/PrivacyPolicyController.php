<?php

namespace App\Http\Controllers\Web\Backend\Pages;

use Illuminate\Http\Request;
use App\Models\PrivecyAndTerms;
use App\Http\Controllers\Controller;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $data = PrivecyAndTerms::get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }
}
