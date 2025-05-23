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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/24/13
 * Time: 12:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\OrderformBundle\Entity\ArrayFieldAbstract;

#[ORM\MappedSuperclass]
abstract class AccessionArrayFieldAbstract extends ArrayFieldAbstract {



    /**
     * Set accession
     *
     * @param \App\OrderformBundle\Entity\Accession $accession
     * @return Accession Field
     */
    public function setAccession(\App\OrderformBundle\Entity\Accession $accession = null)
    {
        $this->accession = $accession;

        return $this;
    }

    /**
     * Get accession
     *
     * @return \App\OrderformBundle\Entity\Accession
     */
    public function getAccession()
    {
        return $this->accession;
    }

    /**
     * @param mixed $field
     */
    public function setField($field=null)
    {
        $this->field = $field;
        $this->setFieldChangeArray("field",$this->field,$field);
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    //set and get parent
    public function setParent($parent)
    {
        $this->setAccession($parent);
        return $this;
    }
    public function getParent()
    {
        return $this->getAccession();
    }

}