${SegmentFile}

!include WinMessages.nsh

${SegmentInit}
	;Check for improper install/upgrade without running the PA.c Installer which can cause issues
	ReadINIStr $0 "$EXEDIR\App\AppInfo\appinfo.ini" "Installer" "Run"
	${If} $0 == "false"
		MessageBox MB_OK|MB_ICONEXCLAMATION `Warning: LibreOffice Portable was installed or upgraded without using the official PortableApps.com Installer which can cause compatibility issues and may be a violation of the application's license. You may encounter issues while using this application. Please visit PortableApps.com to obtain the official release of this application to install or upgrade.`
		DeleteINISec "$EXEDIR\App\AppInfo\appinfo.ini" "Installer"
		
	;Ensure we have a proper Documents path
	ExpandEnvStrings $1 "%PortableApps.comDocuments%"
	${If} $1 == ""
	${OrIfNot} ${FileExists} "$1\*.*"
		${GetParent} $EXEDIR $3
		${GetParent} $3 $1
		${If} $1 == "" ;Be sure we didn't just GetParent on Root
			StrCpy $1 $3
		${EndIf}
		${If} ${FileExists} "$1\Documents\*.*"
			StrCpy $2 "$1\Documents"
		${Else}
			${GetRoot} $EXEDIR $1
			${If} ${FileExists} "$1\Documents\*.*"
				StrCpy $2 "$1\Documents"
			${Else}
				StrCpy $2 "$1"
			${EndIf}
		${EndIf}
		System::Call 'Kernel32::SetEnvironmentVariable(t, t) i("PortableApps.comDocuments", "$2").r0'
	${EndIf}
	${EndIf}
!macroend

${SegmentPrePrimary}
	;Load app ttf fonts
	FindFirst $0 $1 "$EXEDIR\App\fonts\*.ttf"
	${DoWhile} $1 != ""
		System::Call "gdi32::AddFontResource(t'$EXEDIR\App\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Load app otf fonts
	FindFirst $0 $1 "$EXEDIR\App\fonts\*.otf"
	${DoWhile} $1 != ""
		System::Call "gdi32::AddFontResource(t'$EXEDIR\App\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Load user ttf fonts
	FindFirst $0 $1 "$EXEDIR\Data\fonts\*.ttf"
	${DoWhile} $1 != ""
		System::Call "gdi32::AddFontResource(t'$EXEDIR\Data\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Load user otf fonts
	FindFirst $0 $1 "$EXEDIR\Data\fonts\*.otf"
	${DoWhile} $1 != ""
		System::Call "gdi32::AddFontResource(t'$EXEDIR\Data\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Let all running apps know
	SendMessage ${HWND_BROADCAST} ${WM_FONTCHANGE} 0 0 /TIMEOUT=1
!macroend

${SegmentPostPrimary}
	;Remove app ttf fonts
	FindFirst $0 $1 "$EXEDIR\App\fonts\*.ttf"
	${DoWhile} $1 != ""
		System::Call "gdi32::RemoveFontResource(t'$EXEDIR\App\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Remove app otf fonts
	FindFirst $0 $1 "$EXEDIR\App\fonts\*.otf"
	${DoWhile} $1 != ""
		System::Call "gdi32::RemoveFontResource(t'$EXEDIR\App\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Remove user ttf fonts
	FindFirst $0 $1 "$EXEDIR\Data\fonts\*.ttf"
	${DoWhile} $1 != ""
		System::Call "gdi32::RemoveFontResource(t'$EXEDIR\Data\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Remove user otf fonts
	FindFirst $0 $1 "$EXEDIR\Data\fonts\*.otf"
	${DoWhile} $1 != ""
		System::Call "gdi32::RemoveFontResource(t'$EXEDIR\Data\fonts\$1')i .r2"
		FindNext $0 $1
	${Loop}
	FindClose $0
	
	;Let all running apps know
	SendMessage ${HWND_BROADCAST} ${WM_FONTCHANGE} 0 0 /TIMEOUT=1
!macroend