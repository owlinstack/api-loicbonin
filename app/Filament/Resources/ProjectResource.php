<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = 'Projets';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Détails du Projet')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) =>
                            $set('slug', str($state)->slug()->toString())
                        ),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->required()
                        ->columnSpanFull()
                        ->rows(3),
                    Forms\Components\MarkdownEditor::make('long_description')
                        ->columnSpanFull(),
                ]),

            Section::make('Technologies & Liens')
                ->columns(2)
                ->schema([
                    Forms\Components\TagsInput::make('tech_stack')
                        ->required()
                        ->placeholder('Ajouter une technologie'),
                    Forms\Components\Toggle::make('featured')
                        ->label('Projet mis en avant')
                        ->inline(false),
                    Forms\Components\TextInput::make('live_url')
                        ->url()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('repo_url')
                        ->url()
                        ->maxLength(255),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tech_stack')
                    ->badge()
                    ->separator(','),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('live_url')
                    ->limit(30),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('featured')
                    ->label('Mis en avant'),
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
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
