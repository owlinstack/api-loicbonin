<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\CodeProject;
use Illuminate\Database\Eloquent\Collection;

final class CodeTreeService
{
    /**
     * Build the full code tree (all root folders and files).
     *
     * @return array<int, mixed>
     */
    public function getFullTree(): array
    {
        /** @var Collection<int, CodeFolder> $rootFolders */
        $rootFolders = CodeFolder::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        /** @var Collection<int, CodeFile> $rootFiles */
        $rootFiles = CodeFile::with('linkedArticle')
            ->whereNull('folder_id')
            ->orderBy('sort_order')
            ->get();

        return array_merge(
            $this->buildFolderTree($rootFolders),
            $this->mapFiles($rootFiles),
        );
    }

    /**
     * Build the code tree scoped to a given project.
     *
     * @return array<int, mixed>
     */
    public function getProjectTree(CodeProject $project): array
    {
        /** @var Collection<int, CodeFolder> $rootFolders */
        $rootFolders = CodeFolder::where('code_project_id', $project->id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return $this->buildFolderTree($rootFolders);
    }

    /**
     * Recursively build a folder tree node array.
     *
     * @param  Collection<int, CodeFolder>  $folders
     * @return array<int, mixed>
     */
    private function buildFolderTree(Collection $folders): array
    {
        $tree = [];

        foreach ($folders as $folder) {
            /** @var Collection<int, CodeFolder> $childFolders */
            $childFolders = CodeFolder::where('parent_id', $folder->id)
                ->orderBy('sort_order')
                ->get();

            /** @var Collection<int, CodeFile> $files */
            $files = CodeFile::with('linkedArticle')
                ->where('folder_id', $folder->id)
                ->orderBy('sort_order')
                ->get();

            $tree[] = [
                'name'     => $folder->name,
                'path'     => $folder->path,
                'children' => array_merge(
                    $this->buildFolderTree($childFolders),
                    $this->mapFiles($files),
                ),
            ];
        }

        return $tree;
    }

    /**
     * Map a collection of CodeFile models to the API array shape.
     *
     * @param  Collection<int, CodeFile>  $files
     * @return array<int, mixed>
     */
    private function mapFiles(Collection $files): array
    {
        return $files->map(fn (CodeFile $f) => [
            'name'               => $f->name,
            'path'               => $f->path,
            'language'           => $f->language,
            'content'            => $f->content,
            'linkedArticleSlug'  => $f->linkedArticle?->slug,
            'linkedArticleTitle' => $f->linkedArticle?->title,
        ])->toArray();
    }
}
