Pour lancer le projet :

// 1. In the root folder.

docker-compose up -d 
docker exec symfony_docker composer create-project symfony/skeleton html


// Pensez ensuite à aller exécuter toutes vos commandes depuis l'intérieur du container.
Par exemple : test

cd symfony_project
composer require orm


(Demandez à Composer de NE PAS créer une config Docker pour la database)
Enfin, modifiez la config DB dans le fichier .env de Symfony :

DATABASE_URL=mysql://root:mysqlpass@db:3306/symfony_db?serverVersion=mariadb-10.7.1# server-sound
