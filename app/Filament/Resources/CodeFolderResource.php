<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CodeFolderResource\Pages;
use App\Models\CodeFolder;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class CodeFolderResource extends Resource
{
    protected static ?string $model = CodeFolder::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Dossiers Code';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Détails du Dossier')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('ex: app, Http, Controllers')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $get, $set): void {
                            // Si parent_id est défini, on peut pré-remplir le chemin avec le chemin parent
                            $parentId = $get('parent_id');
                            if ($parentId) {
                                $parent = CodeFolder::find($parentId);
                                if ($parent) {
                                    $set('path', rtrim($parent->path, '/').'/'.ltrim($state, '/'));

                                    return;
                                }
                            }
                            $set('path', ltrim($state, '/'));
                        }),
                    Forms\Components\TextInput::make('path')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder('ex: app/Http/Controllers'),
                    Forms\Components\Select::make('parent_id')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Dossier Parent')
                        ->live(),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->label('Ordre de tri')
                        ->required(),
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
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Dossier Parent')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->label('Tri'),
                Tables\Columns\TextColumn::make('files_count')
                    ->counts('files')
                    ->badge()
                    ->label('Fichiers'),
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
            'index' => Pages\ListCodeFolders::route('/'),
            'create' => Pages\CreateCodeFolder::route('/create'),
            'edit' => Pages\EditCodeFolder::route('/{record}/edit'),
        ];
    }
}
