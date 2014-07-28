<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="siteParameters")
 */
class SiteParameters {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $maxIdleTime;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $environment;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $siteEmail;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $dbServerAddress;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $dbServerPort;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $dbServerAccountUserName;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $dbServerAccountPassword;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $dbDatabaseName;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $smtpServerAddress;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aDLDAPServerAddress;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aDLDAPServerPort;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aDLDAPServerOu;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aDLDAPServerAccountUserName;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aDLDAPServerAccountPassword;

    /**
     * @ORM\OneToOne(targetEntity="Institution")
     */
    protected $autoAssignInstitution;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $coPathDBServerAddress;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $coPathDBServerPort;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $coPathDBAccountUserName;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $coPathDBAccountPassword;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $coPathDBName;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aperioeSlideManagerDBServerAddress;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aperioeSlideManagerDBServerPort;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aperioeSlideManagerDBUserName;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aperioeSlideManagerDBPassword;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $aperioeSlideManagerDBName;


    //Footer
    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $institutionurl;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $institutionname;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $departmenturl;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $departmentname;




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




}