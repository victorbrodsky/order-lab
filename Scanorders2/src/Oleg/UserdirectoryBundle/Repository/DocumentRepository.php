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

namespace Oleg\UserdirectoryBundle\Repository;

use Doctrine\ORM\EntityRepository;


class DocumentRepository extends EntityRepository {

    //set document type according to the holder entity. For example, for Comment entity, type is "Comment Document"
    public function processDocuments($documentHolder, $docfieldname=null, $docType=null) {

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
        if( !$docType ) {
            $docType = $this->getDocumentTypeByHolder($documentHolder);
        }

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
                $doctypeStr = 'Fellowship Application Document';
                break;
//            case 'Credentials':
//                $doctypeStr = 'Medical License Document';
//                break;
//            case 'StateLicense':
//                $doctypeStr = 'Certificate of Qualification Document';
//                break;
            default:
                //$doctypeStr = 'Generic Document';
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

