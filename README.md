# Support Agence – suivi des tickets clients

Projet interne de l'agence web : les clients créent leurs tickets depuis la page
d'accueil, le personnel de l'agence les consulte et en modifie le statut,
l'administrateur gère les états, les catégories, les responsables et les tickets.

Application réalisée avec Symfony 6.4 (LTS), PHP 8.1+, MySQL et Bootstrap 5.

## Fonctionnalités

**Partie publique (page d'accueil)**

- formulaire de création de ticket : adresse e-mail du client, description
  (de 20 à 250 caractères, vérifiée en back) et catégorie ;
- la date d'ouverture du ticket est renseignée automatiquement et son état
  initial est « Nouveau » ;
- bouton de connexion pour l'équipe.

**Partie connectée (personnel de l'agence et administrateur)**

- menu de navigation et déconnexion ;
- liste de tous les tickets avec leur état, leur catégorie, leur responsable
  et leurs dates ;
- modification du statut du ticket : quand le ticket passe à « Fermé », la
  date de clôture est renseignée automatiquement.

**Partie administration (administrateur uniquement)**

- création et modification des états, des catégories, des responsables et
  des tickets ;
- suppression des états, catégories et responsables.

## Installation

1. Cloner le dépôt puis installer les dépendances :

```bash
composer install
```

2. Configurer la connexion MySQL dans le fichier `.env`
   (par défaut : `DATABASE_URL="mysql://root:@127.0.0.1:3306/dbb_tickets?serverVersion=10.4.10-MariaDB&charset=utf8mb4"`).

3. Créer la base, lancer les migrations puis charger les données de test :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

4. Lancer le serveur :

```bash
symfony server:start
```

ou avec le serveur PHP intégré :

```bash
php -S 127.0.0.1:8000 -t public
```

## Comptes fournis par les fixtures

| Rôle | Adresse e-mail | Mot de passe |
|------|----------------|--------------|
| Administrateur | admin@agence.fr | Admin2026! |
| Personnel | personnel@agence.fr | Personnel2026! |

## Structure

- `src/Entity` : Ticket, Categorie, Etat, Responsable, User ;
- `src/Controller` : HomeController (accueil public), SecurityController
  (connexion), TicketController (liste, détail, statut), AdminController
  (gestion des états, catégories, responsables et tickets) ;
- `src/Form` : formulaires (public, administrateur, changement de statut,
  référentiels) ;
- `migrations` : création du schéma par migrations ;
- `src/DataFixtures` : jeu d'essais de l'application.