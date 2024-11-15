### Projet Web PHP/MySQL - Gestion des Activités Sportives
Ce projet est une application web développée en PHP et MySQL pour gérer des activités sportives. Il permet aux administrateurs de créer, modifier, annuler et supprimer des activités, ainsi qu'aux utilisateurs de s'inscrire, consulter leur compte, et gérer leur historique.




## Table des Matières
· Fonctionnalités
· Installation
    · Prérequis
    · Configuration de la base de données
    · Configuration du serveur web
    · Configuration du projet
· Utilisation
    · Administration
    · Utilisateur
· Problèmes Courants et Solutions
    · Sensibilité à la Casse des Noms de Tables
    · Permissions de Fichiers et Répertoires
    · Adaptation aux Appareils Mobiles
· Bonnes Pratiques




# Fonctionnalités
· Gestion des Activités : Les administrateurs peuvent ajouter, modifier, annuler et supprimer des activités sportives.
· Gestion des Inscriptions : Les utilisateurs peuvent s'inscrire aux activités, annuler leur inscription et consulter leur historique.
· Gestion des Comptes : Les utilisateurs peuvent consulter et modifier leurs informations personnelles.
· Téléchargement de Photos : Possibilité d'ajouter jusqu'à 4 photos par activité (JPEG, max 2,5 Mo par image).
· Notifications : Les utilisateurs sont notifiés lorsque des activités auxquelles ils sont inscrits sont annulées.
· Historique : Les utilisateurs peuvent consulter et supprimer leur historique d'activités passées.
· Adaptation Mobile : L'interface est responsive et adaptée aux appareils mobiles.






## Installation

#  Prérequis
· Serveur Web : Apache, Nginx ou autre avec support PHP.
· PHP : Version 7.0 ou supérieure.
· MySQL : Version 5.7 ou supérieure.
· Extensions PHP : PDO, MySQLi.


# Configuration de la base de données
1. Créer la base de données :

sql：

    CREATE DATABASE project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


2. Importer les tables et données :

Utilisez le fichier project.sql fourni pour créer les tables et insérer les données initiales.

    bash
    mysql -u username -p project < project.sql
    
Note : Assurez-vous que les tables entrainement, inscription, utilisateur, etc., sont correctement créées avec les noms en minuscules.

# Configuration du serveur web
· Document Root : Placez les fichiers du projet dans le répertoire accessible par le serveur web, par exemple /var/www/html/project/.

· Permissions :

  · Assurez-vous que le serveur web (par exemple, utilisateur www-data) a les permissions nécessaires pour lire 
    et écrire dans le répertoire du projet.
  · Spécifiquement, le répertoire image_entrainement/ doit être accessible en écriture pour permettre le 
    téléchargement et la suppression des images.
# Configuration du projet

1.Fichier de connexion à la base de données :

  · Modifiez le fichier db_connect.php pour y mettre vos identifiants de connexion à la base de données.

php：

    <?php
    $dsn = 'mysql:host=localhost;dbname=project;charset=utf8mb4';
    $username = 'votre_nom_utilisateur';
    $password = 'votre_mot_de_passe';

    try {
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo 'Connexion échouée : ' . $e->getMessage();
    }
    ?>
    
2.Vérification des dépendances :

· Assurez-vous que toutes les extensions PHP nécessaires sont installées et activées.






## Utilisation

# Administration
 · Accès : Connectez-vous en tant qu'administrateur via connect_administrateur.php.

 · Gestion des activités :

     ·Ajouter une activité : Remplissez le formulaire avec les informations de l'activité, y compris les photos.
     ·Modifier une activité : Cliquez sur le bouton "Modifier" dans la liste des activités, apportez les 
      modifications nécessaires.
     ·Annuler une activité : Cliquez sur "Annuler" pour marquer une activité comme annulée. Les utilisateurs 
      inscrits seront notifiés.
     ·Supprimer une activité : Cliquez sur "Supprimer" pour supprimer définitivement l'activité, y compris les 
      inscriptions et les photos associées.

# Utilisateur

   ·Inscription : Créez un compte utilisateur via connect_visitor.php.

   ·Gestion du compte :

      ·Consultez et modifiez vos informations personnelles dans account_detail.php.
      ·Visualisez vos inscriptions actuelles et historiques.
      ·Annulez vos inscriptions aux activités futures.
      ·Supprimez votre historique d'activités passées.

      
## Problèmes Courants et Solutions

# Sensibilité à la Casse des Noms de Tables
Problème : Erreurs SQL indiquant que les tables n'existent pas, par exemple :

sql：


    SQLSTATE[42S02]: Base table or view not found: 1146 Table 'bdd_6_10.Entrainement' doesn't exist
    
Cause : Sous Linux, MySQL est sensible à la casse des noms de tables. Si votre table s'appelle entrainement en minuscules, mais que votre requête SQL utilise Entrainement, une erreur se produira.


Solution :

· Toujours utiliser les noms de tables en minuscules dans vos requêtes SQL.
· Vérifiez et corrigez tous les appels aux tables dans votre code PHP.


# Permissions de Fichiers et Répertoires

Problème : Erreurs lors du téléchargement ou de la suppression de fichiers, par exemple :

css：

    Warning: move_uploaded_file(): Permission denied
    
Cause : Le serveur web n'a pas les permissions nécessaires pour écrire dans le répertoire de destination.


Solution :

· Modifier les permissions du répertoire :

    bash

    sudo chown -R www-data:www-data /var/www/html/project/image_entrainement/
    sudo chmod -R 755 /var/www/html/project/image_entrainement/
    
Remplacez www-data par l'utilisateur de votre serveur web si nécessaire.

· Vérifier les restrictions de sécurité :

   · Si vous utilisez SELinux ou AppArmor, assurez-vous que le serveur web est autorisé à écrire dans le 
     répertoire.

     
# Adaptation aux Appareils Mobiles

Problème : Les pages web ne s'affichent pas correctement sur les appareils mobiles.

Solution :

Ajouter la balise meta viewport dans le <head> de vos pages HTML :

    html：

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
· Utiliser un framework CSS responsive comme Bootstrap :

  · Inclure Bootstrap dans vos pages :

    html：

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    
· Utiliser les classes Bootstrap pour créer une mise en page responsive.

  · Utiliser des unités relatives (%, em, rem) et des media queries dans votre CSS pour adapter le style en 
    fonction de la taille de l'écran.


## Bonnes Pratiques

· Sécurité :

  · Validez et échappez toutes les entrées utilisateur pour prévenir les injections SQL et les attaques XSS.
  · Utilisez des requêtes préparées avec PDO pour interagir avec la base de données.
  
· Gestion des Erreurs :

  · Implémentez une gestion des erreurs appropriée pour aider au débogage tout en évitant de divulguer des 
    informations sensibles aux utilisateurs.
· Structure du Code :

  · Organisez votre code en suivant les principes MVC pour une meilleure maintenabilité.
  · Commentez votre code pour faciliter la compréhension et la collaboration.
  
· Performances :

  · Optimisez vos requêtes SQL et utilisez des index appropriés sur vos tables.
  · Chargez les ressources (CSS, JS, images) de manière efficace, en utilisant des CDN lorsque cela est 
    possible.
    
· Expérience Utilisateur :

  · Assurez-vous que votre interface est intuitive et facile à utiliser.
  · Rendez votre site accessible sur différents navigateurs et appareils.


Note : Ce fichier README a été rédigé pour fournir une vue d'ensemble du projet et aider à résoudre certains problèmes courants. Pour toute question ou assistance supplémentaire, n'hésitez pas à consulter la documentation ou à contacter le développeur principal.
