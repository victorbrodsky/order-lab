<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_siteParameters")
 */
class SiteParameters {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Max idle time in minutes
     * @ORM\Column(type="string", nullable=true)
     */
    private $maxIdleTime;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $environment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $siteEmail;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dbServerAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dbServerPort;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dbServerAccountUserName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dbServerAccountPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $dbDatabaseName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $smtpServerAddress;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aDLDAPServerAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aDLDAPServerPort;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aDLDAPServerOu;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aDLDAPServerAccountUserName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aDLDAPServerAccountPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ldapExePath;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ldapExeFilename;


    /**
     * @ORM\OneToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Institution")
     */
    private $autoAssignInstitution;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aperioeSlideManagerDBServerAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aperioeSlideManagerDBServerPort;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aperioeSlideManagerDBUserName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aperioeSlideManagerDBPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $aperioeSlideManagerDBName;


    //Footer
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $institutionurl;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $institutionname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $departmenturl;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $departmentname;


    //Maintanence mode
    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    private $maintenance;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $maintenanceenddate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $maintenancelogoutmsg;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $maintenanceloginmsg;

    //uploads path
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $scanuploadpath;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $employeesuploadpath;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $avataruploadpath;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fellappuploadpath;


    //site titles and messages
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $mainHomeTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $listManagerTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $eventLogTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $siteSettingsTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $contentAboutPage;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $underLoginMsgUser;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $underLoginMsgScan;

    ///////////////////// FELLAPP /////////////////////
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $allowPopulateFellApp;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationSubjectFellApp;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $confirmationBodyFellApp;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $confirmationEmailFellApp;

    /**
     * Client Email to get GoogleSrevice: i.e. '1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com'
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $clientEmailFellApp;

    /**
     * Path to p12 key file: i.e. /../Util/FellowshipApplication-f1d9f98353e5.p12
     * E:\Program Files (x86)\Aperio\Spectrum\htdocs\order\scanorder\Scanorders2\src\Oleg\FellAppBundle\Util
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $p12KeyPathFellApp;

    /**
     * Impersonate user Email: i.e. olegivanov@pathologysystems.org
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $userImpersonateEmailFellApp;

    /**
     * Id of excel file: i.e. 1DN1BEbONKNmFpHU6xBo69YSLjXCnhRy0IbyXrwMzEzc
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $excelIdFellApp;

    /**
     * Local Institution to which every imported application is set: Pathology Fellowship Programs (WCMC)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $localInstitutionFellApp;

    /**
     * [ checkbox ] Delete successfully imported applications from Google Drive
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleteImportedAplicationsFellApp;

    /**
     * checkbox for "Automatically delete downloaded applications that are older than [X] year(s)
     * (set it at 2) [this is to delete old excel sheets that are downloaded from google drive.
     * Make sure it is functional and Google/Excel sheets containing applications older than
     * the amount of years set by this option is auto-deleted along with the linked downloaded documents.
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleteOldAplicationsFellApp;

    /**
     * Used in checkbox for "Automatically delete downloaded applications that are older than [X] year(s)
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $yearsOldAplicationsFellApp;

    /**
     * Path to spreadsheets: i.e. Spreadsheets
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $spreadsheetsPathFellApp;

    /**
     * Path to upload applicants documents: i.e. FellowshipApplicantUploads
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $applicantsUploadPathFellApp;

    /**
     * Link to the Application Page (so the users can click and see how it looks)
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $applicationPageLinkFellApp;
    ///////////////////// EOF FELLAPP /////////////////////

    // Co-Path //
    //Production
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBServerAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBServerPort;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBAccountUserName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBAccountPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $LISName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $LISVersion;


    //Testing
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBServerAddressTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBServerPortTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBAccountUserNameTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBAccountPasswordTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBNameTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $LISNameTest;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $LISVersionTest;


    //Development
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBServerAddressDevelopment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBServerPortDevelopment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBAccountUserNameDevelopment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBAccountPasswordDevelopment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $coPathDBNameDevelopment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $LISNameDevelopment;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $LISVersionDevelopment;



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
     * @param mixed $aperioeSlideManagerDBName
     */
    public function setAperioeSlideManagerDBName($aperioeSlideManagerDBName)
    {
        $this->aperioeSlideManagerDBName = $aperioeSlideManagerDBName;
    }

