<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'image_url' => asset('storage/' . $banner->image_path),
                    'link_url' => $banner->link_url,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $banners
        ]);
    }
}
