Digital Ocean installation via packer:

1) create new folder "Test"
2) git clone https://github.com/victorbrodsky/order-lab.git
3) cd order-lab/packer/
4) bash deploy-order-digital-ocean.sh --token xxx -os centos --protocol https

Example for a simple use for centos:
bash deploy-order-digital-ocean.sh --token mydigitaloceantoken--os centos

Scripts used in different options: 
centos (default)	 	order-packer-centos.json => centos_install.sh
centosonly  			order-packer-centos-only.json
centos-without-composer 	order-packer-centos-without-composer.json => centos_install.sh
ubuntu				order-packer-ubuntu.json


