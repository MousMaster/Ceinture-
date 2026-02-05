<!DOCTYPE html>
<html lang="fr">
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
            <div class="info-title">Informations de la permanence</div>
            <table class="info-grid">
                <tr>
                    <td class="info-label">Date :</td>
                    <td class="info-value"><strong>{{ $date_permanence }}</strong></td>
                    <td class="info-label">Période :</td>
                    <td class="info-value">{{ $heure_debut }} - {{ $heure_fin }}</td>
                </tr>
                <tr>
                    <td class="info-label">Officier de permanence :</td>
                    <td class="info-value"><strong>{{ $officier->nom_complet }}</strong></td>
                    <td class="info-label">Statut :</td>
                    <td class="info-value">
                        <span class="status-badge status-{{ $permanence->statut->value }}">
                            {{ $permanence->statut->getLabel() }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Matricule :</td>
                    <td class="info-value">{{ $officier->matricule ?? '-' }}</td>
                    <td class="info-label">Date d'édition :</td>
                    <td class="info-value">{{ $date_edition }}</td>
                </tr>
            </table>
        </div>

        <!-- Sous-officiers affectés -->
        @if($affectations->count() > 0)
        <div class="info-section">
            <div class="info-title">Personnel affecté</div>
            <table class="affectations-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Sous-officier</th>
                        <th style="width: 30%;">Matricule</th>
                        <th style="width: 30%;">Site d'affectation</th>
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
            <div class="commentaire-title">Commentaire de l'officier de permanence :</div>
            <div>{{ $permanence->commentaire_officier }}</div>
        </div>
        @endif

        <!-- Événements / Relation managériale -->
        <div class="evenements-section">
            <div class="info-title" style="margin-bottom: 10px;">Relation managériale - Événements</div>
            
            @if($evenements->count() > 0)
            <table class="evenements-table">
                <thead>
                    <tr>
                        <th class="col-heure">Heure</th>
                        <th class="col-auteur">Auteur</th>
                        <th class="col-evenement">Événement / Fait constaté</th>
                        <th class="col-effets">Effets ordonnés</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evenements as $evenement)
                    <tr>
                        <td class="col-heure">{{ $evenement->heure_evenement->format('H:i') }}</td>
                        <td class="col-auteur">
                            {{ $evenement->sousOfficier->nom_complet }}
                            @if($evenement->sous_officier_id === $permanence->officier_id)
                                <br><small>(Officier)</small>
                            @endif
                        </td>
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
                Aucun événement enregistré pour cette permanence.
            </div>
            @endif
        </div>

        <!-- Section signatures et validation -->
        <div class="signatures-section">
            <div class="validation-box">
                <div class="validation-title">Validation du registre</div>
                
                <table class="signature-grid">
                    <tr>
                        <td>
                            <div class="signature-label">Officier de permanence :</div>
                            <div class="signature-value">{{ $officier->nom_complet }}</div>
                            <div class="signature-label">Fonction :</div>
                            <div class="signature-value">Officier de permanence</div>
                            <div class="signature-zone">Signature</div>
                        </td>
                        <td>
                            <div class="signature-label">Date de validation :</div>
                            <div class="signature-value">
                                @if($permanence->validated_at)
                                    {{ $permanence->validated_at->format('d/m/Y à H:i') }}
                                @else
                                    Non validée
                                @endif
                            </div>
                            <div class="signature-label">Numéro de permanence :</div>
                            <div class="signature-value">#{{ str_pad($permanence->id, 6, '0', STR_PAD_LEFT) }}</div>
                        </td>
                    </tr>
                </table>

                @if($permanence->validated_at)
                <div class="validation-electronic">
                    <strong>✓ Document validé électroniquement</strong><br>
                    Validation effectuée le {{ $permanence->validated_at->format('d/m/Y à H:i') }}
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
