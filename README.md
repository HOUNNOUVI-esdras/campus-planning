# Campus Planning

Application d'emploi du temps universitaire, utilisée comme base pour le cours de TDD (Test-Driven Development) de L3.

Les professeurs programment leurs cours pour un groupe d'étudiants. L'administrateur gère les groupes. Les étudiants voient les cours de leurs groupes et chaque participant reçoit un email quand un cours est créé, modifié ou annulé.

Construite avec Laravel 13, des vues Blade simples et Tailwind CSS 4. Les tests utilisent Pest.

## Fonctionnalités

- Calendrier de la semaine, du lundi au samedi, de 08:00 à 20:00.
- Trois rôles : administrateur, professeur, étudiant.
- Création, modification et annulation d'un cours, avec un contrôle des chevauchements pour un même professeur.
- Groupes d'étudiants gérés par l'administrateur. Un cours est suivi par un groupe.
- Notifications par email. Un email en échec n'annule pas l'opération.
- Connexion par email et mot de passe, avec vérification de l'email.

## Installation

Prérequis : PHP 8.4, Composer, Node.js et npm. La base de données par défaut est SQLite.

```bash
composer setup
php artisan migrate:fresh --seed
```

## Lancer l'application

```bash
composer run dev
```

L'application est disponible sur `http://localhost:8000`.

## Comptes de démonstration

Mot de passe commun : `password`.

| Rôle | Email |
| --- | --- |
| Administrateur | `admin@univ.test` |
| Professeur | `awa.sossou@univ.test` |
| Professeur | `koffi.dossou@univ.test` |
| Étudiant | `alice.houngbo@univ.test` |
| Étudiant | `bob.adjovi@univ.test` |

## Tests

```bash
php artisan test
```

Pour lancer aussi le formatage (Pint) et l'analyse statique (PHPStan) :

```bash
composer test
```

## Exercice : les jours spéciaux

À vous d'ajouter cette fonctionnalité, en TDD :

> Un administrateur déclare un jour spécial (par exemple une journée pédagogique). Ce jour-là, aucun cours ne peut être programmé ni déplacé.

Commencez par la liste des scénarios, écrivez un test qui échoue, puis le code le plus simple qui le fait passer.

## Branche `draft`

La branche `draft` contient une solution de l'exercice des jours spéciaux. Consultez-la seulement après avoir essayé.

```bash
git switch draft
```
