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

namespace App\UserdirectoryBundle\Controller;



use App\FellAppBundle\Entity\GlobalFellowshipSpecialty;
use App\OrderformBundle\Entity\Patient;
use App\UserdirectoryBundle\Entity\BuildingList;
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution


use App\UserdirectoryBundle\Entity\Location; //process.py script: replaced namespace by ::class: added use line for classname=Location


use App\UserdirectoryBundle\Entity\ResearchLab; //process.py script: replaced namespace by ::class: added use line for classname=ResearchLab


use App\UserdirectoryBundle\Entity\GrantComment; //process.py script: replaced namespace by ::class: added use line for classname=GrantComment


use App\UserdirectoryBundle\Entity\GrantEffort; //process.py script: replaced namespace by ::class: added use line for classname=GrantEffort


use App\UserdirectoryBundle\Entity\Grant; //process.py script: replaced namespace by ::class: added use line for classname=Grant


use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Entity\UserWrapper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\UserdirectoryBundle\Controller\OrderAbstractController;


use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use App\UserdirectoryBundle\Util\UserUtil;
use App\UserdirectoryBundle\Security\Authentication\AuthUtil;

//TODO: optimise by removing foreach loops:
//create optimalShortName: return abbr, or return short, or return name
//optimalShortNoAbbr: return short, or return name
#[Route(path: '/util')]
class UtilController extends OrderAbstractController {


    
    /**
     * http://127.0.0.1/directory/util/common/generic/city
     * util/common/generic
     */
    #[Route(path: '/common/generic/{name}', name: 'employees_get_generic_select2', methods: ['GET'], options: ['expose' => true])]
    public function getGenericAction( Request $request, $name ) {
        //exit('getGenericAction');
        return $this->getGenericList($request,$name);
    }

    //http://127.0.0.1/directory/util/public/common/generic/city?cycle=new - public
    #[Route(path: '/public/common/generic/{name}', name: 'employees_get_public_generic_select2', methods: ['GET'], options: ['expose' => true])]
    public function getPublicGenericAction( Request $request, $name ) {

        if( $name === 'traininginstitution' ) {
            return $this->getTrainingInstitution($request);
        }

        //exit('getPublicGenericAction');
        if( $name === 'city' ||
            $name === 'traininginstitution' ||
            $name === 'trainingmajors' ||
            $name === 'jobtitle' ||
            $name === 'residencyspecialty'
        ) {
            //Ok. Allow only these above
        } else {
            $output = array();
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }
        return $this->getGenericList($request,$name);
    }

