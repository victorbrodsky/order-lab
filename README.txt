Author: Oleg Ivanov
email:  oli2002@med.cornell.edu

Web-based order form using Symfony2, Doctrine2 and Twitter Bootstrap

# Configuration info:
http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/config.php

# Development mode:
http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/

# Production mode:
http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app.php/

# Note: For production mode execute the following command to clean cache and fix assetic links to js and css
(read: Dumping Asset Files in the dev environment http://symfony.com/doc/current/cookbook/assetic/asset_management.html):
php app/console assetic:dump --env=prod --no-debug
php app/console cache:clear --env=prod --no-debug

# Doctrine:
1) Generate only getters/setters:
php app/console doctrine:generate:entities Oleg/OrderformBundle/Entity/Slide
2) Generate CRUD:
php app/console generate:doctrine:crud --entity=OlegOrderformBundle:Accession --format=annotation --with-write
3) Create database according to Entity
php app/console doctrine:database:create
4) Create tables:
php app/console doctrine:schema:update --force

# Create Symfony project (cd to htdocs/order)
1) create git repository from remote server (bitbucket):
	 git clone https://yourusername@bitbucket.org/weillcornellpathology/scanorder.git 
2) create symfony2 project: 
	php "C:\Users\oli2002\Desktop\php\Composer\composer.phar" create-project symfony/framework-standard-edition Scanorders2
3) create bundle: 
	php app/console generate:bundle --namespace=Acme/HelloBundle --format=yml
4) git add .
5) git commit -m "adding initial"
6) git push -u origin master
7) repeat 3-5 for development

# To get changes on your local machine:
1) cd to dir created by clone
2) git remote update
3) git pull
