#!/usr/bin/env bash

#Run second only if first succeeds
bash alma10_install.sh symfony symfony | tee 1_install.log && \
bash install-multitenancy.sh -u symfony -t symfony -m haproxy -p /srv -s none -d none -e none -l none | tee 2_multitenancy.log