    public function getGenericList( $request, $name ) {

        $cycle = $request->get('cycle');
        $newCycle = false;
        if( $cycle && (strpos((string)$cycle, 'new') !== false || strpos((string)$cycle, 'create') !== false) ) {
            $newCycle = true;
        }

        //echo "name=".$name."<br>";
        $res = $this->getClassBundleByName($name);
        $className = $res['className'];
        $bundleName = $res['bundleName'];

        if( array_key_exists('filterType',$res) ) {
            $filterType = $res['filterType'];
        } else {
            $filterType = NULL;
        }

        //echo "className=".$className."<br>";

        $output = array();

        if( $className ) {

            $em = $this->getDoctrine()->getManager();
            $fullClassName = "App\\".$bundleName."\\Entity\\".$className;

            switch( $className ) {


                case "FellowshipTitleList": //show this object as "abbreviation - name"
                    if( $newCycle && $filterType ) {
                        $optionArr = array('default');
                    } else {
                        $optionArr = array('default','user-added');
                    }

                    //$entities = $em->getRepository('App'.$bundleName.':'.$className)->findBy(
                    //    array('type' => $optionArr)
                    //);
                    $entities = $em->getRepository($fullClassName)->findBy(
                        array('type' => $optionArr)
                    );
                    foreach( $entities as $entity ) {
                        $entityStr = $entity."";
                        if( $entityStr ) {
                            $element = array('id' => $entity->getId(), 'text' => $entityStr);
                            $output[] = $element;
                        }
                    }
                    break;


                default:
                    $query =
                        $em->createQueryBuilder()->from($fullClassName, 'list')
                        ->select("list.id as id, list.name as text")
                        ->orderBy("list.orderinlist","ASC");

                    // Add condition for non-empty names
                    $query->andWhere("list.name IS NOT NULL AND list.name != ''");

                    //$query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
                    if( $newCycle && $filterType ) {
                        $query->andWhere("list.type = :typedef")->setParameters(array('typedef' => 'default'));
                    } else {
                        $query->andWhere("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
                    }

                    $output = $query->getQuery()->getResult();

            } //switch

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }



    #[Route(path: '/common/special/{urlprefix}', name: 'employees_get_special_select2', methods: ['GET'], options: ['expose' => true])]
    public function getSpecialAction( Request $request, $urlprefix ) {

        //$response = null;

        //return $this->getGenericList($request,$urlprefix);
        switch ($urlprefix) {
            case 'fellowshipsubspecialty':
//                $response = $this->redirectToRoute('get-fellowshipsubspecialty-by-parent', [
//                    'request'  => $request
//                ]);
                return $this->redirectToRoute('get-fellowshipsubspecialty-by-parent');
            case 'globalfellowshipspecialty':
                return $this->redirectToRoute('employees_get_globalfellowshipspecialty');
            case 'traininginstitution':
                return $this->redirectToRoute('employees_get_traininginstitution');
            case 'institution-all':
                return $this->redirectToRoute('employees_get_institution-all');
            case 'institution-old':
                return $this->redirectToRoute('employees_get_institution');
            case 'calllogpatientlists':
                return $this->redirectToRoute('employees_get_calllogpatientlists');
            case 'crnpatientlists':
                return $this->redirectToRoute('employees_get_crnpatientlists');
            case 'accessionlists':
                return $this->redirectToRoute('employees_get_accessionlists');
            case 'locationusers':
                return $this->redirectToRoute('employees_get_locationusers');
            case 'building':
                return $this->redirectToRoute('employees_get_building');
            case 'location':
                return $this->redirectToRoute('employees_get_location');
            case 'locationName':
                return $this->redirectToRoute('employees_get_locationname');
            case 'get-location-by-name':
                return $this->redirectToRoute('employees_get_location_by_name');
            case 'specificindividuals':
                return $this->redirectToRoute('employees_get_specificindividuals');
            case 'userwrapper':
                return $this->redirectToRoute('employees_get_userwrapper');
            default:
                exit("COntroller not found by urlprefix=".$urlprefix);
        }

//        if( $response ) {
//            return $response;
//        }

        exit("Invalid response for urlprefix=".$urlprefix);
    }

    #[Route(path: '/common/fellowshipsubspecialty', name: 'get-fellowshipsubspecialty-by-parent', methods: ['GET', 'POST'])]
    public function getDepartmentAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $id = trim((string)$request->get('id') );
        $pid = trim((string)$request->get('pid') );
        //echo "pid=".$pid."<br>";
        //echo "id=".$id."<br>";

        $routeName = $request->get('_route');

        $name = str_replace("get-", "", $routeName);
        $name = str_replace("-by-parent", "", $name);
        $res = $this->getClassBundleByName($name);
        $className = $res['className'];
        $bundleName = $res['bundleName'];

        //echo "className=".$className."<br>";

        if( $className && is_numeric($pid) ) {
            //echo "className=".$className."<br>";
            $query = $em->createQueryBuilder()
                //->from('App'.$bundleName.':'.$className, 'list')
                ->from('App\\'.$bundleName.'\\Entity\\'.$className, 'list')
                ->innerJoin("list.parent", "parent")
                ->select("list.id as id, list.name as text, parent.id as parentid")
                //->select("list.name as id, list.name as text")
                ->orderBy("list.name","ASC");

            $query->where("parent = :pid AND (list.type = :typedef OR list.type = :typeadd)")->setParameters(array('typedef' => 'default','typeadd' => 'user-added','pid'=>$pid));

            $output = $query->getQuery()->getResult();
        } else {
            //echo "pid is not numeric=".$pid."<br>";
            $output = array();
        }

        //add current element by id
        if( $id ) {
            //$entity = $this->getDoctrine()->getRepository('App'.$bundleName.':'.$className)->findOneById($id);
            $entity = $this->getDoctrine()->getRepository("App\\".$bundleName."\\Entity\\".$className)->findOneById($id);
            if( $entity ) {
                if( array_key_exists($entity->getId(), $output) === false ) {
                    $element = array('id'=>$entity->getId(), 'text'=>$entity->getName()."");
                    //$element = array('id'=>$entity->getName()."", 'text'=>$entity->getName()."");
                    $output[] = $element;
                }
            }
        }

        //print_r($output);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/globalfellowshipspecialty', name: 'employees_get_globalfellowshipspecialty', methods: ['GET'])]
    public function getGlobalFellowshipSpecialtyAction() {
        exit('getGlobalFellowshipSpecialtyAction');
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from(GlobalFellowshipSpecialty::class, 'list')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        $locationusers = $query->getQuery()->getResult();

        $output = array();

        $output[] = array('id'=>null,'text'=>'None');
        $output[] = array('id'=>null,'text'=>'Multiple');

        //$output = array_merge($output, $locationusers);

        foreach( $locationusers as $locationuser ) {
            $element = array(
                'id'        => $locationuser->getId(),
                'text'      => $locationuser.""
            );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/traininginstitution', name: 'employees_get_traininginstitution', methods: ['GET'])]
    public function getTrainingInstitutionAction(Request $request) {
//        //exit('getTrainingInstitutionAction');
//        $em = $this->getDoctrine()->getManager();
//
//        $cycle = $request->get('cycle');
//        $newCycle = false;
//        if( $cycle && (strpos((string)$cycle, 'new') !== false || strpos((string)$cycle, 'create') !== false) ) {
//            $newCycle = true;
//        }
//        //echo "cycle=".$cycle." => newCycle=".$newCycle."<br>";
//
//        $query = $em->createQueryBuilder()
//            //->from('AppUserdirectoryBundle:Institution', 'list')
//            ->from(Institution::class, 'list')
//            ->select("list.id as id, list.name as text")
//            ->leftJoin("list.types","types")
//            ->groupBy("list")
//            ->orderBy("list.orderinlist","ASC");
//
//        $query->where("(types.name LIKE :instTypeEducational OR types.name LIKE :instTypeMedical) AND list.level = 0");
//        $paramArr = array('instTypeEducational' => 'Educational','instTypeMedical' => 'Medical');
//
//        if( $newCycle ) {
//            //$query->andWhere("(list.type = :typedef)");
//            //$paramArr['typedef'] = 'default';
//            $query->andWhere("(list.type = :typedef OR list.type = :typeadd)");
//            $paramArr['typedef'] = 'default';
//            $paramArr['typeadd'] = 'user-added';
//        } else {
//            $query->andWhere("(list.type = :typedef OR list.type = :typeadd)");
//            $paramArr['typedef'] = 'default';
//            $paramArr['typeadd'] = 'user-added';
//        }
//
//        $query->setParameters($paramArr);
//
//        $output = $query->getQuery()->getResult();
//
//        //echo "traininginstitution count=".count($output)."<br>";
//        //print_r($output);
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($output));
//        return $response;
        return $this->getTrainingInstitution($request);
    }
    public function getTrainingInstitution(Request $request) {
        //exit('getTrainingInstitutionAction');
        $em = $this->getDoctrine()->getManager();

        $cycle = $request->get('cycle');
        $newCycle = false;
        if( $cycle && (strpos((string)$cycle, 'new') !== false || strpos((string)$cycle, 'create') !== false) ) {
            $newCycle = true;
        }
        //echo "cycle=".$cycle." => newCycle=".$newCycle."<br>";

        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Institution', 'list')
            ->from(Institution::class, 'list')
            ->select("list.id as id, list.name as text")
            ->leftJoin("list.types","types")
            ->groupBy("list")
            ->orderBy("list.orderinlist","ASC");

        $query->where("(types.name LIKE :instTypeEducational OR types.name LIKE :instTypeMedical) AND list.level = 0");
        $paramArr = array('instTypeEducational' => 'Educational','instTypeMedical' => 'Medical');

        if( $newCycle ) {
            //$query->andWhere("(list.type = :typedef)");
            //$paramArr['typedef'] = 'default';
            $query->andWhere("(list.type = :typedef OR list.type = :typeadd)");
            $paramArr['typedef'] = 'default';
            $paramArr['typeadd'] = 'user-added';
        } else {
            $query->andWhere("(list.type = :typedef OR list.type = :typeadd)");
            $paramArr['typedef'] = 'default';
            $paramArr['typeadd'] = 'user-added';
        }

        $query->setParameters($paramArr);

        $output = $query->getQuery()->getResult();

        //echo "traininginstitution count=".count($output)."<br>";
        //print_r($output);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/institution-all', name: 'employees_get_institution_all', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function setInstitutionTreeAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from(Institution::class, 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    #[Route(path: '/common/institution-old/', name: 'employees_get_institution', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getInstitutionAction(Request $request) {

        $id = trim((string)$request->get('id') );

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from(Institution::class, 'list')
            ->select("list.id as id, list.name as text")
            //->select("list.name as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $output = $query->getQuery()->getResult();

        //add current element by id
        if( $id ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
            $entity = $this->getDoctrine()->getRepository(Institution::class)->findOneById($id);
            if( $entity ) {
                if( array_key_exists($entity->getId(), $output) === false ) {
                    $element = array('id'=>$entity->getId(), 'text'=>$entity->getName()."");
                    //$element = array('id'=>$entity->getName()."", 'text'=>$entity->getName()."");
                    $output[] = $element;
                }
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/calllogpatientlists', name: 'employees_get_calllogpatientlists', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getCalllogPatientListsAction(Request $request) {
        $calllogUtil = $this->container->get('calllog_util');
        $patientLists = $calllogUtil->getDefaultPatientLists();

        $output = array();
        foreach ($patientLists as $patientList) {
            $output[] = array(
                'id' => $patientList->getId(),
                'text' => $patientList->getName()
            );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    #[Route(path: '/common/crnpatientlists', name: 'employees_get_crnpatientlists', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getCrnPatientListsAction(Request $request) {
        $crnUtil = $this->container->get('crn_util');
        $patientLists = $crnUtil->getDefaultPatientLists();

        $output = array();
        foreach ($patientLists as $patientList) {
            $output[] = array(
                'id' => $patientList->getId(),
                'text' => $patientList->getName()
            );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/accessionlists', name: 'employees_get_accessionlists', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function getAccessionListsAction(Request $request) {
        $scanorderUtil = $this->container->get('scanorder_utility');
        //Accession list currently is level=1
        $level = 1;
        $accessionLists = $scanorderUtil->getDefaultAccessionLists($level);

        $output = array();
        foreach ($accessionLists as $accessionList) {
            $output[] = array(
                'id' => $accessionList->getId(),
                'text' => $accessionList->getName()
            );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


//    /**
    //     * @Route("/common/commenttype", name="employees_get_commenttype", methods={"GET"})
    //     */
    //    public function getCommenttypeAction() {
    //
    //        $em = $this->getDoctrine()->getManager();
    //
    //        $query = $em->createQueryBuilder()
    //            ->from('AppUserdirectoryBundle:CommentTypeList', 'list')
    //            ->select("list.id as id, list.name as text")
    //            ->orderBy("list.orderinlist","ASC");
    //
    //        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
    //
    //        $output = $query->getQuery()->getResult();
    //
    //        $response = new Response();
    //        $response->headers->set('Content-Type', 'application/json');
    //        $response->setContent(json_encode($output));
    //        return $response;
    //    }
    #[Route(path: '/common/locationusers', name: 'employees_get_locationusers', methods: ['GET'])]
    public function getLocationUsersAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from(User::class, 'list')
            ->select("list")
            //->select("list.id as id, infos.displayName as text")
            ->leftJoin("list.infos", "infos")
            ->leftJoin("list.employmentStatus", "employmentStatus")
            ->leftJoin("employmentStatus.employmentType", "employmentType")
            ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
            ->orderBy("infos.displayName","ASC");

        $locationusers = $query->getQuery()->getResult();

        $output = array();

        $output[] = array('id'=>null,'text'=>'None');
        $output[] = array('id'=>null,'text'=>'Multiple');

        //$output = array_merge($output, $locationusers);

        foreach( $locationusers as $locationuser ) {
            $element = array(
                'id'        => $locationuser->getId(),
                'text'      => $locationuser.""
            );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * get all users except fellowship
     */
    #[Route(path: '/common/genericusers/{usertype}', name: 'employees_get_genericusers', methods: ['GET'], options: ['expose' => true])]
    public function getGenericUsersAction(Request $request, $usertype=null) {

        $em = $this->getDoctrine()->getManager();

        $cycle = $request->get('cycle');
        //echo "cycle=".$cycle."<br>";
        $newCycle = false;
        if( $cycle && (strpos((string)$cycle, 'new') !== false || strpos((string)$cycle, 'create') !== false) ) {
            $newCycle = true;
        }

        $repository = $em->getRepository(User::class);
        $dql = $repository->createQueryBuilder("user");
        $dql->leftJoin("user.infos", "infos");

        //$dql->select('user.id as id, infos.displayName as text');

        $dql->leftJoin("user.keytype", "keytype");
        //$dql->select("user.id as id, CONCAT(infos.displayName,' (',keytype.name,')') as text"); //display user as "displayName (keytype)"
        $dql->select("user.id as id, 
            (CASE WHEN user.keytype IS NULL 
                THEN infos.displayName 
                ELSE CONCAT(infos.displayName,' (',keytype.name,')') END
            ) as text"); //display user as "displayName (keytype)"

        $dql->where("user.createdby != 'googleapi' AND infos.displayName IS NOT NULL"); //googleapi is used only by fellowship application population

        if( $newCycle ) {
            $dql->leftJoin("user.preferences", "preferences");
            $dql->andWhere("preferences.hide IS NULL OR preferences.hide=false");
        }

        $dql->orderBy("infos.displayName","ASC");
        //$dql->groupBy('user');

        $query = $dql->getQuery();

        $users = $query->getResult();

        //$output = array();

//        foreach( $users as $user ) {
//            $element = array(
//                'id'        => $user->getId(),
//                'text'      => $user.""
//            );
//            $output[] = $element;
//        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($users));
        return $response;
    }

    #[Route(path: '/common/building', name: 'employees_get_building', methods: ['GET'])]
    public function getBuildingsAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $cycle = $request->get('cycle');
        $newCycle = false;
        if( $cycle && (strpos((string)$cycle, 'new') !== false || strpos((string)$cycle, 'create') !== false) ) {
            $newCycle = true;
        }

        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:BuildingList', 'list')
            ->from(BuildingList::class, 'list')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->getUser();

        if( $newCycle ) {
            $query->where("list.type = :typedef")->setParameters(array('typedef' => 'default'));
        } else {
            $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
        }

        $buildings = $query->getQuery()->getResult();

        $output = array();
        foreach($buildings as $building) {
            $geoloc = $building->getGeoLocation();

            $street1 = null;
            $street2 = null;
            $city = null;
            $county = null;
            $zip = null;
            if( $geoloc ) {
                $street1 = $geoloc->getStreet1();
                $street2 = $geoloc->getStreet2();
                $city = $geoloc->getCity();
                $county = $geoloc->getCounty();
                $zip = $geoloc->getZip();
            }
            $element = array(
                'id'        => $building->getId(),
                'text'      => $building."",
                'street1'   => $street1,
                'street2'   => $street2,
                'city'      => $city,
                'county'    => $county,
                'country'   => ( $geoloc && $geoloc->getCountry() ? $geoloc->getCountry()->getId() : null ),
                'state'     => ( $geoloc && $geoloc->getState() ? $geoloc->getState()->getId() : null ),
                'zip'       => $zip,
            );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    #[Route(path: '/common/location', name: 'employees_get_location', methods: ['GET'])]
    public function getLocationAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Location', 'list')
            ->from(Location::class, 'list')
            ->select("list")
            ->orderBy("list.id","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        //Exclude from the list locations of type "Patient Contact Information", "Medical Office", and "Inpatient location".
        $andWhere = "locationTypes.name IS NULL OR ".
                    "(" .
                        "locationTypes.name !='Patient Contact Information' AND ".
                        "locationTypes.name !='Medical Office' AND ".
                        "locationTypes.name !='Inpatient location' AND ".
                        "locationTypes.name !='Employee Home'" .
                    ")";

        $query->leftJoin("list.locationTypes", "locationTypes");
        $query->leftJoin("list.user", "user");
        $query->andWhere($andWhere);

        //exclude system user:  "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'"; //"user.email != '-1'"
        $query->andWhere("user.id IS NULL OR (user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system')");

        //do not show (exclude) all locations that are tied to a user who has no current employment periods (all of whose employment periods have an end date)
        $curdate = date("Y-m-d", time());
        $query->leftJoin("user.employmentStatus", "employmentStatus");
        $currentusers = "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
        $query->andWhere($currentusers);

        //echo "query=".$query." | ";

        $locations = $query->getQuery()->getResult();
        //echo "loc count=".count($locations)."<br>";

        $output = array();

        foreach( $locations as $location ) {
            $element = array('id'=>$location->getId(), 'text'=>$location->getNameFull());
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/locationName', name: 'employees_get_locationname', methods: ['GET'])]
    public function getLocationNameAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Location', 'list')
            ->from(Location::class, 'list')
            ->select("list")
            ->orderBy("list.id","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $query->leftJoin("list.locationTypes", "locationTypes");
        //$query->leftJoin("list.user", "user");
        $query->andWhere("locationTypes.name = 'Encounter Location'");

        //exclude system user:  "user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'"; //"user.email != '-1'"
        //$query->andWhere("user.id IS NULL OR (user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system')");

        //do not show (exclude) all locations that are tied to a user who has no current employment periods (all of whose employment periods have an end date)
        //$curdate = date("Y-m-d", time());
        //$query->leftJoin("user.employmentStatus", "employmentStatus");
        //$currentusers = "employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."'";
        //$query->andWhere($currentusers);

        //echo "query=".$query." | ";

        $locations = $query->getQuery()->getResult();
        //echo "loc count=".count($locations)."<br>";

        $output = array();

        foreach( $locations as $location ) {
            $element = array('id'=>$location->getId(), 'text'=>$location->getNameFull());
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/get-location-by-name/', name: 'employees_get_location_by_name', methods: ['GET'], options: ['expose' => true])]
    public function getLocationByNameAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $locationId = trim((string)$request->get('locationId'));

        if( strval($locationId) == strval(intval($locationId)) ) {
            //echo "locationId is integer<br>";
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
            $location = $em->getRepository(Location::class)->find($locationId);
        } else {
            //echo "locationId is string<br>";
            $location = null;
        }

        $output = array();

        if( $location ) {

            $locationTypes = array();
            foreach( $location->getLocationTypes() as $locationType ) {
                $locationTypes[] = $locationType->getId();
            }

            $geoLocationBuilding = null;
            $buildingId = null;
            if( $location->getBuilding() ) {
                $buildingId = $location->getBuilding()->getId();
                $geoLocationBuilding = $location->getBuilding()->getGeoLocation();
            }

            $output['id'] = $location->getId();
            $output['locationTypes'] = $locationTypes;
            $output['phone'] = $location->getPhone();
            $output['room'] = ($location->getRoom()) ? $location->getRoom()->getId() : null;
            $output['suite'] = ($location->getSuite()) ? $location->getSuite()->getId() : null;
            $output['floor'] = ($location->getFloor()) ? $location->getFloor()->getId() : null;
            $output['floorSide'] = $location->getFloorSide();
            $output['building'] = $buildingId;
            $output['comment'] = $location->getComment();

            $output['institution'] = ($location->getInstitution()) ? $location->getInstitution()->getId() : null; //$location->getInstitution();

            $geoLocation = $location->getGeoLocation();

            //priority is on location's geo object
            $street1 = null;
            if( $geoLocation && $geoLocation->getStreet1() ) {
                $street1 = $geoLocation->getStreet1();
            } else {
                if( $geoLocationBuilding && $geoLocationBuilding->getStreet1() ) {
                    $street1 = $geoLocationBuilding->getStreet1();
                }
            }

            $street2 = null;
            if( $geoLocation && $geoLocation->getStreet2() ) {
                $street2 = $geoLocation->getStreet2();
            } else {
                if( $geoLocationBuilding && $geoLocationBuilding->getStreet2() ) {
                    $street2 = $geoLocationBuilding->getStreet2();
                }
            }

            $cityId = null;
            if( $geoLocation && $geoLocation->getCity() ) {
                $cityId = $geoLocation->getCity()->getId();
            } else {
                if( $geoLocationBuilding && $geoLocationBuilding->getCity() ) {
                    $cityId = $geoLocationBuilding->getCity()->getId();
                }
            }

            $countryId = null;
            if( $geoLocation && $geoLocation->getCountry() ) {
                $countryId = $geoLocation->getCountry()->getId();
            } else {
                if( $geoLocationBuilding && $geoLocationBuilding->getCountry() ) {
                    $countryId = $geoLocationBuilding->getCountry()->getId();
                }
            }

            $county = null;
            if( $geoLocation && $geoLocation->getCounty() ) {
                $county = $geoLocation->getCounty();
            } else {
                if( $geoLocationBuilding && $geoLocationBuilding->getCounty() ) {
                    $county = $geoLocationBuilding->getCounty();
                }
            }

            $zip = null;
            if( $geoLocation && $geoLocation->getZip() ) {
                $zip = $geoLocation->getZip();
            } else {
                if( $geoLocationBuilding && $geoLocationBuilding->getZip() ) {
                    $zip = $geoLocationBuilding->getZip();
                }
            }

            $output['street1'] = $street1;  //$geoLocation->getStreet1();
            $output['street2'] = $street2;  //$geoLocation->getStreet2();
            $output['city'] = $cityId;  //($geoLocation->getCity()) ? $geoLocation->getCity()->getId() : null;
            $output['country'] = $countryId;    //($geoLocation->getCountry()) ? $geoLocation->getCountry()->getId() : null;
            $output['county'] = $county;    //$geoLocation->getCounty();
            $output['zip'] = $zip;  //$geoLocation->getZip();

//            if( $geoLocation ) {
//                $output['street1'] = $geoLocation->getStreet1();
//                $output['street2'] = $geoLocation->getStreet2();
//                $output['city'] = ($geoLocation->getCity()) ? $geoLocation->getCity()->getId() : null;
//                $output['country'] = ($geoLocation->getCountry()) ? $geoLocation->getCountry()->getId() : null;
//                $output['county'] = $geoLocation->getCounty();
//                $output['zip'] = $geoLocation->getZip();
//            } else {
//                $output['street1'] = null;
//                $output['street2'] = null;
//                $output['city'] = null;
//                $output['country'] = null;
//                $output['county'] = null;
//                $output['zip'] = null;
//            }

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * check if location can be deleted
     */
    #[Route(path: '/common/location/delete/{id}', name: 'employees_location_delete', methods: ['GET'], requirements: ['id' => '\d+'], options: ['expose' => true])]
    public function getLocationCheckDeleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Location'] by [Location::class]
        $location = $em->getRepository(Location::class)->find($id);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResearchLab'] by [ResearchLab::class]
        $resLabs = $em->getRepository(ResearchLab::class)->findByLocation($location);
        if( count($resLabs) > 0 ) {
            $output = 'not ok';
        } else {
            $output = 'ok';
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    //search if $needle exists in array $products
    public function in_complex_array($needle,$products,$indexstr='text') {
        foreach( $products as $product ) {
            if ( $product[$indexstr] === $needle ) {
                return true;
            }
        }
        return false;
    }


    #[Route(path: '/common/researchlab/{id}/{subjectUser}', name: 'employees_get_researchlab', methods: ['GET'], options: ['expose' => true])]
    public function getResearchlabByIdAction( $id, $subjectUser=null ) {

        if( !is_numeric($id) ) {
            //echo "return null";
            $output = array();
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        //$subjectUserDB = $em->getRepository(User::class)->find($subjectUser);
//        $researchLabDB = $em->getRepository('AppUserdirectoryBundle:ResearchLab')->find($id);
//        if( !$researchLabDB ) {
//            $response = new Response();
//            $response->headers->set('Content-Type', 'application/json');
//            $response->setContent(null);
//            return $response;
//        }

        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:ResearchLab', 'list')
            ->from(ResearchLab::class, 'list')
            ->leftJoin('list.institution','institution')
            ->leftJoin('list.location','location')
            ->leftJoin('list.comments','comments')
            ->leftJoin('comments.author','commentauthor')
            ->leftJoin('list.pis','pis')
            ->leftJoin('pis.pi','piauthor')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->getUser();

        $query->where("institution.id=".$id);

        $labs = $query->getQuery()->getResult();
        //echo "labs count=".count($labs)."<br>";

        $output = array();

        foreach( $labs as $lab ) {

//            if( $subjectUser && is_numeric($subjectUser) ) {
//                $subjectUserDB = $em->getRepository(User::class)->find($subjectUser);
//            } else {
//                $subjectUserDB = null;
//            }
            
            //$commentDb = $em->getRepository('AppUserdirectoryBundle:ResearchLabComment')->findOneBy( array( 'author' => $subjectUserDB, 'researchLab'=>$researchLabDB ) );
            //$piDb = $em->getRepository('AppUserdirectoryBundle:ResearchLabPI')->findOneBy( array( 'pi'=>$subjectUserDB, 'researchLab'=>$researchLabDB ) );

            $transformer = new DateTimeToStringTransformer(null,null,'MM/dd/yyyy');

            $element = array(
                'id'            => $lab->getId(),
                'text'          => $lab."",
                'weblink'       => $lab->getWeblink(),
                'lablocation'   => ( $lab->getLocation() ? $lab->getLocation()->getId() : null ),
                'foundedDate'   => $transformer->transform($lab->getFoundedDate()),
                'dissolvedDate' => $transformer->transform($lab->getDissolvedDate()),
                //'commentDummy'  => ( $commentDb ? $commentDb->getComment() : null ),
                //'piDummy'       => ( $piDb ? $piDb->getPi()->getId() : null ),
            );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * check if researchlab can be deleted
     */
    #[Route(path: '/common/researchlab/deletefromuser/{id}/{subjectUser}', name: 'employees_researchlab_deletefromuser', methods: ['DELETE'], options: ['expose' => true])]
    public function researchLabDeleteAction($id, $subjectUser=null) {

        if( !$subjectUser || $subjectUser == 'undefined' ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode('no subject user'));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($subjectUser);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResearchLab'] by [ResearchLab::class]
        $lab = $em->getRepository(ResearchLab::class)->find($id);

        $output = 'not ok';

        //more effificient than looping (?)
        if( $user && $lab ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:ResearchLab'] by [ResearchLab::class]
            $em->getRepository(ResearchLab::class)->removeDependents( $user, $lab );

            $user->removeResearchLab($lab);
            $em->persist($user);
            $em->flush();
            $output = 'ok';
        }

//        foreach( $user->getResearchLabs() as $lab ) {
//            if( $lab->getId() == $id ) {
//                $user->removeResearchLab($lab);
//                $em->persist($user);
//                $em->flush();
//                $output = 'ok';
//                break;
//            }
//        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    #[Route(path: '/common/grant/{id}/{subjectUser}', name: 'employees_get_grant', methods: ['GET'], options: ['expose' => true])]
    public function getGrantByIdAction( $id, $subjectUser=null ) {

        $userServiceUtil = $this->container->get('user_service_utility');

        if( !is_numeric($id) ) {
            //echo "return null";
            $output = array();
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();       

        $query = $em->createQueryBuilder()
            //->from('AppUserdirectoryBundle:Grant', 'list')
            ->from(Grant::class, 'list')
            ->leftJoin('list.sourceOrganization','sourceOrganization')
            ->leftJoin('list.attachmentContainer','attachmentContainer')
            ->leftJoin('list.comments','comments')
            ->leftJoin('comments.author','commentauthor')
            ->leftJoin('list.efforts','efforts')
            ->leftJoin('efforts.author','effortauthor')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->getUser();

        $query->where("list.id=".$id);

        $grants = $query->getQuery()->getResult();

        //echo "grant count=".count($grants)."<br>";

        $grant = null;

        if( count($grants) == 1 ) {
            $grant = $grants[0];
        }

        $output = array();

        if( $grant ) {
            
            if( $subjectUser && is_numeric($subjectUser) ) {
                $subjectUserDB = $em->getRepository(User::class)->find($subjectUser);
            } else {
                $subjectUserDB = null;
            }

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:GrantComment'] by [GrantComment::class]
            $userComment = $em->getRepository(GrantComment::class)->findOneBy( array( 'author' => $subjectUserDB, 'grant'=>$grant ) );
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:GrantEffort'] by [GrantEffort::class]
            $userEffort = $em->getRepository(GrantEffort::class)->findOneBy( array( 'author'=>$subjectUserDB, 'grant'=>$grant ) );

            $transformer = new DateTimeToStringTransformer(null,null,'MM/dd/yyyy');

            $documentContainers = array();

            if( $grant->getAttachmentContainer() && count($grant->getAttachmentContainer()->getDocumentContainers()) > 0 ) {

                foreach( $grant->getAttachmentContainer()->getDocumentContainers() as $documentConatiner ) {
                    $documentContainer = array();
                    $documentContainer['id'] = $documentConatiner->getId();
                    $documentContainer['text'] = $documentConatiner."";

                    $documents = array();
                    foreach( $documentConatiner->getDocuments() as $document ) {
                        $documentJson = array();
                        $documentJson['id'] =  $document->getId();
                        $documentJson["uniquename"] = $document->getUniquename();
                        $documentJson["originalname"] = $document->getOriginalnameClean();
                        $documentJson["size"] = $document->getSize();
                        //$documentJson["url"] = $document->getAbsoluteUploadFullPath();
                        $documentJson["url"] = $userServiceUtil->getDocumentAbsoluteUrl($document);
                        $documents[] = $documentJson;
                    }

                    $documentContainer['documents'] = $documents;
                }

                $documentContainers[] = $documentContainer;
            }

            $element = array(
                'id'                    => $grant->getId(),
                'text'                  => $grant."",
                'sourceOrganization'    => ( $grant->getSourceOrganization() ? $grant->getSourceOrganization()->getId() : null ),
                'startDate'             => $transformer->transform($grant->getStartDate()),
                'endDate'               => $transformer->transform($grant->getEndDate()),
                'grantLink'             => $grant->getGrantLink(),
                'grantid'               => $grant->getGrantid(),
                'amount'                => $grant->getAmount(),
                'currentYearDirectCost'     => $grant->getCurrentYearDirectCost(),
                'currentYearIndirectCost'   => $grant->getCurrentYearIndirectCost(),
                'totalCurrentYearCost'      => $grant->getTotalCurrentYearCost(),
                'amountLabSpace'            => $grant->getAmountLabSpace(),
                'comment'                   => ( $userComment ? $userComment->getComment() : null ),
                'effort'                    => ( $userEffort ? $userEffort->getEffort()->getId() : null ),
                'documentContainers'        => $documentContainers,
            );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * check if grant can be deleted
     */
    #[Route(path: '/common/grant/deletefromuser/{id}/{subjectUser}', name: 'employees_grant_deletefromuser', methods: ['DELETE'], options: ['expose' => true])]
    public function grantDeleteAction($id, $subjectUser=null) {

        if( !$subjectUser || $subjectUser == 'undefined' ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode('no subject user'));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($subjectUser);
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Grant'] by [Grant::class]
        $grant = $em->getRepository(Grant::class)->find($id);

        $output = 'not ok';

        //more effificient than looping (?)
        if( $user && $grant ) {

        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Grant'] by [Grant::class]
            $em->getRepository(Grant::class)->removeDependents( $user, $grant );

            $user->removeGrant($grant);
            $em->persist($user);
            $em->flush();
            $output = 'ok';
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    #[Route(path: '/cwid', name: 'employees_check_cwid', methods: ['GET'])]
    public function checkCwidAction(Request $request) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $cwid = trim((string)$request->get('number') );
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneByUsername($cwid);

        $output = array();
        if( $user ) {
            $element = array('id'=>$user->getId(), 'firstName'=>$user->getFirstName(), 'lastName'=>$user->getLastName() );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    #[Route(path: '/ssn', name: 'employees_check_ssn', methods: ['GET'])]
    public function checkSsnAction(Request $request) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $ssn = trim((string)$request->get('number') );
        $em = $this->getDoctrine()->getManager();

        $users = null;
        $user = null;

        if( $ssn != "" ) {
            $query = $em->createQueryBuilder()
                //->from('AppUserdirectoryBundle:User', 'user')
                ->from(User::class, 'user')
                ->select("user")
                ->leftJoin("user.credentials", "credentials")
                ->where("credentials.ssn = :ssn")
                ->setParameter('ssn', $ssn)
                ->getQuery();

            $users = $query->getResult();
        }

        $output = array();
        if( $users && count($users) > 0 ) {
            $user = $users[0];
            $element = array('id'=>$user->getId(), 'firstName'=>$user->getFirstName(), 'lastName'=>$user->getLastName() );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Identifier (i.e. EIN)
     */
    #[Route(path: '/ein', name: 'employees_check_ein', methods: ['GET'])]
    public function checkEinAction(Request $request) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $ein = trim((string)$request->get('number') );

        $em = $this->getDoctrine()->getManager();

        $user = null;

        if( $ein != "" ) {
            $query = $em->createQueryBuilder()
                //->from('AppUserdirectoryBundle:User', 'user')
                ->from(User::class, 'user')
                ->select("user")
                ->leftJoin("user.credentials", "credentials")
                ->where("credentials.employeeId = :employeeId")
                ->setParameter('employeeId', $ein)
                ->getQuery();

            $users = $query->getResult();
        }

        $output = array();
        if( $users && count($users) > 0 ) {
            $user = $users[0];
            $element = array('id'=>$user->getId(), 'firstName'=>$user->getFirstName(), 'lastName'=>$user->getLastName() );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    #[Route(path: '/usertype-userid', name: 'employees_check_usertype-userid', methods: ['GET'], options: ['expose' => true])]
    public function checkUsertypeUseridAction(Request $request) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userType = trim((string)$request->get('userType') );
        $userId = trim((string)$request->get('userId') );

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy(array('keytype'=>$userType,'primaryPublicUserId'=>$userId));

        $output = array();
        if( $user ) {
            $element = array('id'=>$user->getId(), 'firstName'=>$user->getFirstName(), 'lastName'=>$user->getLastName() );
            $output[] = $element;
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    
    
    /**
     * Check if this cwid exists in LDAP active directory
     * http://localhost/order/directory/util/ldap-usertype-userid?userId=cwid
     */
    #[Route(path: '/ldap-usertype-userid', name: 'employees_check_ldap-usertype-userid', methods: ['GET'], options: ['expose' => true])]
    public function checkCWIDUsertypeUseridAction(Request $request) {

        if( false === $this->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }
       
        $userId = trim((string)$request->get('userId') ); //cwid
        //$userTypeText = trim((string)$request->get('userTypeText') );
        //echo "userId=$userId<br>";
        //exit('111');

        $output = "ok";
        
        $authUtil = $this->container->get('authenticator_utility');
        
        //search this user if exists in ldap directory
        //$searchRes might be -1 => can not bind to LDAP server, meaning running on testing localhost outside cornell.edu network => user is ok
        //One user has "cn" as displayName rather than cwid. This causes the error that this user does not exists in LDAP
        $useLdapSearch = TRUE;
        if( $useLdapSearch ) {
            $searchRes = $authUtil->searchLdap($userId); //TODO: use ldapType=2 too
            if ($searchRes == NULL || count($searchRes) == 0) {
                $output = "notok";
            }
        }
        exit($output);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * Used by typeahead js
     */
    #[Route(path: '/common/user-data-search/{type}/{limit}/{search}', name: 'employees_user-data-search', methods: ['GET'], options: ['expose' => true])]
    public function getUserDataSearchAction(Request $request) {

        $type = trim((string)$request->get('type') );
        $search = trim((string)$request->get('search') );
        $limit = trim((string)$request->get('limit') );

        //clean $search
        $search = str_replace("'","",$search);
        $search = str_replace('"','',$search);
        $search = preg_replace('/[^A-Za-z0-9\-]/', '', $search); // Removes special chars.

        //echo "type=".$type."<br>";
        //echo "search=".$search."<br>";
        //echo "limit=".$limit."<br>";
        //exit();

        $em = $this->getDoctrine()->getManager();
        $userUtil = $this->container->get('user_utility');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $dql =  $repository->createQueryBuilder("user");
        $dql->select("user.id as id, infos.displayName as text, user.username as username, keytype.id as keytypeid");
        $dql->leftJoin("user.keytype", "keytype");
        $dql->leftJoin("user.infos", "infos");
        $dql->leftJoin("user.preferences", "preferences");

        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");

        //$dql->leftJoin("user.researchLabs", "researchLabs");
        $dql->groupBy('user');
        $dql->addGroupBy('keytype');
        $dql->addGroupBy('infos');
        $dql->orderBy("infos.displayName","ASC");


        $object = null;

        if( $type == "user" ) {
            if( $search == "prefetchmin" ) {
                $criteriastr = "infos.displayName IS NOT NULL";
            } else {
                //$criteriastr = "LOWER(infos.displayName) LIKE LOWER('%".$search."%')";
                $criteriastr = "LOWER(infos.displayName) LIKE LOWER(:search)";
            }
        }

        if( $type == "institution" ) {
            $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
            $dql->leftJoin("administrativeTitles.institution", "administrativeInstitution");
            $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
            $dql->leftJoin("appointmentTitles.institution", "appointmentInstitution");
            $dql->leftJoin("user.medicalTitles", "medicalTitles");
            $dql->leftJoin("medicalTitles.institution", "medicalInstitution");

            if( $search == "prefetchmin" ) {
                $criteriastr = "administrativeInstitution.name IS NOT NULL OR ";
                $criteriastr .= "appointmentInstitution.name IS NOT NULL OR ";
                $criteriastr .= "medicalInstitution.name IS NOT NULL";
            } else {
                //$criteriastr = "administrativeInstitution.name LIKE '%".$search."%' OR ";
                //$criteriastr .= "appointmentInstitution.name LIKE '%".$search."%' OR ";
                //$criteriastr .= "medicalInstitution.name LIKE '%".$search."%'";

                $criteriastr = "administrativeInstitution.name LIKE :search OR ";
                //$criteriastr .= "administrativeInstitution.abbreviation LIKE '%".$search."%' OR ";
                $criteriastr .= "appointmentInstitution.name LIKE :search OR ";
                $criteriastr .= "medicalInstitution.name LIKE :search";
            }

            //time
            $criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            $criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'appointmentInstitution', $criteriastr );
            $criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }

        if( $type == "cwid" || $type == "single" ) {
            if( $search == "prefetchmin" ) {
                $criteriastr = "user.primaryPublicUserId IS NOT NULL";
            } else {
                //$criteriastr = "user.primaryPublicUserId LIKE '%".$search."%'";
                $criteriastr = "user.primaryPublicUserId LIKE :search";
            }
        }

        //administrative appointment title
        if( $type == "admintitle" ) { //|| $type == "single"
            $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
            $dql->leftJoin("administrativeTitles.name", "adminTitleName");
            if( $search == "prefetchmin" ) {
                $criteriastr = "adminTitleName.name IS NOT NULL";
            } else {
                //$criteriastr = "adminTitleName.name LIKE '%".$search."%'";
                $criteriastr = "adminTitleName.name LIKE :search";
            }

            //time
            $criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            //$criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'appointmentInstitution', $criteriastr );
            //$criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }

        //academic title
        if( $type == "academictitle" ) {
            $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
            $dql->leftJoin("appointmentTitles.name", "appointmentTitleName");
            if( $search == "prefetchmin" ) {
                $criteriastr = "appointmentTitleName.name IS NOT NULL";
            } else {
                //$criteriastr = "appointmentTitleName.name LIKE '%".$search."%'";
                $criteriastr = "appointmentTitleName.name LIKE :search";//
            }

            //time
            //$criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            $criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            //$criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }

        //medical title
        if( $type == "medicaltitle" ) {
            $dql->leftJoin("user.medicalTitles", "medicalTitles");
            $dql->leftJoin("medicalTitles.name", "medicalTitleName");
            if( $search == "prefetchmin" ) {
                $criteriastr = "medicalTitleName.name IS NOT NULL";
            } else {
                //$criteriastr = "medicalTitleName.name LIKE '%".$search."%'";
                $criteriastr = "medicalTitleName.name LIKE :search";
            }

            //time
            $criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }


        if( $type == "single" ) {

//            $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
//            $dql->leftJoin("administrativeTitles.name", "adminTitleName");
//
//            if( $search == "prefetchmin" ) {
//                $criteriastr = "adminTitleName.name IS NOT NULL";
//            } else {
//                $criteriastr = "adminTitleName.name LIKE '%".$search."%'";
//            }
//
//            //time
//            $criteriastr = $userUtil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );

            //echo "0 criteriastr=".$criteriastr."<br>";

            if( $criteriastr ) {
                $criteriastr = $criteriastr . " OR ";
            }

            if( $search == "prefetchmin" ) {
                $criteriastr .= "infos.displayName IS NOT NULL";
            } else {
                //$criteriastr .= "LOWER(infos.displayName) LIKE LOWER('%".$search."%')";
                $criteriastr .= "LOWER(infos.displayName) LIKE LOWER(:search)";
            }

            //echo "1 criteriastr=".$criteriastr."<br>";

        }

        //filter out Pathology Fellowship Applicants
        $criteriastr = "(".$criteriastr . ") AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)";

        //filter out previous users
        $curdate = date("Y-m-d", time());
        $criteriastr .= " AND (employmentStatus.id IS NULL OR employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."')";

        //filter out users with excludeFromSearch set to true
        if( false === $this->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $criteriastr .= " AND (preferences.excludeFromSearch IS NULL OR preferences.excludeFromSearch = FALSE)";
            //$criteriastr .= " AND (preferences.excludeFromSearch = TRUE)";
        }

        //echo "criteriastr=".$criteriastr."<br>";
        //exit();

        $dql->where($criteriastr);

        //$query = $em->createQuery($dql)->setMaxResults($limit);
        $query = $dql->getQuery();
        $query->setMaxResults($limit);

        if( str_contains($criteriastr,':search') ) {
            $query->setParameters(
                array(
                    ':search' => '%'.$search.'%',
                )
            );
        }

        $output = $query->getResult();
        //dump($output);
        //exit();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * It is used in User mrn type where the link is created between user and patient
     */
    #[Route(path: '/common/mrntype-identifier', name: 'employees_check_mrntype_identifier', methods: ['GET'], options: ['expose' => true])]
    public function checkMrntypeIdentifierAction(Request $request) {

        if( false === $this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $mrntype = $request->get('mrntype');
        $identifier = $request->get('identifier');

        $em = $this->getDoctrine()->getManager();

//        $keytype = $em->getRepository('AppOrderformBundle:MrnType')->find($mrntype);
//        //construct patient
//        $patientMrn = new PatientMrn();
//        $patient = new Patient();
//        $patientMrn->setStatus('valid');
//        $patientMrn->setField($identifier);
//        $patientMrn->setKeytype($keytype);
//        $patient->addMrn($patientMrn);
//
//        $patientDb = $em->getRepository('AppOrderformBundle:Patient')->findUniqueByKey($patient);


        $query = $em->createQueryBuilder()
            //->from('AppOrderformBundle:Patient', 'patient')
            ->from(Patient::class, 'patient')
            ->select("patient")
            ->leftJoin("patient.mrn", "mrn")
            ->where("mrn.keytype = :keytype AND mrn.field = :field")
            ->setParameters( array('keytype'=>$mrntype,'field'=>$identifier) )
            ->getQuery();

        $patients = $query->getResult();
        //echo "patient count=".count($patients)." ";

        if( count($patients) > 0 ) {
            $output = "OK";
        } else {
            $output = "Invalid";
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    #[Route(path: '/common/check-user-password', name: 'employees_check_user_password', methods: ['POST'], options: ['expose' => true])]
    public function checkUserPasswordAction( Request $request ) {

        if( false === $this->isGranted('IS_AUTHENTICATED_FULLY') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userid = trim((string)$request->get('userid') );
        $userpassword = trim((string)$request->get('userpassword') );

        $user = $this->getUser();
        if( $userid != $user->getId() ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $output = 'notok';

        $em = $this->getDoctrine()->getManager();

        $subjectUser = $em->getRepository(User::class)->find($userid);

        //$encoder = $this->container->get('security.password_encoder');
        //$encodeRes = $encoder->isPasswordValid($user,$userpassword);
        $authUtil = $this->container->get('authenticator_utility');
        $encodeRes = $authUtil->isPasswordValid($user,$userpassword);

        if( $encodeRes ) {
            $output = 'ok';
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * Get all users and user wrappers combined
     */
    #[Route(path: '/common/specificindividuals', name: 'employees_get_specificindividuals', methods: ['GET'])]
    public function getSpecificIndividualsAction(Request $request) {
        return $this->getUserWrappersAction($request);
    }

    /**
     * Get all users and user wrappers combined (previously getProxyusersAction)
     */
    #[Route(path: '/common/userwrapper', name: 'employees_get_userwrapper', methods: ['GET'])]
    public function getUserWrappersAction(Request $request) {

        //exit('getUserWrappersAction');

        $em = $this->getDoctrine()->getManager();
        $loggedUser = $this->getUser();
        $securityUtil = $this->container->get('user_security_utility');
        $cycle = $request->query->get('cycle');

        $output = array();

        ///////////// 1) get all real users /////////////
        if(0) {
            $query = $em->createQueryBuilder()
                ->from(User::class, 'list')
                ->select("list")
                //->groupBy('list.id')
                ->leftJoin("list.infos", "infos")
                ->leftJoin("list.employmentStatus", "employmentStatus")
                ->leftJoin("employmentStatus.employmentType", "employmentType")
                ->where("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                ->andWhere("(list.testingAccount = false OR list.testingAccount IS NULL)")
                ->andWhere("(list.keytype IS NOT NULL AND list.primaryPublicUserId != 'system')")
                ->orderBy("infos.displayName", "ASC");

            $users = $query->getQuery()->getResult();
            //echo "users count=".count($users)."<br>";

            foreach ($users as $user) {
                $element = array('id' => $user."", 'text' => $user . "");
                //$element = array('id' => $user->getUsername()."", 'text' => $user . "");
                //$element = array('id' => $user->getId(), 'text' => $user . "");
                //if( !$this->in_complex_array($user."",$output,'text') ) {
                $output[] = $element;
                //}
            }
        }
        if(1) {
            //Optimising (lighter) version without loop.
            // Using infos.displayName - "displayName" instead of user's toString (getUserNameStr) - "displayName - cwid (keytype)"
            $query = $em->createQueryBuilder()
                //->from('AppUserdirectoryBundle:User', 'list')
                ->from(User::class, 'list')
                ->select("infos.displayName as id, infos.displayName as text")
                //->groupBy('list.id')
                ->leftJoin("list.infos", "infos")
                ->leftJoin("list.employmentStatus", "employmentStatus")
                ->leftJoin("employmentStatus.employmentType", "employmentType")
                ->where("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                ->andWhere("(list.testingAccount = false OR list.testingAccount IS NULL)")
                ->andWhere("(list.keytype IS NOT NULL AND list.primaryPublicUserId != 'system')")
                ->andWhere("infos.displayName IS NOT NULL")
                ->orderBy("infos.displayName", "ASC")
            ;

            $output = $query->getQuery()->getResult();
            //echo "users count=".count($output)."<br>";
        }
        ///////////// EOF 1) get all real users /////////////


        $sourceSystem = $securityUtil->getDefaultSourceSystemByRequest($request);

        ///////////// 2) default user wrappers for this source ///////////////
        ///////////// 3) user-added user wrappers created by logged in user for this source ///////////////
        if(1) {
            $query = $em->createQueryBuilder()
                //->from('AppUserdirectoryBundle:UserWrapper', 'list')
                ->from(UserWrapper::class, 'list')
                ->select("list")
                ->leftJoin("list.user", "user")
                ->leftJoin("user.infos", "infos")
                ->leftJoin("list.creator", "creator")
                ->leftJoin("list.userWrapperSource", "userWrapperSource")
                ->orderBy("infos.displayName", "ASC");

            //default OR user-added user wrappers created by logged in user
            //$query->andWhere("list.type=:default");
            //echo "cycle=".$cycle."<br>";
            if( $cycle != "show" && $cycle != "edit" && $cycle != "amend" ) {
                $query->where("list.type = :typedef OR (list.type = :typeadd AND creator.id=:loggedUser)")->setParameters(
                    array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                        'loggedUser' => $loggedUser->getId()
                    )
                );
            }

            if( $sourceSystem ) {
                //echo "sourceSystem: id=".$sourceSystem->getId()."; ".$sourceSystem."<br>";
                $query->andWhere("userWrapperSource.id IS NULL OR userWrapperSource.id=" . $sourceSystem->getId());
            }

            //echo "query=".$query." <br><br>";
            //exit();

            $userWrappers = $query->getQuery()->getResult();
            foreach ($userWrappers as $userWrapper) {
                $thisId = $userWrapper->getId();
                $element = array(
                    'id' => $thisId,
                    'text' => $userWrapper . ""
                    //'text' => $userWrapper . "" . " [wrapper ID#".$thisId."]" //testing //TODO: fix user wrapper for edit/amend
                );

//                if( $cycle == "show" || $cycle == "edit" || $cycle == "amend" ) {
//                    $output[] = $element;
//                } else {
//                    if( !$this->in_complex_array($userWrapper . "", $output, 'id') ) {
//                        $output[] = $element;
//                    }
//                }

                if( !$this->in_complex_array($userWrapper . "", $output, 'id') ) {
                    $output[] = $element;
                }

            }

            //print_r($output);
            //exit('1');
        }
        ///////////// EOF 2) 3) user wrappers for this source ///////////////

        //$output = array_merge($users,$output);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

//    /**
//     * @Route("/common/{pricelistid}/transresitemcodes", name="employees_get_transresitemcodes", methods={"GET","POST"}, options={"expose"=true})
//     */
//    public function getTransResItemCodesAction(Request $request, $pricelistid=NULL) {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $query = $em->createQueryBuilder()
//            ->from('AppTranslationalResearchBundle:RequestCategoryTypeList', 'list')
//            ->select("list")
//            ->orderBy("list.orderinlist","ASC");
//
//        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
//
//        $categories = $query->getQuery()->getResult();
//
//        $abbreviation = '';
//
//        if( $pricelistid == 'trp-default-pricelist' ) {
//            $abbreviation = '';
//            $priceList = NULL;
//        } else {
//            $priceList = $em->getRepository('AppTranslationalResearchBundle:PriceTypeList')->find($pricelistid);
//
//            if( $priceList ) {
//                $abbreviation = $priceList->getAbbreviation();
//            }
//
//            //$quantitiesArr = $product->calculateQuantities($priceList);
//        }
//
//
//        if( $abbreviation ) {
//            $abbreviation = "-".$abbreviation;
//        }
//
//        $output = array();
//        foreach ($categories as $category) {
//
////            $initialQuantity = $category->getPriceInitialQuantity($priceList);
////            $initialFee = $category->getPriceFee($priceList);
////            $additionalFee = $category->getPriceFeeAdditionalItem($priceList);
////            $categoryItemCode = $category->getProductId($priceList);
////            $categoryName = $category->getName();
//
//            $initialFee = $category->getPriceFee($priceList);
//            //echo "initialFee=[$initialFee] <br>";
//            if( $initialFee === NULL ) {
//                continue;
//            }
//
//            $output[] = array(
//                'id' => $category->getId(),
//                //'id' => $category->getProductId().$abbreviation,
//                'text' => $category->getProductId().$abbreviation,
////                'initialFee' => $initialFee
//            );
//        }
//
//        //testing, add: new code item 1
////        $output[] = array(
////            'id' => "new code item 1",
////            'text' => "new code item 1",
////        );
//
//        $response = new Response();
//        $response->headers->set('Content-Type', 'application/json');
//        $response->setContent(json_encode($output));
//        return $response;
//    }




    public function getClassBundleByName($name) {
        $bundleName = "UserdirectoryBundle";
        //$filterType = array('default'); //change to array('default','user-added')
        $filterType = null;
        switch( $name ) {
            case "identifierkeytype":
                $className = "IdentifierTypeList";
                break;
            case "room":
                $className = "RoomList";
                break;
            case "suite":
                $className = "SuiteList";
                break;
            case "floor":
                $className = "FloorList";
                break;
            case "mailbox":
                $className = "MailboxList";
                break;
            case "effort":
                $className = "EffortList";
                break;
            case "administrativetitletype":
                $className = "AdminTitleList";
                break;
            case "appointmenttitletype":
                $className = "AppTitleList";
                break;
            case "medicaltitletype":
                $className = "MedicalTitleList";
                break;
            case "researchlab":
                $className = "ResearchLab";
                break;
            case "fellowshiptype":
                $className = "FellowshipTypeList";
                break;

            //training
            case "trainingmajors":
                $className = "MajorTrainingList";
                break;
            case "trainingminors":
                $className = "MinorTrainingList";
                break;
            case "traininghonors":
                $className = "HonorTrainingList";
                break;
            case "trainingfellowshiptitle":
                $className = "FellowshipTitleList";
                break;
            case "jobtitle":
                $className = "JobTitleList";
                break;

            //training tree
            case "residencyspecialty":
                $className = "ResidencySpecialty";
                break;
            case "fellowshipsubspecialty":
                $className = "FellowshipSubspecialty";
                break;

            //tree
            case "institutions":
                $className = "Institution";
                break;

            //grants
            case "sourceorganization":
                $className = "SourceOrganization";
                break;
            case "grant":
                $className = "Grant";
                break;

            case "city":
                $className = "CityList";
                break;
            case "organization":
                $className = "OrganizationList";
                break;
            case "userpositions":
                $className = "PositionTypeList";
                break;

            case "referringProviderSpecialty";
                $className = "HealthcareProviderSpecialtiesList";
                $filterType = null;
                break;
            case "referringProviderCommunication";
                $className = "HealthcareProviderCommunicationList";
                $filterType = null;
                break;

            case "transresprojecttypes":
                $className = "ProjectTypeList";
                $bundleName = "TranslationalResearchBundle";
                break;

//            case "transresitemcodes":
//                $className = "RequestCategoryTypeList";
//                $bundleName = "TranslationalResearchBundle";
//                break;

            case "usernametype":
                $className = "UsernameType";
                break;

            case "antibody":
                $className = "AntibodyList";
                $bundleName = "TranslationalResearchBundle";
                break;

            case "residencytracks":
                $className = "ResidencyTrackList";
                break;

            case "learnareas":
                $className = "LearnAreaList";
                $bundleName = "ResAppBundle";
                break;

//            case "patientLists":
//                $bundleName = "OrderformBundle";
//                $className = "PatientListHierarchy";
//                break;

            default:
                $className = null;
        }

        $res = array(
            'className' => $className,
            'bundleName' => $bundleName,
            'filterType' => $filterType
        );

        return $res;
    }

}
