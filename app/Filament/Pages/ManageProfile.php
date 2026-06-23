<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Profile;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

final class ManageProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Mon Profil';

    protected static ?string $title = 'Gérer le Profil';

    protected string $view = 'filament.pages.manage-profile';

    protected static ?int $navigationSort = 6;

    public ?array $data = [];

    public function mount(): void
    {
        $profile = Profile::query()->first() ?? Profile::query()->create([
            'name' => 'Loïc Bonin',
            'bio' => 'Développeur full-stack basé à Lyon.',
            'skills' => [],
            'timeline' => [],
            'show_timeline' => true,
            'education' => [],
            'show_education' => true,
        ]);

        $this->form->fill($profile->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('bio')
                    ->required()
                    ->rows(4),
                FileUpload::make('avatar_url')
                    ->label('Photo de Profil')
                    ->image()
                    ->disk('public')
                    ->directory('avatars')
                    ->preserveFilenames(),
                FileUpload::make('cv_url')
                    ->label('CV (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('cvs')
                    ->downloadable()
                    ->openable()
                    ->preserveFilenames(),
                Repeater::make('skills')
                    ->label('Compétences')
                    ->schema([
                        TextInput::make('term')->required(),
                        TextInput::make('description')->required(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Toggle::make('show_timeline')
                    ->label('Afficher le parcours (Timeline)')
                    ->default(true),
                Repeater::make('timeline')
                    ->label('Parcours (Timeline)')
                    ->schema([
                        TextInput::make('date')->required()->placeholder('ex: 2024 - présent'),
                        TextInput::make('title')->required(),
                        Textarea::make('description')->required()->rows(2),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Toggle::make('show_education')
                    ->label("Afficher l'éducation")
                    ->default(true),
                Repeater::make('education')
                    ->label('Éducation / Formations')
                    ->schema([
                        TextInput::make('date')->required()->placeholder('ex: 2017 - 2019'),
                        TextInput::make('title')->required()->placeholder('ex: Master Informatique, EPITA'),
                        Textarea::make('description')->required()->rows(2),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $profile = Profile::query()->first();
        if ($profile) {
            $profile->update($this->form->getState());

            Notification::make()
                ->success()
                ->title('Profil mis à jour !')
                ->send();
        }
    }
}
