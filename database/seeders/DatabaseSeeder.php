<?php

namespace Database\Seeders;

use App\Enums\StatutPermanence;
use App\Enums\UserType;
use App\Models\Permanence;
use App\Models\PermanenceSousOfficier;
use App\Models\RelationManageriale;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ========== SITES ==========
        $sites = [
            ['nom' => 'Poste Central', 'code' => 'PC', 'localisation' => 'Centre-ville', 'is_active' => true],
            ['nom' => 'Poste Nord', 'code' => 'PN', 'localisation' => 'Zone Nord', 'is_active' => true],
            ['nom' => 'Poste Sud', 'code' => 'PS', 'localisation' => 'Zone Sud', 'is_active' => true],
            ['nom' => 'Poste Est', 'code' => 'PE', 'localisation' => 'Zone Est', 'is_active' => true],
            ['nom' => 'Poste Ouest', 'code' => 'PO', 'localisation' => 'Zone Ouest', 'is_active' => false],
        ];

        foreach ($sites as $site) {
            Site::create($site);
        }

        $this->command->info('✓ Sites créés');

        // ========== UTILISATEURS ==========

        // Administrateur
        $admin = User::create([
            'nom' => 'ADMIN',
            'prenom' => 'System',
            'matricule' => 'ADM001',
            'email' => 'admin@registre.local',
            'password' => Hash::make('password'),
            'type' => UserType::Admin,
            'is_active' => true,
        ]);

        $this->command->info('✓ Administrateur créé: admin@registre.local / password');

        // Officiers
        $officiers = [
            ['nom' => 'DUPONT', 'prenom' => 'Jean', 'matricule' => 'OFF001', 'email' => 'officier1@registre.local'],
            ['nom' => 'MARTIN', 'prenom' => 'Pierre', 'matricule' => 'OFF002', 'email' => 'officier2@registre.local'],
        ];

        $createdOfficiers = [];
        foreach ($officiers as $officier) {
            $createdOfficiers[] = User::create([
                'nom' => $officier['nom'],
                'prenom' => $officier['prenom'],
                'matricule' => $officier['matricule'],
                'email' => $officier['email'],
                'password' => Hash::make('password'),
                'type' => UserType::Officier,
                'is_active' => true,
            ]);
        }

        $this->command->info('✓ Officiers créés: officier1@registre.local, officier2@registre.local / password');

        // Sous-officiers
        $sousOfficiers = [
            ['nom' => 'BERNARD', 'prenom' => 'Michel', 'matricule' => 'SO001', 'email' => 'sousofficier1@registre.local'],
            ['nom' => 'PETIT', 'prenom' => 'François', 'matricule' => 'SO002', 'email' => 'sousofficier2@registre.local'],
            ['nom' => 'DURAND', 'prenom' => 'Paul', 'matricule' => 'SO003', 'email' => 'sousofficier3@registre.local'],
            ['nom' => 'MOREAU', 'prenom' => 'Jacques', 'matricule' => 'SO004', 'email' => 'sousofficier4@registre.local'],
            ['nom' => 'LAMBERT', 'prenom' => 'Antoine', 'matricule' => 'SO005', 'email' => 'sousofficier5@registre.local'],
        ];

        $createdSousOfficiers = [];
        foreach ($sousOfficiers as $so) {
            $createdSousOfficiers[] = User::create([
                'nom' => $so['nom'],
                'prenom' => $so['prenom'],
                'matricule' => $so['matricule'],
                'email' => $so['email'],
                'password' => Hash::make('password'),
                'type' => UserType::SousOfficier,
                'is_active' => true,
            ]);
        }

        $this->command->info('✓ Sous-officiers créés: sousofficier1@registre.local à sousofficier5@registre.local / password');

        // ========== PERMANENCES ==========
        $allSites = Site::where('is_active', true)->get();

        // Permanence 1 - Validée (passée)
        $permanence1 = Permanence::create([
            'officier_id' => $createdOfficiers[0]->id,
            'date' => now()->subDays(5),
            'heure_debut' => '08:00',
            'heure_fin' => '20:00',
            'statut' => StatutPermanence::Validee,
            'commentaire_officier' => 'Permanence du jour terminée sans incident majeur.',
            'validated_at' => now()->subDays(5)->setTime(20, 30),
        ]);

        // Affectations pour permanence 1
        PermanenceSousOfficier::create([
            'permanence_id' => $permanence1->id,
            'sous_officier_id' => $createdSousOfficiers[0]->id,
            'site_id' => $allSites[0]->id,
        ]);
        PermanenceSousOfficier::create([
            'permanence_id' => $permanence1->id,
            'sous_officier_id' => $createdSousOfficiers[1]->id,
            'site_id' => $allSites[1]->id,
        ]);

        // Événements pour permanence 1
        RelationManageriale::create([
            'permanence_id' => $permanence1->id,
            'sous_officier_id' => $createdSousOfficiers[0]->id,
            'heure_evenement' => '09:30',
            'evenement' => 'Prise de service effectuée. Vérification des équipements.',
            'effets_ordonnes' => 'RAS',
            'observations' => 'Tous les équipements opérationnels.',
        ]);
        RelationManageriale::create([
            'permanence_id' => $permanence1->id,
            'sous_officier_id' => $createdSousOfficiers[1]->id,
            'heure_evenement' => '14:15',
            'evenement' => 'Visite de contrôle du commandant.',
            'effets_ordonnes' => 'Mise à jour du registre demandée.',
            'observations' => 'Visite satisfaisante.',
        ]);

        // Permanence 2 - En cours (aujourd'hui)
        $permanence2 = Permanence::create([
            'officier_id' => $createdOfficiers[0]->id,
            'date' => now(),
            'heure_debut' => '08:00',
            'heure_fin' => '20:00',
            'statut' => StatutPermanence::EnCours,
            'commentaire_officier' => null,
            'validated_at' => null,
        ]);

        // Affectations pour permanence 2
        PermanenceSousOfficier::create([
            'permanence_id' => $permanence2->id,
            'sous_officier_id' => $createdSousOfficiers[2]->id,
            'site_id' => $allSites[0]->id,
        ]);
        PermanenceSousOfficier::create([
            'permanence_id' => $permanence2->id,
            'sous_officier_id' => $createdSousOfficiers[3]->id,
            'site_id' => $allSites[2]->id,
        ]);

        // Événements pour permanence 2
        RelationManageriale::create([
            'permanence_id' => $permanence2->id,
            'sous_officier_id' => $createdSousOfficiers[2]->id,
            'heure_evenement' => '08:15',
            'evenement' => 'Prise de service. Relève effectuée.',
            'effets_ordonnes' => null,
            'observations' => 'Conditions météo normales.',
        ]);

        // Permanence 3 - Planifiée (demain)
        $permanence3 = Permanence::create([
            'officier_id' => $createdOfficiers[1]->id,
            'date' => now()->addDay(),
            'heure_debut' => '08:00',
            'heure_fin' => '20:00',
            'statut' => StatutPermanence::Planifiee,
            'commentaire_officier' => null,
            'validated_at' => null,
        ]);

        // Affectations pour permanence 3
        PermanenceSousOfficier::create([
            'permanence_id' => $permanence3->id,
            'sous_officier_id' => $createdSousOfficiers[0]->id,
            'site_id' => $allSites[1]->id,
        ]);
        PermanenceSousOfficier::create([
            'permanence_id' => $permanence3->id,
            'sous_officier_id' => $createdSousOfficiers[4]->id,
            'site_id' => $allSites[3]->id,
        ]);

        // Permanence 4 - Planifiée (après-demain)
        Permanence::create([
            'officier_id' => $createdOfficiers[1]->id,
            'date' => now()->addDays(2),
            'heure_debut' => '08:00',
            'heure_fin' => '20:00',
            'statut' => StatutPermanence::Planifiee,
            'commentaire_officier' => null,
            'validated_at' => null,
        ]);

        $this->command->info('✓ Permanences de test créées');

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('COMPTES DE TEST');
        $this->command->info('========================================');
        $this->command->info('Admin:         admin@registre.local / password');
        $this->command->info('Officier 1:    officier1@registre.local / password');
        $this->command->info('Officier 2:    officier2@registre.local / password');
        $this->command->info('Sous-off 1:    sousofficier1@registre.local / password');
        $this->command->info('Sous-off 2:    sousofficier2@registre.local / password');
        $this->command->info('Sous-off 3:    sousofficier3@registre.local / password');
        $this->command->info('========================================');
    }
}
