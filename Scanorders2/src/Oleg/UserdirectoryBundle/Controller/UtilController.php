<?php

namespace Oleg\UserdirectoryBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


//TODO: optimise by removing foreach loops

/**
 * @Route("/util")
 */
class UtilController extends Controller {
      

    /**
     * @Route("/common/department", name="get-departments-by-parent")
     * @Route("/common/division", name="get-divisions-by-parent")
     * @Route("/common/service", name="get-services-by-parent")
     * @Route("/common/commentsubtype", name="get-commentsubtype-by-parent")
     * @Method({"GET", "POST"})
     */
    public function getDepartmentAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $id = trim( $request->get('id') );
        $pid = trim( $request->get('pid') );
        //echo "pid=".$pid."<br>";
        //echo "id=".$id."<br>";

        $routeName = $request->get('_route');

        $className = "";

        if( $routeName == "get-departments-by-parent") {
            $className = 'Department';
        }
        if( $routeName == "get-divisions-by-parent") {
            $className = 'Division';
        }
        if( $routeName == "get-services-by-parent") {
            $className = 'Service';
        }
        if( $routeName == "get-commentsubtype-by-parent") {
            $className = 'CommentSubTypeList';
        }

        //echo "className=".$className."<br>";

        if( $className != "" && is_numeric($pid) ) {
            //echo "className=".$className."<br>";
            $query = $em->createQueryBuilder()
                ->from('OlegUserdirectoryBundle:'.$className, 'list')
                ->innerJoin("list.parent", "parent")
                ->select("list.id as id, list.name as text")
                //->select("list.name as id, list.name as text")
                ->orderBy("list.orderinlist","ASC");

            $query->where("parent = :pid AND (list.type = :typedef OR list.type = :typeadd)")->setParameters(array('typedef' => 'default','typeadd' => 'user-added','pid'=>$pid));

            $output = $query->getQuery()->getResult();
        } else {
            //echo "pid is not numeric=".$pid."<br>";
            $output = array();
        }

        //add current element by id
        if( $id ) {
            $entity = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:'.$className)->findOneById($id);
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
     * @Route("/common/institution", name="employees_get_institution")
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
     * @Route("/common/commenttype", name="employees_get_commenttype")
     * @Method("GET")
     */
    public function getCommenttypeAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:CommentTypeList', 'list')
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
     * @Route("/common/identifierkeytype", name="employees_get_identifierkeytype")
     * @Method("GET")
     */
    public function getIdentifierKeytypeAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:IdentifierTypeList', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/common/fellowshiptype", name="employees_get_fellowshiptype")
     * @Method("GET")
     */
    public function getFellowshipTypeAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:FellowshipTypeList', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/common/researchlabtitle", name="employees_get_researchlabtitle")
     * @Method("GET")
     */
    public function getResearchLabTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:ResearchLabTitleList', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = :typedef OR list.type = :typeadd")->setParameters(array('typedef' => 'default','typeadd' => 'user-added'));

        $output = $query->getQuery()->getResult();

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
     * @Route("/cwid", name="employees_check_cwid")
     * @Method("GET")
     */
    public function checkCwidAction(Request $request) {

        if( false === $this->get('security.context')->isGranted('ROLE_USERDIRECTORY_EDITOR') ) {
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
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
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
        }

        $ssn = trim( $request->get('number') );
        $em = $this->getDoctrine()->getManager();

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
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
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
            return $this->redirect( $this->generateUrl('employees-order-nopermission') );
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
     * @Route("/common/user-data-search/{type}/{search}", name="employees_user-data-search")
     * @Method("GET")
     */
    public function getUserDataSearchAction(Request $request) {

        $type = trim( $request->get('type') );
        $search = trim( $request->get('search') );

        //echo "type=".$type."<br>";
        //echo "search=".$search."<br>";

        $em = $this->getDoctrine()->getManager();

        $repository = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:User');
        $dql =  $repository->createQueryBuilder("user");
        $dql->select("user.id as id, user.displayName as text");
        $dql->groupBy('user');


        if( $type == "user" ) {
            $object = "user";
            $field = "displayName";
            //$criteriastr = "user.".$field." LIKE '%".$search."%'";
            //$criteriastr = "user.".$field." = '".$search."'";
        }

        if( $type == "cwid" ) {
            $object = "user";
            $field = "primaryPublicUserId";
            //$criteriastr = "user.".$field." LIKE '%".$search."%'";
            //$criteriastr = "user.".$field." = '".$search."'";
        }

        if( $type == "admintitle" ) {
            $dql->leftJoin("user.administrativeTitles", "administrativeTitles");
            $object = "administrativeTitles";
            $field = "name";
            //$criteriastr = "administrativeTitles.".$field." LIKE '%".$search."%'";
            //$criteriastr = "administrativeTitles.".$field." = '".$search."'";
        }


        if( $search == "min" ) {

            $criteriastr = $object.".".$field." IS NOT NULL";

        } else {

            $criteriastr = $object.".".$field." LIKE '%".$search."%'";

        }

        $dql->where($criteriastr);

        $query = $em->createQuery($dql);

        $output = $query->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

}
