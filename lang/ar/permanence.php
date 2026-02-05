<?php

return [
    // Resource
    'resource' => [
        'label' => 'المداومة',
        'plural' => 'المداومات',
        'navigation_group' => 'التسيير',
    ],

    // Statuts
    'statuts' => [
        'planifiee' => 'مخططة',
        'en_cours' => 'جارية',
        'validee' => 'مصادق عليها',
        'annulee' => 'ملغاة',
    ],

    // Champs
    'fields' => [
        'date' => 'تاريخ المداومة',
        'heure_debut' => 'ساعة البداية',
        'heure_fin' => 'ساعة النهاية',
        'officier' => 'ضابط المداومة',
        'officier_id' => 'الضابط المسؤول',
        'statut' => 'الحالة',
        'commentaire_officier' => 'ملاحظة الضابط',
        'validated_at' => 'تمت المصادقة في',
        'periode' => 'الفترة',
    ],

    // Sections
    'sections' => [
        'info' => 'معلومات المداومة',
        'affectations' => 'تعيينات ضباط الصف',
        'commentaire' => 'الملاحظات',
        'statut' => 'الحالة',
        'relation_manageriale' => 'العلاقة الإدارية',
        'evenements' => 'الأحداث',
        'validation' => 'المصادقة على السجل',
        'personnel' => 'الأفراد المعينون',
        'signatures' => 'التوقيعات',
    ],

    // Actions
    'actions' => [
        'create' => 'مداومة جديدة',
        'edit' => 'تعديل المداومة',
        'view' => 'عرض المداومة',
        'delete' => 'حذف المداومة',
        'start' => 'بدء',
        'validate' => 'مصادقة',
        'reopen' => 'إعادة فتح',
        'print' => 'طباعة (PDF)',
        'download_pdf' => 'تحميل PDF',
        'add_event' => 'حدث جديد',
        'add_affectation' => 'إضافة ضابط صف',
    ],

    // Messages
    'messages' => [
        'started' => 'تم بدء المداومة',
        'validated' => 'تمت المصادقة على المداومة',
        'reopened' => 'تم إعادة فتح المداومة',
        'locked' => 'هذه المداومة مصادق عليها ومغلقة.',
        'locked_warning' => 'بمجرد المصادقة، لا يمكن تعديل المداومة.',
        'start_confirm' => 'هل تريد بدء هذه المداومة؟',
        'validate_confirm' => 'هذا الإجراء لا رجعة فيه.',
        'reopen_confirm' => 'سيسمح هذا الإجراء بتعديل المداومة مجدداً.',
        'no_events' => 'لا توجد أحداث مسجلة لهذه المداومة.',
        'access_denied' => 'ليس لديك صلاحية الوصول إلى هذه المداومة.',
        'edit_denied' => 'التعديل ممنوع بعد المصادقة.',
    ],

    // Modals
    'modals' => [
        'start_title' => 'بدء المداومة',
        'validate_title' => 'المصادقة على المداومة',
        'reopen_title' => 'إعادة فتح المداومة',
    ],

    // Relation managériale
    'relation' => [
        'label' => 'حدث',
        'plural' => 'الأحداث',
        'heure_evenement' => 'ساعة الحدث',
        'evenement' => 'الحدث / الواقعة المعاينة',
        'effets_ordonnes' => 'الإجراءات المتخذة',
        'observations' => 'الملاحظات',
        'auteur' => 'المحرر',
        'saisi_le' => 'تاريخ التسجيل',
    ],

    // PDF
    'pdf' => [
        'title' => 'سجل المداومة',
        'date_edition' => 'تاريخ الطباعة',
        'numero' => 'رقم المداومة',
        'validation_electronic' => 'وثيقة مصادق عليها إلكترونياً',
        'validation_date' => 'تمت المصادقة في',
        'signature' => 'التوقيع',
        'fonction' => 'الوظيفة',
        'footer' => 'وثيقة رسمية - يمنع النسخ بدون إذن',
    ],
];
