<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoryController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::withCount(['articles' => function ($query): void {
            $query->where('status', ArticleStatus::Published);
        }])->get();

        return CategoryResource::collection($categories);
    }
}
