<?php
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

namespace App\UserdirectoryBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

//When adding new fields:
// 1) temporarily comment out "- { resource: "setparameters.php" }" in services.yaml
// 2) delete cache manually
// 3) process steps listed in the header of the PostgresqlMigration.php
// 4) enable back - { resource: "setparameters.php" } in services.yaml
#[ORM\Table(name: 'user_siteparameters')]
#[ORM\Entity]
class SiteParameters {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * Max idle time in minutes
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $maxIdleTime;

    #[ORM\Column(type: 'text', nullable: true)]
    private $environment;

    #[ORM\Column(type: 'text', nullable: true)]
    private $version;

    #[ORM\Column(type: 'text', nullable: true)]
    private $siteEmail;

    #[ORM\Column(type: 'string', nullable: true)]
    private $dbServerAddress;

    #[ORM\Column(type: 'string', nullable: true)]
    private $dbServerPort;

    #[ORM\Column(type: 'string', nullable: true)]
    private $dbServerAccountUserName;

    #[ORM\Column(type: 'string', nullable: true)]
    private $dbServerAccountPassword;

    #[ORM\Column(type: 'string', nullable: true)]
    private $dbDatabaseName;

    //////// email (default gmail free SMTP Server Example) //////////
    /**
     * mailerHost: smtp.gmail.com
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $smtpServerAddress;

    /**
     * smtp or gmail (google gmail requires only gmail username and password)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $mailerTransport;

    /**
     * oauth
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $mailerAuthMode;

    /**
     * tls or ssl
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $mailerUseSecureConnection;

    /**
     * GMail account (email@gmail.com)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $mailerUser;

    /**
     * GMail password
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $mailerPassword;

    /**
     * 465 or 587
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $mailerPort;

    /**
     * use spooled email
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $mailerSpool;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $mailerFlushQueueFrequency;

    /**
     * emails will deliver only to these emails
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $mailerDeliveryAddresses;

    //mailer_transport: smtp
    //mailer_user: null
    //mailer_password: null
    //transport: smtp
    //host:      smtp.gmail.com
    //username:     #email@gmail.com
    //password:            #gmail_password
    //    #auth_mode: oauth
    //port:      587
    //encryption: tls
    //////// EOF email (default gmail free SMTP Server Example) //////////
    /**
     * Send request to both authentication Active Directory/LDAP servers when the first is selected for a single log in attempt
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $ldapAll;

    /////////////// LDAP Server 1 ////////////////////
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerAddress;

    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerPort;

    /**
     * LDAP bind used for ldap_search or for simple authentication ldap_bind
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerOu;

    /**
     * Used for ldap_search, if null, the ldap_search is not used
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerAccountUserName;

    /**
     * Used for ldap_search, if null, the ldap_search is not used
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerAccountPassword;

    #[ORM\Column(type: 'string', nullable: true)]
    private $ldapExePath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $ldapExeFilename;

    /**
     * Default Primary Public User ID Type
     */
    #[ORM\OneToOne(targetEntity: 'App\UserdirectoryBundle\Entity\UsernameType')]
    private $defaultPrimaryPublicUserIdType;

    #[ORM\Column(type: 'text', nullable: true)]
    private $ldapMapperEmail;

    #[ORM\OneToOne(targetEntity: 'App\UserdirectoryBundle\Entity\UsernameType')]
    private $ldapMapperPrimaryPublicUserIdType;
    /////////////// EOF LDAP Server 1 ////////////////////
    /////////////// LDAP Server 2 ////////////////////
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerAddress2;

    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerPort2;

    /**
     * LDAP bind used for ldap_search or for simple authentication ldap_bind
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerOu2;

    /**
     * Used for ldap_search, if null, the ldap_search is not used
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerAccountUserName2;

    /**
     * Used for ldap_search, if null, the ldap_search is not used
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $aDLDAPServerAccountPassword2;

    #[ORM\Column(type: 'string', nullable: true)]
    private $ldapExePath2;

    #[ORM\Column(type: 'string', nullable: true)]
    private $ldapExeFilename2;

    #[ORM\Column(type: 'text', nullable: true)]
    private $ldapMapperEmail2;

    #[ORM\OneToOne(targetEntity: 'App\UserdirectoryBundle\Entity\UsernameType')]
    private $ldapMapperPrimaryPublicUserIdType2;

//    /**
    //     * Default Primary Public User ID Type
    //     *
    //     * @ORM\OneToOne(targetEntity="App\UserdirectoryBundle\Entity\UsernameType")
    //     */
    //    private $defaultPrimaryPublicUserIdType2;
    /////////////// EOF LDAP Server 2 ////////////////////
    /**
     * Enable auto-assignment of Institutional Scope
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enableAutoAssignmentInstitutionalScope;

    #[ORM\OneToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $autoAssignInstitution;

    #[ORM\Column(type: 'text', nullable: true)]
    private $pacsvendorSlideManagerDBServerAddress;

    #[ORM\Column(type: 'text', nullable: true)]
    private $pacsvendorSlideManagerDBServerPort;

    #[ORM\Column(type: 'text', nullable: true)]
    private $pacsvendorSlideManagerDBUserName;

    #[ORM\Column(type: 'text', nullable: true)]
    private $pacsvendorSlideManagerDBPassword;

    #[ORM\Column(type: 'text', nullable: true)]
    private $pacsvendorSlideManagerDBName;


    //Footer
    #[ORM\Column(type: 'text', nullable: true)]
    private $institutionurl;

    #[ORM\Column(type: 'text', nullable: true)]
    private $institutionname;

    #[ORM\Column(type: 'text', nullable: true)]
    private $departmenturl;

    #[ORM\Column(type: 'text', nullable: true)]
    private $departmentname;

    #[ORM\Column(type: 'text', nullable: true)]
    private $subinstitutionurl;

    #[ORM\Column(type: 'text', nullable: true)]
    private $subinstitutionname;

    /**
     * Show copyright line on every footer
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $showCopyrightOnFooter;

    //Maintanence mode
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $maintenance;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $maintenanceenddate;

    #[ORM\Column(type: 'text', nullable: true)]
    private $maintenancelogoutmsg;

    #[ORM\Column(type: 'text', nullable: true)]
    private $maintenanceloginmsg;

    //Global note from site settings on all login page
    #[ORM\Column(type: 'text', nullable: true)]
    private $globalNoteLogin;

    //uploads path
    #[ORM\Column(type: 'string', nullable: true)]
    private $scanuploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $employeesuploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $avataruploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $fellappuploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $resappuploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $vacrequploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $transresuploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $callloguploadpath;

    #[ORM\Column(type: 'string', nullable: true)]
    private $crnuploadpath;

    //site titles and messages
    #[ORM\Column(type: 'text', nullable: true)]
    private $mainHomeTitle;

    #[ORM\Column(type: 'text', nullable: true)]
    private $listManagerTitle;

    #[ORM\Column(type: 'text', nullable: true)]
    private $eventLogTitle;

    #[ORM\Column(type: 'text', nullable: true)]
    private $siteSettingsTitle;

    #[ORM\Column(type: 'text', nullable: true)]
    private $contentAboutPage;

    #[ORM\Column(type: 'text', nullable: true)]
    private $underLoginMsgUser;

    #[ORM\Column(type: 'text', nullable: true)]
    private $underLoginMsgScan;

    ///////////////////// FELLAPP /////////////////////
    //    /**
    //     * @ORM\OneToOne(targetEntity="App\FellAppBundle\Entity\FellAppSiteParameter", cascade={"persist","remove"})
    //     */
    //    private $fellappSiteParameter;
    //    /**
    //     * @ORM\OneToOne(targetEntity="App\FellAppBundle\Entity\FellAppSiteParameter", cascade={"persist","remove"})
    //     */
    //    private $fellappSiteParameter;
    #[ORM\OneToOne(targetEntity: 'App\FellAppBundle\Entity\FellappSiteParameter', cascade: ['persist', 'remove'])]
    private $fellappSiteParameter;

    #[ORM\OneToOne(targetEntity: 'App\ResAppBundle\Entity\ResappSiteParameter', cascade: ['persist', 'remove'])]
    private $resappSiteParameter;

//    /**
    //     * Path to the local copy of the fellowship application form
    //     * https://script.google.com/a/macros/pathologysystems.org/d/14jgVkEBCAFrwuW5Zqiq8jsw37rc4JieHkKrkYz1jyBp_DFFyTjRGKgHj/edit
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $codeGoogleFormFellApp;
    //    /**
    //     * @ORM\Column(type="boolean", nullable=true)
    //     */
    //    private $allowPopulateFellApp;
    //    /**
    //     * Automatically send invitation emails to upload recommendation letters
    //     *
    //     * @ORM\Column(type="boolean", nullable=true)
    //     */
    //    private $sendEmailUploadLetterFellApp;
    //    /**
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $confirmationSubjectFellApp;
    //    /**
    //     * Recommendation Letter Salt to generate Recommendation Letter Salted Scrypt Hash ID
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $recLetterSaltFellApp;
    //    /**
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $confirmationBodyFellApp;
    //    /**
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $confirmationEmailFellApp;
    //    /**
    //     * Client Email to get GoogleService: i.e. '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com'
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $clientEmailFellApp;
    //    /**
    //     * Path to p12 key file: i.e. /../Util/FellowshipApplication-f1d9f98353e5.p12
    //     * E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\src\App\FellAppBundle\Util\FellowshipApplication-f1d9f98353e5.p12
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $p12KeyPathFellApp;
    //    /**
    //     * https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $googleDriveApiUrlFellApp;
    //    /**
    //     * Impersonate user Email: i.e. olegivanov@pathologysystems.org
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $userImpersonateEmailFellApp;
    //    /**
    //     * Deprecated. Not used anymore. Replaced by felBackupTemplateFileId in GoogleFormConfig
    //     * Template Google Spreadsheet ID (1ITacytsUV2yChbfOSVjuBoW4aObSr_xBfpt6m_vab48)
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $templateIdFellApp;
    //    /**
    //     * Deprecated. Not used anymore. Replaced by felBackupTemplateFileId in GoogleFormConfig
    //     * Backup Google Spreadsheet ID (19KlO1oCC88M436JzCa89xGO08MJ1txQNgLeJI0BpNGo)
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $backupFileIdFellApp;
    //    /**
    //     * Deprecated. Not used anymore. Replaced by felSpreadsheetFolderId in GoogleFormConfig
    //     * Application Google Drive Folder ID (0B2FwyaXvFk1efmc2VGVHUm5yYjJRWGFYYTF0Z2N6am9iUFVzcTc1OXdoWEl1Vmc0LWdZc0E)
    //     * where the response spreadsheets (response forms) are saved
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $folderIdFellApp;
    //    /**
    //     * Deprecated, not used anymore in new version of google management
    //     * Config.json file folder ID
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $configFileFolderIdFellApp;
    /**
     * NOT USED
     * Backup Sheet Last Modified Date
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $backupUpdateDatetimeFellApp;

//    /**
    //     * TODO: Move to fellapp site settings
    //     * Local Institution to which every imported application is set: Pathology Fellowship Programs (WCMC)
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $localInstitutionFellApp;
    //    /**
    //     * Modify the filename format generated by the Google recommendation letter upload form to include the “institution name” that is supplied in the URL
    //     * Institution for which recommendation letters will be downloaded (fellowship identification string).
    //     * Will be used to filter and only download files that have the matching institution string in the file name.
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $identificationUploadLetterFellApp;
    //    /**
    //     * [ checkbox ] Delete successfully imported applications from Google Drive
    //     *
    //     * @ORM\Column(type="boolean", nullable=true)
    //     */
    //    private $deleteImportedAplicationsFellApp;
    //    /**
    //     * checkbox for "Automatically delete downloaded applications that are older than [X] year(s)
    //     * (set it at 2) [this is to delete old excel sheets that are downloaded from google drive.
    //     * Make sure it is functional and Google/Excel sheets containing applications older than
    //     * the amount of years set by this option is auto-deleted along with the linked downloaded documents.
    //     *
    //     * @ORM\Column(type="boolean", nullable=true)
    //     */
    //    private $deleteOldAplicationsFellApp;
    //    /**
    //     * Used in checkbox for "Automatically delete downloaded applications that are older than [X] year(s)
    //     *
    //     * @ORM\Column(type="integer", nullable=true)
    //     */
    //    private $yearsOldAplicationsFellApp;
    //    /**
    //     * Path to spreadsheets: i.e. Spreadsheets
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $spreadsheetsPathFellApp;
    //
    //    /**
    //     * Path to upload applicants documents: i.e. FellowshipApplicantUploads
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $applicantsUploadPathFellApp;
    //
    //
    //    /**
    //     * Path to upload applicants documents used in ReportGenerator: i.e. Reports
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $reportsUploadPathFellApp;
    //    /**
    //     * Link to the Application Page (so the users can click and see how it looks)
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $applicationPageLinkFellApp;
    ////////////////////// third party software //////////////////////////
    /**
     * C:\Program Files (x86)\LibreOffice 5\program
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $libreOfficeConvertToPDFPathFellApp;
    /**
     * path\LibreOffice 5\program
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $libreOfficeConvertToPDFPathFellAppLinux;

    /**
     * soffice
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $libreOfficeConvertToPDFFilenameFellApp;
    /**
     * soffice
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $libreOfficeConvertToPDFFilenameFellAppLinux;

    /**
     * --headless -convert-to pdf -outdir
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $libreOfficeConvertToPDFArgumentsdFellApp;
    /**
     * --headless -convert-to pdf -outdir
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $libreOfficeConvertToPDFArgumentsdFellAppLinux;

    /**
     * C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $pdftkPathFellApp;
    /**
     * path\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $pdftkPathFellAppLinux;

    /**
     * pdftk
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $pdftkFilenameFellApp;
    /**
     * pdftk
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $pdftkFilenameFellAppLinux;

    /**
     * ###inputFiles### cat output ###outputFile### dont_ask
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $pdftkArgumentsFellApp;
    /**
     * ###inputFiles### cat output ###outputFile### dont_ask
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $pdftkArgumentsFellAppLinux;

    /**
     * Ghostscript
     * C:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $gsPathFellApp;
    /**
     * Ghostscript
     * path\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $gsPathFellAppLinux;

    /**
     * Ghostscript
     * gswin64c.exe
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $gsFilenameFellApp;
    /**
     * Ghostscript
     * gswin64c.exe
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $gsFilenameFellAppLinux;

    /**
     * Ghostscript
     * -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile= ###outputFile###  -c .setpdfwrite -f ###inputFiles###
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $gsArgumentsFellApp;
    /**
     * Ghostscript
     * -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile= ###outputFile###  -c .setpdfwrite -f ###inputFiles###
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $gsArgumentsFellAppLinux;

    /**
     * C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $wkhtmltopdfpath;
    /**
     * path to wkhtmltopdf binary
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $wkhtmltopdfpathLinux;

    /**
     * C:\Program Files\wkhtmltopdf\bin\phantomjs.exe
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $phantomjs;

    #[ORM\Column(type: 'text', nullable: true)]
    private $phantomjsLinux;

    /**
     * C:\Program Files\wkhtmltopdf\examples\rasterize.js
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $rasterize;

    #[ORM\Column(type: 'text', nullable: true)]
    private $rasterizeLinux;
    ////////////////////// EOF third party software //////////////////////////
    ///////////////////// EOF FELLAPP /////////////////////
    // Co-Path //
    //Production
    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBServerAddress;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBServerPort;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBAccountUserName;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBAccountPassword;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBName;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisName;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisVersion;


    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBServerAddressTest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBServerPortTest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBAccountUserNameTest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBAccountPasswordTest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBNameTest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $LISNameTest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $LISVersionTest;


    //Development
    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBServerAddressDevelopment;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBServerPortDevelopment;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBAccountUserNameDevelopment;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBAccountPasswordDevelopment;

    #[ORM\Column(type: 'text', nullable: true)]
    private $lisDBNameDevelopment;

    #[ORM\Column(type: 'text', nullable: true)]
    private $LISNameDevelopment;

    #[ORM\Column(type: 'text', nullable: true)]
    private $LISVersionDevelopment;

    #[ORM\Column(type: 'date', nullable: true)]
    private $academicYearStart;

    #[ORM\Column(type: 'date', nullable: true)]
    private $academicYearEnd;

//    /**
    //     * Not Used: Moved to VacReqSiteParameter
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $holidaysUrl;
    //    /**
    //     * Not Used: Moved to VacReqSiteParameter
    //     *
    //     * @ORM\Column(type="integer", nullable=true)
    //     */
    //    private $vacationAccruedDaysPerMonth;
    //Live Site Root URL: http://c.med.cornell.edu/order/
    #[ORM\Column(type: 'text', nullable: true)]
    private $liveSiteRootUrl;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enableMetaphone;

