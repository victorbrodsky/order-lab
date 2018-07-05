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

namespace Oleg\UserdirectoryBundle\Controller;



use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Util\UserUtil;
use Oleg\UserdirectoryBundle\Security\Authentication\AuthUtil;

//TODO: optimise by removing foreach loops:
//create optimalShortName: return abbr, or return short, or return name
//optimalShortNoAbbr: return short, or return name

/**
 * @Route("/util")
 */
class UtilController extends Controller {


    /**
     * @Route("/common/generic/{name}", name="employees_get_generic_select2")
     * @Method("GET")
     */
    public function getGenericAction( Request $request, $name ) {

        return $this->getGenericList($request,$name);
    }

    public function getGenericList( $request, $name ) {

        $cycle = $request->get('cycle');
        $newCycle = false;
        if( $cycle && (strpos($cycle, 'new') !== false || strpos($cycle, 'create') !== false) ) {
            $newCycle = true;
        }

        //echo "name=".$name."<br>";
        $res = $this->getClassBundleByName($name);
        $className = $res['className'];
        $bundleName = $res['bundleName'];
        $filterType = $res['filterType'];

        //echo "className=".$className."<br>";

        $output = array();

        if( $className ) {

            $em = $this->getDoctrine()->getManager();

            switch( $className ) {


                case "FellowshipTitleList": //show this object as "abbreviation - name"
                    if( $newCycle && $filterType ) {
                        $optionArr = array('default');
                    } else {
                        $optionArr = array('default','user-added');
                    }

                    $entities = $em->getRepository('Oleg'.$bundleName.':'.$className)->findBy(
                        array('type' => $optionArr)
                    );
                    foreach( $entities as $entity ) {
                        $element = array('id'=>$entity->getId(), 'text'=>$entity."");
                        $output[] = $element;
                    }
                    break;


                default:
                    $query = $em->createQueryBuilder()->from('Oleg'.$bundleName.':'.$className, 'list')
                        ->select("list.id as id, list.name as text")
                        ->orderBy("list.orderinlist","ASC");

                    //$query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
                    if( $newCycle && $filterType ) {
                        $query->where("list.type = :typedef")->setParameters(array('typedef' => 'default'));
                    } else {
                        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
                    }

                    $output = $query->getQuery()->getResult();

            } //switch

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }





    /**
     * @Route("/common/fellowshipsubspecialty", name="get-fellowshipsubspecialty-by-parent")
     * @Method({"GET", "POST"})
     */
    public function getDepartmentAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $id = trim( $request->get('id') );
        $pid = trim( $request->get('pid') );
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
                ->from('Oleg'.$bundleName.':'.$className, 'list')
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
            $entity = $this->getDoctrine()->getRepository('Oleg'.$bundleName.':'.$className)->findOneById($id);
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

    /**
     * @Route("/common/traininginstitution", name="employees_get_traininginstitution")
     * @Method({"GET"})
     */
    public function getTrainingInstitutionAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $cycle = $request->get('cycle');
        $newCycle = false;
        if( $cycle && (strpos($cycle, 'new') !== false || strpos($cycle, 'create') !== false) ) {
            $newCycle = true;
        }
        //echo "cycle=".$cycle." => newCycle=".$newCycle."<br>";

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Institution', 'list')
            ->select("list.id as id, list.name as text")
            ->leftJoin("list.types","types")
            ->groupBy("list")
            ->orderBy("list.orderinlist","ASC");

        $query->where("(types.name LIKE :instTypeEducational OR types.name LIKE :instTypeMedical) AND list.level = 0");
        $paramArr = array('instTypeEducational' => 'Educational','instTypeMedical' => 'Medical');

        if( $newCycle ) {
            $query->andWhere("(list.type = :typedef)");
            $paramArr['typedef'] = 'default';
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

    /**
     * @Route("/common/institution-all", name="employees_get_institution_all", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function setInstitutionTreeAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Institution', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }
    /**
     * @Route("/common/institution-old/", name="employees_get_institution", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function getInstitutionAction(Request $request) {

        $id = trim( $request->get('id') );

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Institution', 'list')
            ->select("list.id as id, list.name as text")
            //->select("list.name as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $output = $query->getQuery()->getResult();

        //add current element by id
        if( $id ) {
            $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:Institution')->findOneById($id);
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

    /**
     * @Route("/common/patientlists", name="employees_get_patientlists", options={"expose"=true})
     * @Method({"GET", "POST"})
     */
    public function getPatientListsAction(Request $request) {
        $calllogUtil = $this->get('calllog_util');
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


//    /**
//     * @Route("/common/commenttype", name="employees_get_commenttype")
//     * @Method("GET")
//     */
//    public function getCommenttypeAction() {
//
//        $em = $this->getDoctrine()->getManager();
//
//        $query = $em->createQueryBuilder()
//            ->from('OlegUserdirectoryBundle:CommentTypeList', 'list')
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



    /**
     * @Route("/common/locationusers", name="employees_get_locationusers")
     * @Method("GET")
     */
    public function getLocationUsersAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'list')
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
     * @Route("/common/building", name="employees_get_building")
     * @Method("GET")
     */
    public function getBuildingsAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $cycle = $request->get('cycle');
        $newCycle = false;
        if( $cycle && (strpos($cycle, 'new') !== false || strpos($cycle, 'create') !== false) ) {
            $newCycle = true;
        }

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:BuildingList', 'list')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->get('security.token_storage')->getToken()->getUser();

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


    /**
     * @Route("/common/location", name="employees_get_location")
     * @Method("GET")
     */
    public function getLocationAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Location', 'list')
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

    /**
     * @Route("/common/locationName", name="employees_get_locationname")
     * @Method("GET")
     */
    public function getLocationNameAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Location', 'list')
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

    /**
     * @Route("/common/get-location-by-name/", name="employees_get_location_by_name", options={"expose"=true})
     * @Method("GET")
     */
    public function getLocationByNameAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $locationId = trim($request->get('locationId'));

        if( strval($locationId) == strval(intval($locationId)) ) {
            //echo "locationId is integer<br>";
            $location = $em->getRepository('OlegUserdirectoryBundle:Location')->find($locationId);
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
     *
     * @Route("/common/location/delete/{id}", name="employees_location_delete", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function getLocationCheckDeleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $location = $em->getRepository('OlegUserdirectoryBundle:Location')->find($id);
        $resLabs = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->findByLocation($location);
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


    /**
     * @Route("/common/researchlab/{id}/{subjectUser}", name="employees_get_researchlab")
     * @Method("GET")
     */
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

        //$subjectUserDB = $em->getRepository('OlegUserdirectoryBundle:User')->find($subjectUser);
//        $researchLabDB = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->find($id);
//        if( !$researchLabDB ) {
//            $response = new Response();
//            $response->headers->set('Content-Type', 'application/json');
//            $response->setContent(null);
//            return $response;
//        }

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:ResearchLab', 'list')
            ->leftJoin('list.institution','institution')
            ->leftJoin('list.location','location')
            ->leftJoin('list.comments','comments')
            ->leftJoin('comments.author','commentauthor')
            ->leftJoin('list.pis','pis')
            ->leftJoin('pis.pi','piauthor')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->get('security.token_storage')->getToken()->getUser();

        $query->where("institution.id=".$id);

        $labs = $query->getQuery()->getResult();
        //echo "labs count=".count($labs)."<br>";

        $output = array();

        foreach( $labs as $lab ) {

//            if( $subjectUser && is_numeric($subjectUser) ) {
//                $subjectUserDB = $em->getRepository('OlegUserdirectoryBundle:User')->find($subjectUser);
//            } else {
//                $subjectUserDB = null;
//            }
            
            //$commentDb = $em->getRepository('OlegUserdirectoryBundle:ResearchLabComment')->findOneBy( array( 'author' => $subjectUserDB, 'researchLab'=>$researchLabDB ) );
            //$piDb = $em->getRepository('OlegUserdirectoryBundle:ResearchLabPI')->findOneBy( array( 'pi'=>$subjectUserDB, 'researchLab'=>$researchLabDB ) );

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
     *
     * @Route("/common/researchlab/deletefromuser/{id}/{subjectUser}", name="employees_researchlab_deletefromuser")
     * @Method("DELETE")
     */
    public function researchLabDeleteAction($id, $subjectUser=null) {

        if( !$subjectUser || $subjectUser == 'undefined' ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode('no subject user'));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($subjectUser);
        $lab = $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->find($id);

        $output = 'not ok';

        //more effificient than looping (?)
        if( $user && $lab ) {

            $em->getRepository('OlegUserdirectoryBundle:ResearchLab')->removeDependents( $user, $lab );

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


    /**
     * @Route("/common/grant/{id}/{subjectUser}", name="employees_get_grant")
     * @Method("GET")
     */
    public function getGrantByIdAction( $id, $subjectUser=null ) {
       
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
            ->from('OlegUserdirectoryBundle:Grant', 'list')
            ->leftJoin('list.sourceOrganization','sourceOrganization')
            ->leftJoin('list.attachmentContainer','attachmentContainer')
            ->leftJoin('list.comments','comments')
            ->leftJoin('comments.author','commentauthor')
            ->leftJoin('list.efforts','efforts')
            ->leftJoin('efforts.author','effortauthor')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->get('security.token_storage')->getToken()->getUser();

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
                $subjectUserDB = $em->getRepository('OlegUserdirectoryBundle:User')->find($subjectUser);
            } else {
                $subjectUserDB = null;
            }

            $userComment = $em->getRepository('OlegUserdirectoryBundle:GrantComment')->findOneBy( array( 'author' => $subjectUserDB, 'grant'=>$grant ) );
            $userEffort = $em->getRepository('OlegUserdirectoryBundle:GrantEffort')->findOneBy( array( 'author'=>$subjectUserDB, 'grant'=>$grant ) );

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
                        $documentJson["url"] = $document->getAbsoluteUploadFullPath();
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
     *
     * @Route("/common/grant/deletefromuser/{id}/{subjectUser}", name="employees_grant_deletefromuser")
     * @Method("DELETE")
     */
    public function grantDeleteAction($id, $subjectUser=null) {

        if( !$subjectUser || $subjectUser == 'undefined' ) {
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode('no subject user'));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('OlegUserdirectoryBundle:User')->find($subjectUser);
        $grant = $em->getRepository('OlegUserdirectoryBundle:Grant')->find($id);

        $output = 'not ok';

        //more effificient than looping (?)
        if( $user && $grant ) {

            $em->getRepository('OlegUserdirectoryBundle:Grant')->removeDependents( $user, $grant );

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


    /**
     * @Route("/cwid", name="employees_check_cwid")
     * @Method("GET")
     */
    public function checkCwidAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $cwid = trim( $request->get('number') );
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneByUsername($cwid);

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
     * @Route("/ssn", name="employees_check_ssn")
     * @Method("GET")
     */
    public function checkSsnAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $ssn = trim( $request->get('number') );
        $em = $this->getDoctrine()->getManager();

        $users = null;
        $user = null;

        if( $ssn != "" ) {
            $query = $em->createQueryBuilder()
                ->from('OlegUserdirectoryBundle:User', 'user')
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
     * @Route("/ein", name="employees_check_ein")
     * @Method("GET")
     */
    public function checkEinAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $ein = trim( $request->get('number') );

        $em = $this->getDoctrine()->getManager();

        $user = null;

        if( $ein != "" ) {
            $query = $em->createQueryBuilder()
                ->from('OlegUserdirectoryBundle:User', 'user')
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


    /**
     * @Route("/usertype-userid", name="employees_check_usertype-userid")
     * @Method("GET")
     */
    public function checkUsertypeUseridAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userType = trim( $request->get('userType') );
        $userId = trim( $request->get('userId') );

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneBy(array('keytype'=>$userType,'primaryPublicUserId'=>$userId));

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
     *
     * @Route("/ldap-usertype-userid", name="employees_check_ldap-usertype-userid", options={"expose"=true})
     * @Method("GET")
     */
    public function checkCWIDUsertypeUseridAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }
       
        $userId = trim( $request->get('userId') );
        //$userTypeText = trim( $request->get('userTypeText') );

        $output = "ok";
        
        $em = $this->getDoctrine()->getManager();

        $authUtil = new AuthUtil($this->container,$em);
        
        //search this user if exists in ldap directory
        //$searchRes might be -1 => can not bind to LDAP server, meaning running on testing localhost outside cornell.edu network => user is ok
        $searchRes = $authUtil->searchLdap($userId);
        if( $searchRes == NULL || count($searchRes) == 0 ) {
            $output = "notok";
        }       

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * Used by typeahead js
     * @Route("/common/user-data-search/{type}/{limit}/{search}", name="employees_user-data-search", options={"expose"=true})
     * @Method("GET")
     */
    public function getUserDataSearchAction(Request $request) {

        $type = trim( $request->get('type') );
        $search = trim( $request->get('search') );
        $limit = trim( $request->get('limit') );

        //echo "type=".$type."<br>";
        //echo "search=".$search."<br>";
        //echo "limit=".$limit."<br>";
        //exit();

        $em = $this->getDoctrine()->getManager();
        $userutil = new UserUtil();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
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
                $criteriastr = "infos.displayName LIKE '%".$search."%'";
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
                $criteriastr = "administrativeInstitution.name LIKE '%".$search."%' OR ";
                //$criteriastr .= "administrativeInstitution.abbreviation LIKE '%".$search."%' OR ";
                $criteriastr .= "appointmentInstitution.name LIKE '%".$search."%' OR ";
                $criteriastr .= "medicalInstitution.name LIKE '%".$search."%'";
            }

            //time
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'appointmentInstitution', $criteriastr );
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }

        if( $type == "cwid" || $type == "single" ) {
            if( $search == "prefetchmin" ) {
                $criteriastr = "user.primaryPublicUserId IS NOT NULL";
            } else {
                $criteriastr = "user.primaryPublicUserId LIKE '%".$search."%'";
            }
        }

        //administrative appointment title
        if( $type == "admintitle" ) { //|| $type == "single"
            $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
            $dql->leftJoin("administrativeTitles.name", "adminTitleName");
            if( $search == "prefetchmin" ) {
                $criteriastr = "adminTitleName.name IS NOT NULL";
            } else {
                $criteriastr = "adminTitleName.name LIKE '%".$search."%'";
            }

            //time
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            //$criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'appointmentInstitution', $criteriastr );
            //$criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }

        //academic title
        if( $type == "academictitle" ) {
            $dql->leftJoin("user.appointmentTitles", "appointmentTitles");
            $dql->leftJoin("appointmentTitles.name", "appointmentTitleName");
            if( $search == "prefetchmin" ) {
                $criteriastr = "appointmentTitleName.name IS NOT NULL";
            } else {
                $criteriastr = "appointmentTitleName.name LIKE '%".$search."%'";
            }

            //time
            //$criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );
            //$criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }

        //medical title
        if( $type == "medicaltitle" ) {
            $dql->leftJoin("user.medicalTitles", "medicalTitles");
            $dql->leftJoin("medicalTitles.name", "medicalTitleName");
            if( $search == "prefetchmin" ) {
                $criteriastr = "medicalTitleName.name IS NOT NULL";
            } else {
                $criteriastr = "medicalTitleName.name LIKE '%".$search."%'";
            }

            //time
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
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
//            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'administrativeInstitution', $criteriastr );

            //echo "0 criteriastr=".$criteriastr."<br>";

            if( $criteriastr ) {
                $criteriastr = $criteriastr . " OR ";
            }

            if( $search == "prefetchmin" ) {
                $criteriastr .= "infos.displayName IS NOT NULL";
            } else {
                $criteriastr .= "infos.displayName LIKE '%".$search."%'";
            }

            //echo "1 criteriastr=".$criteriastr."<br>";

        }


        //filter out Pathology Fellowship Applicants
        $criteriastr = "(".$criteriastr . ") AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)";

        //filter out previous users
        $curdate = date("Y-m-d", time());
        $criteriastr .= " AND (employmentStatus.id IS NULL OR employmentStatus.terminationDate IS NULL OR employmentStatus.terminationDate > '".$curdate."')";

        //filter out users with excludeFromSearch set to true
        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $criteriastr .= " AND (preferences.excludeFromSearch IS NULL OR preferences.excludeFromSearch = FALSE)";
            //$criteriastr .= " AND (preferences.excludeFromSearch = TRUE)";
        }

        //echo "criteriastr=".$criteriastr."<br>";
        //exit();

        $dql->where($criteriastr);

        $query = $em->createQuery($dql)->setMaxResults($limit);

        $output = $query->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * It is used in User mrn type where the link is created between user and patient
     *
     * @Route("/common/mrntype-identifier", name="employees_check_mrntype_identifier")
     * @Method("GET")
     */
    public function checkMrntypeIdentifierAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $mrntype = $request->get('mrntype');
        $identifier = $request->get('identifier');

        $em = $this->getDoctrine()->getManager();

//        $keytype = $em->getRepository('OlegOrderformBundle:MrnType')->find($mrntype);
//        //construct patient
//        $patientMrn = new PatientMrn();
//        $patient = new Patient();
//        $patientMrn->setStatus('valid');
//        $patientMrn->setField($identifier);
//        $patientMrn->setKeytype($keytype);
//        $patient->addMrn($patientMrn);
//
//        $patientDb = $em->getRepository('OlegOrderformBundle:Patient')->findUniqueByKey($patient);


        $query = $em->createQueryBuilder()
            ->from('OlegOrderformBundle:Patient', 'patient')
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

    /**
     * @Route("/common/check-user-password", name="employees_check_user_password", options={"expose"=true})
     * @Method("POST")
     */
    public function checkUserPasswordAction( Request $request ) {

        if( false === $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $userid = trim( $request->get('userid') );
        $userpassword = trim( $request->get('userpassword') );

        $user = $this->get('security.token_storage')->getToken()->getUser();
        if( $userid != $user->getId() ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $output = 'notok';

        $em = $this->getDoctrine()->getManager();

        $subjectUser = $em->getRepository('OlegUserdirectoryBundle:User')->find($userid);

        //$encoder = $this->container->get('security.password_encoder');
        //$encoded = $encoder->encodePassword($subjectUser, $userpassword);
        //$bool = StringUtils::equals($subjectUser->getPassword(), $encoded);

        $encoderService = $this->get('security.encoder_factory');
        $encoder = $encoderService->getEncoder($user);
        $bool = $encoder->isPasswordValid($subjectUser->getPassword(), $userpassword, $user->getSalt());

        if( $bool ) {
            $output = 'ok';
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


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

            case "transresprojecttypes":
                $className = "ProjectTypeList";
                $bundleName = "TranslationalResearchBundle";
                break;

            case "usernametype":
                $className = "UsernameType";
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
