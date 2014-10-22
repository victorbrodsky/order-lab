<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;


class DocumentRepository extends EntityRepository {


    public function processDocuments($holder) {

        echo "<br>before processing holder count=".count($holder->getDocuments())."<br>";

        foreach( $holder->getDocuments() as $doc ) {
            echo "doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
            //if document does not have an original or unique names then this is a newly added document => find it in DB and attach it to this holder
            if( $doc->getId() && ( !$doc->getOriginalname() || !$doc->getUniquename() ) ) {

                $holder->removeDocument($doc);

                $docDb = $this->_em->getRepository('OlegUserdirectoryBundle:Document')->find($doc->getId());
                if( $docDb ) {
                    echo "add found doc id=".$docDb->getId().", originalname=".$docDb->getOriginalname().", uniquename=".$docDb->getUniquename()."<br>";
                    $holder->addDocument($docDb);
                }
            }
        }

        echo "after processing holder count=".count($holder->getDocuments())."<br>";
        foreach( $holder->getDocuments() as $doc ) {
            echo "final doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
        }

        //exit('eof documents processing');

        return $holder;
    }

}

