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

namespace Oleg\OrderformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\UserdirectoryBundle\Entity\ListAbstract;

/**
 * @ORM\Entity
 * @ORM\Table(name="scan_stainlist",
 *  indexes={
 *      @ORM\Index( name="stain_name_idx", columns={"name"} )
 *  }
 * )
 */
class StainList extends ListAbstract
{

    /**
     * @ORM\OneToMany(targetEntity="StainList", mappedBy="original")
     **/
    protected $synonyms;

    /**
     * @ORM\ManyToOne(targetEntity="StainList", inversedBy="synonyms")
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id")
     **/
    protected $original;



    //show full name, followed by “ (“ + short name + “,” + abbreviation + synonym#1’s full name + “,”+ synonym#2’s full name … etc. + “)”
    //Stain ID 43: SV40 (BKV, BK virus, JC virus)
    public function createFullTitle()
    {
        $fullTitle = "";

        $titleArr = array();

        if( $this->getShortname() ) {
            $titleArr[] = $this->getShortname();
        }

        if( $this->getAbbreviation() ) {
            if( $this->getAbbreviation() != $this->getShortname() ) {
                $titleArr[] = $this->getAbbreviation();
            }
        }

        foreach( $this->getSynonyms() as $synonym ) {
            if( $synonym->getName() ) {
                $titleArr[] = $synonym->getName();
            }
        }

        if( $this->getName() ) {
            $fullTitle = $this->getName();
            if( count($titleArr) > 0 ) {
                $fullTitle = $fullTitle . " (" . implode(", ", $titleArr) . ")";
            }
        }

        $this->setFulltitle($fullTitle);

        //echo "fullTitle=".$fullTitle."<br>";
        //exit();

        return $fullTitle;
    }

}