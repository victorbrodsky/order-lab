#!/bin/bash

COLOR='\033[1;36m'
NC='\033[0m' # No Color

apitoken=$1
echo apitoken=$apitoken

snapshot_name=$2
echo snapshot_name=$snapshot_name

echo "*** Doctl must be installed! https://www.digitalocean.com/docs/apis-clis/doctl/how-to/install/ ***"
echo "" | doctl auth init --access-token $apitoken #echo "" simulate enter pressed
LASTLINE=$(doctl compute droplet list --format="Public IPv4" | tail -1)
lastlinevars=( $LASTLINE )
DROPLETIP=${lastlinevars[0]}
echo "droplet IP=[$DROPLETIP]"

LASTLINE=$(doctl compute snapshot list | grep "$snapshot_name")
echo "LASTLINE=$LASTLINE"

LASTLINEINFO=( $LASTLINE )

echo -e ${COLOR} *** Getting the first IMAGEID and the second IMAGENAME elements from LASTLINE *** ${NC}
IMAGEID="${LASTLINEINFO[0]}"
IMAGENAME="${LASTLINEINFO[1]}"
#echo -e ${COLOR} IMAGEID="$IMAGEID", IMAGENAME="$IMAGENAME" ${NC}
echo IMAGEID="$IMAGEID", IMAGENAME="$IMAGENAME"