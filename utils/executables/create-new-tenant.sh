#!/bin/sh

POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"
case $key in
	-t|--tenant)
		tenant="$2"
		shift # past argument
		shift # past value
    ;;
    *)    # unknown option
		POSITIONAL+=("$1") # save it in an array for later
		shift # past argument
    ;;
esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters

#tenant ID
echo tenant=$tenant


source ../../packer/install-multitenancy.sh

#f_create_single_order_instance tenantapp3 8086 c/wcm/informatics
#f_create_single_tenant_htppd tenantapp3 8086 c/wcm/informatics
#f_start_single_httpd tenantapp3 8086 c/wcm/informatics


