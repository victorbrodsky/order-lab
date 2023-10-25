#!/bin/bash

apitoken=$1
echo apitoken=$apitoken

echo "*** Doctl must be installed! https://www.digitalocean.com/docs/apis-clis/doctl/how-to/install/ ***"
echo "" | doctl auth init --access-token $apitoken #echo "" simulate enter pressed
LASTLINE=$(doctl compute droplet list --format="Public IPv4" | tail -1)
lastlinevars=( $LASTLINE )
DROPLETIP=${lastlinevars[0]}
echo "droplet IP=[$DROPLETIP]"
