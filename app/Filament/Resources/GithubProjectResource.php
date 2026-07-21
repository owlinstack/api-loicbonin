<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\GithubProjectStatus;
use App\Filament\Resources\GithubProjectResource\Pages;
use App\Models\GithubProject;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Ressource Filament pour la gestion des projets/dépôts GitHub affichés sur le portfolio.
 */
final class GithubProjectResource extends Resource
{
    protected static ?string $model = GithubProject::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-code-bracket-square';

    protected static ?string $navigationLabel = 'Projets GitHub';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Détails du Projet GitHub')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom du Dépôt')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug()->toString())),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('github_url')
                        ->label('URL GitHub')
                        ->placeholder('https://github.com/votre-user/votre-repo')
                        ->url()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            GithubProjectStatus::Draft->value => 'Brouillon',
                            GithubProjectStatus::Published->value => 'Publié',
                            GithubProjectStatus::Archived->value => 'Archivé',
                        ])
                        ->default(GithubProjectStatus::Published->value)
                        ->required(),
                    Forms\Components\Textarea::make('description')
                        ->label('Description Courte')
                        ->columnSpanFull()
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('github_url')
                    ->label('URL')
                    ->limit(35)
                    ->url(fn ($record) => $record->github_url, true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (GithubProjectStatus $state): string => match ($state) {
                        GithubProjectStatus::Published => 'success',
                        GithubProjectStatus::Draft => 'warning',
                        GithubProjectStatus::Archived => 'gray',
                    }),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
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
            'index' => Pages\ListGithubProjects::route('/'),
            'create' => Pages\CreateGithubProject::route('/create'),
            'edit' => Pages\EditGithubProject::route('/{record}/edit'),
        ];
    }
}
