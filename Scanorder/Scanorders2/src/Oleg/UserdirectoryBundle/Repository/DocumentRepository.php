<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;


class DocumentRepository extends EntityRepository {


    public function processDocuments($documentHolder) {

        if( $documentHolder == null ) {
            //echo "not exists: docuemnt=".$documentHolder."<br>";
            return $documentHolder;
        }

        echo $documentHolder. ", id=".$documentHolder->getId()."<br>";

        echo "<br>before processing holder count=".count($documentHolder->getDocuments())."<br>";

        foreach( $documentHolder->getDocuments() as $doc ) {
            echo "doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
            //if document does not have an original or unique names then this is a newly added document => find it in DB and attach it to this holder
            if( $doc->getId() && ( !$doc->getOriginalname() || !$doc->getUniquename() ) ) {

                $documentHolder->removeDocument($doc);

                $docDb = $this->_em->getRepository('OlegUserdirectoryBundle:Document')->find($doc->getId());
                if( $docDb ) {
                    echo "add found doc id=".$docDb->getId().", originalname=".$docDb->getOriginalname().", uniquename=".$docDb->getUniquename()."<br>";
                    $documentHolder->addDocument($docDb);
                }
            }
        }

        echo "after processing holder count=".count($documentHolder->getDocuments())."<br>";
        foreach( $documentHolder->getDocuments() as $doc ) {
            echo "final doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
        }

        //exit('eof documents processing');

        return $documentHolder;
    }

}

