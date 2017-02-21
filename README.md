# O R D E R

>**Important note:** This is a software prototype. While it has been extensively tested, unresolved issues remain. This software should
> not be used in a live production environment unless you are fully prepared to continuously monitor for and deal with encountered issues.
> 
> Specifically, the security, scalability, cross-browser compatibility, user interface responsiveness, performance, data consistency,
> safe multi-user concurrency, proper versioning, and platform independence have been left as exercises to the reader.
>

##About

O R D E R is a web-based software platform for development of clinical, administrative, research, and educational multi-user applications.

It includes several functional example applications:

- Glass Slide Scan Orders

- Employee Directory

- Fellowship Applications

- Deidentifier

- Vacation Request

- Call Log Book


##Support

If you discover a specific issue, [post it here](https://github.com/victorbrodsky/order-lab/issues).


## Contributing documentation

If you would like to contribute documentation using the [Markdown format](http://daringfireball.net/projects/markdown/), please feel free to do so.

The source files are available at [github.com/victorbrodsky/order-lab](https://github.com/victorbrodsky/order-lab).


## Team

- Victor Brodsky ([@victorbrodsky](https://github.com/victorbrodsky))
- Oleg Ivanov ([@cinava](https://github.com/cinava))


## License

[Apache 2.0](https://www.apache.org/licenses/LICENSE-2.0)


## Developer Notes

### Test server links (accessible on the intranet only):

[Configuration info](http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/config.php)

[Development mode](http://collage.med.cornell.edu/order/scanorder/Scanorders2/web/app_dev.php/)

[Production mode](http://collage.med.cornell.edu/order/)

[Admin page](http://collage.med.cornell.edu/order/admin)

### To include assets located in your bundles' Resources/public folder (target is by default "web"):

	 php app/console assets:install

### Note: For production mode execute the following command to clean cache and fix assetic links to js and css
(read: Dumping Asset Files in the dev environment [http://symfony.com/doc/current/cookbook/assetic/asset_management.html](http://symfony.com/doc/current/cookbook/assetic/asset_management.html)):

	 php app/console cache:clear --env=prod --no-debug or (php app/console cache:clear --env=prod --no-debug --no-warmup)
	 php app/console assetic:dump --env=prod --no-debug

### After modification of html, js or css files, run this single console script to clear cache and dump assets in /Scanorders2 folder:

	 deploy_prod

### To run composer update or self-update

	 composer.phar update

### Doctrine:

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

### Create Symfony project (cd to htdocs/order)

1) create git repository from remote server (on Bitbucket for example):

	 git clone https://yourusername@bitbucket.org/weillcornellpathology/scanorder.git 

2) create symfony2 project: 

	php "C:\Users\oli2002\Desktop\php\Composer\composer.phar" create-project symfony/framework-standard-edition Scanorders2

3) create bundle: 

	php app/console generate:bundle --namespace=Acme/HelloBundle --format=yml

4) Run command:

	 git add .

5) Run command:

	 git commit -m "adding initial"

6) Run command:

	 git push -u origin master

7) repeat 3-5 for development

### To get changes onto your local machine:

1) cd to dir created by clone

2) Run command:

	 git remote update

3) Run command:

	 git pull

### If there are some local modified files, then git will not allow a merge with local modifications. There are 3 options (option (b): git stash is enough):

a) Run command:

	 git commit -m "My message"

b) Run command:

	 git stash

c) Run command:

	 git reset --hard

### To force Git to overwrite local files on pull; This will remove all the local files ([http://stackoverflow.com/questions/1125968/force-git-to-overwrite-local-files-on-pull](http://stackoverflow.com/questions/1125968/force-git-to-overwrite-local-files-on-pull))

	 git fetch --all
	 git reset --hard origin/master

### To push changes from a locally created branch (i.e. iss51) to a remote repo

	 git push -u origin iss51

### To create a local branch from a remote repo

	 git fetch origin
	 git checkout --track origin/iss51

### Or:

	 git remote update (note: this is the same as git fetch --all)
	 git pull

### To update the whole tree, even from a subdirectory (including removed of deleted files)

	 git add -u .
	 git commit -m "message"
	 git push -u origin master

### To remove already cached files after changing .gitignore (First commit any outstanding code changes and then run this command)

	 git rm -r --cached .
	 git add .
	 git commit -m ".gitignore is now working"

### To check only a specific file from a remote repository

[http://stackoverflow.com/questions/2466735/how-to-checkout-only-one-file-from-git-repository](http://stackoverflow.com/questions/2466735/how-to-checkout-only-one-file-from-git-repository)

### To download all the recent changes (but not put it in your current checked out code (working area)):

	 git fetch

### To checkout a particular file from the the downloaded changes (origin/master):

	 git checkout origin/master -- path/to/file

### To revert a specific file to a specific version (abcde-commit you want)

	 git checkout abcde file/to/restore

### Testing: run phpunit script, located in symfony's 'bin' folder, with the test file as a parameter:

	 ./bin/phpunit -c app src/Oleg/OrderformBundle/Tests/LoginTest.php

### Testing with casperjs on the original Dev server on the intranet: 

1. run   [/order/test/index.php](http://collage.med.cornell.edu/order/test/index.php)

2. The resulting log and screenshots are in order/test folder)