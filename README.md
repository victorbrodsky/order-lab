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

7. Use the address http://IPADDRESS/order/directory/admin/first-time-login-generation-init/ to generate the initial Administrator login, where DOMAIN is either the IP address of the server, or the domain. Then, log into the server's web page at with the user name "Administrator" and the password "1234567890”. To log in in the future, simply use the regular login page at http://IPADDRESS/order/.

8. To populate the default values for various tables, first use the “Admin” dropdown menu after login in as an administrator. Select the “List Manager”. Near the bottom of the page under “Populate Lists”, click “Populate Country and City Lists”, and confirm. When complete, click “Populate All Lists With Default Values”.  

9. For a live server, set the "Environment" variable's value to "live" in Admin->Site Settings->Platform Settings. For a development server set the "Environment" variable's value to "dev". For a test server set the "Environment" variable's value to "test".

## Installation instructions for MacOS X

>MacOS X instructions are similar to the Linux instructions, as both systems are Unix based. MacOS X instructions are tested on version 10.12.3 “Sierra”.  

1. Sign up for [Digital Ocean](https://www.digitalocean.com/) and obtain an [API access key token](https://www.digitalocean.com/help/api/). It should look similar to this one: e4561f1b44faa16c2b43e94c5685e5960e852326b921883765b3b0e11111f705

2. Choose a folder that will be used for installation of ORDER. This folder will be referred to as “/ORDER_LOCATION/“ in these instructions, which you will need to replace with the actual file path of the location you select.
	
	a) Download [order-lab source code](https://github.com/victorbrodsky/order-lab) by clicking the "Clone or Download" button, followed by "Download as Zip". Move the double click the "order-lab-master" zip file extract the contents, then move the folder to “/ORDER_LOCATION/“.

3. [“Homebrew”](https://brew.sh) can be used to install the necessary software, [Packer](https://www.packer.io/) and [doctl](https://github.com/digitalocean/doctl). This can be performed through the application Terminal.

	a) Install Homebrew: Open Terminal and enter the following command followed by the return key. It will take several minutes to install.

		/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)”

	b) Install Packer and Doctl with the following commands in Terminal, entered one at a time.

		brew install packer
		brew install doctl

4. Edit parameters.yml in this project's folder to set desired values (especially for passwords)

	a) Edit /ORDER_LOCATION/order-lab-master/packer/parameters.yml in a text editor such as TextEdit and save.

5. Deploy ORDER:

	a) Change the working directory in Terminal by entering the following command:

		cd /ORDER_LOCATION/order-lab-master/packer/

	b) Run the deployment script with the following command, replacing “API-TOKEN-FROM-STEP-1” with your unique API token. The script will take several minutes to run.

		bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml


7. Use the address http://IPADDRESS/order/directory/admin/first-time-login-generation-init/ to generate the initial Administrator login, where DOMAIN is either the IP address of the server (provided in the last lines of the script that was run in terminal, in the format “droplet IP=123.45.678.90.”), or the domain. Then, log into the server's web page at with the user name "Administrator" and the password "1234567890”. To log in in the future, simply use the regular login page at http://IPADDRESS/order/.

8. To populate the default values for various tables, first use the “Admin” dropdown menu after login in as an administrator. Select the “List Manager”. Near the bottom of the page under “Populate Lists”, click “Populate Country and City Lists”, and confirm. When complete, click “Populate All Lists With Default Values”.  

9. For a live server, set the "Environment" variable's value to "live" in Admin->Site Settings->Platform Settings. For a development server set the "Environment" variable's value to "dev". For a test server set the "Environment" variable's value to "test".


## Installation Instructions for Windows 
(Tested on Windows 10 and Windows 7)

