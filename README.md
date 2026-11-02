# Symfony : Bootstrap template

Socle à cloner pour démarrer un projet Symfony avec Bootstrap et AssetMapper.
Le bundle Security est installé, mais aucune gestion des utilisateurs ni authentification applicative n'est configurée.

## Stack technique

- Symfony 7.4 LTS, Twig, Doctrine ORM et migrations.
- PHP 8.5.12 sélectionné par `.php-version` ; contrainte Composer : `>=8.5.12 <8.6`.
- AssetMapper, Stimulus et Turbo, sans compilation Node.js nécessaire.
- Bootstrap 5.3.8 et Bootstrap Icons 1.13.1.
- PHPUnit 13 pour les tests.

La configuration versionnée utilise MySQL 8.0.43 par défaut (`.env` et `compose.yaml`). Adapter `DATABASE_URL` à l'installation réelle dans `.env.local` comme indiqué ci-dessous.

## Prérequis

Git, Composer 2, PHP compatible avec le projet et Symfony CLI pour le serveur de développement. Installer les extensions PHP requises par les dépendances et `pdo_mysql`. Docker Compose est facultatif pour les services fournis.

Modifier Composer ou `.php-version` n'installe pas PHP. Vérifier les versions disponibles :

```powershell
php -v
composer --version
symfony local:php:list
symfony php -v
```

`php -v` et `symfony php -v` doivent afficher la version attendue. Composer lancé directement utilise le PHP du terminal.
Le fichier `php.ini` à la racine fournit des réglages pour Symfony CLI, dont le fuseau horaire `Europe/Paris`. Vérifier séparément la configuration PHP d'Apache/Wamp si nécessaire.

## Installation locale avec Wamp et MySQL

### 1. Cloner le dépôt

```powershell
git clone git@github.com:hbernard80/sf74-bootstrap.git
cd sf74-bootstrap
```

Cette URL nécessite une clé SSH GitHub configurée. Adapter le nom du dossier au nouveau projet si nécessaire.

### 2. Configurer l'environnement

Créer `.env.local` à la racine avant d'installer les dépendances :

```dotenv
APP_ENV=dev
APP_SECRET=remplacer-par-un-secret-local-aleatoire
APP_SHARE_DIR=var/share
DEFAULT_URI=http://localhost:8000
DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3306/mon_projet?serverVersion=8.0.43&charset=utf8mb4"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
MAILER_DSN=null://null
SITE_NAME="Mon projet"
SITE_OWNER="Mon entreprise"
SITE_EMAIL_CONTACT=contact@example.test
SITE_EMAIL_NOREPLY=noreply@example.test
```

Adapter les identifiants, le port et `serverVersion` à l'installation MySQL réelle. Encoder les caractères réservés des identifiants dans l'URL de connexion.
Les fichiers `.env.local` et `.env.*.local` sont ignorés par Git. `MAILER_DSN=null://null` désactive la livraison des courriels.

### 3. Installer les dépendances et créer la base

```powershell
composer install
composer check-platform-reqs
New-Item -ItemType Directory -Force var/share
php bin/console doctrine:database:create --if-not-exists
```

Après un clonage, utiliser `composer install` pour respecter `composer.lock`. Les scripts Composer vident le cache, installent les assets des bundles et téléchargent les dépendances de l'importmap.

Le socle ne contient pas encore d'entités ni de migrations. Après avoir créé des entités :

