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

namespace Oleg\TranslationalResearchBundle\Util;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 8/25/2017
 * Time: 09:48 AM
 */
class TransResRequestUtil
{

    protected $container;
    protected $em;
    protected $secTokenStorage;
    protected $secAuth;

    public function __construct( $em, $container ) {
        $this->container = $container;
        $this->em = $em;
        $this->secAuth = $container->get('security.authorization_checker'); //$this->secAuth->isGranted("ROLE_USER")
        $this->secTokenStorage = $container->get('security.token_storage'); //$user = $this->secTokenStorage->getToken()->getUser();
    }


    public function getRequestTotalFeeHtml( $project ) {

        $transResFormNodeUtil = $this->container->get('transres_formnode_util');

        $repository = $this->em->getRepository('OlegTranslationalResearchBundle:TransResRequest');
        $dql =  $repository->createQueryBuilder("transresRequest");
        $dql->select('transresRequest');

        $dql->leftJoin('transresRequest.submitter','submitter');
        $dql->leftJoin('transresRequest.project','project');
        $dql->leftJoin('submitter.infos','submitterInfos');

        $dqlParameters = array();

        $dql->andWhere("project.id = :projectId");

        $dqlParameters["projectId"] = $project->getId();

        $query = $this->em->createQuery($dql);

        if( count($dqlParameters) > 0 ) {
            $query->setParameters($dqlParameters);
        }

        $requests = $query->getResult();

        foreach($requests as $request) {
            $completed = $transResFormNodeUtil->getProjectFormNodeFieldByName(
                $request,
                "Completed #",
                "HemePath Translational Research",
                "HemePath Translational Research Request",
                "Request",
                false
            );
            echo "completed=".$completed."<br>";

            $requestCategoryType = $transResFormNodeUtil->getProjectFormNodeFieldByName(
                $request,
                "Category Type",
                "HemePath Translational Research",
                "HemePath Translational Research Request",
                "Request",
                true
            );
            echo "requestCategoryType ID=".$requestCategoryType->getId()."<br>";
            //echo "requestCategoryType feeUnit=".$requestCategoryType->getFeeUnit()."<br>";
            //echo "requestCategoryType fee=".$requestCategoryType->getFee()."<br>";
        }

        return null;
    }

}