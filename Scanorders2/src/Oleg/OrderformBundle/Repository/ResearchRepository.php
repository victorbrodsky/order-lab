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

        $researchName = $entity->getName();
        $foundResearch = $em->getRepository('OlegOrderformBundle:Research')->findOneByName($researchName);

        if( $foundResearch ) {

            $originalSetTitles = $entity->getSetTitles();

            foreach( $originalSetTitles as $settitle ) {
                $foundResearch->addSetTitles($settitle);
                $settitle->setResearch($foundResearch);
            }

            $this->processPrincipals( $entity, $foundResearch ); //source, dest

            //set primary principal
            $foundResearch->setPrimaryPrincipal( $entity->getPrimaryPrincipal() );

            //$entity->setProjectTitle( $foundResearch );

            $orderinfo->setResearch($foundResearch);


            return $orderinfo;

        } else {
            throw new \Exception( 'Research was not found with name '.$researchName );
        }

        //exit('educ rep');
        return $orderinfo;
    }

    //inputs: source research, destination research
    public function processPrincipals( $source, $dest ) {


        $principals = $source->getPrincipals();

        foreach( $principals as $principal ) {
            $principalstr = $principal->getName();
            //echo "principalstr=".$principalstr."<br>";

            $foundPrincipal = $this->_em->getRepository('OlegOrderformBundle:PIList')->findOneByName($principalstr);

            if( $foundPrincipal ) {
                $dest->addPrincipals($foundPrincipal);
                $foundPrincipal->addResearches($dest);
            }

        }

    }


}
