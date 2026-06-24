<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CodeFileResource\Pages;
use App\Models\CodeFile;
use App\Models\CodeFolder;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Ressource Filament de gestion des fichiers de code source de l'arborescence.
 * Justification : Représente les fichiers de code individuels qui contiennent le code source
 * à afficher sur le front-end et qui peuvent être liés à des articles de blog.
 */
final class CodeFileResource extends Resource
{
    protected static ?string $model = CodeFile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationLabel = 'Fichiers Code';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Métadonnées du Fichier')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('ex: ArticleController.php')
                        ->live(onBlur: true)
                        // Calcule et pré-remplit dynamiquement le chemin absolu du fichier
                        // en concaténant le chemin du dossier parent sélectionné
                        ->afterStateUpdated(function ($state, $get, $set): void {
                            $folderId = $get('folder_id');
                            if ($folderId) {
                                $folder = CodeFolder::find($folderId, ['*']);
                                if ($folder) {
                                    $set('path', rtrim($folder->path, '/').'/'.ltrim($state, '/'));

                                    return;
                                }
                            }
                            $set('path', ltrim($state, '/'));
                        }),
                    Forms\Components\TextInput::make('path')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('ex: app/Http/Controllers/ArticleController.php'),
                    Forms\Components\Select::make('language')
                        ->options([
                            'php' => 'PHP',
                            'typescript' => 'TypeScript',
                            'javascript' => 'JavaScript',
                            'css' => 'CSS',
                            'markdown' => 'Markdown',
                            'json' => 'JSON',
                            'html' => 'HTML',
                            'yaml' => 'YAML',
                            'sql' => 'SQL',
                            'shell' => 'Shell',
                        ])
                        ->required()
                        ->default('php'),
                    Forms\Components\Select::make('folder_id')
                        ->relationship('folder', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Dossier Parent')
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->label('Ordre de tri')
                        ->required(),
                ]),

            Section::make('Contenu du Code')
                ->schema([
                    Forms\Components\Textarea::make('content')
                        ->required()
                        ->columnSpanFull()
                        ->rows(20)
                        ->placeholder('Saisissez ou collez votre code ici...')
                        // Personnalise l'input text-area pour donner l'apparence d'un éditeur de code simple (monospaced)
                        ->extraInputAttributes([
                            'class' => 'font-mono text-xs md:text-sm leading-relaxed bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-700 rounded-lg p-4',
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('path')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('language')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('folder.name')
                    ->label('Dossier')
                    ->sortable(),
                Tables\Columns\TextColumn::make('linkedArticle.title')
                    ->label('Article')
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->label('Tri'),
            ])
            ->defaultSort('path', 'asc')
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCodeFiles::route('/'),
            'create' => Pages\CreateCodeFile::route('/create'),
            'edit' => Pages\EditCodeFile::route('/{record}/edit'),
        ];
    }
}
