<?php

return [
    // Resource
    'resource' => [
        'label' => 'Appareil',
        'plural' => 'Appareils',
        'navigation_group' => 'Configuration',
    ],

    // Statuts
    'statuts' => [
        'actif' => 'Actif',
        'hors_service' => 'Hors service',
    ],

    // Champs
    'fields' => [
        'nom' => 'Nom de l\'appareil',
        'type' => 'Type',
        'categorie' => 'Catégorie',
        'destinataire' => 'Destinataire',
        'numero_serie' => 'Numéro de série',
        'site' => 'Site',
        'site_id' => 'Site associé',
        'statut' => 'Statut',
        'description' => 'Description',
        'is_active' => 'Actif',
    ],

    // Sections
    'sections' => [
        'info' => 'Informations de l\'appareil',
        'localisation' => 'Localisation',
    ],

    // Actions
    'actions' => [
        'create' => 'Nouvel appareil',
        'edit' => 'Modifier l\'appareil',
        'delete' => 'Supprimer l\'appareil',
    ],

    // Messages
    'messages' => [
        'created' => 'Appareil créé avec succès',
        'updated' => 'Appareil mis à jour',
        'deleted' => 'Appareil supprimé',
        'no_appareil' => 'Aucun appareil disponible',
    ],

    // Relevés énergie
    'energie' => [
        'label' => 'Relevé d\'énergie',
        'plural' => 'Relevés d\'énergie',
        'title' => 'Suivi de l\'énergie',
        'pourcentage' => 'Pourcentage d\'énergie',
        'heure_releve' => 'Heure du relevé',
        'observations' => 'Observations',
        'auteur' => 'Relevé par',
        'add' => 'Nouveau relevé',
        'no_releve' => 'Aucun relevé enregistré',
    ],

    // Redémarrages
    'redemarrage' => [
        'label' => 'Redémarrage',
        'plural' => 'Redémarrages',
        'title' => 'Suivi des redémarrages',
        'nombre' => 'Nombre de redémarrages',
        'motif' => 'Motif du redémarrage',
        'heure_debut' => 'Heure de début',
        'heure_fin' => 'Heure de fin',
        'decision' => 'Décision / Commentaire',
        'auteur' => 'Enregistré par',
        'add' => 'Nouveau redémarrage',
        'no_redemarrage' => 'Aucun redémarrage enregistré',
    ],

    // Placeholders
    'placeholders' => [
        'select_appareil' => 'Sélectionnez un appareil',
        'select_site' => 'Tous les sites',
        'select_destinataire' => 'Aucun destinataire spécifique',
    ],

    // Helpers
    'helpers' => [
        'destinataire' => 'À qui est destiné cet appareil (officier ou opérateur)',
    ],
];