    #[ORM\Column(type: 'text', nullable: true)]
    private $pathMetaphone;

    /**
     * Initial Configuration Completed
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $initialConfigurationCompleted;

    #[ORM\Column(type: 'text', nullable: true)]
    private $networkDrivePath;

    /**
     * Permitted failed log in attempts
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $permittedFailedLoginAttempt;

    #[ORM\Column(type: 'text', nullable: true)]
    private $captchaSiteKey;

    #[ORM\Column(type: 'text', nullable: true)]
    private $captchaSecretKey;

    /**
     * Enable Captcha at Sign Up
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $captchaEnabled;


    ////////////////////////// LDAP notice messages /////////////////////////
    /**
     * Notice for attempting to reset password for an LDAP-authenticated account.
     * The password for your [[CWID]] can only be changed or reset by visiting the enterprise password management page or by calling the help desk at ‭1 (212) 746-4878:
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noticeAttemptingPasswordResetLDAP;
    
    /**
     * Notice to prompt user to use Active Directory account to log in:
     * Please use your CWID to log in.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $loginInstruction;

    /**
     * Notice to prompt user with no Active Directory account to sign up for a new account:
     * Sign up for an account if you have no CWID.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noticeSignUpNoCwid;

    /**
     * Account request question asking whether applicant has an Active Directory account:
     * Do you (the person for whom the account is being requested) have a [CWID] username?
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noticeHasLdapAccount;

    /**
     * Full local name for active directory account:
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $noticeLdapName;
    ////////////////////////// EOF LDAP notice messages /////////////////////////
    /////////////// Specific Site Parameters //////////////////////
    /**
     * New User pre-populated. Defaults for an Organizational Group
     */
    #[ORM\OneToMany(targetEntity: 'OrganizationalGroupDefault', mappedBy: 'siteParameter', cascade: ['persist', 'remove'])]
    private $organizationalGroupDefaults;

    /**
     * Defaults for an Organizational Group
     */
    #[ORM\OneToOne(targetEntity: 'App\CallLogBundle\Entity\CalllogSiteParameter', cascade: ['persist', 'remove'])]
    private $calllogSiteParameter;

    #[ORM\Column(type: 'text', nullable: true)]
    private $calllogResources;

    #[ORM\OneToOne(targetEntity: 'App\CrnBundle\Entity\CrnSiteParameter', cascade: ['persist', 'remove'])]
    private $crnSiteParameter;

    #[ORM\OneToOne(targetEntity: 'App\DashboardBundle\Entity\DashboardSiteParameter', cascade: ['persist', 'remove'])]
    private $dashboardSiteParameter;

    /**
     * Navbar Employee List Filter Institution #1: [Dropdown with WCM selected]
     */
    #[ORM\ManyToOne(targetEntity: 'Institution')]
    private $navbarFilterInstitution1;

    /**
     * Navbar Employee List Filter Institution #1: [Dropdown with NYP selected]
     */
    #[ORM\ManyToOne(targetEntity: 'Institution')]
    private $navbarFilterInstitution2;

    /**
     * Default Accession Type for Deidentifier Defaults
     */
    #[ORM\ManyToOne(targetEntity: 'App\OrderformBundle\Entity\AccessionType')]
    private $defaultDeidentifierAccessionType;

    /**
     * Default Accession Type for ScanOrder Type
     */
    #[ORM\ManyToOne(targetEntity: 'App\OrderformBundle\Entity\AccessionType')]
    private $defaultScanAccessionType;

    /**
     * Default Mrn Type for ScanOrder Type
     */
    #[ORM\ManyToOne(targetEntity: 'App\OrderformBundle\Entity\MrnType')]
    private $defaultScanMrnType;

    /**
     * Default Slide Delivery
     */
    #[ORM\ManyToOne(targetEntity: 'App\OrderformBundle\Entity\OrderDelivery')]
    private $defaultScanDelivery;

//    /**
    //     * Default Institutional PHI Scope
    //     *
    //     * @ORM\ManyToOne(targetEntity="App\UserdirectoryBundle\Entity\Institution")
    //     */
    //    private $defaultInstitutionalPHIScope;
    /**
     * Default Organization Recipient
     */
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $defaultOrganizationRecipient;

    /**
     * Default Scanner
     */
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Equipment')]
    private $defaultScanner;

//    /**
    //     * @ORM\ManyToMany(targetEntity="Document", cascade={"persist","remove"})
    //     * @ORM\JoinTable(name="user_siteparameter_platformLogo",
    //     *      joinColumns={@ORM\JoinColumn(name="siteParameter_id", referencedColumnName="id")},
    //     *      inverseJoinColumns={@ORM\JoinColumn(name="platformLogo_id", referencedColumnName="id", unique=true)}
    //     *      )
    //     **/
    //    protected $platformLogos;
    #[ORM\JoinTable(name: 'user_siteparameter_platformLogo')]
    #[ORM\JoinColumn(name: 'siteParameter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'platformLogo_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'DESC'])]
    private $platformLogos;

    //High Resolution Platform Logo (2x)
    #[ORM\JoinTable(name: 'user_siteparameter_highresplatformlogo')]
    #[ORM\JoinColumn(name: 'siteparameter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'logo_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\Document', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['createdate' => 'DESC'])]
    private $highResPlatformLogos;

    //Logo width (300 or 320)
    #[ORM\Column(type: 'text', nullable: true)]
    private $logoWidth;

    //Logo height (80 or 180)
    #[ORM\Column(type: 'text', nullable: true)]
    private $logoHeight;

    /**
     * connection channel used in symfony's routing: http or https or null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $connectionChannel;

    /**
     * Real connection channel used in urls (http or https or null)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $urlConnectionChannel;

    /**
     * Translational Research Project Request Specialty Selection Note
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $transresProjectSelectionNote;

    /**
     * Pathology Department for Translational Research Dashboard
     */
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\Institution')]
    private $transresDashboardInstitution;

