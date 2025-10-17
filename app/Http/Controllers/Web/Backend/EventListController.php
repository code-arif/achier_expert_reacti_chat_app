<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class EventListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $events = Event::with('user')->get();

            return DataTables::of($events)
                ->addIndexColumn()
                ->addColumn('title', fn($item) => $item->title)
                ->addColumn('description', function ($item) {
                    return strlen($item->description) > 50 ? substr($item->description, 0, 50) . '...' : $item->description;
                })
                ->addColumn('start_date', fn($item) => $item->start_date->format('Y-m-d') . ' || ' . $item->start_time->format('h:i A'))
                ->addColumn('end_date', fn($item) => $item->end_date->format('Y-m-d') . ' || ' . $item->end_time->format('h:i A'))

                 ->addColumn('user', fn($item) => $item->user->f_name . ' ' . $item->user->l_name)

                ->addColumn('status', function ($item) {
                    $status = $item->status;
                    $colors = [
                        'going_live' => 'success',
                        'pending' => 'warning',
                        'postponed' => 'info',
                        'cancelled' => 'danger',
                        'completed' => 'primary',
                    ];

                    $label = ucfirst(str_replace('_', ' ', $status));
                    $badgeClass = $colors[$status] ?? 'secondary';

                    return '<span class="badge bg-' . $badgeClass . '">' . $label . '</span>';
                })

                ->rawColumns(['description', 'status'])
                ->make();
        }

        return view("backend.layouts.event.index");
    }
}
