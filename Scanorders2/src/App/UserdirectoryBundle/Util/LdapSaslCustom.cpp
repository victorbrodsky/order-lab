/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

// LdapSaslCustom.cpp : Defines the entry point for the console application.
//
//  Add Wldap32.lib to your project.

#include "stdafx.h"

#include "windows.h"
#include "winldap.h"
#include "stdio.h"

#include <sstream>
#include <string>
#include <iostream>

using namespace std;

const size_t newsize = 100;
char* getErrorName(ULONG returnCode);

//  Entry point for application
int main(int argc, char* argv[]) {

    PWCHAR hostName = NULL;
	PWCHAR dn = NULL;
	char* domain = "";	//use empty domain, because nyh users don't have it
	char* userName = NULL;
	char* pwd = NULL;
    LDAP* pLdapConnection = NULL;
	ULONG ldap_port = LDAP_PORT;	//LDAP_PORT is the default port, 389.
    ULONG version = LDAP_VERSION3;	//ldap vesrion
	ULONG numReturns = 1;			//number of return query results
    ULONG getOptSuccess = 0;
    ULONG connectSuccess = 0;
    INT returnCode = -1;
	char* returnCodeString;
	SEC_WINNT_AUTH_IDENTITY auth;

    //  Verify that the user passed a hostname.
    if (argc > 1)
    {
        //  Convert argv[] to a wchar_t*       
		size_t origsize = strlen(argv[1]) + 1;
        size_t convertedChars = 0;
        wchar_t wcstring[newsize];
        mbstowcs_s(&convertedChars, wcstring, origsize, argv[1], _TRUNCATE);
        wcscat_s(wcstring, L" (wchar_t *)");
        hostName = wcstring;
		
		ldap_port = strtoul(argv[2],NULL,0);
		userName = argv[3];
		pwd = argv[4];
    }
    else
    {
        hostName = NULL;
    }

	// check hostName
	if( hostName == NULL ) {
		cout << "hostName is not provided" << endl;
		goto end_exit;
	}

	//cout << "host= " << hostName << ", ldap_port=" << ldap_port << ", userName=" << userName << endl;

    //  Initialize a session. 
    pLdapConnection = ldap_init(
		hostName, 
		ldap_port
	);

	

    if( pLdapConnection == NULL ) {
        //  Set the HRESULT based on the Windows error code.
        char hr = HRESULT_FROM_WIN32(GetLastError());
        printf( "ldap_init failed with 0x%x.\n",hr);
        goto end_exit;
    }
    else {
        //printf("ldap_init succeeded \n");
	}

    //  Set the version to 3.0 (default is 2.0).
    returnCode = ldap_set_option(pLdapConnection, LDAP_OPT_PROTOCOL_VERSION, (void*)&version);
    if( returnCode == LDAP_SUCCESS ) {
        //printf("ldap_set_option succeeded - version set to 3\n");
	} else {
        printf("SetOption Error:%0X\n", returnCode);
        goto end_exit;
    }

	// Set the limit on the number of entries returned to 1.
	returnCode = ldap_set_option(pLdapConnection, LDAP_OPT_SIZELIMIT, (void*) &numReturns);
    if( returnCode == LDAP_SUCCESS ) {
        //printf("ldap_set_option succeeded - limit set to 1\n");
	} else {
        printf("SetOption Error:%0X\n", returnCode);
        goto end_exit;
    }

    // Connect to the server.
    connectSuccess = ldap_connect(pLdapConnection, NULL);

    if(connectSuccess == LDAP_SUCCESS) {
        //printf("ldap_connect succeeded \n");
	} else {
        printf("ldap_connect failed with 0x%x.\n",connectSuccess);
        goto end_exit;
    }

	// check userName and pwd
	if( userName == "" || userName == NULL ) {
		cout << "userName is not provided" << endl;
		goto end_exit;
	}

	if( pwd == "" || pwd == NULL ) {
		cout << "password is not provided" << endl;
		goto end_exit;
	}
    
	////////////////////////// ldap //////////////////////////
	//printf("Binding ...\n");	
	//cout << "userName=" << userName << endl;
	
	//auth.Domain = (unsigned short *)domain;
	auth.Domain         = reinterpret_cast<unsigned short*>( domain );
	auth.DomainLength   = strlen( domain );
	auth.User           = reinterpret_cast<unsigned short*>( userName );
	auth.UserLength     = strlen( userName );
	auth.Password       = reinterpret_cast<unsigned short*>( pwd );
	auth.PasswordLength = strlen( pwd );
	auth.Flags          = SEC_WINNT_AUTH_IDENTITY_ANSI;
		 
	returnCode = ldap_bind_s(
		pLdapConnection, 
		dn,						//dn
		(PWCHAR) &auth,			//cred		
		LDAP_AUTH_NEGOTIATE		//LDAP_AUTH_SIMPLE - work as simple
	);
	//cout << "returnCode=" << returnCode << endl;

	returnCodeString = getErrorName(returnCode);
	cout << returnCodeString << endl;

	if( returnCode == LDAP_SUCCESS )
    {
        //printf("ldap_bind_s succeeded \n");
        auth.Password = NULL;	// Remove password pointer
        pwd = NULL;				// Remove password pointer
    }
    else
    {
        printf("ldap_bind_s failed with 0x%lx.\n",returnCode);
        ldap_unbind(pLdapConnection);
        return -1;
    }

	//On error cleanup and exit.
	end_exit:

	ldap_unbind(pLdapConnection);

	if( returnCode == LDAP_SUCCESS ) {
		return 1;
	} else {		
		return -1;
	}

	////////////////////////// EOF ldap //////////////////////////
}


char* getErrorName( ULONG code ) {
	char* res;

	switch( code ) {
		case LDAP_SUCCESS:
			res = "LDAP_SUCCESS";
			break;
		case LDAP_INVALID_CREDENTIALS:
			res = "LDAP_INVALID_CREDENTIALS";
			break;
		case LDAP_INAPPROPRIATE_AUTH:
			res = "LDAP_INAPPROPRIATE_AUTH";
			break;
		case LDAP_AUTH_METHOD_NOT_SUPPORTED:
			res = "LDAP_INAPPROPRIATE_AUTH";
			break;
		case LDAP_PARAM_ERROR:
			res = "LDAP_PARAM_ERROR";
			break;
		default:
			res = "Uknown Error";
	}

	return res;
}

