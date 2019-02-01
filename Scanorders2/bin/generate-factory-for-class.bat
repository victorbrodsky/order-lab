@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/zendframework/zend-servicemanager/bin/generate-factory-for-class
php "%BIN_TARGET%" %*