```powershell
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### 4. Lancer le serveur

```powershell
symfony server:start -d
```

Ouvrir l'URL affichée par Symfony CLI ; la page d'accueil se trouve à `/`. Adapter `DEFAULT_URI` au port et au protocole utilisés.
Pour HTTPS local, installer le certificat sur chaque machine avec `symfony server:ca:install`.
Pour arrêter le serveur : `symfony server:stop`.

## Exécuter l'application avec Docker

Cette configuration est destinée au développement local. Docker Desktop doit être démarré en mode conteneurs Linux. Elle fournit PHP 8.5.12 avec Apache et Composer, MySQL 8.0.43, phpMyAdmin et Mailpit. Wamp et Symfony CLI ne sont pas nécessaires pour ce parcours.

Depuis la racine du dépôt cloné :

```powershell
docker compose up -d --build --wait --wait-timeout 300
docker compose ps
```

Le premier démarrage télécharge les images, compile les extensions PHP et exécute `composer install`. Il nécessite un accès Internet et peut prendre plusieurs minutes. L'installation Composer est relancée à chaque démarrage du service `app` pour tenir compte du fichier lock.

| Service | Accès depuis Windows |
| --- | --- |
| Application Symfony | http://localhost:8000 |
| phpMyAdmin | http://localhost:8080 |
| Interface Mailpit | http://localhost:8025 |
| SMTP Mailpit | 127.0.0.1:1025 |
| MySQL | 127.0.0.1:3307 |

Dans phpMyAdmin, utiliser le compte MySQL `app` avec le mot de passe de développement `!ChangeMe!`. Le serveur MySQL est préconfiguré ; aucune connexion automatique n'est activée. Ce compte gère la base `app`.

Dans le conteneur Symfony, les connexions utilisent les noms des services et leurs ports internes :

```dotenv
DATABASE_URL="mysql://app:!ChangeMe!@database:3306/app?serverVersion=8.0.43&charset=utf8mb4"
MAILER_DSN=smtp://mailer:1025
```

Ces valeurs sont injectées par Compose et prennent le pas sur les valeurs des fichiers Symfony `.env.local`. Le nom du site, le propriétaire et les adresses de contact ont aussi des valeurs par défaut dans Compose.

Pour personnaliser Docker sans modifier `.env`, créer `.env.docker.local` (ignoré par Git), par exemple :

```dotenv
SITE_NAME="Mon projet"
SITE_OWNER="Mon entreprise"
APP_PORT=8000
PHPMYADMIN_PORT=8080
DOCKER_APP_SECRET=remplacer-par-un-secret-local-aleatoire
```

Puis utiliser ce fichier pour les commandes Compose :

```powershell
docker compose --env-file .env.docker.local up -d --build --wait --wait-timeout 300
```

Les paramètres `PHP_VERSION`, `MYSQL_VERSION`, `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `MYSQL_PORT`, `MAILPIT_SMTP_PORT` et `MAILPIT_HTTP_PORT` sont également configurables. Garder `PHP_VERSION` cohérent avec `.php-version` et les exigences Composer. Si les identifiants MySQL contiennent des caractères réservés d'URL, fournir `DOCKER_DATABASE_URL` avec les identifiants encodés.
Les identifiants par défaut sont réservés au développement local. Ils initialisent une nouvelle base ; modifier ces variables ne change pas les comptes d'un volume déjà initialisé.

Le code source est monté dans `/app` : les modifications sont visibles dans le conteneur. Les dépendances PHP, le cache et les assets téléchargés utilisent des volumes séparés de ceux de Windows. Apache sert uniquement le répertoire `public/`. Les ports publiés sont limités à la machine locale.

Commandes courantes (ajouter `--env-file .env.docker.local` après `docker compose` si ce fichier est utilisé) :

```powershell
docker compose exec app php bin/console about
docker compose exec app php bin/phpunit
docker compose exec app composer check-platform-reqs
docker compose logs -f app
docker compose down
```

MySQL crée la base `app` au premier démarrage. Après ajout d'entités et de migrations, les appliquer avec `docker compose exec app php bin/console doctrine:migrations:migrate`.
Pour les messages asynchrones, lancer `docker compose exec app php bin/console messenger:setup-transports`, puis `docker compose exec app php bin/console messenger:consume async -vv` dans un terminal dédié.

