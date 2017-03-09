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


## Installation instructions for Linux

>**Warning:** This software was developed and tested in a Windows-based environment to accommodate existing servers. To ease further
> development and testing, the [Packer](https://www.packer.io/)-based deployment script for a [Digital Ocean](https://www.digitalocean.com/)
> virtual machine (VM) is provided. Additional extensive testing is necessary to discover and address unresolved issues associated with
> cross-platform compatibility (and Linux specifically). The installation instructions assume the use of a Linux platform (such as 
> [Ubuntu](https://www.ubuntu.com/)). The specific commercial hosting provider was chosen as an example for convenience.
> 

1. Sign up for [Digital Ocean](https://www.digitalocean.com/) and obtain an [API access key token](https://www.digitalocean.com/help/api/). It should look similar to this one: e4561f1b44faa16c2b43e94c5685e5960e852326b921883765b3b0e11111f705

2. Download and uncompress or clone the source code from [github.com/victorbrodsky/order-lab](https://github.com/victorbrodsky/order-lab)

3. Install [Packer](https://www.packer.io/)

4. Install [doctl](https://github.com/digitalocean/doctl)

5. Edit /packer/parameters.yml in this project's folder to set desired values (especially for passwords)

6. Run /packer/deploy-order-digital-ocean.sh via (make sure to supply your API token):

	 	bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml

7. Log into the resulting server's web page with the user name "Administrator" and the password "1234567890"

8. For a live server, set the "Environment" variable's value to "live" in Admin->Site Settings->Platform Settings. For a development server set the "Environment" variable's value to "dev". For a test server set the "Environment" variable's value to "test".


## Installation Instructions for Windows

1. Install an Apache-PHP-MySQL stack of your choice (preferably with PHP version 5.6), such as those listed below. MS SQL Server has been tested as well.

	* [AMPPS](http://www.ampps.com/)
	* [WAMP](http://www.wampserver.com/en/)
	* [XAMPP](https://www.apachefriends.org/index.html)

2. Modify php.ini:


	a) Enable the ldap extension

	 	extension=php_ldap.dll

	b) Enable the pdo extension

	For PHP version 5.6 "[php_pdo_sqlsrv_56_ts.dll and php_sqlsrv_56_ts.dll](http://www.microsoft.com/en-us/download/details.aspx?id=20098)" should be placed in PHP/ext folder, and the following lines added to php.ini:

	 	extension=php_sqlsrv_56_ts.dll
	 	extension=php_pdo_sqlsrv_56_ts.dll

	For the older PHP version 5.4 "[php_pdo_sqlsrv_54_ts.dll and php_sqlsrv_54_ts.dll](http://www.microsoft.com/en-us/download/details.aspx?id=20098)" should be placed in PHP/ext folder, and the following line added to php.ini:

	 	extension=php_sqlsrv_54_ts.dll
	 	extension=php_pdo_sqlsrv_54_ts.dll

	c) Download the [Microsoft ODBC Driver 11 for PHP for SQL Server](https://www.microsoft.com/en-us/download/details.aspx?id=36434)

		* For 64 bit (x64) Operating Systems use the x64\msodbcsql.msi installation file
		* For 32 bit (x86) Operating Systems use the x86\msodbcsql.msi installation file

	d) Enable the GD library extension

	 	extension=php_gd2.dll

	e) Enable Internationalization support

	 	extension=php_intl.dll

	f) Enable the file info extension

		extension=php_fileinfo.dll

	g) For PHP version 5.4 [download OPCache](http://windows.php.net/downloads/pecl/releases/opcache/7.0.3/php_opcache-7.0.3-5.4-ts-vc9-x86.zip) and [enable it](http://stackoverflow.com/questions/24155516/how-to-install-zend-opcache-extension-php-5-4-on-windows); for PHP version 5.6 just [enable OPCache](http://php.net/manual/en/opcache.setup.php).

		zend_extension="PATH-TO-WEB-SERVER\WebServer\PHP\Ext\php_opcache.dll"

	h) Make sure that Apache can find and load icu*.dll files from the PHP base folder. One possible solution is to copy these files to Apache's "bin" folder:

		* icudt49.dll
		* icuin49.dll
		* icuio49.dll
		* icule49.dll
		* iculx49.dll
		* icutu49.dll
		* icuuc49.dll


3. Modify Apache's httpd.conf as specified in the [Symfony web server configuration](http://symfony.com/doc/current/setup/web_server_configuration.html):

	<VirtualHost *:80>
		<Directory "C:\path-to-lab-order\Scanorders2\web\"
			Options +FollowSymLinks -Includes
			AllowOverride All  
			Require all granted
		</Directory>
		Alias /order "C:\path-to-lab-order\Scanorders2\web\"
		Alias /ORDER "C:\path-to-lab-order\Scanorders2\web\"
		RewriteRule ^/ORDER(.*)$ /order$1 [R=301]
		ErrorLog ${APACHE_LOG_DIR}/error.log
		CustomLog ${APACHE_LOG_DIR}/access.log combined
	</VirtualHost>

4. [Download](https://getcomposer.org/download/) and [install](https://getcomposer.org/doc/00-intro.md) Composer. Make sure the ...\WebServer\PHP path and the composer's path are added to the system path.

5. Edit Symfony's app/config/parameters.yml to set the desired values:

    	database_driver: pdo_sqlsrv
    	database_host: 127.0.0.1
    	database_port: null
    	database_name: ScanOrder
    	database_user: symfony
    	database_password: symfony
    	mailer_transport: smtp
    	mailer_host: 127.0.0.1
    	mailer_user: null
    	mailer_password: null
    	locale: en   
    	delivery_strategy: realtime

6. Create the application's database and the associated database user:

	a) create a database user name specified in the database_user line of parameters.yml file with "super user" permissions.

	b) create a database with name specified in the database_name line of parameters.yml file

	c) assign the user created in step a) to the Database created in step b) (symfony2->properties->User Mapping-> map ScanOrder with db_owner)
	
