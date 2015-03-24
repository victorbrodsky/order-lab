<?php

namespace Oleg\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class BaseTitle extends BaseUserAttributes
{

    /**
     * Primary, Secondary
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $priority;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="EffortList",cascade={"persist"})
     **/
    protected $effort;

    /**
     * @ORM\ManyToOne(targetEntity="Institution",cascade={"persist"})
     */
    protected $institution;

    /**
     * @ORM\ManyToOne(targetEntity="Department",cascade={"persist"})
     */
    protected $department;

    /**
     * @ORM\ManyToOne(targetEntity="Division",cascade={"persist"})
     */
    protected $division;

    /**
     * @ORM\ManyToOne(targetEntity="Service",cascade={"persist"})
     */
    protected $service;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pgystart;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pgylevel;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $supervisorInstitution;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $supervisorDepartment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $supervisorDivision;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $supervisorService;



    function __construct($author=null)
    {
        parent::__construct($author);

        $this->supervisorInstitution = false;
        $this->supervisorDepartment = false;
        $this->supervisorDivision = false;
        $this->supervisorService = false;
    }


    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param mixed $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param mixed $institution
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;
    }

    /**
     * @return mixed
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param mixed $effort
     */
    public function setEffort($effort)
    {
        $this->effort = $effort;
    }

    /**
     * @return mixed
     */
    public function getEffort()
    {
        return $this->effort;
    }

    /**
     * @param mixed $supervisorDepartment
     */
    public function setSupervisorDepartment($supervisorDepartment)
    {
        if( !$this->getDepartment() && $supervisorDepartment ) {
            return;
        }

        //echo "supervisorDepartment=".$supervisorDepartment."<br>";
        $this->supervisorDepartment = $supervisorDepartment;

        //echo "user=".$this->getUser()."<br>";
        if( $supervisorDepartment ) {
            if( $this->getDepartment() ) {
                //$this->getDepartment()->addHead( $this->getUser() );
            }
        } else {
            if( $this->getDepartment() )
                $this->getDepartment()->removeHead( $this->getUser() );
        }
    }

    /**
     * @return mixed
     */
    public function getSupervisorDepartment()
    {
        return $this->supervisorDepartment;
    }

    /**
     * @param mixed $supervisorDivision
     */
    public function setSupervisorDivision($supervisorDivision)
    {
        if( !$this->getDivision() ) {
            return;
        }

        $this->supervisorDivision = $supervisorDivision;

        if( $supervisorDivision ) {
            $this->getDivision()->addHead( $this->getUser() );
        } else {
            $this->getDivision()->removeHead( $this->getUser() );
        }
    }

    /**
     * @return mixed
     */
    public function getSupervisorDivision()
    {
        return $this->supervisorDivision;
    }

    /**
     * @param mixed $supervisorInstitution
     */
    public function setSupervisorInstitution($supervisorInstitution)
    {
        if( !$this->getInstitution() ) {
            return;
        }

        $this->supervisorInstitution = $supervisorInstitution;

        if( $supervisorInstitution ) {
            $this->getInstitution()->addHead( $this->getUser() );
        } else {
            $this->getInstitution()->removeHead( $this->getUser() );
        }
    }

    /**
     * @return mixed
     */
    public function getSupervisorInstitution()
    {
        return $this->supervisorInstitution;
    }

    /**
     * @param mixed $supervisorService
     */
    public function setSupervisorService($supervisorService)
    {
        if( !$this->getService() ) {
            return;
        }

        $this->supervisorService = $supervisorService;

        if( $supervisorService ) {
            $this->getService()->addHead( $this->getUser() );
        } else {
            $this->getService()->removeHead( $this->getUser() );
        }
    }

    /**
     * @return mixed
     */
    public function getSupervisorService()
    {
        return $this->supervisorService;
    }

//    /**
//     * @ORM\PreRemove
//     */
    public function unsetHeads() {
        //exit('on remove');
        //remove possible links institution-head
        if( $this->getInstitution() ) {
            if( $this->getInstitution()->getHeads()->contains($this->getUser()) ) {
                $this->getInstitution()->removeHead( $this->getUser() );
            }
        }

        //department
        if( $this->getDepartment() ) {
            if( $this->getDepartment()->getHeads()->contains($this->getUser()) ) {
                $this->getDepartment()->removeHead( $this->getUser() );
            }
        }

        //division
        if( $this->getDivision() ) {
            if( $this->getDivision()->getHeads()->contains($this->getUser()) ) {
                $this->getDivision()->removeHead( $this->getUser() );
            }
        }

        //service
        if( $this->getService() ) {
            if( $this->getService()->getHeads()->contains($this->getUser()) ) {
                $this->getService()->removeHead( $this->getUser() );
            }
        }

    }

//    /**
//     * @ORM\preFlush
//     */
    public function setHeads() {
        //add possible links institution-head
        if( $this->getInstitution() ) {
            if( !$this->getInstitution()->getHeads()->contains($this->getUser()) ) {
                $this->getInstitution()->addHead( $this->getUser() );
            }
        }

        //department
        if( $this->getDepartment() ) {
            if( !$this->getDepartment()->getHeads()->contains($this->getUser()) ) {
                $this->getDepartment()->addHead($this->getUser());
            }
        }

        //division
        if( $this->getDivision() ) {
            if( !$this->getDivision()->getHeads()->contains($this->getUser()) ) {
                $this->getDivision()->addHead( $this->getUser() );
            }
        }

        //service
        if( $this->getService() ) {
            if( !$this->getService()->getHeads()->contains($this->getUser()) ) {
                $this->getService()->addHead( $this->getUser() );
            }
        }
    }


    /**
     * @param \DateTime $pgylevel
     */
    public function setPgylevel($pgylevel)
    {
        $this->pgylevel = $pgylevel;
    }

    /**
     * @return \DateTime
     */
    public function getPgylevel()
    {
        return $this->pgylevel;
    }

    /**
     * @param \DateTime $pgystart
     */
    public function setPgystart($pgystart)
    {
        $this->pgystart = $pgystart;
    }

    /**
     * @return \DateTime
     */
    public function getPgystart()
    {
        return $this->pgystart;
    }

    public function calculateExpectedPgy() {

        $newPgyLevel = null;

        if( $this->pgylevel != "" ) {
            $newPgyLevel = $this->pgylevel;
        }

        //During academic year that started on: [July 1st 2011]
        //The Post Graduate Year (PGY) level was: [1]
        //Expected Current Post Graduate Year (PGY) level: [4] (not a true fleld in the database, not editble)
        //
        //D- If both the date and the PGY have value and the academic year is not current
        // (meaning the current date is later than listed date +1 year (in the example above, if current date is later than July 1st 2012) ,
        // the function takes the current year (for example 2014), subtracts the year in the date field (let's say 2011), and add the result to the current PGY level value
        // (let's say 1, replacing it with 4), then updates the year of the field with current (2011->2014).
        if( $this->pgystart != "" && $this->pgylevel != "" ) {

            $today = new \DateTime();
            $curYear = $today->format("Y");
            $pgyYear = $this->pgystart->format("Y");
            $diffYear = intval($curYear) - intval($pgyYear);

            //echo 'diffYear='.$diffYear."<br>";

            if( $diffYear >= 1 ) {

                //add the result to the current PGY level value
                $newPgyLevel = intval($this->pgylevel) + ( intval($curYear) - intval($pgyYear) );
            }

        }

        return $newPgyLevel;
    }

}