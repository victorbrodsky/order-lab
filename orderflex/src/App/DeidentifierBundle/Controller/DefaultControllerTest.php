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

//To initialize this bundle make sure:
//1) add a new source to SourceSystemList "Deidentifier"
//2) add a new AccessionType "Deidentifier ID"
//3) add new roles by running "Populate All Lists With Default Values" in user directory list manager
//4) add permission "Generate new Deidentifier ID" (Object:Accession, Action:create)
//5) add permission "Search by Deidentifier ID" (Object:Accession, Action:read)
//6) run "Synchronise DB with the source code changes" on the "List Manager" page to sync roles (assign site) and sync the code with EventTypeList in DB
//7) add permissions to ROLE_DEIDENTIFICATOR_WCM_NYP_ENQUIRER: Search by Deidentifier ID (WCMC,NYP)
//8) add permissions to ROLE_DEIDENTIFICATOR_WCM_NYP_GENERATOR: Generate new Deidentifier ID (WCMC,NYP)
//9) add permissions to ROLE_DEIDENTIFICATOR_WCM_NYP_HONEST_BROKER: Generate new Deidentifier ID (WCMC,NYP) and Search by Deidentifier ID (WCMC,NYP)


namespace App\DeidentifierBundle\Controller;



use App\OrderformBundle\Entity\AccessionType;
use App\TranslationalResearchBundle\Entity\Product;
use App\UserdirectoryBundle\Entity\Grant;
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\DeidentifierBundle\Form\DeidentifierSearchType;
use App\OrderformBundle\Entity\Accession;
use App\OrderformBundle\Entity\AccessionAccession;
use App\UserdirectoryBundle\Entity\AccessRequest;
use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\UserdirectoryBundle\Entity\SiteList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
//use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends OrderAbstractController
{
    
    /**
     * @Route("/about-test", name="deidentifier_about_test_page")
     * @Template("AppUserdirectoryBundle/Default/about.html.twig")
     */
    public function aboutAction( Request $request ) {

        //Testing IDENTITY comparing to AUTO
        $em = $this->getDoctrine()->getManager();
        //$em = $this->managerRegistry->getManager();
        $user = $this->getUser();

        $product = new Product($user);
        $em->persist($product);
        echo "product id (IDENTITY)=".$product->getId()."<br>";

        $site = new SiteList($user);
        $em->persist($site);
        echo "site id (Auto)=".$site->getId()."<br>";

        $grant = new Grant($user);
        //$em->getRepository(Grant::class)->testGrant();
        $em->persist($grant);
        echo "grant id (Auto)=".$grant->getId()."<br>";

        //return $this->redirect( $this->generateUrl('deidentifier-nopermission') );
        //return $this->redirectToRoute('deidentifier-nopermission');
        //exit('111');

        return array('sitename'=>$this->getParameter('deidentifier.sitename'));
    }


}
