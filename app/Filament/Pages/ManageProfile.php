<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Profile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ManageProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Mon Profil';
    protected static ?string $title = 'Gérer le Profil';
    protected string $view = 'filament.pages.manage-profile';
    protected static ?int $navigationSort = 6;

    public ?array $data = [];

    public function mount(): void
    {
        $profile = Profile::first() ?? Profile::create([
            'name' => 'Loïc Bonin',
            'bio' => "Développeur full-stack basé à Paris.",
            'skills' => [],
            'timeline' => [],
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
                FileUpload::make('cv_url')
                    ->label('CV (PDF)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('cvs')
                    ->downloadable()
                    ->openable(),
                Repeater::make('skills')
                    ->label('Compétences')
                    ->schema([
                        TextInput::make('term')->required(),
                        TextInput::make('description')->required(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Repeater::make('timeline')
                    ->label('Parcours (Timeline)')
                    ->schema([
                        TextInput::make('date')->required()->placeholder('ex: 2024 - présent'),
                        TextInput::make('title')->required(),
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
        $profile = Profile::first();
        if ($profile) {
            $profile->update($this->form->getState());

            Notification::make()
                ->success()
                ->title('Profil mis à jour !')
                ->send();
        }
    }
}
