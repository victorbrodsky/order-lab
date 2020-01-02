Visual Studio 2012 has been used to compile LdapSaslCustom.exe

LdapSaslCustom.exe hostname ldapport username password

1) Add Wldap32.lib to your project:

Put a reference to the *.lib in the project. 
Right click the project name in the Solution Explorer and then select 
Configuration Properties->Linker->Input 
and put the name of the lib in the Additional Dependencies property.

2) To make exe independent:
Check in the properties -> C/C++ -> Code Generation -> Runtime Library 
and make sure it is set to an option that does not say "debug" or "DLL" in it:
http://i.imgur.com/5EJy3hj.png

3) Copy LdapSaslCustom.exe from Release folder to UserdirectoryBundle\Util folder