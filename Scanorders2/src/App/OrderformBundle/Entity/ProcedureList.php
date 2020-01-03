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

namespace App\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use App\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_procedureList",
 *  indexes={
 *      @ORM\Index( name="procedure_name_idx", columns={"name"} )
 *  }
 * )
 */
class ProcedureList extends ListAbstract
{
    /**
     * @ORM\OneToMany(targetEntity="ProcedureList", mappedBy="original", cascade={"persist"})
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="ProcedureList", inversedBy="synonyms", cascade={"persist"})
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", nullable=true)
     **/
    protected $original;

//    /**
//     * @ORM\OneToMany(targetEntity="ProcedureName", mappedBy="field")
//     */
//    protected $procedurename;


    public function __construct() {
        $this->synonyms = new ArrayCollection();
        //$this->procedurename = new ArrayCollection();
    }   

//    /**
//     * Add procedurename
//     *
//     * @param \App\OrderformBundle\Entity\ProcedureName $procedurename
//     * @return ProcedureList
//     */
//    public function addProcedurename(\App\OrderformBundle\Entity\ProcedureName $procedurename)
//    {
//        if( $procedurename && !$this->procedurename->contains($procedurename) ) {
//            $this->procedurename->add($procedurename);
//            $procedurename->setField($this);
//        }
//
//        return $this;
//    }
//
//    /**
//     * Remove procedurename
//     *
//     * @param \App\OrderformBundle\Entity\ProcedureName $procedurename
//     */
//    public function removeProcedurename(\App\OrderformBundle\Entity\ProcedureName $procedurename)
//    {
//        $this->procedurename->removeElement($procedurename);
//    }
//
//    /**
//     * Get procedurename
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getProcedurename()
//    {
//        return $this->procedurename;
//    }


}