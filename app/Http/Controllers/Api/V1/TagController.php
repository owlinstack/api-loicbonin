<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

final class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::query()->pluck('name');

        return response()->json($tags);
    }
}
