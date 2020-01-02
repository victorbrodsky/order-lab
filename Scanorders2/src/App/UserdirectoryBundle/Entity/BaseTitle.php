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
     * @ORM\ManyToOne(targetEntity="EffortList")
     **/
    protected $effort;

    /**
     * @ORM\ManyToOne(targetEntity="Institution")
     */
    protected $institution;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pgystart;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pgylevel;



    function __construct($author=null)
    {
        parent::__construct($author);
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