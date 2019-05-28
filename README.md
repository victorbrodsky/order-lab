# O R D E R

>**Important note:** This is an evolving software prototype that has been extensively tested and is now being used
> in a live environment. Nevertheless, if you choose to run this software in a live production environment yourself, be prepared 
> to continuously monitor for and deal with encountered issues.
> 
> Specifically, the security, scalability, cross-browser compatibility, user interface responsiveness, performance, data consistency 
> assurance, safe multi-user concurrency, proper versioning, and platform independence may need additional attention.
>


## About

O R D E R is a web-based software platform for development of clinical, administrative, research, and educational multi-user applications.

It includes several functional applications tied by LDAP/AD-capable single sign-on:

- Employee Directory

- Vacation Request Approval / Vacation Day Carryover / Away Calendar System

- Call Log Book

- Fellowship Application and Recommendation Letter Submission / Candidate Interview Evaluation System

- Glass Slide Scan Ordering System

- De-identifier / Honest Broker System for Accession Numbers

- Translational Research Project Approval, Work Order Processing, Invoicing, and Dashboards


## Data Models

The [core data models of the key objects are provided in UML and JPG formats](https://github.com/victorbrodsky/order-lab/tree/master/Scanorders2/uml), although additional attributes may have been added since their creation.


## Support

If you discover a specific issue, [post it here](https://github.com/victorbrodsky/order-lab/issues).


## Contributing documentation

If you would like to contribute documentation using the [Markdown format](http://daringfireball.net/projects/markdown/), please feel free to do so.

The source files are available at [github.com/victorbrodsky/order-lab](https://github.com/victorbrodsky/order-lab).


## Team

- Victor Brodsky ([@victorbrodsky](https://github.com/victorbrodsky))
- Oleg Ivanov ([@cinava](https://github.com/cinava))
- [Acknowledgments](https://github.com/victorbrodsky/order-lab/blob/master/AUTHORS)

## License

[Apache 2.0](https://www.apache.org/licenses/LICENSE-2.0)


## Installation instructions for deploying a Linux-based server on Linux

>**Warning:** This software was initially developed and tested in a Windows-based environment to accommodate existing servers. To ease further
> development and testing, the [Packer](https://www.packer.io/)-based deployment script for a [Digital Ocean](https://www.digitalocean.com/)
> virtual machine (VM) is provided. Additional testing is necessary to discover and address unresolved issues associated with
> cross-platform compatibility (and Linux specifically). The installation instructions assume the use of a Linux platform (such as 
> [Ubuntu](https://www.ubuntu.com/)). The specific commercial hosting provider was chosen as an example for convenience.
> 

1. Sign up for [Digital Ocean](https://www.digitalocean.com/) and obtain an [API access key token](https://www.digitalocean.com/help/api/). It should look similar to this one: e4561f1b44faa16c2b43e94c5685e5960e852326b921883765b3b0e11111f705

2. [Download](https://github.com/victorbrodsky/order-lab/archive/master.zip) and uncompress or [clone](https://help.github.com/articles/cloning-a-repository/) the source code from [github.com/victorbrodsky/order-lab](https://github.com/victorbrodsky/order-lab) to a folder of your choice by running the following commands in the Terminal / shell:

	 	sudo apt install -y git
        git clone https://github.com/victorbrodsky/order-lab.git

3. Install [Packer](https://www.packer.io/) and [doctl](https://github.com/digitalocean/doctl) by following the recommended installation instructions or by using snap:

        snap install packer
        snap install doctl

    Make sure to add both to your PATH. Alternatively, you can check for the latest versions of each [Packer](https://www.packer.io/downloads.html) and [doctl](https://github.com/digitalocean/doctl/releases), substitute the versions into the commands below instead of the now current 1.4.1 and 1.18.0 (as of 5/23/2019), and run them:

        wget -P ~/Downloads https://releases.hashicorp.com/packer/1.4.1/packer_1.4.1_linux_amd64.zip
        sudo mkdir /usr/local/packer
        sudo unzip ~/Downloads/packer_1.4.1_linux_amd64.zip -d /usr/local/packer
        wget -P ~/Downloads https://github.com/digitalocean/doctl/releases/download/v1.18.0/doctl-1.18.0-linux-amd64.tar.gz
        sudo mkdir /usr/local/doctl
        sudo tar xf ~/Downloads/doctl-1.18.0-linux-amd64.tar.gz -C /usr/local/doctl
        echo "export PATH=\"\$PATH:/usr/local/packer:/usr/local/doctl\"" >> ~/.bashrc
        source ~/.bashrc

4. Optionally edit order-lab/packer/parameters.yml in this project's folder to set desired values (especially for passwords). Make sure to [disable sleep mode in your local Linux operating system](https://askubuntu.com/questions/1014965/automatic-suspend-computer-will-suspend-very-soon-because-of-inactivity-ho/1014968#1014968) to allow the script in the next step to complete successfully (it takes a while to run as it communicates with the remote servers). If you used "git clone" command to download the source code, use the following command in the terminal:

		cd order-lab/packer
	
	to get into the /packer folder or if you downloaded the source code and unzipped the zip file, use:

		cd order-lab-master/packer

5. Decide whether you want to (a) use no domain name and no https/SSL, accessing the server via its IP (b) use your domain name and https/SSL certificate, or (c) use your domain name and no https/SSL certificate:

	(a) Run /packer/deploy-order-digital-ocean.sh via the following command (make sure to substitute your API token, database user name, and the database password. If "dbusername" and "dbpassword" are not provided, the default "symfony"/"symfony" values are used.):

        bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword
		
	(b) Obtain a domain name from a registrar (for example from [Google Domains](https://domains.google/#/)) if you don't have one already and follow these [instructions to add the nameservers of your Digital Ocean webhost](https://www.digitalocean.com/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars). It may take up to 48 hours for the domain name to start working. Get an SSL certificate from a Certificate Authority such as [Let's Encrypt](https://letsencrypt.org/) or [Comodo](https://comodosslstore.com/positivessl.aspx) using the [Certificate Signing Request (CSR)](https://developers.google.com/web/fundamentals/security/encrypt-in-transit/enable-https). You can also [generate a local certificate](https://letsencrypt.org/docs/certificates-for-localhost/) for testing purposes. Copy the SSL Certificate file (www.example.com.crt) and SSL Private Key file (www.example.com.key) to the /packer folder. The SSL Private Key file (www.example.com.key) is the one that was generated when preparing the Certificate Signing Request (CSR), likely via a command similar to this one:
	
		openssl genrsa -out www.example.com.key 2048
		
	which was then followed by:
	
		openssl req -new -sha256 -key www.example.com.key -out www.example.com.csr
	
	to generate the [Certificate Signing Request (CSR)](https://developers.google.com/web/fundamentals/security/encrypt-in-transit/enable-https)file, later used to obtain the SSL certificate file (www.example.com.crt) from your Certificate Authority. Once both the www.example.com.crt and the www.example.com.key files are in the /packer folder, run the following command in the terminal (make sure to substitute your API token (from step 1 above), database user name (such as "symfony"), the database password (such as "symfony"), your domain name (such as "example.com"), and the SSL certificate and private key file names below.):

        bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword https example.com www.example.com.crt www.example.com.key
		
	(c) Obtain a domain name from a registrar (for example from [Google Domains](https://domains.google/#/)) if you don't have one already and follow these [instructions to add the nameservers of your Digital Ocean webhost](https://www.digitalocean.com/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars). It may take up to 48 hours for the domain name to start working. Run the following command in the terminal (make sure to substitute your API token, database user name, the database password, and your domain name (such as "example.com") instead of "example.com" below. If "dbusername" and "dbpassword" are not provided, the default "symfony"/"symfony" values are used.):

        bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword http example.com

	Note: If you get your SSL certificate from [Let's Encrypt](https://letsencrypt.org/), make sure to follow their recommendation to ensure the certificate gets updated in a timely fashion and to avoid expiration. [certbot](https://certbot.eff.org/lets-encrypt/ubuntuxenial-apache) and a [symfony bundle](https://packagist.org/packages/cert/letsencrypt-bundle) are available.  In the past, uncommenting (removing "#" from the beginning of) the line 289 in /order-lab/Scanorders2/app/config/security.yml file was necessary to enable SSL, but everything should be done automatically now and is controlled via the "Connection Channel" variable in Site Settings being set to either “https” or "http".

6. If the browser window with this URL does not open automatically at the end of the previous step, visit http://IPADDRESS/order/directory/admin/first-time-login-generation-init/ (or either http://example.com/order/directory/admin/first-time-login-generation-init/ or https://example.com/order/directory/admin/first-time-login-generation-init/ depending on whether you used the domain name and the ssl certificate in the command above) to generate the initial Administrator account, where IPADDRESS is the IP address of the server. Wait until the site redirects to the log in screen (it might take a while.)

7. Log into the application with the user name "Administrator" and the password "1234567890" at http://IPADDRESS/order/directory/ (make sure to select "Local User" above the user name field first). You should see the http://IPADDRESS/order/directory/settings/initial-configuration page asking you to supply the initial variables for your instance. If you choose to use Gmail's SMTP server to enable the site to send email notifications, make sure to [enable 2-step-verification, generate an 'app password', and disable 2-step-verification](https://support.google.com/mail/answer/185833?hl=en). You can test your email settings later by visiting http://IPADDRESS/order/directory/send-a-test-email/ and sending a test email message. Upon submission of this initial configuration form, visit http://IPADDRESS/order/directory/admin/update-system-cache-assets/ to enable the site footer to reflect the values you supplied. Make sure to change the default password for the Administrator account either on this initial configuration page or by visiting the account's profile page http://IPADDRESS/order/directory/user/2 and clicking 'Edit', then set the server's "Environment" variable's value to "live", "dev" or "test" in Admin->Site Settings->Platform Settings http://IPADDRESS/order/directory/settings/.

8. Populate the database tables with default values by logging into the Employee Directory site as the Administrator, selecting "Admin" > 'Site Settings' in the top navigation bar, and arriving at (http://IPADDRESS/order/directory/settings/). Near the bottom of the page under the 'Miscellaneous' heading, click each link in the order listed, and confirm the action in each resulting window, then wait for each function to finish: 

    1) Populate Country and City Lists (http://IPADDRESS/order/directory/admin/populate-country-city-list-with-default-values)
    2) Populate All Lists With Default Values (Part A) (http://IPADDRESS/order/directory/admin/populate-all-lists-with-default-values)
    3) Populate All Lists With Default Values (Part B) (http://IPADDRESS/order/scan/admin/populate-all-lists-with-default-values)
    4c) Import Antibodies for the Postgres database (http://IPADDRESS/order/translational-research/generate-antibody-list/ihc_antibody_postgresql.sql)
    5) Pre-generate form node tree fields for Call Log Book (http://IPADDRESS/order/directory/admin/list/generate-form-node-tree/)
    6) Pre-generate empty custom lists (http://IPADDRESS/order/directory/admin/list/generate-empty-lists/)

9. Select the sites you would like to be accessible on the homepage besides the "Employee Directory" by visiting https://IPADDRESS/order/directory/admin/list-manager/id/2 , clicking the "Action" button to the right of the desired site name, and clicking "Edit", then putting a checkmark in the "Show Link On Home Page" and "Show Link in Navbar" fields, followed by clicking the "Update" button.

10. To enable submission of applications for the Fellowship application site via Google services, use the files in the /order-lab/Scanorders2/src/Oleg/FellAppBundle/Util/GoogleForm folder with the [Google Apps Script](https://developers.google.com/apps-script/). Make sure to add your Google Apps Script API key on the Site Settings page http://IPADDRESS/order/directory/settings/.

11. If bulk import of the initial set of users is desired, download the [ImportUsersTemplate.xlsx](https://github.com/victorbrodsky/order-lab/tree/master/importLists) file from the /importLists folder, fill it out with the user details, and upload it back via the the Navigation bar's "Admin > Import Users" (http://IPADDRESS/order/directory/import-users/spreadsheet) function on the Employee Directory site.

12. In order to later update to the latest version, connect to your server via:

        ssh root@YOUR-IP-OR-DOMAIN-NAME

    (you may be asked to change your password if connecting for the first time), then run:

        cd /usr/local/bin/order-lab
        git pull
        cd Scanorders2
        bash deploy_prod.sh

Note: If you choose to use MySQL database on Linux instead of Postgres, you will need to increase the size of the sort buffer by setting "sort_buffer_size" to 512K in /etc/mysql/my.cnf.

##  Installation instructions for deploying a Linux-based server on MacOS X

> MacOS X instructions were tested on version 10.12.3 'Sierra'.  

1. Sign up for [Digital Ocean](https://www.digitalocean.com/) and obtain an [API access key token](https://www.digitalocean.com/help/api/). It should look similar to this one: e4561f1b44faa16c2b43e94c5685e5960e852326b921883765b3b0e11111f705

2. Choose a folder that will be used for installation of ORDER. This folder will be referred to as '/ORDER_LOCATION/' in these instructions, which you will need to replace with the actual file path of the location you select.
	
	a) Download [order-lab source code](https://github.com/victorbrodsky/order-lab) by clicking the "Clone or Download" button, followed by "Download as Zip". Double click the "order-lab-master" zip file, extract the contents, then move the folder to '/ORDER_LOCATION/'. Alternatively, if it is not installed already, install [Git](https://git-scm.com/) by either installing [Xcode](https://itunes.apple.com/us/app/xcode/id497799835) or by entering 'git' in the Terminal application's window and following the instructions to install the "command line developer tools". After Git is installed, change the directory to your chosen '/ORDER_LOCATION/' and run the git clone command in the Terminal window:

		cd /ORDER_LOCATION/
		git clone https://github.com/victorbrodsky/order-lab.git

3. ['Homebrew'](https://brew.sh) can be used to install the necessary software: [Packer](https://www.packer.io/) and [doctl](https://github.com/digitalocean/doctl). This can be performed through the Terminal application. To install Homebrew, open Terminal and enter the following command followed by the return key. It will take several minutes to install.

		/usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"

4. Install Packer and Doctl with the following Homebrew commands in the Terminal, entered one at a time:

		brew install packer
		brew install doctl

5. Optionally edit /ORDER_LOCATION/order-lab-master/packer/parameters.yml (or /ORDER_LOCATION/order-lab/packer/parameters.yml if you used git clone command to download the source code) in this project's folder to set desired values (especially for passwords) using a text editor such as TextEdit and save.  Make sure to disable sleep mode in your local OSX operating system (using a tool like [Amphetamine](https://itunes.apple.com/us/app/amphetamine/id937984704?mt=12) or using your preferred method) to allow the script in the next step to complete successfully (it takes a while to run as it communicates with the remote servers). If you used "git clone" command to download the source code, use:

		cd order-lab/packer
	
	to get into the /packer folder or if you downloaded the source code and unzipped the zip file, use:

		cd order-lab-master/packer

6. Decide whether you want to (a) use no domain name and no https/SSL, accessing the server via its IP (b) use your domain name and https/SSL certificate, or (c) use your domain name and no https/SSL certificate:

	(a) Run /packer/deploy-order-digital-ocean.sh via the following command (make sure to substitute your API token, database user name, and the database password. If "dbusername" and "dbpassword" are not provided, the default "symfony"/"symfony" values are used.):

        bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword
		
	(b) Obtain a domain name from a registrar (for example from [Google Domains](https://domains.google/#/)) if you don't have one already and follow these [instructions to add the nameservers of your Digital Ocean webhost](https://www.digitalocean.com/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars). It may take up to 48 hours for the domain name to start working. Get an SSL certificate from a Certificate Authority such as [Let's Encrypt](https://letsencrypt.org/) or [Comodo](https://comodosslstore.com/positivessl.aspx) using the [Certificate Signing Request (CSR)](https://developers.google.com/web/fundamentals/security/encrypt-in-transit/enable-https). You can also [generate a local certificate](https://letsencrypt.org/docs/certificates-for-localhost/) for testing purposes. Copy the SSL Certificate file (www.example.com.crt) and SSL Private Key file (www.example.com.key) to the /packer folder. The SSL Private Key file (www.example.com.key) is the one that was generated when preparing the Certificate Signing Request (CSR), likely via a command similar to this one:
	
		openssl genrsa -out www.example.com.key 2048
		
	which was then followed by:
	
		openssl req -new -sha256 -key www.example.com.key -out www.example.com.csr
	
	to generate the [Certificate Signing Request (CSR)](https://developers.google.com/web/fundamentals/security/encrypt-in-transit/enable-https)file, later used to obtain the SSL certificate file (www.example.com.crt) from your Certificate Authority. Once both the www.example.com.crt and the www.example.com.key files are in the /packer folder, run the following command in the terminal (make sure to substitute your API token (from step 1 above), database user name (such as "symfony"), the database password (such as "symfony"), your domain name (such as "example.com"), and the SSL certificate and private key file names below.):

        bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword https example.com www.example.com.crt www.example.com.key
		
	(c) Obtain a domain name from a registrar (for example from [Google Domains](https://domains.google/#/)) if you don't have one already and follow these [instructions to add the nameservers of your Digital Ocean webhost](https://www.digitalocean.com/community/tutorials/how-to-point-to-digitalocean-nameservers-from-common-domain-registrars). It may take up to 48 hours for the domain name to start working. Run the following command in the terminal (make sure to substitute your API token, database user name, the database password, and your domain name (such as "example.com") instead of "example.com" below. If "dbusername" and "dbpassword" are not provided, the default "symfony"/"symfony" values are used.):

        bash deploy-order-digital-ocean.sh API-TOKEN-FROM-STEP-1 parameters.yml dbusername dbpassword http example.com

	Note: If you get your SSL certificate from [Let's Encrypt](https://letsencrypt.org/), make sure to follow their recommendation to ensure the certificate gets updated in a timely fashion and to avoid expiration. [certbot](https://certbot.eff.org/lets-encrypt/ubuntuxenial-apache) and a [symfony bundle](https://packagist.org/packages/cert/letsencrypt-bundle) are available.  In the past, uncommenting (removing "#" from the beginning of) the line 289 in /order-lab/Scanorders2/app/config/security.yml file was necessary to enable SSL, but everything should be done automatically now and is controlled via the "Connection Channel" variable in Site Settings being set to either “https” or "http".

7. If the browser window with this URL does not open automatically at the end of the previous step, visit http://IPADDRESS/order/directory/admin/first-time-login-generation-init/ (or either http://example.com/order/directory/admin/first-time-login-generation-init/ or https://example.com/order/directory/admin/first-time-login-generation-init/ depending whether you used the domain name and the ssl certificate in the command above) to generate the initial Administrator account, where IPADDRESS is the IP address of the server. Wait until the site redirects to the log in screen (it might take a while.)

8. Select "Local User" above the user name field and log into the application with the user name "Administrator" and the password "1234567890" at http://IPADDRESS/order/directory/ (or http://example.com/order/directory/ or https://example.com/order/directory/ depending on whether you used your domain name and SSL certificates during set up). You should see the http://IPADDRESS/order/directory/settings/initial-configuration page asking you to supply the initial variables for your instance. If you choose to use Gmail's SMTP server to enable the site to send email notifications, make sure to [enable 2-step-verification, generate an 'app password', and disable 2-step-verification](https://support.google.com/mail/answer/185833?hl=en). You can test your email settings later by visiting http://IPADDRESS/order/directory/send-a-test-email/ and sending a test email message. Upon submission of this initial configuration form, visit http://IPADDRESS/order/directory/admin/update-system-cache-assets/ to enable the site footer to reflect the values you supplied. Make sure to change the default password for the Administrator account either on this initial configuration page or by visiting the account's profile page http://IPADDRESS/order/directory/user/2 and clicking 'Edit', then set the server's "Environment" variable's value to "live", "dev" or "test" in Admin->Site Settings->Platform Settings http://IPADDRESS/order/directory/settings/.

9. Populate the database tables with default values by logging into the Employee Directory site as the Administrator, selecting "Admin" > 'Site Settings' in the top navigation bar, and arriving at (http://IPADDRESS/order/directory/settings/). Near the bottom of the page under the 'Miscellaneous' heading, click each link in the order listed, and confirm the action in each resulting window, then wait for each function to finish: 

    1) Populate Country and City Lists (http://IPADDRESS/order/directory/admin/populate-country-city-list-with-default-values)
    2) Populate All Lists With Default Values (Part A) (http://IPADDRESS/order/directory/admin/populate-all-lists-with-default-values)
    3) Populate All Lists With Default Values (Part B) (http://IPADDRESS/order/scan/admin/populate-all-lists-with-default-values)
    4c) Import Antibodies for the Postgres database (http://IPADDRESS/order/translational-research/generate-antibody-list/ihc_antibody_postgresql.sql)
    5) Pre-generate form node tree fields for Call Log Book (http://IPADDRESS/order/directory/admin/list/generate-form-node-tree/)
    6) Pre-generate empty custom lists (http://IPADDRESS/order/directory/admin/list/generate-empty-lists/)

10. Select the sites you would like to be accessible on the homepage besides the "Employee Directory" by visiting https://IPADDRESS/order/directory/admin/list-manager/id/2 , clicking the "Action" button to the right of the desired site name, and clicking "Edit", then putting a checkmark in the "Show Link On Home Page" and "Show Link in Navbar" fields, followed by clicking the "Update" button.

11. To enable submission of applications for the Fellowship application site via Google services, use the files in the /order-lab/Scanorders2/src/Oleg/FellAppBundle/Util/GoogleForm folder with the [Google Apps Script](https://developers.google.com/apps-script/). Make sure to add your Google Apps Script API key on the Site Settings page http://IPADDRESS/order/directory/settings/.

12. If bulk import of the initial set of users is desired, download the [ImportUsersTemplate.xlsx](https://github.com/victorbrodsky/order-lab/tree/master/importLists) file from the /importLists folder, fill it out with the user details, and upload it back via the the Navigation bar's "Admin > Import Users" (http://IPADDRESS/order/directory/import-users/spreadsheet) function on the Employee Directory site.

13. In order to later update to the latest version, connect to your server via:

        ssh root@YOUR-IP-OR-DOMAIN-NAME

    (you may be asked to change your password if connecting for the first time), then run:

        cd /usr/local/bin/order-lab
        git pull
        cd Scanorders2
        bash deploy_prod.sh

Note: If you choose to use MySQL database on Linux instead of Postgres, you will need to increase the size of the sort buffer by setting "sort_buffer_size" to 512K in /etc/mysql/my.cnf.

## Installation Instructions for deploying a Windows-based server directly

> Tested on Windows 10 and Windows 7

1. Choose a folder on the server that will be used for installation of ORDER. This folder will be referred to as "C:\ORDER_LOCATION\" in these instructions, which you will need to replace with the actual file path of the location you select. Several files will need to be downloaded and installed.
	
	a) Download the [installer for GIT version control](https://git-scm.com/download/win/) appropriate for your version of Windows. Run the installer with the standard installation options. (Tested with version 2.12.0.)

	b) Install all of the Microsoft Visual C++ Redistributable Packages relevant to your Windows version.

	For 64 bit Windows (x64) install both these 64 bit versions and the 32 bit versions below:

    * [VC9 2008 SP1 Package (x64)](https://www.microsoft.com/en-us/download/details.aspx?id=2092)
    * [VC 2008 SP1 ATL Package (x64)](https://www.microsoft.com/en-us/download/details.aspx?id=11895)
    * [VC 2008 SP1 MFC Package (x64)](https://www.microsoft.com/en-us/download/details.aspx?id=26368)
    * [VC10 2010 SP1 Package (x64)](http://www.microsoft.com/en-us/download/details.aspx?id=13523)
    * [VC11 2012 Update 4 (x64)](http://www.microsoft.com/en-us/download/details.aspx?id=30679)
    * [VC13 2013 Update 5 (x64)](https://support.microsoft.com/en-us/help/4032938/)
    * [VC16 2015-2019 (VC16 x64) 14.20.27508](https://aka.ms/vs/16/release/VC_redist.x64.exe)

	For 32 bit Windows (x86):

    * [VC9 2008 SP1 Package (x86)](http://www.microsoft.com/en-us/download/details.aspx?id=5582)
    * [VC 2008 SP1 ATL Package (x86)](https://www.microsoft.com/en-us/download/details.aspx?id=11895)
    * [VC 2008 SP1 MFC Package (x86)](https://www.microsoft.com/en-us/download/details.aspx?id=26368)
    * [VC10 2010 SP1 Package (x86)](http://www.microsoft.com/en-us/download/details.aspx?id=8328)
    * [VC11 2012 Update 4 (x86)](http://www.microsoft.com/en-us/download/details.aspx?id=30679)
    * [VC13 2013 Update 5 (x86)](https://support.microsoft.com/en-us/help/4032938/)
    * [VC16 2015-2019 (VC16 x86) 14.20.27508](https://aka.ms/vs/16/release/VC_redist.x86.exe)

	c) Install an Apache-PHP-MySQL stack of your choice (for example: [AMPPS](http://www.ampps.com/downloads), [WAMP](http://www.wampserver.com/en/), or [XAMPP](https://www.apachefriends.org/index.html). PHP version 5.6.7 was initially tested and is preferred, but PHP version 7.1 appears to work as well and avoids the "PHP Fatal error: Allowed memory size of X bytes exhausted..." after running "composer update" in the later installation steps. MSSQL Server has been tested as well. The following instructions will assume AMPPS with PHP 5.6.7 were chosen.

	d) Download the [installer for AMPPS](http://www.ampps.com/downloads) Apache-PHP-MySQL stack appropriate for your version of Windows. Run the installer with the standard installation options. After installation, open AMPPS, and when prompted to install "C++ Redistributable Visual Studio", select "Yes". (Tested with version 3.6.)

	e) Download [order-lab source code](https://github.com/victorbrodsky/order-lab) by clicking the "Clone or Download" button, followed by "Download as Zip". Right click the "order-lab-master" zip file and select "Extract All...". Select the folder you have chosen for installation ("C:\ORDER_LOCATION\") as the destination for the extracted files. Alternatively, change the directory to your chosen 'ORDER_LOCATION' and run the git clone command in the Command Prompt window (although note that using git to clone the repository will place the code in a folder titled "order-lab" as opposed to un-zipping the downloaded zip file which places it into "order-lab-master" folder by default, so make sure to use the correct path to your source code copy in the later installation steps):

		cd C:\ORDER_LOCATION
		git clone https://github.com/victorbrodsky/order-lab.git

2. Configure AMPPS. At this point, AMPPS should be open, and Apache web server, PHP, and MySQL should display as "Running". If Apache or MySQL is not running, turn it on by clicking the switch to the left of its name in the AMPPS tray menu.

	a) Select PHP version 5.6.7. In AMPPS, select Options (an icon that appears as a grid of squares near the top of the AMPPS window) and select "Change PHP Version". Select "PHP 5.6.7". After restarting, Apache, PHP, and MySQL should return to the "Running" state. You can also select PHP version 7.1 to avoid the "PHP Fatal error: Allowed memory size of X bytes exhausted..." after running "composer update" in the later installation steps.

		
	b) Configure the php.ini file. Click on the gear icon the right of "Php-!" in the AMPPS application window. Click the wrench icon ("Configuration") to the right of the gear icon. The php.ini file will open in a text editor. Find the line matching ";date.timezone = " and replace it with date.timezone = "UTC". Other [timezone values are available here](http://php.net/manual/en/timezones.php) but "UTC" should work.

	c) Enable the ldap extensions, international support, the GD library extension, and the file info extension by removing the semicolon at the beginning of the following lines in php.ini (if a semicolon is present), saving the php.ini file, and restarting the Apache web server: 

		;extension=php_ldap.dll
		;extension=php_intl.dll
		;extension=php_gd2.dll
		;extension=php_fileinfo.dll

	d) Temporarily modify the value of the memory_limit in php.ini by editing the existing line or add the following line to the php.ini to enable the later "composer update" command in step 4c to complete successfully. You can comment this line out later by specifying "memory_limit = 2048M" and restarting the Apache web server.

		memory_limit = -1

	e) If an option other than AMPPS is being used, the following configuration step may be necessary. If AMPPS is being used, skip this step.

	The pdo extension may need to be enabled. For PHP version 5.6 "php_pdo_sqlsrv_56_ts.dll" and "php_sqlsrv_56_ts.dll" files should be placed in PHP/ext folder, and the following lines added to php.ini:

			extension=php_sqlsrv_56_ts.dll
			extension=php_pdo_sqlsrv_56_ts.dll

	For the older PHP version 5.4 "php_pdo_sqlsrv_54_ts.dll" and "php_sqlsrv_54_ts.dll" should be placed in PHP/ext folder, and the following lines added to php.ini:

 			extension=php_sqlsrv_54_ts.dll
			extension=php_pdo_sqlsrv_54_ts.dll

	If you are using MSSQL Server, you will need to download and install the [Microsoft ODBC Driver 11 for SQL Server](https://www.microsoft.com/en-us/download/details.aspx?id=36434) for PHP to access the database:

			For 64 bit (x64) Operating Systems use the x64\msodbcsql.msi installation file
			For 32 bit (x86) Operating Systems use the x86\msodbcsql.msi installation file

	You may need to download and enable OPcache if you are not using AMPPS (in which it is already enabled by default). Note: this step is required if OPcache is not running. PHP configuration can be verified on the http://IPADDRESS/order/order/info.php page after step 3.

	For PHP version 5.4 [download OPcache](http://windows.php.net/downloads/pecl/releases/opcache/7.0.3/php_opcache-7.0.3-5.4-ts-vc9-x86.zip) and [enable it](http://stackoverflow.com/questions/24155516/how-to-install-zend-opcache-extension-php-5-4-on-windows); for PHP version 5.6 just [enable OPCache](http://php.net/manual/en/opcache.setup.php) by adding this line to php.ini:

			zend_extension="PATH-TO-WEB-SERVER\WebServer\PHP\Ext\php_opcache.dll"

	f) Make sure that Apache can find and load icu*.dll files from the PHP base folder. One possible solution is to copy these files to Apache's "bin" folder:

			 * icudt49.dll
			 * icuin49.dll
			 * icuio49.dll
			 * icule49.dll
			 * iculx49.dll
			 * icutu49.dll
			 * icuuc49.dll

	g) Set up an alias on the server directing Apache to the order-lab-master files. In the AMPPS tray menu, click the globe icon at the top of the window to open [http://localhost/AMPPS/](http://localhost/AMPPS/) in the web browser (recent versions of Firefox or Chrome are preferred). From this [AMPPS Home page](http://localhost/AMPPS/), various server settings can be configured. Open "Alias Manager", click "Add New", enter "order" under "Alias Name" and "C:/ORDER_LOCATION/order-lab-master/Scanorders2/web" (or "C:/ORDER_LOCATION/order-lab/Scanorders2/web" if you cloned using git) under "Path" (using backslashes in the file path, rather than forward slashes). Click "Create Alias". Now create a second alias with the name "ORDER" and the same path.

	If you are not using AMPPS, different Apache-PHP-MySQL stacks may require modifying the httpd.conf file directly by setting the alias to the order-lab www folder as follows:

	If you unzipped the downloaded source code:

			<VirtualHost *:80>
				<Directory 'C:\ORDER_LOCATION\order-lab-master\Scanorders2\web\"
					Options +FollowSymLinks -Includes
					AllowOverride All  
					Require all granted
				</Directory>
				Alias /order "C:\ORDER_LOCATION\order-lab-master\Scanorders2\web\"
				Alias /ORDER "C:\ORDER_LOCATION\order-lab-master\Scanorders2\web\"
				RewriteRule ^/ORDER(.*)$ /order$1 [R=301]
				ErrorLog ${APACHE_LOG_DIR}/error.log
				CustomLog ${APACHE_LOG_DIR}/access.log combined
			</VirtualHost>
		
	Or if you cloned the source code using the git clone command:

			<VirtualHost *:80>
				<Directory 'C:\ORDER_LOCATION\order-lab\Scanorders2\web\"
					Options +FollowSymLinks -Includes
					AllowOverride All  
					Require all granted
				</Directory>
				Alias /order "C:\ORDER_LOCATION\order-lab\Scanorders2\web\"
				Alias /ORDER "C:\ORDER_LOCATION\order-lab\Scanorders2\web\"
				RewriteRule ^/ORDER(.*)$ /order$1 [R=301]
				ErrorLog ${APACHE_LOG_DIR}/error.log
				CustomLog ${APACHE_LOG_DIR}/access.log combined
			</VirtualHost>
		
		
	h) Restart the apache server and make sure Apache and PHP are running.	

	i) Create the database and the database user. On the [AMPPS Home page](http://localhost/AMPPS/), open "Add Database". Enter the name "ScanOrder" for the database. Click "Create". Open the "Databases" tab. Click "Check Privileges" to the right of the newly created "ScanOrder" database. Click "Add user". Enter "symfony2" as the "User Name". Change the "Host" to "Local". Enter "symfony2" (for example) as the password, and confirm the password. Ensure the option "Grant all privileges on database "Scanorder"" is checked. Check the box for "Global Privileges" "Check All". Click "Go" at the bottom of the page to create the database user.
	
3. Download [Composer](https://getcomposer.org/download/) and [install](https://getcomposer.org/doc/00-intro.md). Run the installer (tested with version 1.4.1). When prompted to "Choose the command-line PHP you want to use", browse to the file location "C:\Program Files (x86)\AMPPS\php\php.exe". For other options, choose the default configuration.

4. Update and configure Symfony:

	a) AMPPS uses MySQL and not MSSQL, so this parameter must be changed: Open the file "C:\ORDER_LOCATION\order-lab-master\Scanorders2\app\config\parameters.yml" (or "C:\ORDER_LOCATION\order-lab\Scanorders2\app\config\parameters.yml" if you used the git clone command) in a text editor. Replace the line "database_driver: pdo_sqlsrv" (useful for MSSQL Server) with "database_driver: pdo_mysql" (to use MySQL server we are using for these instructions). Save and close the file.

	b) Open a Windows Command Prompt. Change the directory to the Scanorders2 folder by entering the command:

		cd C:\ORDER_LOCATION\order-lab-master\Scanorders2\
	
	or, if you used the git clone command, run:

		cd C:\ORDER_LOCATION\order-lab\Scanorders2\

	c) Update symfony vendors by entering the command:

		composer self-update

	When it is complete, enter the command:

		composer update

	The update will take several minutes. If you see the "PHP Fatal error: Allowed memory size of X bytes exhausted..." error, you may need to temporarily add the "memory_limit = -1" line to php.ini, restart Apache web server (or switch to PHP version 7.1) and try running "composer update" again. It should run until it reaches the point where it will prompt for missing parameters by displaying:

		database_driver (pdo_sqlsrv): Some parameters are missing. Please provide them.

	When prompted for the following values, type in the value to the right of the semicolon below and press the return key. When prompted for other values not listed below, leave them blank and press the return key. These values are located in Symfony's parameters file (app/config/parameters.yml) and set the defined Database configuration values (make sure to enter the "database_password: " that matches the one you chose in step 2i above):

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
		
	Note for Google Email Server: 
		Use Google API password as follow:
		a) Enable 2-step verification
		b) Generate Google App specific password
		c) Disable 2-step verification
		
	
	
5. Deployment
	
	a) Run the deployment script: Open a Windows File Explorer window and navigate to "C:\ORDER_LOCATION\order-lab-master\Scanorders2\" (or to "C:\ORDER_LOCATION\order-lab\Scanorders2\" if you used the git clone command). Right click on an empty space in the window, and select "Git Bash here". In the Git Bash window that opens, enter the command "bash ./deploy_prod.sh". The script will take several minutes to run. "Deploy complete." will appear when it is finished.
		
	b) Create the Administrator account with password 1234567890 by opening the following URL in your browser (specify the server's IP or domain name instead of "localhost"):

	[http://localhost/order/directory/admin/first-time-login-generation-init/](http://localhost/order/directory/admin/first-time-login-generation-init/)  Wait until the site re-directs to the log in screen (it might take a while.)

	c) Log into the application with the user name "Administrator" and the password "1234567890" at http://IPADDRESS/order/directory/ (make sure to select "Local User" above the user name field first). You should see the http://IPADDRESS/order/directory/settings/initial-configuration page asking you to supply the initial variables for your instance. If you choose to use Gmail's SMTP server to enable the site to send email notifications, make sure to [enable 2-step-verification, generate an 'app password', and disable 2-step-verification](https://support.google.com/mail/answer/185833?hl=en). You can test your email settings later by visiting http://IPADDRESS/order/directory/send-a-test-email/ and sending a test email message. Upon submission of this initial configuration form, visit http://IPADDRESS/order/directory/admin/update-system-cache-assets/ to enable the site footer to reflect the values you supplied. Make sure to change the default password for the Administrator account either on this initial configuration page or by visiting the account's profile page http://IPADDRESS/order/directory/user/2 and clicking 'Edit', then set the server's "Environment" variable's value to "live", "dev" or "test" in Admin->Site Settings->Platform Settings http://IPADDRESS/order/directory/settings/.
	
	d) Populate the database tables with default values by logging into the Employee Directory site as the Administrator, selecting "Admin" > 'Site Settings' in the top navigation bar, and arriving at (http://IPADDRESS/order/directory/settings/). Near the bottom of the page under 'Miscellaneous' heading, click each link in the order listed, and confirm the action in each resulting window, then wait for each function to finish: 

        1) Populate Country and City Lists (http://IPADDRESS/order/directory/admin/populate-country-city-list-with-default-values)
        2) Populate All Lists With Default Values (Part A) (http://IPADDRESS/order/directory/admin/populate-all-lists-with-default-values)
        3) Populate All Lists With Default Values (Part B) (http://IPADDRESS/order/scan/admin/populate-all-lists-with-default-values)
        4a) Import Antibodies for the MySQL database (http://IPADDRESS/order/translational-research/generate-antibody-list/ihc_antibody_mysql.sql)
        5) Pre-generate form node tree fields for Call Log Book (http://IPADDRESS/order/directory/admin/list/generate-form-node-tree/)
        6) Pre-generate empty custom lists (http://IPADDRESS/order/directory/admin/list/generate-empty-lists/)
	
	e) Run the deployment script again by following step 5a above:

	 	bash deploy_prod.sh

6. Obtain and install these optional applications to enable associated functionality on the server (then ensure the path for each is correctly set on this page http://IPADDRESS/order/directory/settings/):

    * [wkhtmltopdf](http://wkhtmltopdf.org) for html to pdf conversion (default path on Windows: C:\Program Files\wkhtmltopdf\ )
    * [LibreOffice](https://www.libreoffice.org/) for Word to PDF conversion (default path on Windows: C:\Program Files (x86)\LibreOffice 5\ )
    * [GhostScript](https://www.ghostscript.com/) for PDF decryption
    * [PDFtk Server](https://www.pdflabs.com/tools/pdftk-server/) for PDF merging

7. To enable HTTPS (SSL/TLS), first either purchase the certificate from your preferred vendor and add it to the server, or install either the [ACMESharp](https://github.com/ebekker/ACMESharp), [Certes](https://github.com/fszlin/certes), or [WinACME](https://github.com/PKISharp/win-acme) with a [Let's Encrypt](https://letsencrypt.org/) certificate (you can also use a [symfony bundle](https://packagist.org/packages/cert/letsencrypt-bundle)). For certificates from Let's Encrypt, verify that the scheduled task to automatically update them is set up since they expire in 90 days. Once that is done, follow these steps:

        A) Copy the obtained certificate file (named your-certificate.cer) to path yourpath/conf/ssl.crt/
        B) Copy the obtained private key file (named your-key.key) to path yourpath/conf/ssl.key/
        C) In the config file httpd.conf enable “virtual host” by adding the following lines
			LoadModule ssl_module modules/mod_ssl.so
			<VirtualHost *:443>
				DocumentRoot "yourpath/htdocs/"
				ServerName yourservername:443
				SSLEngine on

				SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL
				SSLCertificateFile "yourpath/conf/ssl.crt/your-certificate.cer"
				SSLCertificateKeyFile "yourpath/conf/ssl.key/your-key.key"
			</VirtualHost>   
		D) Restart the web server (Apache)
        E) In Site Settings change the variable named "Connection Channel" to “https”
        F) Run deploy_prod.sh

8. Select the sites you would like to be accessible on the homepage besides the "Employee Directory" by visiting https://IPADDRESS/order/directory/admin/list-manager/id/2 , clicking the "Action" button to the right of the desired site name, and clicking "Edit", then putting a checkmark in the "Show Link On Home Page" and "Show Link in Navbar" fields, followed by clicking the "Update" button.

9. To enable submission of applications for the Fellowship application site via Google services, use the files in the /order-lab/Scanorders2/src/Oleg/FellAppBundle/Util/GoogleForm folder with the [Google Apps Script](https://developers.google.com/apps-script/). Make sure to add your Google Apps Script API key on the Site Settings page http://IPADDRESS/order/directory/settings/.

10. If bulk import of the initial set of users is desired, download the [ImportUsersTemplate.xlsx](https://github.com/victorbrodsky/order-lab/tree/master/importLists) file from the /importLists folder, fill it out with the user details, and upload it back via the the Navigation bar's "Admin > Import Users" (http://IPADDRESS/order/directory/import-users/spreadsheet) function on the Employee Directory site.

11. In order to later update to the latest version, connect to your server via:

        ssh root@YOUR-IP-OR-DOMAIN-NAME

    (you may be asked to change your password if connecting for the first time), then:

        cd C:\ORDER_LOCATION\order-lab-master\
        git pull
        cd Scanorders2
        bash deploy_prod.sh (via details in step 5 (a) above)

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

### To install dependencies via composer

	Update composer:
	composer.phar self-update

	On dev server:
	composer.phar update
	 
	On live server:
    composer.phar install

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

2. The resulting log and screen shots are in order/test folder)