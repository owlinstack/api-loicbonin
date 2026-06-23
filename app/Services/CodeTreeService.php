<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\CodeProject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

final class CodeTreeService
{
    /**
     * Build the full code tree (all root folders and files).
     *
     * @return array<int, mixed>
     */
    public function getFullTree(): array
    {
        /** @var Collection<int, CodeFolder> $allFolders */
        $allFolders = CodeFolder::with('parent')->orderBy('sort_order')->get();

        /** @var Collection<int, CodeFile> $allFiles */
        $allFiles = CodeFile::with('linkedArticle')
            ->orderBy('sort_order')
            ->get();

        $rootFolders = $allFolders->filter(fn (CodeFolder $folder) => ! $folder->parent_id);
        $rootFiles = $allFiles->filter(fn (CodeFile $file) => ! $file->folder_id);

        /** @var SupportCollection<string, Collection<int, CodeFolder>> $foldersGrouped */
        $foldersGrouped = $allFolders->filter(fn (CodeFolder $folder) => (bool) $folder->parent_id)
            ->groupBy('parent_id');

        /** @var SupportCollection<string, Collection<int, CodeFile>> $filesGrouped */
        $filesGrouped = $allFiles->filter(fn (CodeFile $file) => (bool) $file->folder_id)
            ->groupBy('folder_id');

        return array_merge(
            $this->buildFolderTreeFromMemory($rootFolders, $foldersGrouped, $filesGrouped),
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
        /** @var Collection<int, CodeFolder> $allFolders */
        $allFolders = CodeFolder::with('parent')->orderBy('sort_order')->get();

        /** @var Collection<int, CodeFile> $allFiles */
        $allFiles = CodeFile::with('linkedArticle')
            ->orderBy('sort_order')
            ->get();

        // Filter folders that belong to the project in memory
        $projectFolders = $allFolders->filter(function (CodeFolder $folder) use ($project, $allFolders) {
            $current = $folder;
            while ($current) {
                if ($current->code_project_id === $project->id) {
                    return true;
                }
                $current = $current->parent_id
                    ? $allFolders->first(fn (CodeFolder $f) => $f->id === $current->parent_id)
                    : null;
            }

            return false;
        });

        $folderIds = $projectFolders->pluck('id')->all();

        // Filter files that belong to any of these folders
        $projectFiles = $allFiles->filter(fn (CodeFile $file) => \in_array($file->folder_id, $folderIds, true));

        $rootFolders = $projectFolders->filter(fn (CodeFolder $folder) => ! $folder->parent_id);

        /** @var SupportCollection<string, Collection<int, CodeFolder>> $foldersGrouped */
        $foldersGrouped = $projectFolders->filter(fn (CodeFolder $folder) => (bool) $folder->parent_id)
            ->groupBy('parent_id');

        /** @var SupportCollection<string, Collection<int, CodeFile>> $filesGrouped */
        $filesGrouped = $projectFiles->groupBy('folder_id');

        return $this->buildFolderTreeFromMemory($rootFolders, $foldersGrouped, $filesGrouped);
    }

    /**
     * Recursively build a folder tree node array from in-memory collections.
     *
     * @param  Collection<int, CodeFolder>  $folders
     * @param  SupportCollection<string, Collection<int, CodeFolder>>  $foldersGrouped
     * @param  SupportCollection<string, Collection<int, CodeFile>>  $filesGrouped
     * @return array<int, mixed>
     */
    private function buildFolderTreeFromMemory(
        Collection $folders,
        SupportCollection $foldersGrouped,
        SupportCollection $filesGrouped,
    ): array {
        $tree = [];

        foreach ($folders as $folder) {
            /** @var Collection<int, CodeFolder> $childFolders */
            $childFolders = $foldersGrouped->get($folder->id) ?? new Collection;

            /** @var Collection<int, CodeFile> $files */
            $files = $filesGrouped->get($folder->id) ?? new Collection;

            $tree[] = [
                'name' => $folder->name,
                'path' => $folder->path,
                'children' => array_merge(
                    $this->buildFolderTreeFromMemory($childFolders, $foldersGrouped, $filesGrouped),
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
        return array_values($files->map(fn (CodeFile $f) => [
            'name' => $f->name,
            'path' => $f->path,
            'language' => $f->language,
            'content' => $f->content,
            'linkedArticleSlug' => $f->linkedArticle?->slug,
            'linkedArticleTitle' => $f->linkedArticle?->title,
        ])->toArray());
    }
}
