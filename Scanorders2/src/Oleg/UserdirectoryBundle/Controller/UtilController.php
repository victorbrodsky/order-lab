<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oleg\OrderformBundle\Helper\FormHelper;


//TODO: optimise by removing foreach loops

/**
 * OrderInfo controller.
 *
 * @Route("/util")
 */
class UtilController extends Controller {
      

    /**
     * @Route("/service", name="get-service")
     * @Method("GET")
     */
    public function getServiceAction() {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Service', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        if( $opt == 'default' ) {
            if( $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ) {
                $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);
            } else {
                $query->where('list.type = :type ')->setParameter('type', 'default');
            }
        } else {
            //find user's services to include them in the list
            $user = $em->getRepository('OlegUserdirectoryBundle:User')->findOneById($opt);
            $getServices = $user->getServices();	//TODO: user's or allowed services?

            foreach( $getServices as $serviceId ) {
                $whereServicesList = $whereServicesList . " OR list.id=".$serviceId->getId();
            }
            //$query->where('list.type = :type OR list.creator = :user_id ' . $whereServicesList)->setParameter('type', 'default')->setParameter('user_id', $opt);
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user_id) ".$whereServicesList)->setParameter('type', 'default')->setParameter('user_id', $opt);
        }

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }


    /**
     * @Route("/projecttitle", name="get-projecttitle")
     * @Method("GET")
     */
    public function getProjectTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:ProjectTitleList', 'list')
            ->select("list.name as id, list.name as text")
            //->where("list.type = 'default'")
            ->orderBy("list.orderinlist","ASC");

//        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
//        }

        //echo "query=".$query."<br \>";

        $output = $query->getQuery()->getResult();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $opt ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($opt);
            if( $orderinfo->getResearch() ) {
                $strEneterd = $orderinfo->getResearch()->getProjectTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
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
     * @Route("/settitle", name="get-settitle")
     * @Method("GET")
     */
    public function getSetTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') ); //projectTitle name
        $orderoid = trim( $request->get('orderoid') );
        //echo 'opt='.$opt.' => ';

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:SetTitleList', 'list')
            ->select("list.name as id, list.name as text")
            ->leftJoin("list.projectTitle","parent")
            ->where("parent.name = :pname AND list.type = :type")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pname' => $opt,
                'type' => 'default'
            ));

        //echo "query=".$query."<br>";
        $output = $query->getQuery()->getResult();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $orderoid ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($orderoid);
            if( $orderinfo->getResearch() ) {
                $strEneterd = $orderinfo->getResearch()->getSetTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
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
     * @Route("/coursetitle", name="get-coursetitle")
     * @Method("GET")
     */
    public function getCourseTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );
        //$type = trim( $request->get('type') );

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:CourseTitleList', 'list')
            ->select("list.name as id, list.name as text")
            ->where("list.type = 'default'")
            ->orderBy("list.orderinlist","ASC");

//        if( $opt ) {
            $user = $this->get('security.context')->getToken()->getUser();
            $query->where("list.type = :type OR ( list.type = 'user-added' AND list.creator = :user)");
            $query->setParameters( array('type' => 'default', 'user' => $user) );
//        }

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();
        //$output = array();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $opt ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($opt);
            if( $strEneterd = $orderinfo->getEducational() ) {
                $strEneterd = $orderinfo->getEducational()->getCourseTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
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
     * @Route("/lessontitle", name="get-lessontitle")
     * @Method("GET")
     */
    public function getLessonTitleAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') ); //parent id: courseTitle id
        $orderoid = trim( $request->get('orderoid') );
        //echo 'opt='.$opt.' => ';

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:LessonTitleList', 'list')
            ->select("list.name as id, list.name as text")
            ->leftJoin("list.courseTitle","parent")
            ->where("parent.name = :pname AND list.type = :type")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pname' => $opt,
                'type' => 'default'
            ));

        //echo "query=".$query."<br>";

        $output = $query->getQuery()->getResult();

        //add old name. The name might be changed by admin, so check and add if not existed, the original name eneterd by a user when order was created
        if( $orderoid ) {
            $orderinfo = $this->getDoctrine()->getRepository('OlegOrderformBundle:OrderInfo')->findOneByOid($orderoid);
            if( $orderinfo->getEducational() ) {
                $strEneterd = $orderinfo->getEducational()->getLessonTitleStr();
                $element = array('id'=>$strEneterd, 'text'=>$strEneterd);
                if( !$this->in_complex_array($element,$output) ) {
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
     * @Route("/optionalusereducational", name="get-optionalusereducational")
     * @Route("/optionaluserresearch", name="get-optionaluserresearch")
     * @Method("GET")
     */
    public function getOptionalUserAction() {

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') ); //parent name: courseTitle name
        $routeName = $request->get('_route');

        if( $routeName == "get-optionalusereducational" ) {
            $role = "ROLE_SCANORDER_COURSE_DIRECTOR";
            $className = 'DirectorList';
            $pname = 'courses';
        }
        if( $routeName == "get-optionaluserresearch" ) {
            $role = "ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR";
            $className = 'PIList';
            $pname = 'projectTitles';
        }

        if(0) {
            echo "opt=".$opt." => ";
            $project = $this->getDoctrine()->getRepository('OlegUserdirectoryBundle:CourseTitleList')->findOneById($opt);
            $pis = $project->getDirectors();
            echo "countpis=".count($pis)." => ";
            foreach( $project->getDirectors() as $pi ) {
                echo "pi name=".$pi->getName()." | ";
            }
        }

        //1) add PIList with parent name = $opt
        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:'.$className, 'list')
            ->select("list.name as id, list.name as text")
            ->leftJoin("list.".$pname,"parents")
            ->where("parents.name = :pname AND (list.type = :type OR list.type = :type2)")
            ->orderBy("list.orderinlist","ASC")
            ->setParameters( array(
                'pname' => $opt,
                'type' => 'default',
                'type2' => 'user-added'
            ));

        $output = $query->getQuery()->getResult();

        //var_dump($output);

        //2) add users with ROLE_SCANORDER_COURSE_DIRECTOR and ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR
        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:User', 'list')
            //->select("list.id as id, list.username as text")
            ->select("list")
            ->where("list.roles LIKE :role")
            ->orderBy("list.id","ASC")
            ->setParameter('role', '%"' . $role . '"%');

        $users = $query->getQuery()->getResult();

        foreach( $users as $user ) {
            $element = array('id'=>$user."", 'text'=>$user."");
            if( !$this->in_complex_array($user."",$output) ) {
                $output[] = $element;
            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;

    }



    /**
     * @Route("/department", name="get-department")
     * @Method("GET")
     */
    public function getDepartmentAction() {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Department', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);

        $output = $query->getQuery()->getResult();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($output));
        return $response;
    }

    /**
     * @Route("/institution", name="get-institution")
     * @Method("GET")
     */
    public function getInstitutionAction() {

        $whereServicesList = "";

        $em = $this->getDoctrine()->getManager();

        $request = $this->get('request');
        $opt = trim( $request->get('opt') );

        $query = $em->createQueryBuilder()
            ->from('OlegUserdirectoryBundle:Institution', 'list')
            ->select("list.id as id, list.name as text")
            ->orderBy("list.orderinlist","ASC");

        $user = $this->get('security.context')->getToken()->getUser();

        $query->where("list.type = 'default' OR ( list.type = 'user-added' AND list.creator = :user)")->setParameter('user',$user);

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

}
