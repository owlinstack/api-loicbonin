<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Profile;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Page d'administration dédiée pour gérer et contrôler l'assistant RAG Chat.
 * Justification : Espace dédié séparé du profil utilisateur pour isoler les contrôles de microservices et de consommation IA.
 */
final class ManageRagChat extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Assistant RAG';

    protected static ?string $title = 'Gestion de l\'Assistant RAG Chat';

    protected string $view = 'filament.pages.manage-rag-chat';

    protected static ?int $navigationSort = 7;

    public ?array $data = [];

    /**
     * Hydrate le formulaire au chargement de la page.
     */
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
            'rag_chat_enabled' => false,
            'rag_llm_provider' => 'gemini',
        ]);

        $this->form->fill([
            'rag_chat_enabled' => $profile->rag_chat_enabled ?? false,
            'rag_llm_provider' => $profile->rag_llm_provider ?? 'gemini',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Activation & Moteur LLM')
                    ->description('Activez ou désactivez l\'assistant et choisissez le moteur de réponse LLM.')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Toggle::make('rag_chat_enabled')
                            ->label('Activer l\'assistant RAG Chat')
                            ->helperText('Lorsque désactivé, le widget est masqué du site et le backend bloque immédiatement les requêtes.')
                            ->default(false),

                        Select::make('rag_llm_provider')
                            ->label('Fournisseur du modèle de langage (LLM)')
                            ->helperText('Sélectionnez le fournisseur utilisé pour générer la réponse RAG.')
                            ->options([
                                'gemini' => 'API Gemini (Cloud - Rapide & Haute Précision)',
                                'local' => 'LLM Local (Ollama / Local - Économique / Offline)',
                            ])
                            ->default('gemini')
                            ->required(),
                    ]),

                Section::make('Moteur Vectoriel & Embeddings (Informations)')
                    ->description('Configuration du modèle d\'embeddings et de la base vectorielle.')
                    ->icon('heroicon-o-circle-stack')
                    ->schema([
                        Placeholder::make('embedding_info')
                            ->label('Modèle d\'Embedding actif')
                            ->content('Gemini Embedding 2 (Cloud) / Gemma ONNX (Local fallback) — Géré au niveau du microservice vectoriel.'),
                    ]),
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

    /**
     * Enregistre l'état du RAG Chat.
     */
    public function save(): void
    {
        $profile = Profile::query()->first();
        if ($profile) {
            $state = $this->form->getState();
            $profile->update([
                'rag_chat_enabled' => $state['rag_chat_enabled'] ?? false,
                'rag_llm_provider' => $state['rag_llm_provider'] ?? 'gemini',
            ]);

            $statusText = ($state['rag_chat_enabled'] ?? false) ? 'activé' : 'désactivé';

            Notification::make()
                ->success()
                ->title("Configuration RAG mise à jour avec succès (Chat {$statusText}) !")
                ->send();
        }
    }
}
