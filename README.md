# BileMo
Projet 7, Créez un web service exposant une API, de la formation OpenClassroom Développeur d'application - php / symfony


## Présentation du projet
L'objectif du projet est de créer une api pour le client BileMo ou il est possible de :
    - Consulter la liste des produits BileMo
    - Consulter les détails d'un produit BileMo
    - Consulter la liste des utilisateurs inscrits liés à un client sur le site web
    - Consulter le détail d'un utilisateur inscrit lié à un client
    - Ajouter un nouvel utilisateur lié à un client
    - Supprimer un utilisateur ajouté par un client
    - Seuls les clients références peuvent accéder aux API

## Initialisation du projet

### Création de la base de donnée
```shell
mysql -u [user] -p -e "CREATE DATABASE bilemo"
```

### Installation des dépendances
```shell
composer install
```


### Création des tables
```shell
symfony console doctrime:schema:update
```

### Ajout de donnée
```shell
php bin/console doctrine:fixtures:load
```
## Configuration du projet
Pour configurer le projet il suffit de copier le fichier ".env" qui se trouve a la racine du projet et replacer les "ChangeMe" par vos informations. 
Puis de renomer le fichier en ".env.local".

## Access a l'API DOC
http://127.0.0.1:8000/api/doc