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

        $projectTitleName = $entity->getProjectTitle()->getName();
        $foundProjectTitle = $em->getRepository('OlegOrderformBundle:ProjectTitleList')->findOneByName($projectTitleName);

        if( $foundProjectTitle ) {

            $originalProjectTitle = $entity->getProjectTitle();
            $originalSetTitles = $originalProjectTitle->getSetTitles();

            foreach( $originalSetTitles as $settitle ) {
                $foundProjectTitle->addSetTitles($settitle);
                $settitle->setProjectTitle($foundProjectTitle);
            }

            $this->processPrincipals( $originalProjectTitle, $foundProjectTitle ); //source, dest

            //set primary principal
            $foundProjectTitle->setPrimaryPrincipal( $originalProjectTitle->getPrimaryPrincipal() );

            $entity->setProjectTitle( $foundProjectTitle );

            $orderinfo->setResearch($entity);


            return $orderinfo;

        } else {
            throw new \Exception( 'Research Project was not found with name '.$projectTitleName );
        }

        //exit('educ rep');
        return $orderinfo;
    }

    public function processPrincipals( $source, $dest ) {


        $principals = $source->getPis();

        foreach( $principals as $principal ) {
            $principalstr = $principal->getName();
            //echo "principalstr=".$principalstr."<br>";

            $foundPrincipal = $this->_em->getRepository('OlegOrderformBundle:PIList')->findOneByName($principalstr);

            if( $foundPrincipal ) {
                $dest->addPis($foundPrincipal);
                $foundPrincipal->addProject($dest);
            }

        }

    }


}
