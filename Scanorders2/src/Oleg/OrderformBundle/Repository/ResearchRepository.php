<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;


class ResearchRepository extends EntityRepository {

    public function processEntity( $orderinfo ) {

        $entity = $orderinfo->getResearch();

        if( $entity->isEmpty() ) {
            $orderinfo->setResearch(NULL);
            return $orderinfo;
        }

        $cwid = null;
        $user = null;
        $em = $this->_em;

//        echo "str=".$str."<br>";
//        echo "research obj=".$entity;
//        echo "entity->getProjectTitle()->getName()=".$entity->getProjectTitle()->getName()."<br>";

//        $query = $em->createQueryBuilder()
//            ->from('OlegOrderformBundle:Research', 'list')
//            ->select("list")
//            ->leftJoin("list.projectTitle","projectTitle")
//            ->where("projectTitle.name=:name")
//            //->orderBy("list.orderinlist","ASC")
//            ->setParameter("name",$entity->getProjectTitle()->getName());
//        $foundEntity = $query->getQuery()->getResult();

        $projectTitleName = $entity->getProjectTitle()->getName();
        $foundProjectTitle = $em->getRepository('OlegOrderformBundle:ProjectTitleList')->findOneByName($projectTitleName);

        if( $foundProjectTitle ) {

//            echo "found 1 reserach <br>";

            $originalProjectTitle = $entity->getProjectTitle();
            $originalSetTitles = $originalProjectTitle->getSetTitles();

//            echo "count settitles=".count($originalSetTitles)."<br>";

            //$count = 0;
            foreach( $originalSetTitles as $settitle ) {
                //echo $count.": ".$settitle;
                $foundProjectTitle->addSetTitles($settitle);
                $settitle->setProjectTitle($foundProjectTitle);
                //$count++;
            }

//            echo "2 research obj=".$entity;
            $this->processPrincipals( $originalProjectTitle, $foundProjectTitle ); //source, dest

            //$origSetTitles = $entity->getProjectTitle()->getSetTitles();
//            echo "count SetTitles=".count($origSetTitles)."<br>";

//            foreach( $origSetTitles as $settitle ) {
//                $settitle->setProjectTitle($foundProjectTitle);
//            }

            //process PI
//            $principals = $originalProjectTitle->getPrincipals();
//            foreach( $principals as $principal ) {
//                $foundUser = $this->processUser($principal);
//                if( $foundUser ) {
//                    $foundProjectTitle->addSetTitles($settitle);
//                    $settitle->setProjectTitle($foundProjectTitle);
//                }
//
//                //$count++;
//            }

            $entity->setProjectTitle( $foundProjectTitle );

            $orderinfo->setResearch($entity);

            //exit();
            return $orderinfo;

        } else {

            exit("OHHH");

            $str = $entity->getPrincipalstr();

            if( is_int($str) ) {

                //here $str is the user id
                $user = $em->getRepository('OlegOrderformBundle:User')->findOneById($str);

            } else {

                //get cwid
                $strArr = explode(" ",$str);

                if( count($strArr) > 0 ) {
                    $cwid = $strArr[0];
                }

                if( $cwid ) {
                    //echo "cwid=".$cwid."<br>";
                    $user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername($cwid);
                }

            }

            if( $user ) {
                //echo "user=".$user."<br>";
                //$entity->setPrincipal($user);
                $orderinfo->setResearch($entity);
            }

        }

        //exit('educ rep');
        return $orderinfo;
    }

    public function processPrincipals( $source, $dest ) {


        $principals = $source->getPrincipals();

        foreach( $principals as $principal ) {
            $principalstr = $principal->getName();
            echo "principalstr=".$principalstr."<br>";

            $foundPrincipal = $this->_em->getRepository('OlegOrderformBundle:PIList')->findOneByName($principalstr);

            if( $foundPrincipal ) {
                $dest->addPrincipals($foundPrincipal);
            }

//            //get cwid
//            $strArr = explode(" ",$principalstr);
//
//            if( count($strArr) > 0 ) {
//                $cwid = $strArr[0];
//            }
//
//            if( $cwid ) {
//                //echo "cwid=".$cwid."<br>";
//                $user = $this->_em->getRepository('OlegOrderformBundle:User')->findOneByUsername($cwid);
//            }

            //if( $user ) {
                //echo "user=".$user."<br>";
                //$originalProjectTitle->setPrincipals($user);
            //}

        }

    }


}
