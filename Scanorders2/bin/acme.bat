@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/acmephp/acmephp/bin/acme
php "%BIN_TARGET%" %*
