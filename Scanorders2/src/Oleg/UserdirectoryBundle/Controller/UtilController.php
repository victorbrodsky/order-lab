<?php

namespace Oleg\UserdirectoryBundle\Controller;



use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer;

use Oleg\UserdirectoryBundle\Util\UserUtil;

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
    public function getGenericAction( $name ) {

        return $this->getGenericList($name);
    }

    public function getGenericList( $name ) {

        //echo "name=".$name."<br>";
        $res = $this->getClassBundleByName($name);
        $className = $res['className'];
        $bundleName = $res['bundleName'];

        //echo "className=".$className."<br>";

        $output = array();

        if( $className ) {

            $em = $this->getDoctrine()->getManager();

            switch( $className ) {

                case "FellowshipTitleList": //show this object as "abbreviation - name"
                    $entities = $em->getRepository('Oleg'.$bundleName.':'.$className)->findBy(
                        array('type' => array('default','user-added'))
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
                    $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));
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

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Institution', 'list')
            ->select("list.id as id, list.name as text")
            ->leftJoin("list.types","types")
            ->orderBy("list.orderinlist","ASC");

        $query->where("types.name LIKE :instType AND list.level = 0 AND (list.type = :typedef OR list.type = :typeadd)")
            ->setParameters(array('typedef' => 'default','typeadd' => 'user-added', 'instType' => 'Educational' ));

        $output = $query->getQuery()->getResult();

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
            ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType IS NULL")
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
    public function getBuildingsAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:BuildingList', 'list')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $buildings = $query->getQuery()->getResult();

        $output = array();
        foreach($buildings as $building) {
            $geoloc = $building->getGeoLocation();
            $element = array(
                'id'        => $building->getId(),
                'text'      => $building."",
                'street1'   => $geoloc->getStreet1(),
                'street2'   => $geoloc->getStreet2(),
                'city'   => $geoloc->getCity(),
                'county'   => $geoloc->getCounty(),
                'country'   => ( $geoloc->getCountry() ? $geoloc->getCountry()->getId() : null ),
                'state'   => ( $geoloc->getState() ? $geoloc->getState()->getId() : null ),
                'zip'   => $geoloc->getZip(),
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
        $query->andWhere("user IS NULL OR (user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system')");

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

        if( !is_numeric($id) || !$subjectUser || $subjectUser == 'undefined' ) {
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
            ->leftJoin('list.location','location')
            ->leftJoin('list.comments','comments')
            ->leftJoin('comments.author','commentauthor')
            ->leftJoin('list.pis','pis')
            ->leftJoin('pis.pi','piauthor')
            ->select("list")
            ->orderBy("list.orderinlist","ASC");

        //$user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.id=".$id);

        $labs = $query->getQuery()->getResult();

        $output = array();

        foreach( $labs as $lab ) {

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

        if( !is_numeric($id) || !$subjectUser || $subjectUser == 'undefined' ) {
            //echo "return null";
            $output = array();
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(json_encode($output));
            return $response;
        }

        $em = $this->getDoctrine()->getManager();

        $subjectUserDB = $em->getRepository('OlegUserdirectoryBundle:User')->find($subjectUser);

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

        //$user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.id=".$id);

        $grants = $query->getQuery()->getResult();

        //echo "grant count=".count($grants)."<br>";

        $grant = null;

        if( count($grants) == 1 ) {
            $grant = $grants[0];
        }

        $output = array();

        if( $grant ) {

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
                        $documentJson["originalname"] = $document->getOriginalname();
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

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
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

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
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

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
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

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
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
     * @Route("/common/user-data-search/{type}/{limit}/{search}", name="employees_user-data-search")
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

        $dql->leftJoin("user.employmentStatus", "employmentStatus");
        $dql->leftJoin("employmentStatus.employmentType", "employmentType");

        //$dql->leftJoin("user.researchLabs", "researchLabs");
        $dql->groupBy('user');
        $dql->addGroupBy('keytype');
        $dql->addGroupBy('infos');
        $dql->orderBy("infos.displayName","ASC");


        $object = null;

        if( $type == "user" ) {
            if( $search == "min" ) {
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

            if( $search == "min" ) {
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

        if( $type == "cwid" ) {
            if( $search == "min" ) {
                $criteriastr = "user.primaryPublicUserId IS NOT NULL";
            } else {
                $criteriastr = "user.primaryPublicUserId LIKE '%".$search."%'";
            }
        }

        //administrative appointment title
        if( $type == "admintitle" ) {
            $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
            $dql->leftJoin("administrativeTitles.name", "adminTitleName");
            if( $search == "min" ) {
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
            if( $search == "min" ) {
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
            if( $search == "min" ) {
                $criteriastr = "medicalTitleName.name IS NOT NULL";
            } else {
                $criteriastr = "medicalTitleName.name LIKE '%".$search."%'";
            }

            //time
            $criteriastr = $userutil->getCriteriaStrByTime( $dql, 'current_only', 'medicalInstitution', $criteriastr );
        }

        //filter out Pathology Fellowship Applicants
        $criteriastr = "(".$criteriastr . ") AND (employmentType.name != 'Pathology Fellowship Applicant' OR employmentType IS NULL)";

        //echo "criteriastr=".$criteriastr."<br>";

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

        if( false === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ) {
            return $this->redirect( $this->generateUrl('employees-nopermission') );
        }

        $mrntype = $request->get('mrntype');
        $identifier = $request->get('identifier');

        $em = $this->getDoctrine()->getManager();

        $keytype = $em->getRepository('OlegOrderformBundle:MrnType')->find($mrntype);

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


    public function getClassBundleByName($name) {
        $bundleName = "UserdirectoryBundle";
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



            default:
                $className = null;
        }

        $res = array(
            'className' => $className,
            'bundleName' => $bundleName
        );

        return $res;
    }

}
