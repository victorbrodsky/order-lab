Digital Ocean installation via packer:

1) create new folder "Test"
2) git clone https://github.com/victorbrodsky/order-lab.git
3) cd order-lab/packer/
4) bash deploy-order-digital-ocean.sh --token xxx -os centos7 --protocol https

Example for a simple use for centos:
bash deploy-order-digital-ocean.sh --token mydigitaloceantoken--os centos7

Scripts used in different options: 
centos (default)	 	order-packer-centos7.json => centos7_install.sh
centosonly  			order-packer-centos-only.json
centos-without-composer 	order-packer-centos-without-composer.json => centos_install.sh
ubuntu				order-packer-ubuntu.json


##############################
Packer: > v1.7.0

Installed the latest packer version 1.9.1:
$ scoop install packer

and digitalocean plugin: 
$ packer init order-packer-alma9.json.pkr.hcl


Then I create a droplet as usual:
1) create new folder "Test" and in this folder run 2, 3, 4 below
2) $ git clone https://github.com/victorbrodsky/order-lab.git
3) $ cd order-lab/packer/
4) $ bash deploy-order-digital-ocean.sh --token mytoken --os alma9
 
To install AlmaLinux 9: bash deploy-order-digital-ocean.sh --token mytoken --os alma9
To install AlmaLinux 8: bash deploy-order-digital-ocean.sh --token mytoken --os alma8
To install Centos: bash deploy-order-digital-ocean.sh --token mytoken --os centos7
##############################