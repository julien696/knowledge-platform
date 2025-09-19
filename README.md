# Knowledge Platform
API développer avec API Platform v4, Mysql, stripe, et des fixtures pour les données tests.

## Installation

1. **Cloner le projet**

2. **Installer les dépendances**  
```composer install```

 3. **Configuration du fichier .env.local** 
- APP_ENV=dev
- APP_SECRET=clés_secréte
- DATABASE_URL
- JWT_SECRET_KEY
- JWT_PUBLIC_KEY
- JWT_PASSPHRASE
- MAILER_DSN=smtp://localhost:1025
- CORS_ALLOW_ORIGIN=* 
- MAILER_FROM 

4. **Création de la base de données**  
```php bin/console doctrine:database:create```

5. **Exécuter les migrations**  
```php bin/console doctrine:migrations:migrate```

6. **Charger les données de test**  
```php bin/console doctrine:fixtures:load```

7. **Lancer le serveur**  
```php -S localhost:8000 -t public```

## Documentation API
- **Swagger UI** : http://localhost:8000/api/docs

## Utilisateurs de test
- **Utilisateur** : `johndoe@gmail.com` / `johndoe`
- **Admin** : `admin@gmail.com` / `adminpassword`