1. Choose a folder that will be used for installation of ORDER. This folder will be referred to as "C:\ORDER_LOCATION\" in these instructions, which you will need to replace with the actual file path of the location you select. Several files will need to be downloaded and installed.
	
	a) Download the [installer for GIT version control](https://git-scm.com/download/win/) appropriate for your version of Windows. Run the installer with the standard installation options. (Tested with version 2.12.0.)

	b) Install an Apache-PHP-MySQL stack of your choice (preferably with PHP version 5.6 such as AMPPS, WAMP, or XAMPP.  MS SQL Server has been tested as well). The following instructions will assume AMPPS has been chosen.

	c) Download the [installer for AMPPS](http://www.ampps.com/downloads) Apache-PHP-MySQL stack appropriate for your version of Windows. Run the installer with the standard installation options. After installation, open AMPPS, and when prompted to install "C++ Redistributable Visual Studio", select "Yes". (Tested with version 3.6.)

	d) Download [order-lab source code](https://github.com/victorbrodsky/order-lab) by clicking the "Clone or Download" button, followed by "Download as Zip". Right click the "order-lab-master" zip file and select "Extract AllÖ". Select the folder you have chosen for installation ("C:\ORDER_LOCATION\") as the destination for the extracted files.

2. Configure AMPPS. At this point, AMPPS should be open, and Apache web server, Php, and MySQL should display as "Running". If Apache or MySQL is not running, turn it on by clicking the switch to the left of itís name in AMPPS.

	a) Select PHP version 5.6. In AMPPS, select Options (an icon that appears as a grid of squares near the top of the AMPPS window) and select "Change PHP Version". Select "PHP 5.6". After restarting, Apache, Php, and MySQL should return to "Running".

		
	b) Configure the php.ini file. Click on the gear icon the right of "Php-!" in the AMPPS application window. Click the wrench icon ("Configuration") to the right of the gear icon. The php.ini file will open in a text editor. 
		Find the line matching ";date.timezone = " and replace it with "date.timezone = ‘TIMEZONE’”, where TIMEZONE is replaced with one of the timezone options available [here](http://php.net/manual/en/timezones.php), such as “date.timezone = ‘America/New_York’”.

		Enable the ldap extensions, international support, the GD library extension, and the file info extension by removing the semicolon at the beginning of the following lines (if a semicolon is present): 
		;extension=php_ldap.dll
		;extension=php_ldap.dll
		;extension=php_gd2.dll
		;extension=php_fileinfo.dll

	c) If an option other than AMPPS is being used, the following configurations may be necessary. If AMPPS is being used, skip this step.

		The pdo extension may need to be enabled. For PHP version 5.6 "php_pdo_sqlsrv_56_ts.dll and php_sqlsrv_56_ts.dll" should be placed in PHP/ext folder, and the following lines added to php.ini:
			extension=php_sqlsrv_56_ts.dll
			extension=php_pdo_sqlsrv_56_ts.dll

		For the older PHP version 5.4 "php_pdo_sqlsrv_54_ts.dll and php_sqlsrv_54_ts.dll" should be placed in PHP/ext folder, and the following line added to php.ini:
 			extension=php_sqlsrv_54_ts.dll
			extension=php_pdo_sqlsrv_54_ts.dll

		If you are using MSSQL Server, you will need to download the Microsoft ODBC Driver 11 for PHP for SQL Server
			For 64 bit (x64) Operating Systems use the x64\msodbcsql.msi installation file
			For 32 bit (x86) Operating Systems use the x86\msodbcsql.msi installation file

	d) You may need to download and enable OPCache if you are not using AMPPS (in which it is enabled by default). Note: this step is required if OPCache is not running. Php configuration can be verified on the /order/info.php page after step 3.


		a) For PHP version 5.4 download OPCache and enable it; for PHP version 5.6 just enable OPCache.

			zend_extension="PATH-TO-WEB-SERVER\WebServer\PHP\Ext\php_opcache.dll"


		b) Make sure that Apache can find and load icu*.dll files from the PHP base folder. One possible solution is to copy these files to Apache's "bin" folder:
			 * icudt49.dll
			 * icuin49.dll
			 * icuio49.dll
			 * icule49.dll
			 * iculx49.dll
			 * icutu49.dll
			 * icuuc49.dll

	g) Set up an alias in the server directing to the order-lab-master files. In AMPPS, click the globe icon at the top of the window to open "http://localhost/AMPPS/" in a web browser (recent versions of Firefox or Chrome are preferred). From this [AMPPS Home page](http://localhost/AMPPS/), various server settings can be configured. Open "Alias Manager". "Click Add New". Enter "order" under "Alias Name", and "C:/ORDER_LOCATION/order-lab-master/Scanorder2/web" under "Path" (using backslashes in the file path, rather than forward slashes). Click "Create Alias". Now create a second alias with the name "ORDER" and the same path.

		If you are not using AMPPS, different Apache-PHP-MySQL stacks may require modifying the httpd.conf file. If so, set alias to the order-lab www folder:

			<VirtualHost *:80>
				<Directory “C:\ORDER_LOCATION\Scanorders2\web\"
					Options +FollowSymLinks -Includes
					AllowOverride All  
					Require all granted
				</Directory>
				Alias /order "C:\ORDER_LOCATION\Scanorders2\web\"
				Alias /ORDER "C:\ORDER_LOCATION\Scanorders2\web\"
				RewriteRule ^/ORDER(.*)$ /order$1 [R=301]
				ErrorLog ${APACHE_LOG_DIR}/error.log
				CustomLog ${APACHE_LOG_DIR}/access.log combined
			</VirtualHost>
		
		
	h) Restart the apache server and make sure Apache and php are running.	

	i) Create a database and user. On the [AMPPS Home page](http://localhost/AMPPS/), open "Add Database". Enter the name "ScanOrder" for the database. Click "Create". Open the "Databases" tab. Click "Check Privileges" to the right of the newly created "ScanOrder" database. Click "Add user". Enter "symfony2" as the "User Name". Change the "Host" to "Local". Enter "symfony2" as the password, and confirm the password. Ensure the option "Grant all privileges on database "Scanorder"" is checked. Check the box for "Global Privileges" "Check Alll". Click "Go" at the bottom of the page to create the user.
	
3. Download [Composer](https://getcomposer.org/download/) and [install](https://getcomposer.org/doc/00-intro.md). Run the installer (tested with version 1.4.1). When prompted to "Choose the command-line PHP you want to use", browse to the file location "C:\Program Files (x86)\AMPPS\php\php.exe". For other options, choose the default configuration.

4. Update and configure Symfony.

	a) AMPPS uses MySQL and not MSSQL, so this parameter must be changed: Open the file "C:\ORDER_LOCATION\order-lab-master\Scanorders2\app\config\parameters.yml" in a text editor. Replace the line "database_driver: pdo_sqlsrv" with "database_driver: pdo_mysql". Save and close the file.

	b) Open a Windows Command Prompt. Change the directory to the Scanorders2 folder by entering the command "cd C:\ORDER_LOCATION\order-lab-master\Scanorders2\".

	c) Update symfony vendors by entering the command "composer self-update". When it is complete, enter the command "composer update". The update will take several minutes. It will run until it reached the point where it will prompt for missing parameters by displaying: "database_driver (pdo_sqlsrv): Some parameters are missing. Please provide them.". 

	d) When prompted for the following values, return the value to the right and enter return. When prompted for other values, leave blank and enter return. These values are located in Symfony’s parameters file (app/config/parameters.yml) and set the defined Database configuration values.

		database_host: 127.0.0.1
		database_port: null
		database_name: ScanOrder
		database_user: symfony2
		database_password: symfony2
		mailer_transport: smtp
		mailer_host: 127.0.0.1
		mailer_user: null
		mailer_password: null
		locale: en   
		delivery_strategy: realtime
	
