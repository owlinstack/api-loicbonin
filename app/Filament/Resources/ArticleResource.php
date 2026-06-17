<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ArticleStatus;
use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Articles';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->schema([
            Section::make('Contenu')
                ->icon('heroicon-o-document-text')
                ->columns(1)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug()->toString())
                        ),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Textarea::make('excerpt')
                        ->required()
                        ->rows(3),
                    Forms\Components\MarkdownEditor::make('content')
                        ->required()
                        ->minHeight('400px')
                        ->placeholder('Rédigez le contenu de votre article ici au format Markdown...')
                        ->fileAttachmentsDisk('public')
                        ->fileAttachmentsDirectory('attachments')
                        ->toolbarButtons([
                            'attachFiles',
                            'blockquote',
                            'bold',
                            'bulletList',
                            'codeBlock',
                            'heading',
                            'italic',
                            'link',
                            'orderedList',
                            'redo',
                            'strike',
                            'undo',
                        ]),
                ]),

            Section::make('Métadonnées')
                ->icon('heroicon-o-tag')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'label')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('label')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug()->toString())
                                ),
                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->unique('categories', 'slug')
                                ->maxLength(255),
                        ]),
                    Forms\Components\Select::make('tags')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                        ]),
                    Forms\Components\Select::make('status')
                        ->options(ArticleStatus::class)
                        ->required()
                        ->default(ArticleStatus::Draft),
                    Forms\Components\TextInput::make('reading_time')
                        ->numeric()
                        ->required()
                        ->suffix('min'),
                    Forms\Components\Toggle::make('featured')
                        ->label('Article mis en avant'),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Date de publication'),
                ]),

            Section::make('Code Source Associé')
                ->icon('heroicon-o-code-bracket')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('code_type')
                        ->label('Type de liaison')
                        ->options([
                            'none' => 'Aucun',
                            'file' => 'Fichier de code seul',
                            'folder' => 'Dossier de code',
                            'project' => 'Projet de code',
                        ])
                        ->default('none')
                        ->reactive()
                        ->afterStateHydrated(function ($set, $record): void {
                            if ($record) {
                                if ($record->code_file_id) {
                                    $set('code_type', 'file');
                                } elseif ($record->code_folder_id) {
                                    $set('code_type', 'folder');
                                } elseif ($record->code_project_id) {
                                    $set('code_type', 'project');
                                } else {
                                    $set('code_type', 'none');
                                }
                            }
                        })
                        ->afterStateUpdated(function ($state, $set): void {
                            $set('code_file_id', null);
                            $set('code_folder_id', null);
                            $set('code_project_id', null);
                        }),
                    Forms\Components\Select::make('code_file_id')
                        ->label('Fichier de code')
                        ->relationship('codeFile', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn ($get) => $get('code_type') === 'file')
                        ->required(fn ($get) => $get('code_type') === 'file'),
                    Forms\Components\Select::make('code_folder_id')
                        ->label('Dossier de code')
                        ->relationship('codeFolder', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn ($get) => $get('code_type') === 'folder')
                        ->required(fn ($get) => $get('code_type') === 'folder'),
                    Forms\Components\Select::make('code_project_id')
                        ->label('Projet de code')
                        ->relationship('codeProject', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn ($get) => $get('code_type') === 'project')
                        ->required(fn ($get) => $get('code_type') === 'project'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.label')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ArticleStatus $state) => match ($state) {
                        ArticleStatus::Published => 'success',
                        ArticleStatus::Draft => 'warning',
                        ArticleStatus::Archived => 'gray',
                    }),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ArticleStatus::class),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'label'),
            ])
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
