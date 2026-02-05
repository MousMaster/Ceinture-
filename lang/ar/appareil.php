<?php

return [
    // Resource
    'resource' => [
        'label' => 'جهاز',
        'plural' => 'الأجهزة',
        'navigation_group' => 'الإعدادات',
    ],

    // Statuts
    'statuts' => [
        'actif' => 'نشط',
        'hors_service' => 'خارج الخدمة',
    ],

    // Champs
    'fields' => [
        'nom' => 'اسم الجهاز',
        'type' => 'النوع',
        'categorie' => 'الفئة',
        'numero_serie' => 'الرقم التسلسلي',
        'site' => 'الموقع',
        'site_id' => 'الموقع المرتبط',
        'statut' => 'الحالة',
        'description' => 'الوصف',
        'is_active' => 'نشط',
    ],

    // Sections
    'sections' => [
        'info' => 'معلومات الجهاز',
        'localisation' => 'الموقع',
    ],

    // Actions
    'actions' => [
        'create' => 'جهاز جديد',
        'edit' => 'تعديل الجهاز',
        'delete' => 'حذف الجهاز',
    ],

    // Messages
    'messages' => [
        'created' => 'تم إنشاء الجهاز بنجاح',
        'updated' => 'تم تحديث الجهاز',
        'deleted' => 'تم حذف الجهاز',
        'no_appareil' => 'لا يوجد جهاز متاح',
    ],

    // Relevés énergie
    'energie' => [
        'label' => 'قراءة الطاقة',
        'plural' => 'قراءات الطاقة',
        'title' => 'متابعة الطاقة',
        'pourcentage' => 'نسبة الطاقة',
        'heure_releve' => 'وقت القراءة',
        'observations' => 'ملاحظات',
        'auteur' => 'سُجل بواسطة',
        'add' => 'قراءة جديدة',
        'no_releve' => 'لا توجد قراءات مسجلة',
    ],

    // Redémarrages
    'redemarrage' => [
        'label' => 'إعادة تشغيل',
        'plural' => 'إعادات التشغيل',
        'title' => 'متابعة إعادات التشغيل',
        'nombre' => 'عدد إعادات التشغيل',
        'motif' => 'سبب إعادة التشغيل',
        'heure_debut' => 'وقت البداية',
        'heure_fin' => 'وقت النهاية',
        'decision' => 'القرار / التعليق',
        'auteur' => 'سُجل بواسطة',
        'add' => 'إعادة تشغيل جديدة',
        'no_redemarrage' => 'لا توجد إعادات تشغيل مسجلة',
    ],

    // Placeholders
    'placeholders' => [
        'select_appareil' => 'اختر جهازاً',
        'select_site' => 'جميع المواقع',
    ],
];