Les données MySQL sont conservées dans le volume `mysql_data`. Un ancien volume PostgreSQL `database_data` n'est ni réutilisé ni supprimé ; cette configuration ne migre pas ses données.
`docker compose down` conserve les volumes ; l'option `-v` les supprime, y compris les données MySQL.

## Tests et contrôles qualité

L'environnement de test charge `.env` et `.env.test`, mais pas `.env.local`. Créer `.env.test.local` pour fournir les réglages propres aux tests :

```dotenv
SITE_NAME="Mon projet"
SITE_OWNER="Mon entreprise"
SITE_EMAIL_CONTACT=contact@example.test
SITE_EMAIL_NOREPLY=noreply@example.test
DATABASE_URL="sqlite:///:memory:"
MESSENGER_TRANSPORT_DSN=in-memory://
MAILER_DSN=null://null
```

Cette configuration nécessite `pdo_sqlite` et suffit au test d'accueil actuel, qui ne manipule aucune donnée. Pour les futurs tests de persistance, configurer une base de test avec le même moteur que l'application. Doctrine ajoute le suffixe `_test` au nom de la base dans l'environnement de test.

```powershell
composer validate --strict --no-check-publish
composer check-platform-reqs
php bin/console lint:yaml config --parse-tags --env=test
php bin/console lint:twig templates --env=test
php bin/console lint:container --resolve-env-vars --env=test
php bin/phpunit
```

Le test fonctionnel de l'accueil vérifie la réponse HTTP, le titre et les liens du menu. Le workflow `.github/workflows/ci.yaml` lance ces contrôles à chaque push et pull request, avec PHP sélectionné depuis `.php-version` et des variables de test dédiées.

## Éléments fournis

- Templates découpés en header, navigation, contenu principal et footer.
- Thème de formulaires `bootstrap_5_horizontal_layout.html.twig`.
- Police Roboto hébergée localement dans `assets/fonts/`.
- Nom du site, propriétaire affiché dans le pied de page (`SITE_OWNER`) et adresses de contact configurables via les variables `SITE_*`.
- Pagerfanta pour la pagination.
- Trait `src/Trait/TimestampableTrait.php` pour les dates de création et de modification.
- Apache Pack installé en dépendance de développement.

Le routage Messenger des courriels est commenté dans `config/packages/messenger.yaml`. Les notifications chat et SMS restent routées vers le transport asynchrone. Si ce transport Doctrine est utilisé, préparer ses tables et démarrer un worker :

```powershell
php bin/console messenger:setup-transports
php bin/console messenger:consume async -vv
```

## Utiliser le trait de dates

Chaque entité utilisant `TimestampableTrait` doit porter `#[ORM\HasLifecycleCallbacks]` sur sa classe pour que Doctrine appelle les méthodes du trait :

```php
use App\Trait\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Article
{
    use TimestampableTrait;

    // Ajouter l'identifiant et les propriétés métier de l'entité.
}
```

Lors de l'insertion, `createdAt` et `updatedAt` sont initialisés avec la même date si ces valeurs n'ont pas déjà été définies. Les dates explicites sont conservées, par exemple lors d'un import.
Lors d'une modification détectée par Doctrine, `updatedAt` est actualisé et `createdAt` est conservé. Un `flush()` sans modification ne déclenche pas `PreUpdate`. Les mises à jour directes en SQL ou DQL ne déclenchent pas ces callbacks.

Les tests d'intégration utilisent une entité de test et SQLite en mémoire (`pdo_sqlite`), sans toucher à la base MySQL. Ils vérifient les valeurs enregistrées puis relues après insertion, import et modification :

```powershell
php bin/phpunit --filter TimestampableTraitTest
```

Ils sont également exécutés par la CI avec le reste des tests.

## À ajouter selon le projet

- Entités, migrations et fixtures métier.
- Authentification, utilisateurs et règles d'accès.
- Traductions et tests des fonctionnalités métier.
- Configuration de déploiement adaptée à l'hébergement.
