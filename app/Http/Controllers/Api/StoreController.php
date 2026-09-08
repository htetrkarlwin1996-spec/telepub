<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MusicStore;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    /**
     * List active music stores.
     */
    public function index(): JsonResponse
    {
        $stores = MusicStore::where('is_active', true)->get();

        return response()->json(['data' => $stores]);
    }
}
