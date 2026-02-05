<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Alerte de sécurité --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-warning-600 dark:text-warning-400">
                    <x-heroicon-o-shield-exclamation class="h-5 w-5" />
                    Zone réservée à l'administrateur
                </div>
            </x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Cette section permet d'exporter les données du système. 
                <strong>Toutes les actions d'export sont journalisées</strong> pour des raisons de sécurité et d'audit.
            </p>
        </x-filament::section>

        {{-- Formulaire d'export --}}
        <x-filament::section>
            <x-slot name="heading">Options d'export</x-slot>
            <x-slot name="description">
                Sélectionnez les données à exporter et le format souhaité.
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        {{-- Informations sur les formats --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-information-circle class="h-5 w-5" />
                    Informations sur les formats
                </div>
            </x-slot>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">CSV (Comma Separated Values)</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Format tabulaire compatible Excel et autres tableurs. 
                        Idéal pour l'analyse des données et les rapports.
                    </p>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">JSON</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Format technique structuré. 
                        Idéal pour les sauvegardes et l'interopérabilité avec d'autres systèmes.
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Données disponibles --}}
        <x-filament::section collapsible collapsed>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-table-cells class="h-5 w-5" />
                    Données exportables
                </div>
            </x-slot>

            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-users class="h-5 w-5 text-primary-500 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">Utilisateurs</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tous les utilisateurs du système (sans les mots de passe) : nom, prénom, matricule, email, type, statut.
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <x-heroicon-o-calendar-days class="h-5 w-5 text-primary-500 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">Permanences</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Toutes les permanences avec leurs affectations : date, période, officier, sous-officiers, statut.
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <x-heroicon-o-document-text class="h-5 w-5 text-primary-500 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">Événements</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Relations managériales : heure, auteur, événement, effets ordonnés, observations.
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-primary-500 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-white">Journaux d'audit</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Historique complet des actions : qui, quoi, quand. Traçabilité complète des opérations.
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
