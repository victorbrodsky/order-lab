<?php

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */


namespace Oleg\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_vacation")
 */
class VacReqRequestVacation extends VacReqRequestBase
{

    /**
     * @ORM\OneToOne(targetEntity="VacReqRequest", mappedBy="requestVacation")
     */
    private $requestForm;






    /**
     * @return mixed
     */
    public function getRequestForm()
    {
        return $this->requestForm;
    }

    /**
     * @param mixed $requestForm
     */
    public function setRequestForm($requestForm)
    {
        $this->requestForm = $requestForm;
    }



}