7. Update symfony vendors by running these console commands in path-to-lab-order/Scanorders2 folder:

		composer.phar self-update

	 	composer update

8. Deployment
	
	a) Run the deployment script to clean the cache and install assets in path-to-lab-order/Scanorders2 folder: 
	
	 	deploy
	
	b) Create the Administrator account with password 1234567890 by opening the following URL in your browser (specify the server's IP or domain name):

	[http://localhost/order/folder/admin/first-time-login-generation-init/](http://localhost/order/folder/admin/first-time-login-generation-init/)
	
	c) Generate default country and city lists by navigating to Employee Directory->Admin->List Manager and clicking "Populate Country and City Lists"
	
	d) Generate all other default parameters by navigating to Employee Directory->Admin->List Manager and clicking "Populate All Lists With Default Values"
	
	c) Generate default parameters for the "Glass Slide Scan Orders" site by navigating to Glass Slide Scan Orders->Scan Order List Manager->and clicking "Populate All Lists With Default Values".
	
	d) Run the deployment script again:

	 	deploy

9. For a live server, set the "Environment" variable's value to "live" in Admin->Site Settings->Platform Settings. For a development server set the "Environment" variable's value to "dev". For a test server set the "Environment" variable's value to "test".

10. Obtain and install these optional applications:

	* [wkhtmltopdf](http://wkhtmltopdf.org) for html to pdf conversion ( default path: C:\Program Files\wkhtmltopdf\ )
	* [LibreOffice](https://www.libreoffice.org/) for Word to PDF conversion (default path: C:\Program Files (x86)\LibreOffice 5\ )
	* [GhostScript](https://www.ghostscript.com/) for PDF decryption
	* [PDFtk Server](https://www.pdflabs.com/tools/pdftk-server/) for PDF merging
	* [PHPExcel](https://github.com/PHPOffice/PHPExcel) for operating with Excel files

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

1) cd to folder created by clone

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

### To update the whole tree, even from a subfolder (including removed of deleted files)

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