    /**
     * Name of the group that approves research projects involving human subjects: [IRB]
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $transresHumanSubjectName;

    /**
     * Name of the group that approves research projects involving animal subjects: [IACUC]
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $transresAnimalSubjectName;

    /**
     * Name of the business entity responsible for the translational research site - default "Center for Translational Pathology"
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $transresBusinessEntityName;

    /**
     * Abbreviated name of the business entity responsible for the translational research site - default "CTP"
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $transresBusinessEntityAbbreviation;

    /**
     * E-Mail Platform Administrator in case of critical errors
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $emailCriticalError;

    #[ORM\JoinTable(name: 'user_siteparameter_emailcriticalerrorexceptionuser')]
    #[ORM\JoinColumn(name: 'siteparameter_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'exceptionuser_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\User')]
    private $emailCriticalErrorExceptionUsers;

    /**
     * Restart Apache in case of critical this many errors over the course of 10 minutes:
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $restartServerErrorCounter;

    /**
     * Note Regarding Remote Access
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $remoteAccessUrl;

    #[ORM\OneToOne(targetEntity: 'App\VacReqBundle\Entity\VacReqSiteParameter', cascade: ['persist', 'remove'])]
    private $vacreqSiteParameter;

    #[ORM\OneToOne(targetEntity: 'TelephonySiteParameter', cascade: ['persist', 'remove'])]
    private $telephonySiteParameter;

    //Server Monitor
    /**
     * Other, external server monitor by cron: view-med checks if view is running (view's url is responding)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $externalMonitorUrl;

    /**
     * This server monitor: independent script checks by cron if url on this server is running
     * This script must be independent from Symfony, PHP, Postgresql
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $monitorScript;

    /**
     * Monitor check interval in minutes
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $monitorCheckInterval;

    /**
     * Send email notifications to platform administrators when new user records are created: [Yes/No]
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $sendEmailUserAdded;

//    /**
    //     * independent monitor script arguments (i.e. url, smtpServerAddress, mailerPort, mailerUser, mailerPassword, admin emails )
    //     *
    //     * @ORM\Column(type="text", nullable=true)
    //     */
    //    private $monitorScriptArgs;
    /**
     * Configuration json file for backup DB
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $dbBackupConfig;

    /**
     * Configuration json file for backup the uploaded file folder
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private $filesBackupConfig;

    //////// Fields for Server Instance connection ////////
    //ser Group: [WCM Department of Pathology and Laboratory Medicine], [Multi-tenant]
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\AuthUserGroupList')]
    private $authUserGroup;

    //Server Network Accessibility and Role (aka Server Role and Network Access):
    //[Intranet (Solo) / Intranet (Tandem) / Internet (Solo) / Internet (Tandem)] / Internet (Hub)
    //1) SiteParameters has one AuthServerNetworkList (i.e. 'Internet (Hub)')
    //2) AuthServerNetworkList has many HostedUserGroupList (nested tree), (i.e. 'c/wcm/pathology' or 'c/lmh/pathology')
    //3) HostedUserGroupList has one tree node (i.e. 'pathology' with parents 'c/wcm/pathology')
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\AuthServerNetworkList')]
    private $authServerNetwork;

    //Tandem Partner Server URL: [https://view.med.cornell.edu]
    #[ORM\ManyToOne(targetEntity: 'App\UserdirectoryBundle\Entity\AuthPartnerServerList')]
    private $authPartnerServer;
    //////// EOF Fields for Server Instance connection ////////

    //Secret key for transfer
    #[ORM\Column(type: 'text', nullable: true)]
    private $secretKey;

    //Instance ID [6 letters]
    #[ORM\Column(type: 'text', nullable: true)]
    private $instanceId;

    //Show the section with the list of tenants on the homepage
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $showTenantsHomepage;

//    #[ORM\Column(type: 'text', nullable: true)]
//    private $tenantPrefixUrlSlug;

    //Move to AuthServerNetworkList
    //hostedUserGroup is the tenant id (i.e. 'c/wcm/pathology' or 'c/lmh/pathology')
//    #[ORM\JoinTable(name: 'user_siteparameter_hostedusergroup')]
//    #[ORM\JoinColumn(name: 'siteParameter_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\InverseJoinColumn(name: 'hostedusergroup_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
//    #[ORM\ManyToMany(targetEntity: 'App\UserdirectoryBundle\Entity\HostedUserGroupList', cascade: ['persist', 'remove'])]
//    #[ORM\OrderBy(['createdate' => 'DESC'])]
//    private $hostedUserGroups;
      //Homepage and About Us Page Content (if Server Role and Network Access field is set to “Internet (Hub)

    //#[ORM\OneToOne(targetEntity: 'TransferSiteParameter', cascade: ['persist', 'remove'])]
    //private $interfaceTransfer;

    function __construct( $addobjects=true )
    {
        $this->organizationalGroupDefaults = new ArrayCollection();
        $this->platformLogos = new ArrayCollection();
        $this->highResPlatformLogos = new ArrayCollection();
        //$this->hostedUserGroups = new ArrayCollection();
        $this->setMaintenance(false);
        $this->setShowCopyrightOnFooter(true);
        $this->setLdapAll(true);
        $this->setInitialConfigurationCompleted(false);
        $this->setShowTenantsHomepage(true);
    }



    public function addOrganizationalGroupDefault($item)
    {
        if( $item && !$this->organizationalGroupDefaults->contains($item) ) {
            $this->organizationalGroupDefaults->add($item);
            //This ($item->setSiteParameter($this)) caused A new entity was found through the relationship
            // 'App\UserdirectoryBundle\Entity\OrganizationalGroupDefault#siteParameter
            // that was not configured to cascade persist operations for entity: App\UserdirectoryBundle\Entity\SiteParameters@6889
            $item->setSiteParameter($this);
        }

        return $this;
    }
    public function removeOrganizationalGroupDefault($item)
    {
        $this->organizationalGroupDefaults->removeElement($item);
    }
    public function getOrganizationalGroupDefaults()
    {
        return $this->organizationalGroupDefaults;
    }

    public function addPlatformLogo($item)
    {
        if( $item && !$this->platformLogos->contains($item) ) {
            $this->platformLogos->add($item);
            $item->createUseObject($this);
        }

        return $this;
    }
    public function removePlatformLogo($item)
    {
        $this->platformLogos->removeElement($item);
        $item->clearUseObject();
    }
    public function getPlatformLogos()
    {
        return $this->platformLogos;
    }

    public function addHighResPlatformLogo($item)
    {
        if( $item && !$this->highResPlatformLogos->contains($item) ) {
            $this->highResPlatformLogos->add($item);
            $item->createUseObject($this);
        }

        return $this;
    }
    public function removeHighResPlatformLogo($item)
    {
        $this->highResPlatformLogos->removeElement($item);
        $item->clearUseObject();
    }
    public function getHighResPlatformLogos()
    {
        return $this->highResPlatformLogos;
    }

    /**
     * @return mixed
     */
    public function getLogoWidth()
    {
        return $this->logoWidth;
    }

    /**
     * @param mixed $logoWidth
     */
    public function setLogoWidth($logoWidth)
    {
        $this->logoWidth = $logoWidth;
    }

    /**
     * @return mixed
     */
    public function getLogoHeight()
    {
        return $this->logoHeight;
    }

