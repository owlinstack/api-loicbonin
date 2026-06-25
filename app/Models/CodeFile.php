<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Modèle représentant un fichier de code source individuel.
 * Justification : Stocke le contenu brut, le langage de programmation et le chemin d'un fichier source
 * pour permettre une navigation et un rendu interactifs dans le portfolio, et se lie optionnellement à une explication textuelle (Article).
 *
 * @property string $id
 * @property string $name
 * @property string $path
 * @property string $language
 * @property string $content
 * @property string|null $folder_id
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CodeFolder|null $folder
 * @property-read Article|null $linkedArticle
 */
final class CodeFile extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'path',
        'language',
        'content',
        'folder_id',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * @return BelongsTo<CodeFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(CodeFolder::class, 'folder_id');
    }

    /**
     * @return HasOne<Article, $this>
     */
    public function linkedArticle(): HasOne
    {
        return $this->hasOne(Article::class, 'code_file_id');
    }
}
