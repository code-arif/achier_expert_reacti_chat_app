<?php

namespace App\Http\Controllers\Web\Backend;

use App\Models\User;
use App\Helper\Helper;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class UserListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::where('role', '!=', 'admin');

            // Apply filter BEFORE ->get()
            if ($request->has('role') && $request->role !== 'all') {
                $query->where('role', $request->role);
            }

            $users = $query->get(); // now run the query

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('name', fn($row) => $row->f_name . ' ' . $row->l_name)
                ->addColumn('email', fn($row) => $row->email ?? '---')
                ->addColumn('profession', fn($row) => $row->profession ?? '---')
                ->addColumn('address', function ($item) {
                    return strlen($item->address) > 20 ? substr($item->address, 0, 20) . '...' : $item->address;
                })
                ->addColumn('country', fn($item) => $item->country ?? '---')
                ->addColumn('city', fn($row) => $row->city ?? '---')
                ->addColumn('created_at', fn($row) => optional($row->created_at)->format('Y-m-d'))
                ->addColumn('role', function ($item) {
                    $role = $item->role;
                    $colors = [
                        'user' => 'success',
                        'dj' => 'warning',
                        'promoter' => 'info',
                        'artist' => 'danger',
                        'venue' => 'primary',
                    ];
                    $label = ucfirst(str_replace('_', ' ', $role));
                    $badgeClass = $colors[$role] ?? 'secondary';
                    return '<span class="badge bg-' . $badgeClass . '">' . $label . '</span>';
                })
                ->rawColumns(['role'])
                ->make();
        }

        return view("backend.layouts.user.index");
    }
}
