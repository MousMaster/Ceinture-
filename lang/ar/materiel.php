<?php

return [
    // Destinataires
    'destinataires' => [
        'officier' => 'ضابط',
        'operateur' => 'عامل',
    ],

    // États de fonctionnement
    'etats' => [
        'fonctionne' => 'يعمل',
        'endommage' => 'تالف',
        'hors_service' => 'خارج الخدمة',
    ],

    // Réception du matériel
    'reception' => [
        'title' => 'استلام المعدات',
        'no_reception' => 'لا توجد معدات مسجلة',
        'no_reception_desc' => 'أضف استلام المعدات لكل شخص.',

        // Champs
        'fields' => [
            'destinataire' => 'المستلم',
            'appareil' => 'الجهاز',
            'recu_integralite' => 'استلم بالكامل',
            'etat_fonctionnement' => 'حالة التشغيل',
            'commentaire' => 'ملاحظة',
        ],

        // Helpers
        'helpers' => [
            'destinataire' => 'اختر الضابط أو عامل (رئيس المركز ليس لديه معدات)',
            'appareil' => 'قائمة مفلترة حسب دور المستلم',
            'recu_integralite' => 'حدد إذا تم استلام المعدات كاملة',
        ],

        // Actions
        'actions' => [
            'add' => 'إضافة استلام',
        ],
    ],

    // Réception (affichage)
    'recu' => [
        'oui' => 'نعم',
        'non' => 'لا',
    ],

    // Export PDF
    'pdf' => [
        'section_officier' => 'المعدات المستلمة من طرف الضابط',
        'section_operateurs' => 'المعدات المستلمة من طرف العاملين',
        'no_materiel_officier' => 'لا توجد معدات مسجلة للضابط',
        'no_materiel_operateurs' => 'لا توجد معدات مسجلة للعاملين',
        'columns' => [
            'appareil' => 'الجهاز',
            'recu' => 'مستلم',
            'etat' => 'الحالة',
            'commentaire' => 'ملاحظة',
        ],
    ],
];