    /**
     * @return mixed
     */
    public function getAperioeSlideManagerDBName()
    {
        return $this->aperioeSlideManagerDBName;
    }

    /**
     * @param mixed $aperioeSlideManagerDBPassword
     */
    public function setAperioeSlideManagerDBPassword($aperioeSlideManagerDBPassword)
    {
        $this->aperioeSlideManagerDBPassword = $aperioeSlideManagerDBPassword;
    }

    /**
     * @return mixed
     */
    public function getAperioeSlideManagerDBPassword()
    {
        return $this->aperioeSlideManagerDBPassword;
    }

    /**
     * @param mixed $aperioeSlideManagerDBServerAddress
     */
    public function setAperioeSlideManagerDBServerAddress($aperioeSlideManagerDBServerAddress)
    {
        $this->aperioeSlideManagerDBServerAddress = $aperioeSlideManagerDBServerAddress;
    }

    /**
     * @return mixed
     */
    public function getAperioeSlideManagerDBServerAddress()
    {
        return $this->aperioeSlideManagerDBServerAddress;
    }

    /**
     * @param mixed $aperioeSlideManagerDBServerPort
     */
    public function setAperioeSlideManagerDBServerPort($aperioeSlideManagerDBServerPort)
    {
        $this->aperioeSlideManagerDBServerPort = $aperioeSlideManagerDBServerPort;
    }

    /**
     * @return mixed
     */
    public function getAperioeSlideManagerDBServerPort()
    {
        return $this->aperioeSlideManagerDBServerPort;
    }

    /**
     * @param mixed $aperioeSlideManagerDBUserName
     */
    public function setAperioeSlideManagerDBUserName($aperioeSlideManagerDBUserName)
    {
        $this->aperioeSlideManagerDBUserName = $aperioeSlideManagerDBUserName;
    }

    /**
     * @return mixed
     */
    public function getAperioeSlideManagerDBUserName()
    {
        return $this->aperioeSlideManagerDBUserName;
    }

