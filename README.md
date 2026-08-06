# Symfony : Bootstrap template

> 📆 01/08/2026

Installation de Symfony prêt à cloner pour le démarrage d'un projet, histoire de gagner du temps. Composants de sécurité/utilisateurs non installés.

## Stack technique

* Symfony 7.4 <abbr title="Long-Term Support">L.T.S.</abbr> (webapp)
* PHP 8.5.0
* MySQL 8.0.43
* Bootstrap 5.3.8

## Contient

* Installation avec l'option `webpack` (ORM Doctrine, Twig, Formulaires/Validation, AssetMapper etc.)
  * Fichiers _.phpversion_ et _.php.ini_.
  * Certificat SSL local (à refaire si clonage).
* Composants installés :
  * Apache Pack (installé pour l'environnement de dev, même si utilisation du serveur interne avec Symfony CLI)
  * PagerFanta pour la pagination (composants _babdev/pagerfanta-bundle_, _pagerfanta/doctrine-orm-adapter_ et _pagerfanta/twig_)
* Framework CSS Bootstrap :
  * Intégration + librairie Icons via AssetMapper.
  * Application du thème de formulaire _bootstrap\_5\_horizontal\_layout_ (fichier _config/packages/twig.yaml_)
  * Passage du template _templates/base.html.twig_ à Bootstrap avec un menu responsive.
* Intégration de la police Google Fonts _Roboto_ (méthode : fichier _.woff_ dans le répertoire _assets/fonts/_).
* Structuration sémantique et découpage propre des templates : header, menu (nav), main et footer.
* Ajout de paramètres pour le nom du site et les adresses mail.
* Désactivation de l'envoi de mail asynchrone (pour l'environnement de dev)
* Création d'un fichier _.env.local_ pour création d'une base de données en environnement de développement.
* Création d'un répertoire _var/share/_ pour les fichiers (datas, documents, photos/avatars utilisateurs etc.). Le paramètre _APP_SHARE_DIR_ est créé par l'installation Symfony dans les fichiers _.env_.
* Contrôleur _HomeController_ (temporaire, à écraser par les contrôleurs réels du projet à réaliser), avec son template _templates/home/index.html.twig_ et son fichier de test unitaire _tests/Controller/HomeControllerTest.php_.
* Trait pour la gestion des dates dans les entités (propriétés `created_at` et `updated_at`) :_src/Trait/TimestampableTrait.php_.

## Ne contient pas

* Entités / Fixtures
* Tests unitaires (sauf pour le contrôleur _HomeController, généré automatiquement)
* Traductions
* Utilisateurs et sécurité
