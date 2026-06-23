<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CodeProjectResource\Pages;
use App\Models\CodeProject;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class CodeProjectResource extends Resource
{
    protected static ?string $model = CodeProject::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = 'Projets Code';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Détails du Projet de Code')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) => $set('slug', str($state)->slug()->toString())),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\Toggle::make('is_published')
                        ->label('Publié')
                        ->default(true),
                    Forms\Components\Textarea::make('description')
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Publié'),
                Tables\Columns\TextColumn::make('folders_count')
                    ->counts('folders')
                    ->badge()
                    ->label('Dossiers'),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListCodeProjects::route('/'),
            'create' => Pages\CreateCodeProject::route('/create'),
            'edit' => Pages\EditCodeProject::route('/{record}/edit'),
        ];
    }
}
