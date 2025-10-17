<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PostListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $posts = Post::with('user')->get();

            return DataTables::of($posts)
                ->addIndexColumn()

                ->addColumn('user', fn($item) => $item->user->f_name . ' ' . $item->user->l_name)

                ->addColumn('content', function ($item) {
                    return strlen($item->content) > 50 ? substr($item->content, 0, 50) . '...' : $item->content;
                })
                ->addColumn('like', fn($item) => $item->like_count)
                ->addColumn('comment', fn($item) => $item->comment_count)
                ->addColumn('share', fn($item) => $item->share_count)

                ->addColumn('created_at', fn($item) => $item->created_at->format('Y-m-d h:i A'))

                ->rawColumns(['content'])
                ->make();
        }

        return view("backend.layouts.post.index");
    }
}
