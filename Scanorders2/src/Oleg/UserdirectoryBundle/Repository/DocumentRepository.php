<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;


class DocumentRepository extends EntityRepository {


    public function processDocuments($documentHolder) {

        if( $documentHolder == null ) {
            echo "not exists: document=".$documentHolder."<br>";
            return $documentHolder;
        }

        if( count($documentHolder->getDocuments()) == 0 ) {
            echo "return: no documents<br>";
            return $documentHolder;
        }

        echo $documentHolder. ", id=".$documentHolder->getId()."<br>";
        echo "<br>before processing holder count=".count($documentHolder->getDocuments())."<br>";

        //get type by $documentHolder class
        $docType = $this->getDocumentTypeByHolder($documentHolder);

        foreach( $documentHolder->getDocuments() as $doc ) {
            echo "doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
            //if document does not have an original or unique names then this is a newly added document => find it in DB and attach it to this holder
            if( $doc->getId() && ( !$doc->getOriginalname() || !$doc->getUniquename() ) ) {

                $documentHolder->removeDocument($doc);

                echo "before get doc: id=".$doc->getId()."<br>";

                $docDb = $this->_em->getRepository('OlegUserdirectoryBundle:Document')->find($doc->getId());

                if( $docDb ) {

                    //set type if not set
                    if( !$docDb->getType() && $docType ) {
                        $docDb->setType($docType);
                    }

                    echo "add found doc id=".$docDb->getId().", originalname=".$docDb->getOriginalname().", uniquename=".$docDb->getUniquename()."<br>";
                    $documentHolder->addDocument($docDb);
                }
            }
        }

        echo "after processing holder count=".count($documentHolder->getDocuments())."<br>";
        //foreach( $documentHolder->getDocuments() as $doc ) {
            //echo "final doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
        //}

        //exit('eof documents processing');

        return $documentHolder;
    }


    public function getDocumentTypeByHolder( $documentHolder ) {

        if( $documentHolder == null ) {
            return null;
        }

        $class = new \ReflectionClass($documentHolder);
        $className = $class->getShortName();

        $documentType = null;
        $doctypeStr = null;

        switch( $className ) {
            case 'AdminComment':
            case 'ConfidentialComment':
            case 'PrivateComment':
            case 'PublicComment':
                $doctypeStr = 'Comment Document';
                break;
            case 'PartPaper':
                $doctypeStr = 'Part Document';
                break;
            case 'User':
                $doctypeStr = 'Avatar Image';
                break;
            default:
                //
        }

        if( $doctypeStr ) {
            $documentType = $this->_em->getRepository('OlegUserdirectoryBundle:DocumentTypeList')->findOneByName($doctypeStr);
        }

        //echo "documentType=".$documentType."<br>";
        //exit('className='.$className);

        return $documentType;

    }

}

