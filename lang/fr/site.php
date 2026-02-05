<?php

return [
    // Resource
    'resource' => [
        'label' => 'Site',
        'plural' => 'Sites',
        'navigation_group' => 'Administration',
    ],

    // Champs
    'fields' => [
        'nom' => 'Nom du site',
        'code' => 'Code',
        'localisation' => 'Localisation',
        'description' => 'Description',
        'is_active' => 'Actif',
        'affectations_count' => 'Affectations',
        'created_at' => 'Créé le',
    ],

    // Sections
    'sections' => [
        'info' => 'Informations du site',
    ],

    // Messages
    'messages' => [
        'code_help' => 'Code unique d\'identification',
        'deactivate_help' => 'Désactiver pour masquer ce site',
    ],
];