5. Deployment
	
	a) Run the deployment script. Open a Windows File Explorer window and navigate to "C:\ORDER_LOCATION\order-lab-master\Scanorders2\". Right click in an empty space in the window, and select "Git Bash here". In the Git Bash window that opens, enter the command "./deploy_prod.sh". The script will take several minutes to run. "Deploy complete." will appear when finished.
		
	b) Create the Administrator account with password 1234567890 by opening the following URL in your browser (specify the server's IP or domain name):

	[http://localhost/order/directory/admin/first-time-login-generation-init/](http://localhost/order/directory/admin/first-time-login-generation-init/)
	
	c) Generate default country and city lists by navigating to Employee Directory->Admin->List Manager and clicking "Populate Country and City Lists"
	
	d) Generate all other default parameters by navigating to Employee Directory->Admin->List Manager and clicking "Populate All Lists With Default Values"
	
	e) Generate default parameters for the "Glass Slide Scan Orders" site by navigating to Glass Slide Scan Orders->Scan Order List Manager->and clicking "Populate All Lists With Default Values".
	
	f) Run the deployment script again:

	 	deploy_prod.sh

6. Use the address http://localhost/order/directory/admin/first-time-login-generation-init/ to generate the initial Administrator login. Then, log into the server's web page at with the user name "Administrator" and the password "1234567890”. To log in in the future, simply use the regular login page at http://localhost/order/.

7. To populate the default values for various tables, first use the “Admin” dropdown menu after login in as an administrator. Select the “List Manager”. Near the bottom of the page under “Populate Lists”, click “Populate Country and City Lists”, and confirm. When complete, click “Populate All Lists With Default Values”.  

8. For a live server, set the "Environment" variable's value to "live" in Admin->Site Settings->Platform Settings. For a development server set the "Environment" variable's value to "dev". For a test server set the "Environment" variable's value to "test".

9. Obtain and install these optional applications:

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

	 deploy_prod.sh

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