    /**
     * @param mixed $logoHeight
     */
    public function setLogoHeight($logoHeight)
    {
        $this->logoHeight = $logoHeight;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @param mixed $maxIdleTime
     */
    public function setMaxIdleTime($maxIdleTime)
    {
        $this->maxIdleTime = $maxIdleTime;
    }

    /**
     * @return mixed
     */
    public function getMaxIdleTime()
    {
        return $this->maxIdleTime;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
    

    /**
     * @param mixed $aDLDAPServerAccountPassword
     */
    public function setADLDAPServerAccountPassword($aDLDAPServerAccountPassword)
    {
        $this->aDLDAPServerAccountPassword = $aDLDAPServerAccountPassword;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerAccountPassword()
    {
        return $this->aDLDAPServerAccountPassword;
    }

    /**
     * @param mixed $aDLDAPServerAccountUserName
     */
    public function setADLDAPServerAccountUserName($aDLDAPServerAccountUserName)
    {
        $this->aDLDAPServerAccountUserName = $aDLDAPServerAccountUserName;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerAccountUserName()
    {
        return $this->aDLDAPServerAccountUserName;
    }

    /**
     * @param mixed $aDLDAPServerAddress
     */
    public function setADLDAPServerAddress($aDLDAPServerAddress)
    {
        $this->aDLDAPServerAddress = $aDLDAPServerAddress;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerAddress()
    {
        return $this->aDLDAPServerAddress;
    }

    /**
     * @param mixed $aDLDAPServerOu
     */
    public function setADLDAPServerOu($aDLDAPServerOu)
    {
        $this->aDLDAPServerOu = $aDLDAPServerOu;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerOu()
    {
        return $this->aDLDAPServerOu;
    }

    /**
     * @param mixed $aDLDAPServerPort
     */
    public function setADLDAPServerPort($aDLDAPServerPort)
    {
        $this->aDLDAPServerPort = $aDLDAPServerPort;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerPort()
    {
        return $this->aDLDAPServerPort;
    }

    /**
     * @param mixed $pacsvendorSlideManagerDBName
     */
    public function setPacsvendorSlideManagerDBName($pacsvendorSlideManagerDBName)
    {
        $this->pacsvendorSlideManagerDBName = $pacsvendorSlideManagerDBName;
    }

    /**
     * @return mixed
     */
    public function getPacsvendorSlideManagerDBName()
    {
        return $this->pacsvendorSlideManagerDBName;
    }

    /**
     * @param mixed $pacsvendorSlideManagerDBPassword
     */
    public function setPacsvendorSlideManagerDBPassword($pacsvendorSlideManagerDBPassword)
    {
        $this->pacsvendorSlideManagerDBPassword = $pacsvendorSlideManagerDBPassword;
    }

    /**
     * @return mixed
     */
    public function getPacsvendorSlideManagerDBPassword()
    {
        return $this->pacsvendorSlideManagerDBPassword;
    }

    /**
     * @param mixed $pacsvendorSlideManagerDBServerAddress
     */
    public function setPacsvendorSlideManagerDBServerAddress($pacsvendorSlideManagerDBServerAddress)
    {
        $this->pacsvendorSlideManagerDBServerAddress = $pacsvendorSlideManagerDBServerAddress;
    }

    /**
     * @return mixed
     */
    public function getPacsvendorSlideManagerDBServerAddress()
    {
        return $this->pacsvendorSlideManagerDBServerAddress;
    }

    /**
     * @param mixed $pacsvendorSlideManagerDBServerPort
     */
    public function setPacsvendorSlideManagerDBServerPort($pacsvendorSlideManagerDBServerPort)
    {
        $this->pacsvendorSlideManagerDBServerPort = $pacsvendorSlideManagerDBServerPort;
    }

    /**
     * @return mixed
     */
    public function getPacsvendorSlideManagerDBServerPort()
    {
        return $this->pacsvendorSlideManagerDBServerPort;
    }

    /**
     * @param mixed $pacsvendorSlideManagerDBUserName
     */
    public function setPacsvendorSlideManagerDBUserName($pacsvendorSlideManagerDBUserName)
    {
        $this->pacsvendorSlideManagerDBUserName = $pacsvendorSlideManagerDBUserName;
    }

    /**
     * @return mixed
     */
    public function getPacsvendorSlideManagerDBUserName()
    {
        return $this->pacsvendorSlideManagerDBUserName;
    }

    /**
     * @param mixed $lisDBAccountPassword
     */
    public function setLisDBAccountPassword($lisDBAccountPassword)
    {
        $this->lisDBAccountPassword = $lisDBAccountPassword;
    }

    /**
     * @return mixed
     */
    public function getLisDBAccountPassword()
    {
        return $this->lisDBAccountPassword;
    }

    /**
     * @param mixed $lisDBAccountUserName
     */
    public function setLisDBAccountUserName($lisDBAccountUserName)
    {
        $this->lisDBAccountUserName = $lisDBAccountUserName;
    }

    /**
     * @return mixed
     */
    public function getLisDBAccountUserName()
    {
        return $this->lisDBAccountUserName;
    }

    /**
     * @param mixed $lisDBName
     */
    public function setLisDBName($lisDBName)
    {
        $this->lisDBName = $lisDBName;
    }

    /**
     * @return mixed
     */
    public function getLisDBName()
    {
        return $this->lisDBName;
    }

    /**
     * @param mixed $lisDBServerAddress
     */
    public function setLisDBServerAddress($lisDBServerAddress)
    {
        $this->lisDBServerAddress = $lisDBServerAddress;
    }

    /**
     * @return mixed
     */
    public function getLisDBServerAddress()
    {
        return $this->lisDBServerAddress;
    }

    /**
     * @param mixed $lisDBServerPort
     */
    public function setLisDBServerPort($lisDBServerPort)
    {
        $this->lisDBServerPort = $lisDBServerPort;
    }

    /**
     * @return mixed
     */
    public function getLisDBServerPort()
    {
        return $this->lisDBServerPort;
    }

    /**
     * @param mixed $LISName
     */
    public function setLisName($LISName)
    {
        $this->lisName = $LISName;
    }

    /**
     * @return mixed
     */
    public function getLisName()
    {
        return $this->lisName;
    }

    /**
     * @param mixed $LISVersion
     */
    public function setLisVersion($LISVersion)
    {
        $this->lisVersion = $LISVersion;
    }

    /**
     * @return mixed
     */
    public function getLisVersion()
    {
        return $this->lisVersion;
    }



    /**
     * @param mixed $dbDatabaseName
     */
    public function setDbDatabaseName($dbDatabaseName)
    {
        $this->dbDatabaseName = $dbDatabaseName;
    }

    /**
     * @return mixed
     */
    public function getDbDatabaseName()
    {
        return $this->dbDatabaseName;
    }

    /**
     * @param mixed $dbServerAccountPassword
     */
    public function setDbServerAccountPassword($dbServerAccountPassword)
    {
        $this->dbServerAccountPassword = $dbServerAccountPassword;
    }

    /**
     * @return mixed
     */
    public function getDbServerAccountPassword()
    {
        return $this->dbServerAccountPassword;
    }

    /**
     * @param mixed $dbServerAccountUserName
     */
    public function setDbServerAccountUserName($dbServerAccountUserName)
    {
        $this->dbServerAccountUserName = $dbServerAccountUserName;
    }

    /**
     * @return mixed
     */
    public function getDbServerAccountUserName()
    {
        return $this->dbServerAccountUserName;
    }

    /**
     * @param mixed $dbServerAddress
     */
    public function setDbServerAddress($dbServerAddress)
    {
        $this->dbServerAddress = $dbServerAddress;
    }

    /**
     * @return mixed
     */
    public function getDbServerAddress()
    {
        return $this->dbServerAddress;
    }

    /**
     * @param mixed $dbServerPort
     */
    public function setDbServerPort($dbServerPort)
    {
        $this->dbServerPort = $dbServerPort;
    }

    /**
     * @return mixed
     */
    public function getDbServerPort()
    {
        return $this->dbServerPort;
    }

    /**
     * @param mixed $siteEmail
     */
    public function setSiteEmail($siteEmail)
    {
        $this->siteEmail = $siteEmail;
    }

    /**
     * @return mixed
     */
    public function getSiteEmail()
    {
        return $this->siteEmail;
    }

    /**
     * @param mixed $smtpServerAddress
     */
    public function setSmtpServerAddress($smtpServerAddress)
    {
        $this->smtpServerAddress = $smtpServerAddress;
    }

    /**
     * @return mixed
     */
    public function getSmtpServerAddress()
    {
        return $this->smtpServerAddress;
    }

    /**
     * @return mixed
     */
    public function getMailerTransport()
    {
        return $this->mailerTransport;
    }

    /**
     * @param mixed $mailerTransport
     */
    public function setMailerTransport($mailerTransport)
    {
        $this->mailerTransport = $mailerTransport;
    }

    /**
     * @return mixed
     */
    public function getMailerAuthMode()
    {
        return $this->mailerAuthMode;
    }

    /**
     * @param mixed $mailerAuthMode
     */
    public function setMailerAuthMode($mailerAuthMode)
    {
        $this->mailerAuthMode = $mailerAuthMode;
    }

    /**
     * @return mixed
     */
    public function getMailerUseSecureConnection()
    {
        return $this->mailerUseSecureConnection;
    }

    /**
     * @param mixed $mailerUseSecureConnection
     */
    public function setMailerUseSecureConnection($mailerUseSecureConnection)
    {
        $this->mailerUseSecureConnection = $mailerUseSecureConnection;
    }

    /**
     * @return mixed
     */
    public function getMailerUser()
    {
        return $this->mailerUser;
    }

    /**
     * @param mixed $mailerUser
     */
    public function setMailerUser($mailerUser)
    {
        $this->mailerUser = $mailerUser;
    }

    /**
     * @return mixed
     */
    public function getMailerPassword()
    {
        return $this->mailerPassword;
    }

    /**
     * @param mixed $mailerPassword
     */
    public function setMailerPassword($mailerPassword)
    {
        $this->mailerPassword = $mailerPassword;
    }

    /**
     * @return mixed
     */
    public function getMailerPort()
    {
        return $this->mailerPort;
    }

    /**
     * @param mixed $mailerPort
     */
    public function setMailerPort($mailerPort)
    {
        $this->mailerPort = $mailerPort;
    }

    /**
     * @return mixed
     */
    public function getMailerSpool()
    {
        return $this->mailerSpool;
    }

    /**
     * @param mixed $mailerSpool
     */
    public function setMailerSpool($mailerSpool)
    {
        $this->mailerSpool = $mailerSpool;
    }

    /**
     * @return mixed
     */
    public function getMailerFlushQueueFrequency()
    {
        return $this->mailerFlushQueueFrequency;
    }

    /**
     * @param mixed $mailerFlushQueueFrequency
     */
    public function setMailerFlushQueueFrequency($mailerFlushQueueFrequency)
    {
        $this->mailerFlushQueueFrequency = $mailerFlushQueueFrequency;
    }

    /**
     * @return mixed
     */
    public function getMailerDeliveryAddresses()
    {
        return $this->mailerDeliveryAddresses;
    }

    /**
     * @param mixed $mailerDeliveryAddresses
     */
    public function setMailerDeliveryAddresses($mailerDeliveryAddresses)
    {
        $this->mailerDeliveryAddresses = $mailerDeliveryAddresses;
    }

    /**
     * @param mixed $autoAssignInstitution
     */
    public function setAutoAssignInstitution($autoAssignInstitution)
    {
        $this->autoAssignInstitution = $autoAssignInstitution;
    }

    /**
     * @return mixed
     */
    public function getAutoAssignInstitution()
    {
        return $this->autoAssignInstitution;
    }

    /**
     * @return mixed
     */
    public function getEnableAutoAssignmentInstitutionalScope()
    {
        return $this->enableAutoAssignmentInstitutionalScope;
    }

    /**
     * @param mixed $enableAutoAssignmentInstitutionalScope
     */
    public function setEnableAutoAssignmentInstitutionalScope($enableAutoAssignmentInstitutionalScope)
    {
        $this->enableAutoAssignmentInstitutionalScope = $enableAutoAssignmentInstitutionalScope;
    }

    /**
     * @param mixed $departmentname
     */
    public function setDepartmentname($departmentname)
    {
        $this->departmentname = $departmentname;
    }

    /**
     * @return mixed
     */
    public function getDepartmentname()
    {
        return $this->departmentname;
    }

    /**
     * @param mixed $departmenturl
     */
    public function setDepartmenturl($departmenturl)
    {
        $this->departmenturl = $departmenturl;
    }

    /**
     * @return mixed
     */
    public function getDepartmenturl()
    {
        return $this->departmenturl;
    }

    /**
     * @param mixed $institutionname
     */
    public function setInstitutionname($institutionname)
    {
        $this->institutionname = $institutionname;
    }

    /**
     * @return mixed
     */
    public function getInstitutionname()
    {
        return $this->institutionname;
    }

    /**
     * @param mixed $institutionurl
     */
    public function setInstitutionurl($institutionurl)
    {
        $this->institutionurl = $institutionurl;
    }

    /**
     * @return mixed
     */
    public function getInstitutionurl()
    {
        return $this->institutionurl;
    }

    /**
     * @return mixed
     */
    public function getSubinstitutionurl()
    {
        return $this->subinstitutionurl;
    }

    /**
     * @param mixed $subinstitutionurl
     */
    public function setSubinstitutionurl($subinstitutionurl)
    {
        $this->subinstitutionurl = $subinstitutionurl;
    }

    /**
     * @return mixed
     */
    public function getSubinstitutionname()
    {
        return $this->subinstitutionname;
    }

    /**
     * @param mixed $subinstitutionname
     */
    public function setSubinstitutionname($subinstitutionname)
    {
        $this->subinstitutionname = $subinstitutionname;
    }

    /**
     * @return mixed
     */
    public function getShowCopyrightOnFooter()
    {
        return $this->showCopyrightOnFooter;
    }

    /**
     * @param mixed $showCopyrightOnFooter
     */
    public function setShowCopyrightOnFooter($showCopyrightOnFooter)
    {
        $this->showCopyrightOnFooter = $showCopyrightOnFooter;
    }

    /**
     * @param mixed $maintenance
     */
    public function setMaintenance($maintenance)
    {
        $this->maintenance = $maintenance;
    }

    /**
     * @return mixed
     */
    public function getMaintenance()
    {
        return $this->maintenance;
    }

    /**
     * @param mixed $maintenanceenddate
     */
    public function setMaintenanceenddate($maintenanceenddate)
    {
        $this->maintenanceenddate = $maintenanceenddate;
    }

    /**
     * @return mixed
     */
    public function getMaintenanceenddate()
    {
        return $this->maintenanceenddate;
    }

    public function getMaintenanceenddateString() {
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y H:i');
        return $transformer->transform($this->maintenanceenddate);
    }

    /**
     * @param mixed $maintenanceloginmsg
     */
    public function setMaintenanceloginmsg($maintenanceloginmsg)
    {
        $this->maintenanceloginmsg = $maintenanceloginmsg;
    }

    /**
     * @return mixed
     */
    public function getMaintenanceloginmsg()
    {
        return $this->maintenanceloginmsg;
    }

    public function getMaintenanceloginmsgWithDate()
    {
        $msg = str_replace("[[datetime]]", $this->getUntilDate(), $this->getMaintenanceloginmsg());
        return $msg;
    }

    public function getUntilDate() {

        $transformer = new DateTimeToStringTransformer(null,"America/New_York",'m/d/Y H:i');
        $now = new \DateTime('now');
        $nowStr = $transformer->transform($now);

        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y H:i');
        $maint = $this->getMaintenanceenddate();
        $maintStr = $transformer->transform($maint);

        //echo "maint=".$maintStr.", now=".$nowStr."<br>";

        $now_time = strtotime($nowStr);
        $maint_time = strtotime($maintStr);

        //echo "maint=".$maint_time.", now=".$now_time."<br>";

        if( !$this->getMaintenanceenddate() || $maint_time < $now_time ) {
            $untilDate = date_modify( $now, '+1 hour' );
            $transformer = new DateTimeToStringTransformer(null,"America/New_York",'m/d/Y H:i');
            $untilDateStr = $transformer->transform($untilDate);
        } else {
            $untilDateStr = $this->getMaintenanceenddateString();
        }

        return $untilDateStr;
    }

    /**
     * @param mixed $maintenancelogoutmsg
     */
    public function setMaintenancelogoutmsg($maintenancelogoutmsg)
    {
        $this->maintenancelogoutmsg = $maintenancelogoutmsg;
    }

    /**
     * @return mixed
     */
    public function getMaintenancelogoutmsg()
    {
        return $this->maintenancelogoutmsg;
    }
    public function getMaintenancelogoutmsgWithDate()
    {
        $msg = str_replace("[[datetime]]", $this->getUntilDate(), $this->getMaintenancelogoutmsg());
        return $msg;
    }

    /**
     * @return mixed
     */
    public function getGlobalNoteLogin()
    {
        return $this->globalNoteLogin;
    }

    /**
     * @param mixed $globalNoteLogin
     */
    public function setGlobalNoteLogin($globalNoteLogin)
    {
        $this->globalNoteLogin = $globalNoteLogin;
    }

    /**
     * @param mixed $employeesuploadpath
     */
    public function setEmployeesuploadpath($employeesuploadpath)
    {
        $this->employeesuploadpath = $employeesuploadpath;
    }

    /**
     * @return mixed
     */
    public function getEmployeesuploadpath()
    {
        return $this->employeesuploadpath;
    }

    /**
     * @param mixed $scanuploadpath
     */
    public function setScanuploadpath($scanuploadpath)
    {
        $this->scanuploadpath = $scanuploadpath;
    }

    /**
     * @return mixed
     */
    public function getScanuploadpath()
    {
        return $this->scanuploadpath;
    }

    /**
     * @return mixed
     */
    public function getFellappSiteParameter()
    {
        return $this->fellappSiteParameter;
    }

    /**
     * @param mixed $fellappSiteParameter
     */
    public function setFellappSiteParameter($fellappSiteParameter)
    {
        $this->fellappSiteParameter = $fellappSiteParameter;
    }

    /**
     * @param mixed $fellappuploadpath
     */
    public function setFellappuploadpath($fellappuploadpath)
    {
        $this->fellappuploadpath = $fellappuploadpath;
    }

    /**
     * @return mixed
     */
    public function getFellappuploadpath()
    {
        return $this->fellappuploadpath;
    }

    /**
     * @param mixed $avataruploadpath
     */
    public function setAvataruploadpath($avataruploadpath)
    {
        $this->avataruploadpath = $avataruploadpath;
    }

    /**
     * @return mixed
     */
    public function getAvataruploadpath()
    {
        return $this->avataruploadpath;
    }

    /**
     * @param mixed $listManagerTitle
     */
    public function setListManagerTitle($listManagerTitle)
    {
        $this->listManagerTitle = $listManagerTitle;
    }

    /**
     * @return mixed
     */
    public function getListManagerTitle()
    {
        return $this->listManagerTitle;
    }

    /**
     * @param mixed $mainHomeTitle
     */
    public function setMainHomeTitle($mainHomeTitle)
    {
        $this->mainHomeTitle = $mainHomeTitle;
    }

    /**
     * @return mixed
     */
    public function getMainHomeTitle()
    {
        return $this->mainHomeTitle;
    }

    /**
     * @param mixed $eventLogTitle
     */
    public function setEventLogTitle($eventLogTitle)
    {
        $this->eventLogTitle = $eventLogTitle;
    }

    /**
     * @return mixed
     */
    public function getEventLogTitle()
    {
        return $this->eventLogTitle;
    }

    /**
     * @param mixed $siteSettingsTitle
     */
    public function setSiteSettingsTitle($siteSettingsTitle)
    {
        $this->siteSettingsTitle = $siteSettingsTitle;
    }

    /**
     * @return mixed
     */
    public function getSiteSettingsTitle()
    {
        return $this->siteSettingsTitle;
    }

    /**
     * @param mixed $contentAboutPage
     */
    public function setContentAboutPage($contentAboutPage)
    {
        $this->contentAboutPage = $contentAboutPage;
    }

    /**
     * @return mixed
     */
    public function getContentAboutPage()
    {
        return $this->contentAboutPage;
    }

    /**
     * @param mixed $underLoginMsgScan
     */
    public function setUnderLoginMsgScan($underLoginMsgScan)
    {
        $this->underLoginMsgScan = $underLoginMsgScan;
    }

    /**
     * @return mixed
     */
    public function getUnderLoginMsgScan()
    {
        return $this->underLoginMsgScan;
    }

    /**
     * @param mixed $underLoginMsgUser
     */
    public function setUnderLoginMsgUser($underLoginMsgUser)
    {
        $this->underLoginMsgUser = $underLoginMsgUser;
    }

    /**
     * @return mixed
     */
    public function getUnderLoginMsgUser()
    {
        return $this->underLoginMsgUser;
    }

    /**
     * @param mixed $ldapExeFilename
     */
    public function setLdapExeFilename($ldapExeFilename)
    {
        $this->ldapExeFilename = $ldapExeFilename;
    }

    /**
     * @return mixed
     */
    public function getLdapExeFilename()
    {
        return $this->ldapExeFilename;
    }

    /**
     * @return mixed
     */
    public function getLdapAll()
    {
        return $this->ldapAll;
    }

    /**
     * @param mixed $ldapAll
     */
    public function setLdapAll($ldapAll)
    {
        $this->ldapAll = $ldapAll;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerAddress2()
    {
        return $this->aDLDAPServerAddress2;
    }

    /**
     * @param mixed $aDLDAPServerAddress2
     */
    public function setADLDAPServerAddress2($aDLDAPServerAddress2)
    {
        $this->aDLDAPServerAddress2 = $aDLDAPServerAddress2;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerPort2()
    {
        return $this->aDLDAPServerPort2;
    }

    /**
     * @param mixed $aDLDAPServerPort2
     */
    public function setADLDAPServerPort2($aDLDAPServerPort2)
    {
        $this->aDLDAPServerPort2 = $aDLDAPServerPort2;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerOu2()
    {
        return $this->aDLDAPServerOu2;
    }

    /**
     * @param mixed $aDLDAPServerOu2
     */
    public function setADLDAPServerOu2($aDLDAPServerOu2)
    {
        $this->aDLDAPServerOu2 = $aDLDAPServerOu2;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerAccountUserName2()
    {
        return $this->aDLDAPServerAccountUserName2;
    }

    /**
     * @param mixed $aDLDAPServerAccountUserName2
     */
    public function setADLDAPServerAccountUserName2($aDLDAPServerAccountUserName2)
    {
        $this->aDLDAPServerAccountUserName2 = $aDLDAPServerAccountUserName2;
    }

    /**
     * @return mixed
     */
    public function getADLDAPServerAccountPassword2()
    {
        return $this->aDLDAPServerAccountPassword2;
    }

    /**
     * @param mixed $aDLDAPServerAccountPassword2
     */
    public function setADLDAPServerAccountPassword2($aDLDAPServerAccountPassword2)
    {
        $this->aDLDAPServerAccountPassword2 = $aDLDAPServerAccountPassword2;
    }

    /**
     * @return mixed
     */
    public function getLdapExePath2()
    {
        return $this->ldapExePath2;
    }

    /**
     * @param mixed $ldapExePath2
     */
    public function setLdapExePath2($ldapExePath2)
    {
        $this->ldapExePath2 = $ldapExePath2;
    }

    /**
     * @return mixed
     */
    public function getLdapExeFilename2()
    {
        return $this->ldapExeFilename2;
    }

    /**
     * @param mixed $ldapExeFilename2
     */
    public function setLdapExeFilename2($ldapExeFilename2)
    {
        $this->ldapExeFilename2 = $ldapExeFilename2;
    }

    /**
     * @return mixed
     */
    public function getLdapMapperEmail()
    {
        return $this->ldapMapperEmail;
    }

    /**
     * @param mixed $ldapMapperEmail
     */
    public function setLdapMapperEmail($ldapMapperEmail)
    {
        $this->ldapMapperEmail = $ldapMapperEmail;
    }

    /**
     * @return mixed
     */
    public function getLdapMapperEmail2()
    {
        return $this->ldapMapperEmail2;
    }

    /**
     * @param mixed $ldapMapperEmail2
     */
    public function setLdapMapperEmail2($ldapMapperEmail2)
    {
        $this->ldapMapperEmail2 = $ldapMapperEmail2;
    }

    /**
     * @return mixed
     */
    public function getLdapMapperPrimaryPublicUserIdType()
    {
        return $this->ldapMapperPrimaryPublicUserIdType;
    }

    /**
     * @param mixed $ldapMapperPrimaryPublicUserIdType
     */
    public function setLdapMapperPrimaryPublicUserIdType($ldapMapperPrimaryPublicUserIdType)
    {
        $this->ldapMapperPrimaryPublicUserIdType = $ldapMapperPrimaryPublicUserIdType;
    }

    /**
     * @return mixed
     */
    public function getLdapMapperPrimaryPublicUserIdType2()
    {
        return $this->ldapMapperPrimaryPublicUserIdType2;
    }

    /**
     * @param mixed $ldapMapperPrimaryPublicUserIdType2
     */
    public function setLdapMapperPrimaryPublicUserIdType2($ldapMapperPrimaryPublicUserIdType2)
    {
        $this->ldapMapperPrimaryPublicUserIdType2 = $ldapMapperPrimaryPublicUserIdType2;
    }

    /**
     * @return mixed
     */
    public function getDefaultPrimaryPublicUserIdType()
    {
        return $this->defaultPrimaryPublicUserIdType;
    }

    /**
     * @param mixed $defaultPrimaryPublicUserIdType
     */
    public function setDefaultPrimaryPublicUserIdType($defaultPrimaryPublicUserIdType)
    {
        $this->defaultPrimaryPublicUserIdType = $defaultPrimaryPublicUserIdType;
    }

    /**
     * @param mixed $ldapExePath
     */
    public function setLdapExePath($ldapExePath)
    {
        $this->ldapExePath = $ldapExePath;
    }

    /**
     * @return mixed
     */
    public function getLdapExePath()
    {
        return $this->ldapExePath;
    }

//    /**
//     * @param mixed $allowPopulateFellApp
//     */
//    public function setAllowPopulateFellApp($allowPopulateFellApp)
//    {
//        $this->allowPopulateFellApp = $allowPopulateFellApp;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function getAllowPopulateFellApp()
//    {
//        return $this->allowPopulateFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getSendEmailUploadLetterFellApp()
//    {
//        return $this->sendEmailUploadLetterFellApp;
//    }
//
//    /**
//     * @param mixed $sendEmailUploadLetterFellApp
//     */
//    public function setSendEmailUploadLetterFellApp($sendEmailUploadLetterFellApp)
//    {
//        $this->sendEmailUploadLetterFellApp = $sendEmailUploadLetterFellApp;
//    }


//    public function getConfirmationSubjectFellApp() {
//        return $this->confirmationSubjectFellApp;
//    }
//
//    public function setConfirmationSubjectFellApp($confirmationSubjectFellApp) {
//        $this->confirmationSubjectFellApp = $confirmationSubjectFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getRecLetterSaltFellApp()
//    {
//        return $this->recLetterSaltFellApp;
//    }
//
//    /**
//     * @param mixed $recLetterSaltFellApp
//     */
//    public function setRecLetterSaltFellApp($recLetterSaltFellApp)
//    {
//        $this->recLetterSaltFellApp = $recLetterSaltFellApp;
//    }

//    public function getConfirmationBodyFellApp() {
//        return $this->confirmationBodyFellApp;
//    }
//
//    public function setConfirmationBodyFellApp($confirmationBodyFellApp) {
//        $this->confirmationBodyFellApp = $confirmationBodyFellApp;
//    }

//    public function getConfirmationEmailFellApp() {
//        return $this->confirmationEmailFellApp;
//    }
//
//    public function setConfirmationEmailFellApp($confirmationEmailFellApp) {
//        $this->confirmationEmailFellApp = $confirmationEmailFellApp;
//    }

    /**
     * @param mixed $LISNameDevelopment
     */
    public function setLISNameDevelopment($LISNameDevelopment)
    {
        $this->LISNameDevelopment = $LISNameDevelopment;
    }

    /**
     * @return mixed
     */
    public function getLISNameDevelopment()
    {
        return $this->LISNameDevelopment;
    }

    /**
     * @param mixed $LISNameTest
     */
    public function setLISNameTest($LISNameTest)
    {
        $this->LISNameTest = $LISNameTest;
    }

    /**
     * @return mixed
     */
    public function getLISNameTest()
    {
        return $this->LISNameTest;
    }

    /**
     * @param mixed $LISVersionDevelopment
     */
    public function setLISVersionDevelopment($LISVersionDevelopment)
    {
        $this->LISVersionDevelopment = $LISVersionDevelopment;
    }

    /**
     * @return mixed
     */
    public function getLISVersionDevelopment()
    {
        return $this->LISVersionDevelopment;
    }

    /**
     * @param mixed $LISVersionTest
     */
    public function setLISVersionTest($LISVersionTest)
    {
        $this->LISVersionTest = $LISVersionTest;
    }

    /**
     * @return mixed
     */
    public function getLISVersionTest()
    {
        return $this->LISVersionTest;
    }

    /**
     * @param mixed $lisDBAccountPasswordDevelopment
     */
    public function setLisDBAccountPasswordDevelopment($lisDBAccountPasswordDevelopment)
    {
        $this->lisDBAccountPasswordDevelopment = $lisDBAccountPasswordDevelopment;
    }

    /**
     * @return mixed
     */
    public function getLisDBAccountPasswordDevelopment()
    {
        return $this->lisDBAccountPasswordDevelopment;
    }

    /**
     * @param mixed $lisDBAccountPasswordTest
     */
    public function setLisDBAccountPasswordTest($lisDBAccountPasswordTest)
    {
        $this->lisDBAccountPasswordTest = $lisDBAccountPasswordTest;
    }

    /**
     * @return mixed
     */
    public function getLisDBAccountPasswordTest()
    {
        return $this->lisDBAccountPasswordTest;
    }

    /**
     * @param mixed $lisDBAccountUserNameDevelopment
     */
    public function setLisDBAccountUserNameDevelopment($lisDBAccountUserNameDevelopment)
    {
        $this->lisDBAccountUserNameDevelopment = $lisDBAccountUserNameDevelopment;
    }

    /**
     * @return mixed
     */
    public function getLisDBAccountUserNameDevelopment()
    {
        return $this->lisDBAccountUserNameDevelopment;
    }

    /**
     * @param mixed $lisDBAccountUserNameTest
     */
    public function setLisDBAccountUserNameTest($lisDBAccountUserNameTest)
    {
        $this->lisDBAccountUserNameTest = $lisDBAccountUserNameTest;
    }

    /**
     * @return mixed
     */
    public function getLisDBAccountUserNameTest()
    {
        return $this->lisDBAccountUserNameTest;
    }

    /**
     * @param mixed $lisDBNameDevelopment
     */
    public function setLisDBNameDevelopment($lisDBNameDevelopment)
    {
        $this->lisDBNameDevelopment = $lisDBNameDevelopment;
    }

    /**
     * @return mixed
     */
    public function getLisDBNameDevelopment()
    {
        return $this->lisDBNameDevelopment;
    }

    /**
     * @param mixed $lisDBNameTest
     */
    public function setLisDBNameTest($lisDBNameTest)
    {
        $this->lisDBNameTest = $lisDBNameTest;
    }

    /**
     * @return mixed
     */
    public function getLisDBNameTest()
    {
        return $this->lisDBNameTest;
    }

    /**
     * @param mixed $lisDBServerAddressDevelopment
     */
    public function setLisDBServerAddressDevelopment($lisDBServerAddressDevelopment)
    {
        $this->lisDBServerAddressDevelopment = $lisDBServerAddressDevelopment;
    }

    /**
     * @return mixed
     */
    public function getLisDBServerAddressDevelopment()
    {
        return $this->lisDBServerAddressDevelopment;
    }

    /**
     * @param mixed $lisDBServerAddressTest
     */
    public function setLisDBServerAddressTest($lisDBServerAddressTest)
    {
        $this->lisDBServerAddressTest = $lisDBServerAddressTest;
    }

    /**
     * @return mixed
     */
    public function getLisDBServerAddressTest()
    {
        return $this->lisDBServerAddressTest;
    }

    /**
     * @param mixed $lisDBServerPortDevelopment
     */
    public function setLisDBServerPortDevelopment($lisDBServerPortDevelopment)
    {
        $this->lisDBServerPortDevelopment = $lisDBServerPortDevelopment;
    }

    /**
     * @return mixed
     */
    public function getLisDBServerPortDevelopment()
    {
        return $this->lisDBServerPortDevelopment;
    }

    /**
     * @param mixed $lisDBServerPortTest
     */
    public function setLisDBServerPortTest($lisDBServerPortTest)
    {
        $this->lisDBServerPortTest = $lisDBServerPortTest;
    }

    /**
     * @return mixed
     */
    public function getLisDBServerPortTest()
    {
        return $this->lisDBServerPortTest;
    }

//    /**
//     * @return mixed
//     */
//    public function getClientEmailFellApp()
//    {
//        return $this->clientEmailFellApp;
//    }
//
//    /**
//     * @param mixed $clientEmailFellApp
//     */
//    public function setClientEmailFellApp($clientEmailFellApp)
//    {
//        $this->clientEmailFellApp = $clientEmailFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getP12KeyPathFellApp()
//    {
//        return $this->p12KeyPathFellApp;
//    }
//
//    /**
//     * @param mixed $p12KeyPathFellApp
//     */
//    public function setP12KeyPathFellApp($p12KeyPathFellApp)
//    {
//        $this->p12KeyPathFellApp = $p12KeyPathFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getUserImpersonateEmailFellApp()
//    {
//        return $this->userImpersonateEmailFellApp;
//    }
//
//    /**
//     * @param mixed $userImpersonateEmailFellApp
//     */
//    public function setUserImpersonateEmailFellApp($userImpersonateEmailFellApp)
//    {
//        $this->userImpersonateEmailFellApp = $userImpersonateEmailFellApp;
//    }


//    /**
//     * @return mixed
//     */
//    public function getLocalInstitutionFellApp()
//    {
//        return $this->localInstitutionFellApp;
//    }
//
//    /**
//     * @param mixed $localInstitutionFellApp
//     */
//    public function setLocalInstitutionFellApp($localInstitutionFellApp)
//    {
//        $this->localInstitutionFellApp = $localInstitutionFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getIdentificationUploadLetterFellApp()
//    {
//        return $this->identificationUploadLetterFellApp;
//    }
//
//    /**
//     * @param mixed $identificationUploadLetterFellApp
//     */
//    public function setIdentificationUploadLetterFellApp($identificationUploadLetterFellApp)
//    {
//        $this->identificationUploadLetterFellApp = $identificationUploadLetterFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getDeleteImportedAplicationsFellApp()
//    {
//        return $this->deleteImportedAplicationsFellApp;
//    }
//
//    /**
//     * @param mixed $deleteImportedAplicationsFellApp
//     */
//    public function setDeleteImportedAplicationsFellApp($deleteImportedAplicationsFellApp)
//    {
//        $this->deleteImportedAplicationsFellApp = $deleteImportedAplicationsFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getDeleteOldAplicationsFellApp()
//    {
//        return $this->deleteOldAplicationsFellApp;
//    }
//
//    /**
//     * @param mixed $deleteOldAplicationsFellApp
//     */
//    public function setDeleteOldAplicationsFellApp($deleteOldAplicationsFellApp)
//    {
//        $this->deleteOldAplicationsFellApp = $deleteOldAplicationsFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getSpreadsheetsPathFellApp()
//    {
//        return $this->spreadsheetsPathFellApp;
//    }
//
//    /**
//     * @param mixed $spreadsheetsPathFellApp
//     */
//    public function setSpreadsheetsPathFellApp($spreadsheetsPathFellApp)
//    {
//        $this->spreadsheetsPathFellApp = $spreadsheetsPathFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getApplicantsUploadPathFellApp()
//    {
//        return $this->applicantsUploadPathFellApp;
//    }
//
//    /**
//     * @param mixed $applicantsUploadPathFellApp
//     */
//    public function setApplicantsUploadPathFellApp($applicantsUploadPathFellApp)
//    {
//        $this->applicantsUploadPathFellApp = $applicantsUploadPathFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getYearsOldAplicationsFellApp()
//    {
//        return $this->yearsOldAplicationsFellApp;
//    }
//
//    /**
//     * @param mixed $yearsOldAplicationsFellApp
//     */
//    public function setYearsOldAplicationsFellApp($yearsOldAplicationsFellApp)
//    {
//        $this->yearsOldAplicationsFellApp = $yearsOldAplicationsFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getApplicationPageLinkFellApp()
//    {
//        return $this->applicationPageLinkFellApp;
//    }
//
//    /**
//     * @param mixed $applicationPageLinkFellApp
//     */
//    public function setApplicationPageLinkFellApp($applicationPageLinkFellApp)
//    {
//        $this->applicationPageLinkFellApp = $applicationPageLinkFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getReportsUploadPathFellApp()
//    {
//        return $this->reportsUploadPathFellApp;
//    }
//
//    /**
//     * @param mixed $reportsUploadPathFellApp
//     */
//    public function setReportsUploadPathFellApp($reportsUploadPathFellApp)
//    {
//        $this->reportsUploadPathFellApp = $reportsUploadPathFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getGoogleDriveApiUrlFellApp()
//    {
//        return $this->googleDriveApiUrlFellApp;
//    }
//
//    /**
//     * @param mixed $googleDriveApiUrlFellApp
//     */
//    public function setGoogleDriveApiUrlFellApp($googleDriveApiUrlFellApp)
//    {
//        $this->googleDriveApiUrlFellApp = $googleDriveApiUrlFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getCodeGoogleFormFellApp()
//    {
//        return $this->codeGoogleFormFellApp;
//    }
//
//    /**
//     * @param mixed $codeGoogleFormFellApp
//     */
//    public function setCodeGoogleFormFellApp($codeGoogleFormFellApp)
//    {
//        $this->codeGoogleFormFellApp = $codeGoogleFormFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getTemplateIdFellApp()
//    {
//        return $this->templateIdFellApp;
//    }
//
//    /**
//     * @param mixed $templateIdFellApp
//     */
//    public function setTemplateIdFellApp($templateIdFellApp)
//    {
//        $this->templateIdFellApp = $templateIdFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getBackupFileIdFellApp()
//    {
//        return $this->backupFileIdFellApp;
//    }
//
//    /**
//     * @param mixed $backupFileIdFellApp
//     */
//    public function setBackupFileIdFellApp($backupFileIdFellApp)
//    {
//        $this->backupFileIdFellApp = $backupFileIdFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getFolderIdFellApp()
//    {
//        return $this->folderIdFellApp;
//    }
//
//    /**
//     * @param mixed $folderIdFellApp
//     */
//    public function setFolderIdFellApp($folderIdFellApp)
//    {
//        $this->folderIdFellApp = $folderIdFellApp;
//    }

//    /**
//     * @return mixed
//     */
//    public function getConfigFileFolderIdFellApp()
//    {
//        return $this->configFileFolderIdFellApp;
//    }
//
//    /**
//     * @param mixed $configFileFolderIdFellApp
//     */
//    public function setConfigFileFolderIdFellApp($configFileFolderIdFellApp)
//    {
//        $this->configFileFolderIdFellApp = $configFileFolderIdFellApp;
//    }

    /**
     * @return mixed
     */
    public function getBackupUpdateDatetimeFellApp()
    {
        return $this->backupUpdateDatetimeFellApp;
    }

    /**
     * @param mixed $backupUpdateDatetimeFellApp
     */
    public function setBackupUpdateDatetimeFellApp($backupUpdateDatetimeFellApp)
    {
        $this->backupUpdateDatetimeFellApp = $backupUpdateDatetimeFellApp;
    }

    /**
     * @return mixed
     */
    public function getVacrequploadpath()
    {
        return $this->vacrequploadpath;
    }

    /**
     * @param mixed $vacrequploadpath
     */
    public function setVacrequploadpath($vacrequploadpath)
    {
        $this->vacrequploadpath = $vacrequploadpath;
    }

    /**
     * @return mixed
     */
    public function getTransresuploadpath()
    {
        return $this->transresuploadpath;
    }

    /**
     * @param mixed $transresuploadpath
     */
    public function setTransresuploadpath($transresuploadpath)
    {
        $this->transresuploadpath = $transresuploadpath;
    }

    /**
     * @return mixed
     */
    public function getCallloguploadpath()
    {
        return $this->callloguploadpath;
    }

    /**
     * @param mixed $callloguploadpath
     */
    public function setCallloguploadpath($callloguploadpath)
    {
        $this->callloguploadpath = $callloguploadpath;
    }

    /**
     * @return mixed
     */
    public function getCrnuploadpath()
    {
        return $this->crnuploadpath;
    }

    /**
     * @param mixed $crnuploadpath
     */
    public function setCrnuploadpath($crnuploadpath)
    {
        $this->crnuploadpath = $crnuploadpath;
    }

    /**
     * @return mixed
     */
    public function getAcademicYearStart()
    {
        return $this->academicYearStart;
    }

    /**
     * @param mixed $academicYearStart
     */
    public function setAcademicYearStart($academicYearStart)
    {
        $this->academicYearStart = $academicYearStart;
    }

    /**
     * @return mixed
     */
    public function getAcademicYearEnd()
    {
        return $this->academicYearEnd;
    }

    /**
     * @param mixed $academicYearEnd
     */
    public function setAcademicYearEnd($academicYearEnd)
    {
        $this->academicYearEnd = $academicYearEnd;
    }

//    /**
//     * @return mixed
//     */
//    public function getHolidaysUrl()
//    {
//        return $this->holidaysUrl;
//    }
//
//    /**
//     * @param mixed $holidaysUrl
//     */
//    public function setHolidaysUrl($holidaysUrl)
//    {
//        $this->holidaysUrl = $holidaysUrl;
//    }

//    /**
//     * @return mixed
//     */
//    public function getVacationAccruedDaysPerMonth()
//    {
//        return $this->vacationAccruedDaysPerMonth;
//    }
//
//    /**
//     * @param mixed $vacationAccruedDaysPerMonth
//     */
//    public function setVacationAccruedDaysPerMonth($vacationAccruedDaysPerMonth)
//    {
//        $this->vacationAccruedDaysPerMonth = $vacationAccruedDaysPerMonth;
//    }

    /**
     * @return mixed
     */
    public function getLiveSiteRootUrl()
    {
        return $this->liveSiteRootUrl;
    }

    /**
     * @param mixed $liveSiteRootUrl
     */
    public function setLiveSiteRootUrl($liveSiteRootUrl)
    {
        $this->liveSiteRootUrl = $liveSiteRootUrl;
    }

    /**
     * @return mixed
     */
    public function getEnableMetaphone()
    {
        return $this->enableMetaphone;
    }

    /**
     * @param mixed $enableMetaphone
     */
    public function setEnableMetaphone($enableMetaphone)
    {
        $this->enableMetaphone = $enableMetaphone;
    }

    /**
     * @return mixed
     */
    public function getPathMetaphone()
    {
        return $this->pathMetaphone;
    }

    /**
     * @param mixed $pathMetaphone
     */
    public function setPathMetaphone($pathMetaphone)
    {
        $this->pathMetaphone = $pathMetaphone;
    }

    /**
     * @return mixed
     */
    public function getCalllogResources()
    {
        return $this->calllogResources;
    }

    /**
     * @param mixed $calllogResources
     */
    public function setCalllogResources($calllogResources)
    {
        $this->calllogResources = $calllogResources;
    }

    /**
     * @return mixed
     */
    public function getLoginInstruction()
    {
        return $this->loginInstruction;
    }

    /**
     * @param mixed $loginInstruction
     */
    public function setLoginInstruction($loginInstruction)
    {
        $this->loginInstruction = $loginInstruction;
    }

    /**
     * @return mixed
     */
    public function getInitialConfigurationCompleted()
    {
        return $this->initialConfigurationCompleted;
    }

    /**
     * @param mixed $initialConfigurationCompleted
     */
    public function setInitialConfigurationCompleted($initialConfigurationCompleted)
    {
        $this->initialConfigurationCompleted = $initialConfigurationCompleted;
    }

    ////////////////////// third party software //////////////////////////
    /////////////////////// WINDOWS /////////////////////////
    /**
     * @return mixed
     */
    public function getLibreOfficeConvertToPDFArgumentsdFellApp()
    {
        return $this->libreOfficeConvertToPDFArgumentsdFellApp;
    }

    /**
     * @param mixed $libreOfficeConvertToPDFArgumentsdFellApp
     */
    public function setLibreOfficeConvertToPDFArgumentsdFellApp($libreOfficeConvertToPDFArgumentsdFellApp)
    {
        $this->libreOfficeConvertToPDFArgumentsdFellApp = $libreOfficeConvertToPDFArgumentsdFellApp;
    }

    /**
     * @return mixed
     */
    public function getLibreOfficeConvertToPDFFilenameFellApp()
    {
        return $this->libreOfficeConvertToPDFFilenameFellApp;
    }

    /**
     * @param mixed $libreOfficeConvertToPDFFilenameFellApp
     */
    public function setLibreOfficeConvertToPDFFilenameFellApp($libreOfficeConvertToPDFFilenameFellApp)
    {
        $this->libreOfficeConvertToPDFFilenameFellApp = $libreOfficeConvertToPDFFilenameFellApp;
    }

    /**
     * @return mixed
     */
    public function getLibreOfficeConvertToPDFPathFellApp()
    {
        return $this->libreOfficeConvertToPDFPathFellApp;
    }

    /**
     * @param mixed $libreOfficeConvertToPDFPathFellApp
     */
    public function setLibreOfficeConvertToPDFPathFellApp($libreOfficeConvertToPDFPathFellApp)
    {
        $this->libreOfficeConvertToPDFPathFellApp = $libreOfficeConvertToPDFPathFellApp;
    }

    /**
     * @return mixed
     */
    public function getPdftkPathFellApp()
    {
        return $this->pdftkPathFellApp;
    }

    /**
     * @param mixed $pdftkPathFellApp
     */
    public function setPdftkPathFellApp($pdftkPathFellApp)
    {
        $this->pdftkPathFellApp = $pdftkPathFellApp;
    }

    /**
     * @return mixed
     */
    public function getGsPathFellApp()
    {
        return $this->gsPathFellApp;
    }

    /**
     * @param mixed $gsPathFellApp
     */
    public function setGsPathFellApp($gsPathFellApp)
    {
        $this->gsPathFellApp = $gsPathFellApp;
    }

    /**
     * @return mixed
     */
    public function getGsFilenameFellApp()
    {
        return $this->gsFilenameFellApp;
    }

    /**
     * @param mixed $gsFilenameFellApp
     */
    public function setGsFilenameFellApp($gsFilenameFellApp)
    {
        $this->gsFilenameFellApp = $gsFilenameFellApp;
    }

    /**
     * @return mixed
     */
    public function getGsArgumentsFellApp()
    {
        return $this->gsArgumentsFellApp;
    }

    /**
     * @param mixed $gsArgumentsFellApp
     */
    public function setGsArgumentsFellApp($gsArgumentsFellApp)
    {
        $this->gsArgumentsFellApp = $gsArgumentsFellApp;
    }

    /**
     * @return mixed
     */
    public function getPdftkFilenameFellApp()
    {
        return $this->pdftkFilenameFellApp;
    }

    /**
     * @param mixed $pdftkFilenameFellApp
     */
    public function setPdftkFilenameFellApp($pdftkFilenameFellApp)
    {
        $this->pdftkFilenameFellApp = $pdftkFilenameFellApp;
    }

    /**
     * @return mixed
     */
    public function getPdftkArgumentsFellApp()
    {
        return $this->pdftkArgumentsFellApp;
    }

    /**
     * @param mixed $pdftkArgumentsFellApp
     */
    public function setPdftkArgumentsFellApp($pdftkArgumentsFellApp)
    {
        $this->pdftkArgumentsFellApp = $pdftkArgumentsFellApp;
    }

    /////////////// LINUX /////////////////
    /**
     * @return mixed
     */
    public function getLibreOfficeConvertToPDFPathFellAppLinux()
    {
        return $this->libreOfficeConvertToPDFPathFellAppLinux;
    }

    /**
     * @param mixed $libreOfficeConvertToPDFPathFellAppLinux
     */
    public function setLibreOfficeConvertToPDFPathFellAppLinux($libreOfficeConvertToPDFPathFellAppLinux)
    {
        $this->libreOfficeConvertToPDFPathFellAppLinux = $libreOfficeConvertToPDFPathFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getLibreOfficeConvertToPDFFilenameFellAppLinux()
    {
        return $this->libreOfficeConvertToPDFFilenameFellAppLinux;
    }

    /**
     * @param mixed $libreOfficeConvertToPDFFilenameFellAppLinux
     */
    public function setLibreOfficeConvertToPDFFilenameFellAppLinux($libreOfficeConvertToPDFFilenameFellAppLinux)
    {
        $this->libreOfficeConvertToPDFFilenameFellAppLinux = $libreOfficeConvertToPDFFilenameFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getLibreOfficeConvertToPDFArgumentsdFellAppLinux()
    {
        return $this->libreOfficeConvertToPDFArgumentsdFellAppLinux;
    }

    /**
     * @param mixed $libreOfficeConvertToPDFArgumentsdFellAppLinux
     */
    public function setLibreOfficeConvertToPDFArgumentsdFellAppLinux($libreOfficeConvertToPDFArgumentsdFellAppLinux)
    {
        $this->libreOfficeConvertToPDFArgumentsdFellAppLinux = $libreOfficeConvertToPDFArgumentsdFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getPdftkPathFellAppLinux()
    {
        return $this->pdftkPathFellAppLinux;
    }

    /**
     * @param mixed $pdftkPathFellAppLinux
     */
    public function setPdftkPathFellAppLinux($pdftkPathFellAppLinux)
    {
        $this->pdftkPathFellAppLinux = $pdftkPathFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getPdftkFilenameFellAppLinux()
    {
        return $this->pdftkFilenameFellAppLinux;
    }

    /**
     * @param mixed $pdftkFilenameFellAppLinux
     */
    public function setPdftkFilenameFellAppLinux($pdftkFilenameFellAppLinux)
    {
        $this->pdftkFilenameFellAppLinux = $pdftkFilenameFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getPdftkArgumentsFellAppLinux()
    {
        return $this->pdftkArgumentsFellAppLinux;
    }

    /**
     * @param mixed $pdftkArgumentsFellAppLinux
     */
    public function setPdftkArgumentsFellAppLinux($pdftkArgumentsFellAppLinux)
    {
        $this->pdftkArgumentsFellAppLinux = $pdftkArgumentsFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getGsPathFellAppLinux()
    {
        return $this->gsPathFellAppLinux;
    }

    /**
     * @param mixed $gsPathFellAppLinux
     */
    public function setGsPathFellAppLinux($gsPathFellAppLinux)
    {
        $this->gsPathFellAppLinux = $gsPathFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getGsFilenameFellAppLinux()
    {
        return $this->gsFilenameFellAppLinux;
    }

    /**
     * @param mixed $gsFilenameFellAppLinux
     */
    public function setGsFilenameFellAppLinux($gsFilenameFellAppLinux)
    {
        $this->gsFilenameFellAppLinux = $gsFilenameFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getGsArgumentsFellAppLinux()
    {
        return $this->gsArgumentsFellAppLinux;
    }

    /**
     * @param mixed $gsArgumentsFellAppLinux
     */
    public function setGsArgumentsFellAppLinux($gsArgumentsFellAppLinux)
    {
        $this->gsArgumentsFellAppLinux = $gsArgumentsFellAppLinux;
    }

    /**
     * @return mixed
     */
    public function getWkhtmltopdfpath()
    {
        return $this->wkhtmltopdfpath;
    }

    /**
     * @param mixed $wkhtmltopdfpath
     */
    public function setWkhtmltopdfpath($wkhtmltopdfpath)
    {
        $this->wkhtmltopdfpath = $wkhtmltopdfpath;
    }

    /**
     * @return mixed
     */
    public function getWkhtmltopdfpathLinux()
    {
        return $this->wkhtmltopdfpathLinux;
    }

    /**
     * @param mixed $wkhtmltopdfpathLinux
     */
    public function setWkhtmltopdfpathLinux($wkhtmltopdfpathLinux)
    {
        $this->wkhtmltopdfpathLinux = $wkhtmltopdfpathLinux;
    }
    ////////////////////// EOF third party software //////////////////////////

    /**
     * @return mixed
     */
    public function getNetworkDrivePath()
    {
        return $this->networkDrivePath;
    }

    /**
     * @param mixed $networkDrivePath
     */
    public function setNetworkDrivePath($networkDrivePath)
    {
        $this->networkDrivePath = $networkDrivePath;
    }

    /**
     * @return mixed
     */
    public function getPermittedFailedLoginAttempt()
    {
        return $this->permittedFailedLoginAttempt;
    }

    /**
     * @param mixed $permittedFailedLoginAttempt
     */
    public function setPermittedFailedLoginAttempt($permittedFailedLoginAttempt)
    {
        $this->permittedFailedLoginAttempt = $permittedFailedLoginAttempt;
    }

    /**
     * @return mixed
     */
    public function getCaptchaSiteKey()
    {
        return $this->captchaSiteKey;
    }

    /**
     * @param mixed $captchaSiteKey
     */
    public function setCaptchaSiteKey($captchaSiteKey)
    {
        $this->captchaSiteKey = $captchaSiteKey;
    }

    /**
     * @return mixed
     */
    public function getCaptchaSecretKey()
    {
        return $this->captchaSecretKey;
    }

    /**
     * @param mixed $captchaSecretKey
     */
    public function setCaptchaSecretKey($captchaSecretKey)
    {
        $this->captchaSecretKey = $captchaSecretKey;
    }

    /**
     * @return mixed
     */
    public function getCaptchaEnabled()
    {
        return $this->captchaEnabled;
    }

    /**
     * @param mixed $captchaEnabled
     */
    public function setCaptchaEnabled($captchaEnabled)
    {
        $this->captchaEnabled = $captchaEnabled;
    }

    /**
     * @return mixed
     */
    public function getNoticeAttemptingPasswordResetLDAP()
    {
        return $this->noticeAttemptingPasswordResetLDAP;
    }

    /**
     * @param mixed $noticeAttemptingPasswordResetLDAP
     */
    public function setNoticeAttemptingPasswordResetLDAP($noticeAttemptingPasswordResetLDAP)
    {
        $this->noticeAttemptingPasswordResetLDAP = $noticeAttemptingPasswordResetLDAP;
    }

    /**
     * @return mixed
     */
    public function getNoticeSignUpNoCwid()
    {
        return $this->noticeSignUpNoCwid;
    }

    /**
     * @param mixed $noticeSignUpNoCwid
     */
    public function setNoticeSignUpNoCwid($noticeSignUpNoCwid)
    {
        $this->noticeSignUpNoCwid = $noticeSignUpNoCwid;
    }

    /**
     * @return mixed
     */
    public function getNoticeHasLdapAccount()
    {
        return $this->noticeHasLdapAccount;
    }

    /**
     * @param mixed $noticeHasLdapAccount
     */
    public function setNoticeHasLdapAccount($noticeHasLdapAccount)
    {
        $this->noticeHasLdapAccount = $noticeHasLdapAccount;
    }

    /**
     * @return mixed
     */
    public function getNoticeLdapName()
    {
        return $this->noticeLdapName;
    }

    /**
     * @param mixed $noticeLdapName
     */
    public function setNoticeLdapName($noticeLdapName)
    {
        $this->noticeLdapName = $noticeLdapName;
    }

    /**
     * @return mixed
     */
    public function getCalllogSiteParameter()
    {
        return $this->calllogSiteParameter;
    }

    /**
     * @param mixed $calllogSiteParameter
     */
    public function setCalllogSiteParameter($calllogSiteParameter)
    {
        $this->calllogSiteParameter = $calllogSiteParameter;
    }

    /**
     * @return mixed
     */
    public function getCrnSiteParameter()
    {
        return $this->crnSiteParameter;
    }

    /**
     * @param mixed $crnSiteParameter
     */
    public function setCrnSiteParameter($crnSiteParameter)
    {
        $this->crnSiteParameter = $crnSiteParameter;
    }

    /**
     * @return mixed
     */
    public function getNavbarFilterInstitution1()
    {
        return $this->navbarFilterInstitution1;
    }

    /**
     * @param mixed $navbarFilterInstitution1
     */
    public function setNavbarFilterInstitution1($navbarFilterInstitution1)
    {
        $this->navbarFilterInstitution1 = $navbarFilterInstitution1;
    }

    /**
     * @return mixed
     */
    public function getNavbarFilterInstitution2()
    {
        return $this->navbarFilterInstitution2;
    }

    /**
     * @param mixed $navbarFilterInstitution2
     */
    public function setNavbarFilterInstitution2($navbarFilterInstitution2)
    {
        $this->navbarFilterInstitution2 = $navbarFilterInstitution2;
    }

    /**
     * @return mixed
     */
    public function getDefaultDeidentifierAccessionType()
    {
        return $this->defaultDeidentifierAccessionType;
    }

    /**
     * @param mixed $defaultDeidentifierAccessionType
     */
    public function setDefaultDeidentifierAccessionType($defaultDeidentifierAccessionType)
    {
        $this->defaultDeidentifierAccessionType = $defaultDeidentifierAccessionType;
    }

    /**
     * @return mixed
     */
    public function getDefaultScanAccessionType()
    {
        return $this->defaultScanAccessionType;
    }

    /**
     * @param mixed $defaultScanAccessionType
     */
    public function setDefaultScanAccessionType($defaultScanAccessionType)
    {
        $this->defaultScanAccessionType = $defaultScanAccessionType;
    }

    /**
     * @return mixed
     */
    public function getDefaultScanMrnType()
    {
        return $this->defaultScanMrnType;
    }

    /**
     * @param mixed $defaultScanMrnType
     */
    public function setDefaultScanMrnType($defaultScanMrnType)
    {
        $this->defaultScanMrnType = $defaultScanMrnType;
    }

    /**
     * @return mixed
     */
    public function getDefaultScanDelivery()
    {
        return $this->defaultScanDelivery;
    }

    /**
     * @param mixed $defaultScanDelivery
     */
    public function setDefaultScanDelivery($defaultScanDelivery)
    {
        $this->defaultScanDelivery = $defaultScanDelivery;
    }

//    /**
//     * @return mixed
//     */
//    public function getDefaultInstitutionalPHIScope()
//    {
//        return $this->defaultInstitutionalPHIScope;
//    }
//
//    /**
//     * @param mixed $defaultInstitutionalPHIScope
//     */
//    public function setDefaultInstitutionalPHIScope($defaultInstitutionalPHIScope)
//    {
//        $this->defaultInstitutionalPHIScope = $defaultInstitutionalPHIScope;
//    }

    /**
     * @return mixed
     */
    public function getDefaultOrganizationRecipient()
    {
        return $this->defaultOrganizationRecipient;
    }

    /**
     * @param mixed $defaultOrganizationRecipient
     */
    public function setDefaultOrganizationRecipient($defaultOrganizationRecipient)
    {
        $this->defaultOrganizationRecipient = $defaultOrganizationRecipient;
    }

    /**
     * @return mixed
     */
    public function getDefaultScanner()
    {
        return $this->defaultScanner;
    }

    /**
     * @param mixed $defaultScanner
     */
    public function setDefaultScanner($defaultScanner)
    {
        $this->defaultScanner = $defaultScanner;
    }

    /**
     * @return mixed
     */
    public function getPhantomjs()
    {
        return $this->phantomjs;
    }

    /**
     * @param mixed $phantomjs
     */
    public function setPhantomjs($phantomjs)
    {
        $this->phantomjs = $phantomjs;
    }

    /**
     * @return mixed
     */
    public function getPhantomjsLinux()
    {
        return $this->phantomjsLinux;
    }

    /**
     * @param mixed $phantomjsLinux
     */
    public function setPhantomjsLinux($phantomjsLinux)
    {
        $this->phantomjsLinux = $phantomjsLinux;
    }

    /**
     * @return mixed
     */
    public function getRasterize()
    {
        return $this->rasterize;
    }

    /**
     * @param mixed $rasterize
     */
    public function setRasterize($rasterize)
    {
        $this->rasterize = $rasterize;
    }

    /**
     * @return mixed
     */
    public function getRasterizeLinux()
    {
        return $this->rasterizeLinux;
    }

    /**
     * @param mixed $rasterizeLinux
     */
    public function setRasterizeLinux($rasterizeLinux)
    {
        $this->rasterizeLinux = $rasterizeLinux;
    }

    /**
     * @return mixed
     */
    public function getConnectionChannel()
    {
        return $this->connectionChannel;
    }

    /**
     * @param mixed $connectionChannel
     */
    public function setConnectionChannel($connectionChannel)
    {
        $this->connectionChannel = $connectionChannel;
    }

    /**
     * @return mixed
     */
    public function getUrlConnectionChannel()
    {
        return $this->urlConnectionChannel;
    }

    /**
     * @param mixed $urlConnectionChannel
     */
    public function setUrlConnectionChannel($urlConnectionChannel)
    {
        $this->urlConnectionChannel = $urlConnectionChannel;
    }

    /**
     * @return mixed
     */
    public function getTransresProjectSelectionNote()
    {
        return $this->transresProjectSelectionNote;
    }

    /**
     * @param mixed $transresProjectSelectionNote
     */
    public function setTransresProjectSelectionNote($transresProjectSelectionNote)
    {
        $this->transresProjectSelectionNote = $transresProjectSelectionNote;
    }

    /**
     * @return mixed
     */
    public function getTransresDashboardInstitution()
    {
        return $this->transresDashboardInstitution;
    }

    /**
     * @param mixed $transresDashboardInstitution
     */
    public function setTransresDashboardInstitution($transresDashboardInstitution)
    {
        $this->transresDashboardInstitution = $transresDashboardInstitution;
    }

    /**
     * @return mixed
     */
    public function getTransresHumanSubjectName()
    {
        return $this->transresHumanSubjectName;
    }

    /**
     * @param mixed $transresHumanSubjectName
     */
    public function setTransresHumanSubjectName($transresHumanSubjectName)
    {
        $this->transresHumanSubjectName = $transresHumanSubjectName;
    }

    /**
     * @return mixed
     */
    public function getTransresAnimalSubjectName()
    {
        return $this->transresAnimalSubjectName;
    }

    /**
     * @param mixed $transresAnimalSubjectName
     */
    public function setTransresAnimalSubjectName($transresAnimalSubjectName)
    {
        $this->transresAnimalSubjectName = $transresAnimalSubjectName;
    }

    /**
     * @return mixed
     */
    public function getTransresBusinessEntityName()
    {
        return $this->transresBusinessEntityName;
    }

    /**
     * @param mixed $transresBusinessEntityName
     */
    public function setTransresBusinessEntityName($transresBusinessEntityName)
    {
        $this->transresBusinessEntityName = $transresBusinessEntityName;
    }

    /**
     * @return mixed
     */
    public function getTransresBusinessEntityAbbreviation()
    {
        return $this->transresBusinessEntityAbbreviation;
    }

    /**
     * @param mixed $transresBusinessEntityAbbreviation
     */
    public function setTransresBusinessEntityAbbreviation($transresBusinessEntityAbbreviation)
    {
        $this->transresBusinessEntityAbbreviation = $transresBusinessEntityAbbreviation;
    }

    /**
     * @return mixed
     */
    public function getEmailCriticalError()
    {
        return $this->emailCriticalError;
    }

    /**
     * @param mixed $emailCriticalError
     */
    public function setEmailCriticalError($emailCriticalError)
    {
        $this->emailCriticalError = $emailCriticalError;
    }

    /**
     * @return mixed
     */
    public function getRestartServerErrorCounter()
    {
        return $this->restartServerErrorCounter;
    }

    /**
     * @param mixed $restartServerErrorCounter
     */
    public function setRestartServerErrorCounter($restartServerErrorCounter)
    {
        $this->restartServerErrorCounter = $restartServerErrorCounter;
    }

    /**
     * @return mixed
     */
    public function getRemoteAccessUrl()
    {
        return $this->remoteAccessUrl;
    }

    /**
     * @param mixed $remoteAccessUrl
     */
    public function setRemoteAccessUrl($remoteAccessUrl)
    {
        $this->remoteAccessUrl = $remoteAccessUrl;
    }

    public function getEmailCriticalErrorExceptionUsers()
    {
        return $this->emailCriticalErrorExceptionUsers;
    }
    public function addEmailCriticalErrorExceptionUser($item)
    {
        if( $item && !$this->emailCriticalErrorExceptionUsers->contains($item) ) {
            $this->emailCriticalErrorExceptionUsers->add($item);
        }
        return $this;
    }
    public function removeEmailCriticalErrorExceptionUser($item)
    {
        $this->emailCriticalErrorExceptionUsers->removeElement($item);
    }

    /**
     * @return mixed
     */
    public function getResappuploadpath()
    {
        return $this->resappuploadpath;
    }

    /**
     * @param mixed $resappuploadpath
     */
    public function setResappuploadpath($resappuploadpath)
    {
        $this->resappuploadpath = $resappuploadpath;
    }

    /**
     * @return mixed
     */
    public function getResappSiteParameter()
    {
        return $this->resappSiteParameter;
    }

    /**
     * @param mixed $resappSiteParameter
     */
    public function setResappSiteParameter($resappSiteParameter)
    {
        $this->resappSiteParameter = $resappSiteParameter;
    }

    /**
     * @return mixed
     */
    public function getVacreqSiteParameter()
    {
        return $this->vacreqSiteParameter;
    }

    /**
     * @param mixed $vacreqSiteParameter
     */
    public function setVacreqSiteParameter($vacreqSiteParameter)
    {
        $this->vacreqSiteParameter = $vacreqSiteParameter;
    }

    /**
     * @return mixed
     */
    public function getTelephonySiteParameter()
    {
        return $this->telephonySiteParameter;
    }

    /**
     * @param mixed $telephonySiteParameter
     */
    public function setTelephonySiteParameter($telephonySiteParameter)
    {
        $this->telephonySiteParameter = $telephonySiteParameter;
    }

    /**
     * @return mixed
     */
    public function getDashboardSiteParameter()
    {
        return $this->dashboardSiteParameter;
    }
    /**
     * @param mixed $dashboardSiteParameter
     */
    public function setDashboardSiteParameter($dashboardSiteParameter)
    {
        $this->dashboardSiteParameter = $dashboardSiteParameter;
    }

    /**
     * @return mixed
     */
    public function getExternalMonitorUrl()
    {
        return $this->externalMonitorUrl;
    }

    /**
     * @param mixed $externalMonitorUrl
     */
    public function setExternalMonitorUrl($externalMonitorUrl)
    {
        $this->externalMonitorUrl = $externalMonitorUrl;
    }

    /**
     * @return mixed
     */
    public function getMonitorScript()
    {
        return $this->monitorScript;
    }

    /**
     * @param mixed $monitorScript
     */
    public function setMonitorScript($monitorScript)
    {
        $this->monitorScript = $monitorScript;
    }

    /**
     * @return mixed
     */
    public function getMonitorCheckInterval()
    {
        return $this->monitorCheckInterval;
    }

    /**
     * @param mixed $monitorCheckInterval
     */
    public function setMonitorCheckInterval($monitorCheckInterval)
    {
        $this->monitorCheckInterval = $monitorCheckInterval;
    }

    /**
     * @return mixed
     */
    public function getSendEmailUserAdded()
    {
        return $this->sendEmailUserAdded;
    }

    /**
     * @param mixed $sendEmailUserAdded
     */
    public function setSendEmailUserAdded($sendEmailUserAdded)
    {
        $this->sendEmailUserAdded = $sendEmailUserAdded;
    }

    /**
     * @return mixed
     */
    public function getDbBackupConfig()
    {
        return $this->dbBackupConfig;
    }

    /**
     * @param mixed $dbBackupConfig
     */
    public function setDbBackupConfig($dbBackupConfig)
    {
        $this->dbBackupConfig = $dbBackupConfig;
    }

    /**
     * @return mixed
     */
    public function getFilesBackupConfig()
    {
        return $this->filesBackupConfig;
    }

    /**
     * @param mixed $filesBackupConfig
     */
    public function setFilesBackupConfig($filesBackupConfig)
    {
        $this->filesBackupConfig = $filesBackupConfig;
    }

    /**
     * @return mixed
     */
    public function getAuthUserGroup()
    {
        return $this->authUserGroup;
    }

    /**
     * @param mixed $authUserGroup
     */
    public function setAuthUserGroup($authUserGroup)
    {
        $this->authUserGroup = $authUserGroup;
    }

    /**
     * @return mixed
     */
    public function getAuthServerNetwork()
    {
        return $this->authServerNetwork;
    }

    /**
     * @param mixed $authServerNetwork
     */
    public function setAuthServerNetwork($authServerNetwork)
    {
        $this->authServerNetwork = $authServerNetwork;
    }

    /**
     * @return mixed
     */
    public function getAuthPartnerServer()
    {
        return $this->authPartnerServer;
    }

    /**
     * @param mixed $authPartnerServer
     */
    public function setAuthPartnerServer($authPartnerServer)
    {
        $this->authPartnerServer = $authPartnerServer;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param mixed $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * @return mixed
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param mixed $instanceId
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;
    }

    /**
     * @return mixed
     */
    public function getShowTenantsHomepage()
    {
        return $this->showTenantsHomepage;
    }

    /**
     * @param mixed $showTenantsHomepage
     */
    public function setShowTenantsHomepage($showTenantsHomepage)
    {
        $this->showTenantsHomepage = $showTenantsHomepage;
    }

    



//    /**
//     * @return mixed
//     */
//    public function getTenantPrefixUrlSlug()
//    {
//        return $this->tenantPrefixUrlSlug;
//    }
//
//    /**
//     * @param mixed $tenantPrefixUrlSlug
//     */
//    public function setTenantPrefixUrlSlug($tenantPrefixUrlSlug)
//    {
//        $this->tenantPrefixUrlSlug = $tenantPrefixUrlSlug;
//    }



//    public function addHostedUserGroup($item)
//    {
//        if( $item && !$this->hostedUserGroups->contains($item) ) {
//            $this->hostedUserGroups->add($item);
//        }
//
//        return $this;
//    }
//    public function removeHostedUserGroup($item)
//    {
//        $this->hostedUserGroups->removeElement($item);
//    }
//    public function getHostedUserGroups()
//    {
//        return $this->hostedUserGroups;
//    }


    







    

//    /**
//     * @return mixed
//     */
//    public function getMonitorScriptArgs()
//    {
//        return $this->monitorScriptArgs;
//    }
//
//    /**
//     * @param mixed $monitorScriptArgs
//     */
//    public function setMonitorScriptArgs($monitorScriptArgs)
//    {
//        $this->monitorScriptArgs = $monitorScriptArgs;
//    }

    

}