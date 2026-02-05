<?php

return [
    // Resource
    'resource' => [
        'label' => 'Utilisateur',
        'plural' => 'Utilisateurs',
        'navigation_group' => 'Administration',
    ],

    // Types
    'types' => [
        'admin' => 'Administrateur',
        'officier' => 'Officier',
        'sous_officier' => 'Sous-officier',
        'viewer' => 'Consultant',
    ],

    // Fonctions (sous-officier)
    'fonctions' => [
        'operateur' => 'Opérateur',
        'chef_poste' => 'Chef de poste',
    ],

    // Champs
    'fields' => [
        'nom' => 'Nom',
        'prenom' => 'Prénom',
        'nom_complet' => 'Nom complet',
        'matricule' => 'Matricule',
        'email' => 'Adresse email',
        'password' => 'Mot de passe',
        'type' => 'Type d\'utilisateur',
        'fonction' => 'Fonction',
        'is_active' => 'Actif',
        'created_at' => 'Créé le',
    ],

    // Sections
    'sections' => [
        'personal' => 'Informations personnelles',
        'access' => 'Accès',
    ],

    // Actions
    'actions' => [
        'activate' => 'Activer',
        'deactivate' => 'Désactiver',
        'toggle_active' => 'Activer/Désactiver',
    ],

    // Messages
    'messages' => [
        'deactivate_help' => 'Désactiver pour bloquer l\'accès',
        'read_only' => 'Accès en lecture seule',
    ],
];
