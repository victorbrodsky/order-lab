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

namespace App\UserdirectoryBundle\Repository;


use App\UserdirectoryBundle\Entity\Document;
use App\UserdirectoryBundle\Entity\DocumentTypeList;
use Doctrine\ORM\EntityRepository;


class DocumentRepository extends EntityRepository {

    //set document type according to the holder entity. For example, for Comment entity, type is "Comment Document"
    //$attachmentHolder is the holder that can have many $documentHolder
    public function processDocuments($documentHolder, $docfieldname=null, $docType=null, $attachmentHolder=null) {

        if( $documentHolder == null ) {
            echo "not exists: document=".$documentHolder."<br>";
            return $documentHolder;
        }

        //testing
//        $class = new \ReflectionClass($documentHolder);
//        $className = $class->getShortName();
//        echo "<br><br>className=".$className."<br>";

        if( $docfieldname ) {
            //
        } else {
            $docfieldname = "Document";
        }

        $addMethodName = "add".$docfieldname;
        $removeMethodName = "remove".$docfieldname;
        $getMethod = "get".$docfieldname."s";
        echo "getMethod=".$getMethod."<br>";
        echo "removeMethodName=".$removeMethodName."<br>";

        if( count($documentHolder->$getMethod()) == 0 ) {
            echo "return: no documents<br>";

            //prvenet create an empty DocumentContainer and AttachmentContainer: remove DocumentContainer from AttachmentContainer
            //$attachmentContainer = null;
            //if( $documentHolder->getAttachmentContainer() )
            if( $documentHolder && method_exists($documentHolder, 'getAttachmentContainer') ) {
                $attachmentContainer = $documentHolder->getAttachmentContainer();
                if( $attachmentContainer && method_exists($attachmentContainer, 'removeDocumentContainer') ) {
                    $attachmentContainer->removeDocumentContainer($documentHolder);
                }
            }

            if( $attachmentHolder ) {
                $attachmentHolder->setAttachmentContainer(null);
            }

            return null;
            //return $documentHolder;
        }

        //echo get_class($documentHolder).": holder id=".$documentHolder->getId()."<br>";
        //echo "<br>$getMethod: before processing holder count=".count($documentHolder->$getMethod())."<br>";

        //get type by $documentHolder class
        if( !$docType ) {
            $docType = $this->getDocumentTypeByHolder($documentHolder);
        }

        foreach( $documentHolder->$getMethod() as $doc ) {

//            echo "document id:<br>";
//            print_r($doc->getId());

            $documentId = $doc->getId();
            //echo "doc id=".$documentId."<br>";
//            echo "<br>";

            $documentHolder->$removeMethodName($doc);

            //check if id is numeric to prevent the case when $doc->getId() = "undefined"
            if( $documentId && is_numeric($documentId) ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
                $docDb = $this->_em->getRepository(Document::class)->find($documentId);
                //$docDb = $doc;

                //echo "docDb: [".$docDb."]<br>";
                if( $docDb ) {

                    //echo "docDb id=".$docDb->getId()."<br>";
                    //set type if not set
                    if( !$docDb->getType() && $docType ) {
                        $docDb->setType($docType);
                    }

                    $documentHolder->$addMethodName($docDb);
                } else {
                    //exit("Document not found by id=".$documentId);
                }

            } //if

        } //foreach

//        echo "after processing holder count=".count($documentHolder->$getMethod())."<br>";
//        foreach( $documentHolder->$getMethod() as $doc ) {
//            echo "final doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
//        }

        //exit('eof documents processing');

        return $documentHolder;
    }
    //Why new, empty document is created
    public function processDocumentsTest($documentHolder, $docfieldname=null, $docType=null, $attachmentHolder=null) {

        if( $documentHolder == null ) {
            //echo "not exists: document=".$documentHolder."<br>";
            return $documentHolder;
        }

        //testing
        $class = new \ReflectionClass($documentHolder);
        $className = $class->getShortName();
        echo "<br><br>className=".$className."<br>";

        if( $docfieldname ) {
            //
        } else {
            $docfieldname = "Document";
        }

        $addMethodName = "add".$docfieldname;
        $removeMethodName = "remove".$docfieldname;
        $getMethod = "get".$docfieldname."s";
        echo "getMethod=".$getMethod."<br>";
        echo "removeMethodName=".$removeMethodName."<br>";

        echo "before processing holder count=".count($documentHolder->$getMethod())."<br>";
        foreach( $documentHolder->$getMethod() as $doc ) {
            echo "starting doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
        }

        if( count($documentHolder->$getMethod()) == 0 ) {
            echo "return: no documents<br>";

            //prvenet create an empty DocumentContainer and AttachmentContainer: remove DocumentContainer from AttachmentContainer
            //$attachmentContainer = null;
            //if( $documentHolder->getAttachmentContainer() )
            if( $documentHolder && method_exists($documentHolder, 'getAttachmentContainer') ) {
                $attachmentContainer = $documentHolder->getAttachmentContainer();
                if( $attachmentContainer && method_exists($attachmentContainer, 'removeDocumentContainer') ) {
                    $attachmentContainer->removeDocumentContainer($documentHolder);
                }
            }

            if( $attachmentHolder ) {
                $attachmentHolder->setAttachmentContainer(null);
            }

            return null;
            //return $documentHolder;
        }

        echo get_class($documentHolder).": holder id=".$documentHolder->getId()."<br>";
        echo "<br>$getMethod: before processing holder count=".count($documentHolder->$getMethod())."<br>";

        //get type by $documentHolder class
        if( !$docType ) {
            $docType = $this->getDocumentTypeByHolder($documentHolder);
        }

        foreach( $documentHolder->$getMethod() as $doc ) {

//            echo "document id:<br>";
//            print_r($doc->getId());

            $documentId = $doc->getId();
            echo "doc id=".$documentId."<br>";
//            echo "<br>";

            $documentHolder->$removeMethodName($doc);
            //$this->_em->persist($doc);

            //check if id is numeric to prevent the case when $doc->getId() = "undefined"
            if( $documentId && is_numeric($documentId) ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
                $docDb = $this->_em->getRepository(Document::class)->find($documentId);
                //$docDb = $doc;

                echo "docDb: [".$docDb."]<br>";
                if( $docDb ) {

                    echo "docDb id=".$docDb->getId()."<br>";
                    //set type if not set
                    if( !$docDb->getType() && $docType ) {
                        $docDb->setType($docType);
                    }

                    //$this->_em->persist($docDb);
                    //$this->_em->persist($documentHolder);

                    $documentHolder->$addMethodName($docDb);
                } else {
                    //exit("Document not found by id=".$documentId);
                }

            } //if

        } //foreach

        echo "after processing holder count=".count($documentHolder->$getMethod())."<br>";
        foreach( $documentHolder->$getMethod() as $doc ) {
            echo "final doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
        }

        //exit('eof documents processing');

        return $documentHolder;
    }

