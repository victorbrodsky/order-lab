<?php

namespace Oleg\OrderformBundle\Repository;

use Doctrine\ORM\EntityRepository;


class ResearchRepository extends EntityRepository {

    public function processEntity( $orderinfo ) {

        $cwid = null;
        $user = null;
        $em = $this->_em;

        $entity = $orderinfo->getResearch();

        $str = $entity->getPrincipalstr();
        echo "str=".$str."<br>";
        echo "research obj=".$entity;
        echo "entity->getProjectTitle()->getName()=".$entity->getProjectTitle()->getName()."<br>";

//        $query = $em->createQueryBuilder()
//            ->from('OlegOrderformBundle:Research', 'list')
//            ->select("list")
//            ->leftJoin("list.projectTitle","projectTitle")
//            ->where("projectTitle.name=:name")
//            //->orderBy("list.orderinlist","ASC")
//            ->setParameter("name",$entity->getProjectTitle()->getName());
//        $foundEntity = $query->getQuery()->getResult();

        $projectTitleName = $entity->getProjectTitle()->getName()."";
        $foundProjectTitle = $em->getRepository('OlegOrderformBundle:ProjectTitleList')->findOneByName($projectTitleName);

//        if( $foundProjectTitle ) {
//            //ProjectTitle 1
//            echo "count res=".count($foundProjectTitle).", projectTitle=".$entity->getProjectTitle()->getName()."<br>";
//            exit();
//        } else {
//            echo "not found!";
//            exit();
//        }

//        if( count($foundProjectTitles) > 1 ) {
//            throw new \Exception('More than one result is found for ProjectTitleList with name '.$entity->getProjectTitle()->getName());
//        }

        if( $foundProjectTitle ) {

            echo "found 1 reserach <br>";

            //$em->persist($foundProjectTitle);
            $originalProjectTitle = $entity->getProjectTitle();
            $originalSetTitles = $originalProjectTitle->getSetTitles();

            echo "count settitles=".count($originalSetTitles)."<br>";

            $count = 0;
            foreach( $originalSetTitles as $settitle ) {
                echo $count.": ".$settitle;
                $foundProjectTitle->addSetTitles($settitle);
            }

            $entity->setProjectTitle( $foundProjectTitle );
            echo "2 research obj=".$entity;

            $origSetTitles = $entity->getProjectTitle()->getSetTitles();
            echo "count SetTitles=".count($origSetTitles)."<br>";

            if( count($origSetTitles) > 1 ) {
                throw new \Exception('More than one SetTitleList exists in ProjectTitle.');
            }

            if( count($origSetTitles) == 1 ) {

                $origSetTitlesName = $origSetTitles->first()->getName()."";
                $foundSetTitle = $em->getRepository('OlegOrderformBundle:SetTitleList')->findOneByName($origSetTitlesName);

                if( $foundSetTitle ) {
                    echo "foundSetTitle=".$foundSetTitle."<br>";
                    //$foundProjectTitle->removeSetTitles( $origSetTitles->first() );
                    //$em->persist($foundSetTitle);
                    //$foundProjectTitle->addSetTitles( $foundSetTitle );
                    $foundSetTitle->setProjectTitle($foundProjectTitle);
                    echo "settitle's projectId=".$foundSetTitle->getProjectTitle()->getId()."<br>";
                }

            }

            $orderinfo->setResearch($entity);

            //exit();
            return $orderinfo;

        } else {

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
                $entity->setPrincipal($user);
                $orderinfo->setResearch($entity);
            }

        }

        //exit('educ rep');
        return $orderinfo;
    }


}
