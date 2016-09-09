Author: Oleg Ivanov
email:  oli2002@med.cornell.edu

Web-based order form using Symfony2, Doctrine2 and Twitter Bootstrap

# Configuration info:
http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/config.php

# Development mode:
http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/

# Production mode:
http://collage.med.cornell.edu/order/

# Admin page (Requires admin role):
http://collage.med.cornell.edu/order/admin

#To include assets located in your bundles' Resources/public folder (target is by default "web"):
php app/console assets:install

# Note: For production mode execute the following command to clean cache and fix assetic links to js and css
(read: Dumping Asset Files in the dev environment http://symfony.com/doc/current/cookbook/assetic/asset_management.html):
php app/console cache:clear --env=prod --no-debug or (php app/console cache:clear --env=prod --no-debug --no-warmup)
php app/console assetic:dump --env=prod --no-debug


# run composer update or self-update
composer.phar update

# Doctrine:
1) Generate only getters/setters:
php app/console doctrine:generate:entities Oleg/OrderformBundle/Entity/Slide
2) Generate CRUD:
php app/console generate:doctrine:crud --entity=OlegOrderformBundle:Accession --format=annotation --with-write
3) Create database according to Entity
php app/console doctrine:database:create
4) Create tables:
php app/console doctrine:schema:update --force
5) Recreate DB:
php app/console doctrine:database:drop --force
php app/console doctrine:database:create

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

# If there are some local modified files, then will not allow to merge with local modifications. There are 3 options (option (b): git stash is enough):
a) git commit -m "My message"
b) git stash
c) git reset --hard

# Force Git to overwrite local files on pull; This will remove all the local files (http://stackoverflow.com/questions/1125968/force-git-to-overwrite-local-files-on-pull)
git fetch --all
git reset --hard origin/master

# To push changes from locally create branch (i.e. iss51) to remote repo
git push -u origin iss51

# To create local branch from remote repo
git fetch origin
git checkout --track origin/iss51
# Or:
git remote update (note: this is the same as git fetch --all)
git pull

#To update whole tree, even from the subdirectory (including remove of deleted files)
git add -u .
git commit -m "message"
git push -u origin master

#To remove already cashed files after changing .gitignore (First commit any outstanding code changes, and then, run this command)
git rm -r --cached .
git add .
git commit -m ".gitignore is now working"

#To checking only a specific file from remote repository
#//git fetch will download all the recent changes, but it will not put it in your current checked out code (working area).
git fetch
//git checkout origin/master -- path/to/file will checkout the particular file from the the downloaded changes (origin/master).
git checkout origin/master -- path/to/file

#To revert a specific file to a specific version (abcde-commit you want)
git checkout abcde file/to/restore

#Testing: run phpunit script, located in symfony's 'bin' folder, with the test file as a parameter:
./bin/phpunit -c app src/Oleg/OrderformBundle/Tests/LoginTest.php

#Testing with casperjs: run   http://collage.med.cornell.edu/order/test/index.php
#The result log and screenshots are in order/test folder