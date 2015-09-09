<?php


namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;


class DocumentRepository extends EntityRepository {


    public function processDocuments($documentHolder, $docfieldname=null) {

        if( $documentHolder == null ) {
           // echo "not exists: document=".$documentHolder."<br>";
            return $documentHolder;
        }

        //$class = new \ReflectionClass($documentHolder);
        //$className = $class->getShortName();
        //echo "<br><br>className=".$className."<br>";

        if( $docfieldname ) {

        } else {
            $docfieldname = "Document";
        }

        $addMethodName = "add".$docfieldname;
        $removeMethodName = "remove".$docfieldname;
        $getMethod = "get".$docfieldname."s";

        if( count($documentHolder->$getMethod()) == 0 ) {
            //echo "return: no documents<br>";
            return $documentHolder;
        }

//        echo $documentHolder. ", id=".$documentHolder->getId()."<br>";
//        echo "<br>before processing holder count=".count($documentHolder->$getMethod())."<br>";

        //get type by $documentHolder class
        $docType = $this->getDocumentTypeByHolder($documentHolder);

        foreach( $documentHolder->$getMethod() as $doc ) {

//            echo "document id:<br>";
//            print_r($doc->getId());
//            echo "<br>";

            $documentHolder->$removeMethodName($doc);

            if( $doc->getId() ) {

                $docDb = $this->_em->getRepository('OlegUserdirectoryBundle:Document')->find($doc->getId());

                //set type if not set
                if( !$docDb->getType() && $docType ) {
                    $docDb->setType($docType);
                }

                $documentHolder->$addMethodName($docDb);

            } //if

        } //foreach

//        echo "after processing holder count=".count($documentHolder->$getMethod())."<br>";
//        foreach( $documentHolder->$getMethod() as $doc ) {
//            echo "final doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
//        }

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
            case 'FellowshipApplication':
            case 'Examination':
                $doctypeStr = 'Fellowship Application Upload';
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


//    public function findOneRecentDocument( $holder, $holderBundleName, $holderClassName, $documentStr ) {
//
//        $repository = $this->_em->getRepository($holderBundleName.':'.$holderClassName);
//        $dql = $repository->createQueryBuilder('holder');
//        $dql->select("holder.documents");
//        $dql->leftJoin("holder.".$documentStr,"documents");
//        //$dql->groupBy("holder.id");
//        //$dql->groupBy("documents");
//        $dql->where("holder.id=".$holder->getId());
//        $dql->setMaxResults(1);
//        $dql->orderBy("documents.createdate","DESC");
//
//        $query = $this->_em->createQuery($dql);
//
//        $documents = $query->getResult();
//
//        echo "doc count=".count($documents)."<br>";
//        echo "doc=".$documents[0]."<br>";
//
//        return $documents[0];
//    }

}

