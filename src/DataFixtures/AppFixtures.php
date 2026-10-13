<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Etat;
use App\Entity\Responsable;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Jeu d'essais de l'application : charge avec
// php bin/console doctrine:fixtures:load
class AppFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // --------------------------------------------------
        // Les utilisateurs : un administrateur et un membre du personnel
        // --------------------------------------------------
        $admin = new User();
        $admin->setEmail('admin@agence.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'Admin2026!'));
        $manager->persist($admin);

        $personnel = new User();
        $personnel->setEmail('personnel@agence.fr');
        $personnel->setRoles(['ROLE_PERSONNEL']);
        $personnel->setPassword($this->hasher->hashPassword($personnel, 'Personnel2026!'));
        $manager->persist($personnel);

        // --------------------------------------------------
        // Les etats des tickets
        // --------------------------------------------------
        $etats = [];
        foreach (['Nouveau', 'Ouvert', 'Résolu', 'Fermé'] as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);
            $etats[$libelle] = $etat;
        }

        // --------------------------------------------------
        // Les categories de tickets
        // --------------------------------------------------
        $categories = [];
        foreach (['Incident', 'Panne', 'Evolution', 'Anomalie', 'Information'] as $libelle) {
            $categorie = new Categorie();
            $categorie->setLibelle($libelle);
            $manager->persist($categorie);
            $categories[$libelle] = $categorie;
        }

        // --------------------------------------------------
        // Les responsables de l'agence
        // --------------------------------------------------
        $dupont = new Responsable();
        $dupont->setPrenom('Marc')->setNom('Dupont')->setEmail('marc.dupont@agence.fr');
        $manager->persist($dupont);

        $martin = new Responsable();
        $martin->setPrenom('Sophie')->setNom('Martin')->setEmail('sophie.martin@agence.fr');
        $manager->persist($martin);

        $bernard = new Responsable();
        $bernard->setPrenom('Luc')->setNom('Bernard')->setEmail('luc.bernard@agence.fr');
        $manager->persist($bernard);

        // --------------------------------------------------
        // Quelques tickets d'exemple
        // --------------------------------------------------
        $tickets = [
            ['client1@mail.fr', 'Le site ne se charge plus depuis ce matin, la page reste blanche.', 'Incident', 'Nouveau', null],
            ['client2@mail.fr', 'Le bouton de paiement ne fonctionne pas sur le navigateur Firefox.', 'Panne', 'Ouvert', $dupont],
            ['client3@mail.fr', 'Il serait pratique d avoir un historique de nos tickets dans l espace client.', 'Evolution', 'Ouvert', $martin],
            ['client4@mail.fr', 'Une erreur 500 apparait quand je modifie mon adresse e-mail.', 'Anomalie', 'Résolu', $dupont],
            ['client5@mail.fr', 'Comment ajouter un nouveau collaborateur à mon compte ?', 'Information', 'Résolu', $bernard],
            ['client6@mail.fr', 'Le tableau de bord affiche des chiffres qui ne sont pas à jour depuis hier.', 'Anomalie', 'Fermé', $martin],
        ];

        foreach ($tickets as $donnees) {
            $ticket = new Ticket();
            $ticket->setAuteur($donnees[0]);
            $ticket->setDescription($donnees[1]);
            $ticket->setCategorie($categories[$donnees[2]]);
            $ticket->setEtat($etats[$donnees[3]]);
            $ticket->setResponsable($donnees[4]);

            // le ticket ferme recoit une date de cloture
            if ($donnees[3] === 'Fermé') {
                $ticket->setDateCloture(new \DateTimeImmutable('-2 days'));
            }

            $manager->persist($ticket);
        }

        $manager->flush();
    }
}