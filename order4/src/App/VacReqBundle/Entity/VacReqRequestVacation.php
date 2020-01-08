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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 4/11/2016
 * Time: 11:35 AM
 */


namespace App\VacReqBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;


/**
 * @ORM\Entity
 * @ORM\Table(name="vacreq_vacation")
 */
class VacReqRequestVacation extends VacReqRequestBase
{


    public function __toString()
    {
        //$break = "\r\n";
        $break = "<br>";
        $transformer = new DateTimeToStringTransformer(null,null,'m/d/Y');

        $res = "### Vacation Request ###".$break;
        $res .= "Status: ".$this->getStatus().$break;
        $res .= "Vacation - First Day Away: ".$transformer->transform($this->getStartDate()).$break;
        $res .= "Vacation - Last Day Away: ".$transformer->transform($this->getEndDate()).$break;
        $res .= "Vacation Days Requested: ".$this->getNumberOfDays().$break;
        //$res .= "First Day Back in Office: ".$transformer->transform($this->getFirstDayBackInOffice()).$break;

        if( $this->getApproverComment() ) {
            $res .= "Approver Comment: ".$this->getApproverComment().$break;
        }

        return $res;
    }

}