    public function processSingleDocument($form, $documentHolder, $docfieldname=null, $docType=null) {

        if( $documentHolder == null ) {
            return $documentHolder;
        }

        //testing
//        $class = new \ReflectionClass($documentHolder);
//        $className = $class->getShortName();
//        echo "<br><br>className=".$className."<br>";

        if( $docfieldname ) {
            //use $docfieldname to construct set and get methods
        } else {
            $docfieldname = "Document";
        }

        $setMethod = "set".$docfieldname;
        $getMethod = "get".$docfieldname;
        //echo "getMethod=".$getMethod."<br>";

        if( !$documentHolder->$getMethod() ) {
            //echo "return: no documents<br>";
            return null;
        }

        //get type by $documentHolder class
        if( !$docType ) {
            $docType = $this->getDocumentTypeByHolder($documentHolder);
        }

        ////////////////// Process Single Document //////////////
        $doc = $documentHolder->$getMethod();
        //echo "original doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";

        if( $doc ) {

            $documentHolder->$setMethod(null);

            //get the document id:
            // name="oleg_translationalresearchbundle_siteparameter[transresLogo][dummyprototypefield][0][id]"
            $dummyfieldArr = $form->get("transresLogo")->get("dummyprototypefield")->getData();
            $documentId = $dummyfieldArr[0]['id'];
            //echo "documentId=".$documentId."<br>";

            if( $documentId ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Document'] by [Document::class]
                $docDb = $this->_em->getRepository(Document::class)->find($documentId);
                if ($docDb) {

                    //echo "docDb id=".$docDb->getId()."<br>";
                    //set type if not set
                    if (!$docDb->getType() && $docType) {
                        $docDb->setType($docType);
                    }
                    
                    $documentHolder->$setMethod($docDb);
                } else {
                    //exit("Document not found by id=".$documentId);
                }

            } //if
        }
        ////////////////// EOF Process Single Document //////////////

//        $doc = $documentHolder->$getMethod();
//        echo "final doc id=".$doc->getId().", originalname=".$doc->getOriginalname().", uniquename=".$doc->getUniquename()."<br>";
//        exit('eof document processing');

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
            case 'ResidencyApplication':
                $doctypeStr = 'Residency Application Document';
                break;
            case 'VacReqRequest':
                $doctypeStr = 'Vacation Request Document';
                break;
//            case 'Credentials':
//                $doctypeStr = 'Medical License Document';
//                break;
//            case 'StateLicense':
//                $doctypeStr = 'Certificate of Qualification Document';
//                break;
//            case 'TransResSiteParameters':
//                $doctypeStr = 'Invoice Logo';
//                break;
            default:
                //$doctypeStr = 'Generic Document';
        }

        if( $doctypeStr ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:DocumentTypeList'] by [DocumentTypeList::class]
            $documentType = $this->_em->getRepository(DocumentTypeList::class)->findOneByName($doctypeStr);
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

