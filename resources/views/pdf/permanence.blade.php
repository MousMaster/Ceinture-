<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $pdf_title }} - {{ $date_permanence }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        /* RTL Support */
        [dir="rtl"] body {
            direction: rtl;
            text-align: right;
        }

        [dir="rtl"] .affectations-table th,
        [dir="rtl"] .affectations-table td,
        [dir="rtl"] .evenements-table th,
        [dir="rtl"] .evenements-table td {
            text-align: right;
        }

        [dir="rtl"] .logo-left {
            text-align: right;
        }

        [dir="rtl"] .logo-right {
            text-align: left;
        }

        [dir="rtl"] .page-number {
            text-align: left;
        }

        .page {
            padding: 15mm;
        }

        /* En-tête avec logos */
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo {
            width: 80px;
            height: auto;
            max-height: 80px;
        }

        .logo-left {
            text-align: left;
            width: 100px;
        }

        .logo-right {
            text-align: right;
            width: 100px;
        }

        .header-center {
            text-align: center;
            padding: 0 20px;
        }

        .institution-name {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .direction-name {
            font-size: 10pt;
            margin-bottom: 5px;
        }

        .system-name {
            font-size: 8pt;
            color: #666;
            margin-bottom: 10px;
        }

        .document-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
            padding: 8px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
        }

        /* Informations de la permanence */
        .info-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .info-title {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
            text-transform: uppercase;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 5px 10px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            width: 150px;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        /* Tableau des affectations */
        .affectations-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .affectations-table th,
        .affectations-table td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        .affectations-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }

        .affectations-table tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        /* Tableau des événements */
        .evenements-section {
            margin-bottom: 20px;
        }

        .evenements-table {
            width: 100%;
            border-collapse: collapse;
        }

        .evenements-table th,
        .evenements-table td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        .evenements-table th {
            background-color: #d0d0d0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        .evenements-table tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .evenements-table .col-heure {
            width: 60px;
            text-align: center;
            font-weight: bold;
        }

        .evenements-table .col-auteur {
            width: 120px;
        }

        .evenements-table .col-evenement {
            width: 35%;
        }

        .evenements-table .col-effets {
            width: 25%;
        }

        /* Section signatures */
        .signatures-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .validation-box {
            border: 2px solid #333;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .validation-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            text-align: center;
        }

        .signature-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-grid td {
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }

        .signature-label {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }

        .signature-value {
            font-size: 10pt;
            margin-bottom: 10px;
        }

        .signature-zone {
            border: 1px dashed #999;
            height: 60px;
            margin-top: 10px;
            text-align: center;
            line-height: 60px;
            color: #999;
            font-size: 8pt;
        }

        .validation-electronic {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background-color: #e8f5e9;
            border: 1px solid #4caf50;
            font-size: 9pt;
        }

        .validation-electronic strong {
            color: #2e7d32;
        }

        /* Pied de page */
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .page-number {
            text-align: right;
        }

        /* Statut */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-validee {
            background-color: #4caf50;
            color: white;
        }

        .status-en_cours {
            background-color: #ff9800;
            color: white;
        }

        .status-planifiee {
            background-color: #9e9e9e;
            color: white;
        }

        /* Commentaires */
        .commentaire-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fff8e1;
            border: 1px solid #ffcc02;
        }

        .commentaire-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .no-events {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- En-tête -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-left">
                        @if($logo_institution)
                            <img src="{{ $logo_institution }}" class="logo" alt="Logo Institution">
                        @else
                            <div style="width: 80px; height: 80px; border: 1px dashed #ccc; text-align: center; line-height: 80px; font-size: 8pt; color: #999;">Logo</div>
                        @endif
                    </td>
                    <td class="header-center">
                        <div class="institution-name">{{ $institution_name }}</div>
                        <div class="direction-name">{{ $direction_name }}</div>
                        <div class="system-name">{{ $system_name }}</div>
                        <div class="document-title">{{ $pdf_title }}</div>
                    </td>
                    <td class="logo-right">
                        @if($logo_direction)
                            <img src="{{ $logo_direction }}" class="logo" alt="Logo Direction">
                        @else
                            <div style="width: 80px; height: 80px; border: 1px dashed #ccc; text-align: center; line-height: 80px; font-size: 8pt; color: #999;">Logo</div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Informations générales -->
        <div class="info-section">
            <div class="info-title">{{ __('permanence.sections.info') }}</div>
            <table class="info-grid">
                <tr>
                    <td class="info-label">{{ __('common.dates.date') }} :</td>
                    <td class="info-value"><strong>{{ $date_permanence }}</strong></td>
                    <td class="info-label">{{ __('permanence.fields.periode') }} :</td>
                    <td class="info-value">{{ $heure_debut }} - {{ $heure_fin }}</td>
                </tr>
                <tr>
                    <td class="info-label">{{ __('permanence.fields.officier') }} :</td>
                    <td class="info-value"><strong>{{ $officier->nom_complet }}</strong></td>
                    <td class="info-label">{{ __('permanence.fields.statut') }} :</td>
                    <td class="info-value">
                        <span class="status-badge status-{{ $permanence->statut->value }}">
                            {{ $permanence->statut->getLabel() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="info-label">{{ __('users.fields.matricule') }} :</td>
                    <td class="info-value">{{ $officier->matricule ?? '-' }}</td>
                    <td class="info-label">{{ __('permanence.pdf.date_edition') }} :</td>
                    <td class="info-value">{{ $date_edition }}</td>
                </tr>
            </table>
        </div>

        <!-- Sous-officiers affectés -->
        @if($affectations->count() > 0)
        <div class="info-section">
            <div class="info-title">{{ __('permanence.sections.personnel') }}</div>
            <table class="affectations-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">{{ __('users.types.sous_officier') }}</th>
                        <th style="width: 30%;">{{ __('users.fields.matricule') }}</th>
                        <th style="width: 30%;">Site</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($affectations as $affectation)
                    <tr>
                        <td>{{ $affectation->sousOfficier->nom_complet }}</td>
                        <td>{{ $affectation->sousOfficier->matricule ?? '-' }}</td>
                        <td>{{ $affectation->site->nom }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Commentaire de l'officier -->
        @if($permanence->commentaire_officier)
        <div class="commentaire-section">
            <div class="commentaire-title">{{ __('permanence.fields.commentaire_officier') }} :</div>
            <div>{{ $permanence->commentaire_officier }}</div>
        </div>
        @endif

        <!-- ========== SECTION MATÉRIEL ========== -->
        
        <!-- Matériel reçu par l'Officier -->
        @if($has_materiel_officier || $has_materiel_operateurs)
        <div class="evenements-section" style="margin-top: 20px;">
            <div class="info-title" style="margin-bottom: 10px; background-color: #fff3e0; padding: 8px;">
                📦 {{ $labels['materiel_section_officier'] }}
            </div>
            
            @if($has_materiel_officier)
            <table class="evenements-table" style="font-size: 9pt;">
                <thead>
                    <tr>
                        <th style="width: 35%;">{{ $labels['materiel_appareil'] }}</th>
                        <th style="width: 15%; text-align: center;">{{ $labels['materiel_recu'] }}</th>
                        <th style="width: 20%;">{{ $labels['materiel_etat'] }}</th>
                        <th style="width: 30%;">{{ $labels['materiel_commentaire'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materiel_officier as $reception)
                    <tr>
                        <td>{{ $reception->appareil->nom }}</td>
                        <td style="text-align: center;">
                            @if($reception->recu_integralite)
                                <span style="color: green;">✓ {{ $labels['materiel_oui'] }}</span>
                            @else
                                <span style="color: red;">✗ {{ $labels['materiel_non'] }}</span>
                            @endif
                        </td>
                        <td>
                            @switch($reception->etat_fonctionnement->value)
                                @case('fonctionne')
                                    <span style="color: green;">{{ $labels['materiel_fonctionne'] }}</span>
                                    @break
                                @case('endommage')
                                    <span style="color: orange;">{{ $labels['materiel_endommage'] }}</span>
                                    @break
                                @case('hors_service')
                                    <span style="color: red;">{{ $labels['materiel_hors_service'] }}</span>
                                    @break
                            @endswitch
                        </td>
                        <td>{{ $reception->commentaire ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-events">
                {{ $labels['materiel_no_officier'] }}
            </div>
            @endif
        </div>

        <!-- Matériel reçu par les Opérateurs (regroupé par personne) -->
        <div class="evenements-section" style="margin-top: 20px;">
            <div class="info-title" style="margin-bottom: 10px; background-color: #e0f2f1; padding: 8px;">
                📦 {{ $labels['materiel_section_operateurs'] }}
            </div>
            
            @if($has_materiel_operateurs)
                @foreach($materiel_operateurs as $operateurData)
                <div class="sous-officier-block" style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; background-color: #fafafa;">
                    <!-- En-tête de l'opérateur -->
                    <div class="sous-officier-header" style="margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px dashed #ccc;">
                        <strong>{{ $operateurData['nom_complet'] }}</strong>
                        <span style="background-color: #2196f3; color: white; padding: 2px 8px; border-radius: 3px; font-size: 8pt; margin-left: 10px;">
                            {{ $operateurData['fonction'] }}
                        </span>
                        <span style="color: #666; font-size: 8pt; margin-left: 10px;">
                            {{ $labels['matricule'] }}: {{ $operateurData['matricule'] }}
                        </span>
                    </div>
                    
                    <!-- Tableau du matériel de cet opérateur -->
                    <table class="evenements-table" style="font-size: 9pt;">
                        <thead>
                            <tr>
                                <th style="width: 35%;">{{ $labels['materiel_appareil'] }}</th>
                                <th style="width: 15%; text-align: center;">{{ $labels['materiel_recu'] }}</th>
                                <th style="width: 20%;">{{ $labels['materiel_etat'] }}</th>
                                <th style="width: 30%;">{{ $labels['materiel_commentaire'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($operateurData['receptions'] as $reception)
                            <tr>
                                <td>{{ $reception->appareil->nom }}</td>
                                <td style="text-align: center;">
                                    @if($reception->recu_integralite)
                                        <span style="color: green;">✓ {{ $labels['materiel_oui'] }}</span>
                                    @else
                                        <span style="color: red;">✗ {{ $labels['materiel_non'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($reception->etat_fonctionnement->value)
                                        @case('fonctionne')
                                            <span style="color: green;">{{ $labels['materiel_fonctionne'] }}</span>
                                            @break
                                        @case('endommage')
                                            <span style="color: orange;">{{ $labels['materiel_endommage'] }}</span>
                                            @break
                                        @case('hors_service')
                                            <span style="color: red;">{{ $labels['materiel_hors_service'] }}</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $reception->commentaire ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
            <div class="no-events">
                {{ $labels['materiel_no_operateurs'] }}
            </div>
            @endif
        </div>
        @endif

        <!-- ========== SECTION ÉVÉNEMENTS ========== -->

        <!-- SECTION 1 : Saisies de l'Officier -->
        <div class="evenements-section">
            <div class="info-title section-officer" style="margin-bottom: 10px; background-color: #e3f2fd; padding: 8px;">
                🎖️ {{ $labels['section_officer'] }}
            </div>
            
            @if($has_evenements_officier)
            <table class="evenements-table">
                <thead>
                    <tr>
                        <th class="col-heure">{{ $labels['hour'] }}</th>
                        <th class="col-evenement">{{ $labels['event_fact'] }}</th>
                        <th class="col-effets">{{ $labels['ordered_effects'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evenements_officier as $evenement)
                    <tr>
                        <td class="col-heure">{{ $evenement->heure_evenement->format('H:i') }}</td>
                        <td class="col-evenement">
                            {{ $evenement->evenement }}
                            @if($evenement->observations)
                                <br><small><em>Obs: {{ $evenement->observations }}</em></small>
                            @endif
                        </td>
                        <td class="col-effets">{{ $evenement->effets_ordonnes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-events">
                {{ $labels['no_officer_events'] }}
            </div>
            @endif
        </div>

        <!-- SECTION 2 : Saisies des Sous-officiers (regroupées par personne) -->
        <div class="evenements-section" style="margin-top: 20px;">
            <div class="info-title section-sous-officiers" style="margin-bottom: 10px; background-color: #e8f5e9; padding: 8px;">
                📝 {{ $labels['section_sous_officiers'] }}
            </div>
            
            @if($has_evenements_sous_officiers)
                @foreach($evenements_sous_officiers as $sousOfficierData)
                <div class="sous-officier-block" style="margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; background-color: #fafafa;">
                    <!-- En-tête du sous-officier avec type -->
                    <div class="sous-officier-header" style="margin-bottom: 8px; padding-bottom: 5px; border-bottom: 1px dashed #ccc;">
                        <strong>{{ $sousOfficierData['nom_complet'] }}</strong>
                        <span style="background-color: #ff9800; color: white; padding: 2px 8px; border-radius: 3px; font-size: 8pt; margin-left: 10px;">
                            {{ $sousOfficierData['fonction'] }}
                        </span>
                        <span style="color: #666; font-size: 8pt; margin-left: 10px;">
                            {{ $labels['matricule'] }}: {{ $sousOfficierData['matricule'] }}
                        </span>
                    </div>
                    
                    <!-- Tableau des saisies de ce sous-officier -->
                    <table class="evenements-table" style="font-size: 9pt;">
                        <thead>
                            <tr>
                                <th class="col-heure">{{ $labels['hour'] }}</th>
                                <th class="col-evenement">{{ $labels['event_fact'] }}</th>
                                <th class="col-effets">{{ $labels['ordered_effects'] }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sousOfficierData['evenements'] as $evenement)
                            <tr>
                                <td class="col-heure">{{ $evenement->heure_evenement->format('H:i') }}</td>
                                <td class="col-evenement">
                                    {{ $evenement->evenement }}
                                    @if($evenement->observations)
                                        <br><small><em>Obs: {{ $evenement->observations }}</em></small>
                                    @endif
                                </td>
                                <td class="col-effets">{{ $evenement->effets_ordonnes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            @else
            <div class="no-events">
                {{ $labels['no_sous_officier_events'] }}
            </div>
            @endif
        </div>

        <!-- Section signatures et validation -->
        <div class="signatures-section">
            <div class="validation-box">
                <div class="validation-title">{{ __('permanence.sections.validation') }}</div>
                
                <table class="signature-grid">
                    <tr>
                        <td>
                            <div class="signature-label">{{ __('permanence.fields.officier') }} :</div>
                            <div class="signature-value">{{ $officier->nom_complet }}</div>
                            <div class="signature-label">{{ __('permanence.pdf.fonction') }} :</div>
                            <div class="signature-value">{{ __('permanence.fields.officier') }}</div>
                            <div class="signature-zone">{{ __('permanence.pdf.signature') }}</div>
                        </td>
                        <td>
                            <div class="signature-label">{{ __('permanence.pdf.validation_date') }} :</div>
                            <div class="signature-value">
                                @if($permanence->validated_at)
                                    {{ $permanence->validated_at->format('d/m/Y H:i') }}
                                @else
                                    -
                                @endif
                            </div>
                            <div class="signature-label">{{ __('permanence.pdf.numero') }} :</div>
                            <div class="signature-value">#{{ str_pad($permanence->id, 6, '0', STR_PAD_LEFT) }}</div>
                        </td>
                    </tr>
                </table>

                @if($permanence->validated_at)
                <div class="validation-electronic">
                    <strong>✓ {{ __('permanence.pdf.validation_electronic') }}</strong><br>
                    {{ __('permanence.pdf.validation_date') }} {{ $permanence->validated_at->format('d/m/Y H:i') }}
                </div>
                @endif
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            {{ $pdf_footer }}<br>
            <small>Édité le {{ $date_edition }} - Permanence du {{ $date_permanence }}</small>
        </div>
    </div>
</body>
</html>
