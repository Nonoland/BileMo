# BeliMo

BeliMo est une application web exposant une API, développée en Symfony 6.3 et PHP 8.1. 


## Prérequis

- PHP 8.1+
- Symfony 6.3+
- MySQL

## Installation

1. Clonez le dépôt dans le répertoire de votre choix :

```bash
git clone https://github.com/Nonoland/BeliMo.git
```

2. Installez les dépendances avec composer :

```bash
cd BeliMo
composer install
```

3. Configurez votre fichier .env pour y ajouter les informations de connexion à votre base de données. Vous pouvez copier le fichier .env.dist et le renommer en .env :

```bash
cp .env.dist .env
```

4. Ouvrez le fichier .env et modifiez la ligne `DATABASE_URL` avec vos informations de connexion à la base de données.

5. Générer les clés privée et publique pour l'authentification par JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

6. Créez la base de données :

```bash
php bin/console doctrine:database:create
```

7. Exécutez les migrations pour créer les tables dans votre base de données :

```bash
php bin/console doctrine:migrations:migrate
```

8. Générer le jeu de données dans votre base de données.

```bash
php bin/console doctrine:fixtures:load
```

9. Ajouter un utilisateur

```bash
php bin/console belimo:create-user
```

10. Vous pouvez maintenant accéder à l'application à l'adresse http://localhost:8000

Documentation API : /doc
Panneau d'administration : /admin
