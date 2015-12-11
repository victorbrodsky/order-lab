<?php

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
     * @ORM\JoinColumn(name="original_id", referencedColumnName="id", onDelete="CASCADE")
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