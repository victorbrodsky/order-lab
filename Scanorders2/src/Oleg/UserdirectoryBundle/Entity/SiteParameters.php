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




}