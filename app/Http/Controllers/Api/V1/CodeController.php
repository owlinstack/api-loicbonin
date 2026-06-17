<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CodeFileResource;
use App\Models\CodeFile;
use App\Models\CodeFolder;
use Illuminate\Http\JsonResponse;

final class CodeController extends Controller
{
    public function tree(): JsonResponse
    {
        // Simplification: Construction récursive simple en mémoire.
        // Plafond: Pas de mise en cache ni d'optimisation de requêtes (N+1 possible).
        // Plan d'évolution: Déplacer la logique dans CodeTreeService avec eager loading et cache Redis.
        $rootFolders = CodeFolder::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $rootFiles = CodeFile::whereNull('folder_id')
            ->orderBy('sort_order')
            ->get();

        $buildTree = function ($folders) use (&$buildTree) {
            $tree = [];
            foreach ($folders as $folder) {
                $childrenFolders = CodeFolder::where('parent_id', $folder->id)
                    ->orderBy('sort_order')
                    ->get();

                $files = CodeFile::where('folder_id', $folder->id)
                    ->orderBy('sort_order')
                    ->get();

                $mappedFiles = $files->map(fn (CodeFile $f) => [
                    'name' => $f->name,
                    'path' => $f->path,
                    'language' => $f->language,
                    'content' => $f->content,
                    'linkedArticleSlug' => $f->linkedArticle?->slug,
                    'linkedArticleTitle' => $f->linkedArticle?->title,
                ])->toArray();

                $tree[] = [
                    'name' => $folder->name,
                    'path' => $folder->path,
                    'children' => array_merge(
                        $buildTree($childrenFolders),
                        $mappedFiles
                    ),
                ];
            }

            return $tree;
        };

        $treeData = array_merge(
            $buildTree($rootFolders),
            $rootFiles->map(fn (CodeFile $f) => [
                'name' => $f->name,
                'path' => $f->path,
                'language' => $f->language,
                'content' => $f->content,
                'linkedArticleSlug' => $f->linkedArticle?->slug,
                'linkedArticleTitle' => $f->linkedArticle?->title,
            ])->toArray()
        );

        return response()->json($treeData);
    }

    public function show(string $path): CodeFileResource
    {
        $file = CodeFile::with('linkedArticle')
            ->where('path', $path)
            ->firstOrFail();

        return new CodeFileResource($file);
    }
}
