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
        /** @var array<int, string> $publishedProjectIds */
        $publishedProjectIds = CodeProject::query()
            ->where('is_published', true)
            ->pluck('id')
            ->all();

        /** @var Collection<int, CodeFolder> $allFolders */
        $allFolders = CodeFolder::with('parent')->orderBy('sort_order')->get();

        /** @var Collection<int, CodeFile> $allFiles */
        $allFiles = CodeFile::with('linkedArticle')
            ->orderBy('sort_order')
            ->get();

        // Sécurité : exclut les dossiers liés à des projets non publiés (fuite d'information)
        $allFolders = $this->filterFoldersByPublishedProjects($allFolders, $publishedProjectIds);
        $validFolderIds = $allFolders->pluck('id')->all();

        // Exclut les fichiers appartenant aux dossiers filtrés
        $allFiles = $allFiles->filter(
            fn (CodeFile $file) => $file->folder_id === null || \in_array($file->folder_id, $validFolderIds, true),
        );

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
     * Filtre une collection de dossiers pour exclure ceux liés à des projets non publiés,
     * en propageant l'exclusion récursivement à tous les sous-dossiers enfants.
     *
     * @param  Collection<int, CodeFolder>  $allFolders
     * @param  array<int, string>  $publishedProjectIds
     * @return Collection<int, CodeFolder>
     */
    private function filterFoldersByPublishedProjects(Collection $allFolders, array $publishedProjectIds): Collection
    {
        // Identifie les dossiers racines des projets non publiés
        $invalidFolderIds = $allFolders
            ->filter(fn (CodeFolder $f) => $f->code_project_id !== null && ! \in_array($f->code_project_id, $publishedProjectIds, true))
            ->pluck('id')
            ->values()
            ->all();

        if (empty($invalidFolderIds)) {
            return $allFolders;
        }

        // Propage récursivement l'exclusion aux sous-dossiers
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($allFolders as $folder) {
                if (! \in_array($folder->id, $invalidFolderIds, true) && \in_array($folder->parent_id, $invalidFolderIds, true)) {
                    $invalidFolderIds[] = $folder->id;
                    $changed = true;
                }
            }
        }

        return $allFolders->reject(fn (CodeFolder $f) => \in_array($f->id, $invalidFolderIds, true));
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
     * Sécurité : retourne null si le fichier appartient à un projet non publié (fuite d'information).
     */
    public function getFileByPath(string $path): ?CodeFile
    {
        /** @var CodeFile|null $file */
        $file = CodeFile::query()
            ->with(['linkedArticle', 'folder'])
            ->where('path', $path)
            ->first();

        if ($file === null) {
            return null;
        }

        // Vérifie que le fichier appartient à un projet publié
        if (! $this->isFileAccessible($file)) {
            return null;
        }

        return $file;
    }

    /**
     * Détermine si un fichier est accessible publiquement.
     * Remonte l'arborescence des dossiers jusqu'au projet associé et vérifie son statut de publication.
     * Un fichier sans projet parent est considéré accessible (dossier global).
     */
    private function isFileAccessible(CodeFile $file): bool
    {
        $folder = $file->folder;

        // Fichier sans dossier parent : accessible (fichier global)
        if ($folder === null) {
            return true;
        }

        // Remonte l'arborescence pour trouver le projet associé
        $current = $folder;
        while ($current !== null) {
            if ($current->code_project_id !== null) {
                // Vérifie en base si ce projet est bien publié
                return CodeProject::query()
                    ->where('id', $current->code_project_id)
                    ->where('is_published', true)
                    ->exists();
            }

            $current->loadMissing('parent');
            $current = $current->parent;
        }

        // Aucun projet trouvé dans l'arborescence : dossier global, accessible
        return true;
    }
}
