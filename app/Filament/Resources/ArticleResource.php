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
        return $schema->schema([
            Section::make('Contenu')
                ->columns(2)
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
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\MarkdownEditor::make('content')
                        ->required()
                        ->columnSpanFull()
                        ->minHeight('400px')
                        ->placeholder('Rédigez le contenu de votre article ici au format Markdown...')
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
                ->columns(3)
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