    /**
     * @param mixed $coPathDBAccountPassword
     */
    public function setCoPathDBAccountPassword($coPathDBAccountPassword)
    {
        $this->coPathDBAccountPassword = $coPathDBAccountPassword;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBAccountPassword()
    {
        return $this->coPathDBAccountPassword;
    }

    /**
     * @param mixed $coPathDBAccountUserName
     */
    public function setCoPathDBAccountUserName($coPathDBAccountUserName)
    {
        $this->coPathDBAccountUserName = $coPathDBAccountUserName;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBAccountUserName()
    {
        return $this->coPathDBAccountUserName;
    }

    /**
     * @param mixed $coPathDBName
     */
    public function setCoPathDBName($coPathDBName)
    {
        $this->coPathDBName = $coPathDBName;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBName()
    {
        return $this->coPathDBName;
    }

    /**
     * @param mixed $coPathDBServerAddress
     */
    public function setCoPathDBServerAddress($coPathDBServerAddress)
    {
        $this->coPathDBServerAddress = $coPathDBServerAddress;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBServerAddress()
    {
        return $this->coPathDBServerAddress;
    }

    /**
     * @param mixed $coPathDBServerPort
     */
    public function setCoPathDBServerPort($coPathDBServerPort)
    {
        $this->coPathDBServerPort = $coPathDBServerPort;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBServerPort()
    {
        return $this->coPathDBServerPort;
    }

    /**
     * @param mixed $LISName
     */
    public function setLISName($LISName)
    {
        $this->LISName = $LISName;
    }

    /**
     * @return mixed
     */
    public function getLISName()
    {
        return $this->LISName;
    }

    /**
     * @param mixed $LISVersion
     */
    public function setLISVersion($LISVersion)
    {
        $this->LISVersion = $LISVersion;
    }

    /**
     * @return mixed
     */
    public function getLISVersion()
    {
        return $this->LISVersion;
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

    /**
     * @param mixed $allowPopulateFellApp
     */
    public function setAllowPopulateFellApp($allowPopulateFellApp)
    {
        $this->allowPopulateFellApp = $allowPopulateFellApp;
    }

    /**
     * @return mixed
     */
    public function getAllowPopulateFellApp()
    {
        return $this->allowPopulateFellApp;
    }

    public function getConfirmationSubjectFellApp() {
        return $this->confirmationSubjectFellApp;
    }

    public function setConfirmationSubjectFellApp($confirmationSubjectFellApp) {
        $this->confirmationSubjectFellApp = $confirmationSubjectFellApp;
    }
    
    public function getConfirmationBodyFellApp() {
        return $this->confirmationBodyFellApp;
    }

    public function setConfirmationBodyFellApp($confirmationBodyFellApp) {
        $this->confirmationBodyFellApp = $confirmationBodyFellApp;
    }

    public function getConfirmationEmailFellApp() {
        return $this->confirmationEmailFellApp;
    }

    public function setConfirmationEmailFellApp($confirmationEmailFellApp) {
        $this->confirmationEmailFellApp = $confirmationEmailFellApp;
    }

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
     * @param mixed $coPathDBAccountPasswordDevelopment
     */
    public function setCoPathDBAccountPasswordDevelopment($coPathDBAccountPasswordDevelopment)
    {
        $this->coPathDBAccountPasswordDevelopment = $coPathDBAccountPasswordDevelopment;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBAccountPasswordDevelopment()
    {
        return $this->coPathDBAccountPasswordDevelopment;
    }

    /**
     * @param mixed $coPathDBAccountPasswordTest
     */
    public function setCoPathDBAccountPasswordTest($coPathDBAccountPasswordTest)
    {
        $this->coPathDBAccountPasswordTest = $coPathDBAccountPasswordTest;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBAccountPasswordTest()
    {
        return $this->coPathDBAccountPasswordTest;
    }

    /**
     * @param mixed $coPathDBAccountUserNameDevelopment
     */
    public function setCoPathDBAccountUserNameDevelopment($coPathDBAccountUserNameDevelopment)
    {
        $this->coPathDBAccountUserNameDevelopment = $coPathDBAccountUserNameDevelopment;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBAccountUserNameDevelopment()
    {
        return $this->coPathDBAccountUserNameDevelopment;
    }

    /**
     * @param mixed $coPathDBAccountUserNameTest
     */
    public function setCoPathDBAccountUserNameTest($coPathDBAccountUserNameTest)
    {
        $this->coPathDBAccountUserNameTest = $coPathDBAccountUserNameTest;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBAccountUserNameTest()
    {
        return $this->coPathDBAccountUserNameTest;
    }

    /**
     * @param mixed $coPathDBNameDevelopment
     */
    public function setCoPathDBNameDevelopment($coPathDBNameDevelopment)
    {
        $this->coPathDBNameDevelopment = $coPathDBNameDevelopment;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBNameDevelopment()
    {
        return $this->coPathDBNameDevelopment;
    }

    /**
     * @param mixed $coPathDBNameTest
     */
    public function setCoPathDBNameTest($coPathDBNameTest)
    {
        $this->coPathDBNameTest = $coPathDBNameTest;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBNameTest()
    {
        return $this->coPathDBNameTest;
    }

    /**
     * @param mixed $coPathDBServerAddressDevelopment
     */
    public function setCoPathDBServerAddressDevelopment($coPathDBServerAddressDevelopment)
    {
        $this->coPathDBServerAddressDevelopment = $coPathDBServerAddressDevelopment;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBServerAddressDevelopment()
    {
        return $this->coPathDBServerAddressDevelopment;
    }

    /**
     * @param mixed $coPathDBServerAddressTest
     */
    public function setCoPathDBServerAddressTest($coPathDBServerAddressTest)
    {
        $this->coPathDBServerAddressTest = $coPathDBServerAddressTest;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBServerAddressTest()
    {
        return $this->coPathDBServerAddressTest;
    }

    /**
     * @param mixed $coPathDBServerPortDevelopment
     */
    public function setCoPathDBServerPortDevelopment($coPathDBServerPortDevelopment)
    {
        $this->coPathDBServerPortDevelopment = $coPathDBServerPortDevelopment;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBServerPortDevelopment()
    {
        return $this->coPathDBServerPortDevelopment;
    }

    /**
     * @param mixed $coPathDBServerPortTest
     */
    public function setCoPathDBServerPortTest($coPathDBServerPortTest)
    {
        $this->coPathDBServerPortTest = $coPathDBServerPortTest;
    }

    /**
     * @return mixed
     */
    public function getCoPathDBServerPortTest()
    {
        return $this->coPathDBServerPortTest;
    }

    /**
     * @return mixed
     */
    public function getClientEmailFellApp()
    {
        return $this->clientEmailFellApp;
    }

    /**
     * @param mixed $clientEmailFellApp
     */
    public function setClientEmailFellApp($clientEmailFellApp)
    {
        $this->clientEmailFellApp = $clientEmailFellApp;
    }

    /**
     * @return mixed
     */
    public function getP12KeyPathFellApp()
    {
        return $this->p12KeyPathFellApp;
    }

    /**
     * @param mixed $p12KeyPathFellApp
     */
    public function setP12KeyPathFellApp($p12KeyPathFellApp)
    {
        $this->p12KeyPathFellApp = $p12KeyPathFellApp;
    }

    /**
     * @return mixed
     */
    public function getUserImpersonateEmailFellApp()
    {
        return $this->userImpersonateEmailFellApp;
    }

    /**
     * @param mixed $userImpersonateEmailFellApp
     */
    public function setUserImpersonateEmailFellApp($userImpersonateEmailFellApp)
    {
        $this->userImpersonateEmailFellApp = $userImpersonateEmailFellApp;
    }

    /**
     * @return mixed
     */
    public function getExcelIdFellApp()
    {
        return $this->excelIdFellApp;
    }

    /**
     * @param mixed $excelIdFellApp
     */
    public function setExcelIdFellApp($excelIdFellApp)
    {
        $this->excelIdFellApp = $excelIdFellApp;
    }

    /**
     * @return mixed
     */
    public function getLocalInstitutionFellApp()
    {
        return $this->localInstitutionFellApp;
    }

    /**
     * @param mixed $localInstitutionFellApp
     */
    public function setLocalInstitutionFellApp($localInstitutionFellApp)
    {
        $this->localInstitutionFellApp = $localInstitutionFellApp;
    }

    /**
     * @return mixed
     */
    public function getDeleteImportedAplicationsFellApp()
    {
        return $this->deleteImportedAplicationsFellApp;
    }

    /**
     * @param mixed $deleteImportedAplicationsFellApp
     */
    public function setDeleteImportedAplicationsFellApp($deleteImportedAplicationsFellApp)
    {
        $this->deleteImportedAplicationsFellApp = $deleteImportedAplicationsFellApp;
    }

    /**
     * @return mixed
     */
    public function getDeleteOldAplicationsFellApp()
    {
        return $this->deleteOldAplicationsFellApp;
    }

    /**
     * @param mixed $deleteOldAplicationsFellApp
     */
    public function setDeleteOldAplicationsFellApp($deleteOldAplicationsFellApp)
    {
        $this->deleteOldAplicationsFellApp = $deleteOldAplicationsFellApp;
    }

    /**
     * @return mixed
     */
    public function getSpreadsheetsPathFellApp()
    {
        return $this->spreadsheetsPathFellApp;
    }

    /**
     * @param mixed $spreadsheetsPathFellApp
     */
    public function setSpreadsheetsPathFellApp($spreadsheetsPathFellApp)
    {
        $this->spreadsheetsPathFellApp = $spreadsheetsPathFellApp;
    }

    /**
     * @return mixed
     */
    public function getApplicantsUploadPathFellApp()
    {
        return $this->applicantsUploadPathFellApp;
    }

    /**
     * @param mixed $applicantsUploadPathFellApp
     */
    public function setApplicantsUploadPathFellApp($applicantsUploadPathFellApp)
    {
        $this->applicantsUploadPathFellApp = $applicantsUploadPathFellApp;
    }

    /**
     * @return mixed
     */
    public function getYearsOldAplicationsFellApp()
    {
        return $this->yearsOldAplicationsFellApp;
    }

    /**
     * @param mixed $yearsOldAplicationsFellApp
     */
    public function setYearsOldAplicationsFellApp($yearsOldAplicationsFellApp)
    {
        $this->yearsOldAplicationsFellApp = $yearsOldAplicationsFellApp;
    }

    /**
     * @return mixed
     */
    public function getApplicationPageLinkFellApp()
    {
        return $this->applicationPageLinkFellApp;
    }

    /**
     * @param mixed $applicationPageLinkFellApp
     */
    public function setApplicationPageLinkFellApp($applicationPageLinkFellApp)
    {
        $this->applicationPageLinkFellApp = $applicationPageLinkFellApp;
    }






}