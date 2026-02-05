<?php

return [
    // Resource
    'resource' => [
        'label' => 'مستخدم',
        'plural' => 'المستخدمون',
        'navigation_group' => 'الإدارة',
    ],

    // Types
    'types' => [
        'admin' => 'مدير النظام',
        'officier' => 'ضابط',
        'sous_officier' => 'ضابط صف',
        'viewer' => 'مستشار',
    ],

    // Fonctions (sous-officier)
    'fonctions' => [
        'operateur' => 'مشغّل',
        'chef_poste' => 'رئيس مركز',
    ],

    // Champs
    'fields' => [
        'nom' => 'اللقب',
        'prenom' => 'الاسم',
        'nom_complet' => 'الاسم الكامل',
        'matricule' => 'الرقم التسلسلي',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'type' => 'نوع المستخدم',
        'fonction' => 'الوظيفة',
        'is_active' => 'نشط',
        'created_at' => 'تاريخ الإنشاء',
    ],

    // Sections
    'sections' => [
        'personal' => 'المعلومات الشخصية',
        'access' => 'الوصول',
    ],

    // Actions
    'actions' => [
        'activate' => 'تفعيل',
        'deactivate' => 'إلغاء التفعيل',
        'toggle_active' => 'تفعيل/إلغاء التفعيل',
    ],

    // Messages
    'messages' => [
        'deactivate_help' => 'إلغاء التفعيل لحظر الوصول',
        'read_only' => 'وصول للقراءة فقط',
    ],
];
