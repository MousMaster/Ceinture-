<?php

return [
    // Resource
    'resource' => [
        'label' => 'Permanence',
        'plural' => 'Permanences',
        'navigation_group' => 'Gestion',
    ],

    // Statuts
    'statuts' => [
        'planifiee' => 'Planifiée',
        'en_cours' => 'En cours',
        'validee' => 'Validée',
        'annulee' => 'Annulée',
    ],

    // Champs
    'fields' => [
        'date' => 'Date de la permanence',
        'heure_debut' => 'Heure de début',
        'heure_fin' => 'Heure de fin',
        'officier' => 'Officier de permanence',
        'officier_id' => 'Officier responsable',
        'statut' => 'Statut',
        'commentaire_officier' => 'Commentaire de l\'officier',
        'validated_at' => 'Validée le',
        'periode' => 'Période',
    ],

    // Sections
    'sections' => [
        'info' => 'Informations de la permanence',
        'affectations' => 'Affectations des sous-officiers',
        'commentaire' => 'Commentaire',
        'statut' => 'Statut',
        'relation_manageriale' => 'Relation Managériale',
        'evenements' => 'Événements',
        'validation' => 'Validation du registre',
        'personnel' => 'Personnel affecté',
        'signatures' => 'Signatures',
    ],

    // Actions
    'actions' => [
        'create' => 'Nouvelle permanence',
        'edit' => 'Modifier la permanence',
        'view' => 'Voir la permanence',
        'delete' => 'Supprimer la permanence',
        'start' => 'Démarrer',
        'validate' => 'Valider',
        'reopen' => 'Rouvrir',
        'print' => 'Imprimer (PDF)',
        'download_pdf' => 'Télécharger PDF',
        'add_event' => 'Nouvel événement',
        'add_affectation' => 'Ajouter un sous-officier',
    ],

    // Messages
    'messages' => [
        'started' => 'Permanence démarrée',
        'validated' => 'Permanence validée',
        'reopened' => 'Permanence rouverte',
        'locked' => 'Cette permanence est validée et fermée.',
        'locked_warning' => 'Une fois validée, la permanence ne pourra plus être modifiée.',
        'start_confirm' => 'Voulez-vous démarrer cette permanence ?',
        'validate_confirm' => 'Cette action est irréversible.',
        'reopen_confirm' => 'Cette action permettra de modifier à nouveau la permanence.',
        'no_events' => 'Aucun événement enregistré pour cette permanence.',
        'access_denied' => 'Vous n\'avez pas les droits pour accéder à cette permanence.',
        'edit_denied' => 'Modification interdite après validation.',
    ],

    // Modals
    'modals' => [
        'start_title' => 'Démarrer la permanence',
        'validate_title' => 'Valider la permanence',
        'reopen_title' => 'Rouvrir la permanence',
    ],

    // Relation managériale
    'relation' => [
        'label' => 'Événement',
        'plural' => 'Événements',
        'heure_evenement' => 'Heure de l\'événement',
        'evenement' => 'Événement / Fait constaté',
        'effets_ordonnes' => 'Effets ordonnés',
        'observations' => 'Observations',
        'auteur' => 'Auteur',
        'saisi_le' => 'Saisi le',
    ],

    // PDF
    'pdf' => [
        'title' => 'REGISTRE DE PERMANENCE',
        'date_edition' => 'Date d\'édition',
        'numero' => 'Numéro de permanence',
        'validation_electronic' => 'Document validé électroniquement',
        'validation_date' => 'Validation effectuée le',
        'signature' => 'Signature',
        'fonction' => 'Fonction',
        'footer' => 'Document officiel - Ne pas reproduire sans autorisation',
    ],
];
