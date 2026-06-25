<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CodeFile;
use App\Models\CodeFolder;
use App\Models\CodeProject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Service gérant la génération et la mise en forme de l'arborescence des fichiers de code.
 * Justification : Charge l'intégralité de l'arborescence (dossiers, sous-dossiers, fichiers) en mémoire
 * en exactement 2 requêtes SQL globales (au lieu d'une cascade récursive sur la base),
 * puis construit récursivement l'arbre en mémoire via le partitionnement par collections.
 */
final class CodeTreeService
{
    /**
     * Génère l'arborescence complète du code (tous les dossiers racines et fichiers).
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
     * Génère l'arborescence du code limitée à un projet donné.
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

        // Filtre en mémoire les dossiers qui appartiennent au projet
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

        // Filtre les fichiers qui appartiennent à l'un de ces dossiers
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
     * Construit récursivement un tableau de nœuds de dossiers à partir de collections en mémoire.
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
     * Transforme une collection de modèles CodeFile au format attendu par l'API.
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

    /**
     * Récupère tous les projets de code publiés triés par nom.
     *
     * @return Collection<int, CodeProject>
     */
    public function listPublishedProjects(): Collection
    {
        return CodeProject::query()
            ->where('is_published', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Récupère un projet de code par son slug s'il est publié.
     */
    public function getProjectBySlug(string $slug): ?CodeProject
    {
        /** @var CodeProject|null $project */
        $project = CodeProject::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        return $project;
    }

    /**
     * Récupère un fichier de code par son chemin avec son article lié.
     */
    public function getFileByPath(string $path): ?CodeFile
    {
        /** @var CodeFile|null $file */
        $file = CodeFile::query()
            ->with('linkedArticle')
            ->where('path', $path)
            ->first();

        return $file;
    }
}
