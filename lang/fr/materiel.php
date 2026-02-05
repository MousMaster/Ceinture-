<?php

return [
    // Destinataires
    'destinataires' => [
        'officier' => 'Officier',
        'operateur' => 'Opérateur',
    ],

    // États de fonctionnement
    'etats' => [
        'fonctionne' => 'Fonctionne',
        'endommage' => 'Endommagé',
        'hors_service' => 'Hors service',
    ],

    // Réception du matériel
    'reception' => [
        'title' => 'Réception du matériel',
        'no_reception' => 'Aucun matériel enregistré',
        'no_reception_desc' => 'Ajoutez la réception du matériel pour chaque personne.',

        // Champs
        'fields' => [
            'destinataire' => 'Destinataire',
            'appareil' => 'Appareil',
            'recu_integralite' => 'Reçu en intégralité',
            'etat_fonctionnement' => 'État de fonctionnement',
            'commentaire' => 'Commentaire',
        ],

        // Helpers
        'helpers' => [
            'destinataire' => 'Sélectionnez l\'officier ou un opérateur (les chefs de poste n\'ont pas de matériel)',
            'appareil' => 'Liste filtrée selon le rôle du destinataire',
            'recu_integralite' => 'Cochez si le matériel a été reçu complet',
        ],

        // Actions
        'actions' => [
            'add' => 'Ajouter une réception',
        ],
    ],

    // Réception (affichage)
    'recu' => [
        'oui' => 'Oui',
        'non' => 'Non',
    ],

    // Export PDF
    'pdf' => [
        'section_officier' => 'Matériel reçu par l\'Officier',
        'section_operateurs' => 'Matériel reçu par les Opérateurs',
        'no_materiel_officier' => 'Aucun matériel enregistré pour l\'officier',
        'no_materiel_operateurs' => 'Aucun matériel enregistré pour les opérateurs',
        'columns' => [
            'appareil' => 'Appareil',
            'recu' => 'Reçu',
            'etat' => 'État',
            'commentaire' => 'Commentaire',
        ],
    ],
];
