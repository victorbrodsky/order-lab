<?php

namespace Oleg\OrderformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Oleg\OrderformBundle\Entity\StainList;
use Oleg\OrderformBundle\Entity\OrganList;
use Oleg\OrderformBundle\Entity\ProcedureList;
use Oleg\OrderformBundle\Entity\PathServiceList;
use Oleg\OrderformBundle\Entity\StatusType;
use Oleg\OrderformBundle\Entity\StatusGroup;
use Oleg\OrderformBundle\Entity\Status;
use Oleg\OrderformBundle\Helper\FormHelper;
use Oleg\OrderformBundle\Entity\User;
use Oleg\OrderformBundle\Form\UserType;
use Oleg\OrderformBundle\Helper\UserUtil;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * StainList controller.
 *
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * Admin Page
     *
     * @Route("/", name="admin_index")
     * @Method("GET")
     * @Template("OlegOrderformBundle:Admin:index.html.twig")
     */
    public function indexAction()
    {
        return $this->render('OlegOrderformBundle:Admin:index.html.twig');
    }


    /**
     * Populate DB
     *
     * @Route("/genall", name="generate_all")
     * @Method("GET")
     * @Template()
     */
    public function generateAllAction()
    {

//        if( false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN') ) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }

        $count_stain = $this->generateStains();
        $count_organ = $this->generateOrgans();
        $count_procedure = $this->generateProcedures();
        $count_statustype = $this->generateStatusType();
        $count_statusgroup = $this->generateStatusGroups();
        $count_status = $this->generateStatuses();
        $count_pathservice = $this->generatePathServices();
        $userutil = new UserUtil();
        $count_users = $userutil->generateUsersExcel($this->getDoctrine()->getManager(),$this->get('security.context')->getToken()->getUser());


        $this->get('session')->getFlashBag()->add(
            'notice',
            'Generated Tables: '.
            'Stains='.$count_stain.', '.
            'Organs='.$count_organ.', '.
            'Procedures='.$count_procedure.', '.
            'Status Types='.$count_statustype.', '.
            'Status Groups='.$count_statusgroup.', '.
            'Statuses='.$count_status.', '.
            'Pathology Services='.$count_pathservice.', '.
            'Users='.$count_users.
            ' (Note: -1 means that this table is already exists)'
        );

        return $this->redirect($this->generateUrl('admin_index'));
    }


    /**
     * Populate DB
     *
     * @Route("/genstain", name="generate_stain")
     * @Method("GET")
     * @Template()
     */
    public function generateStainAction()
    {

        $count = $this->generateStains();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' stain records'
            );

            return $this->redirect($this->generateUrl('stainlist'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }


    /**
     * Populate DB
     *
     * @Route("/genorgan", name="generate_organ")
     * @Method("GET")
     * @Template()
     */
    public function generateOrganAction()
    {

        $count = $this->generateOrgans();

        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' organ records'
            );

            return $this->redirect($this->generateUrl('organlist'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }



    /**
     * Populate DB
     *
     * @Route("/genprocedure", name="generate_procedure")
     * @Method("GET")
     * @Template()
     */
    public function generateProcedureAction()
    {

//        $em = $this->getDoctrine()->getManager();
//        $entities = $em->getRepository('OlegOrderformBundle:ProcedureList')->findAll();

        $count = $this->generateProcedures();

        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' procedure records'
            );

            return $this->redirect($this->generateUrl('procedurelist'));
        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

//        $helper = new FormHelper();
//        $organs = $helper->getProcedure();
//
//        $username = $this->get('security.context')->getToken()->getUser();
//
//        $count = 0;
//        foreach( $organs as $organ ) {
//            $list = new ProcedureList();
//            $list->setCreator( $username );
//            $list->setCreatedate( new \DateTime() );
//            $list->setName( $organ );
//            $list->setType('default');
//
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($list);
//            $em->flush();
//            $count++;
//        }
//
//        $this->get('session')->getFlashBag()->add(
//            'notice',
//            'Created '.$count. ' procedure records'
//        );
//
//        return $this->redirect($this->generateUrl('procedurelist'));

    }


    /**
     * Populate DB
     *
     * @Route("/genpathservice", name="generate_pathservice")
     * @Method("GET")
     * @Template()
     */
    public function generatePathServiceAction()
    {

//        if( false === $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
//            return $this->render('OlegOrderformBundle:Security:login.html.twig');
//        }

        $count = $this->generateStains();
        if( $count >= 0 ) {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Created '.$count. ' stain records'
            );

            return $this->redirect($this->generateUrl('stainlist'));

        } else {

            $this->get('session')->getFlashBag()->add(
                'notice',
                'This table is already exists!'
            );

            return $this->redirect($this->generateUrl('admin_index'));
        }

    }


    //return -1 if failed
    //return number of generated records
    public function generateStains() {

        $helper = new FormHelper();
        $stains = $helper->getStains();

        $username = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:StainList')->findAll();

        if( $entities ) {

            return -1;
        }

        $count = 0;
        foreach( $stains as $stain ) {
            $stainList = new StainList();
            $stainList->setCreator( $username );
            $stainList->setCreatedate( new \DateTime() );
            $stainList->setName( $stain );
            $stainList->setType('default');

            $em = $this->getDoctrine()->getManager();
            $em->persist($stainList);
            $em->flush();
            $count++;
        }

        return $count;
    }

    public function generateOrgans() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:OrganList')->findAll();

        if( $entities ) {

            return -1;
        }

        $helper = new FormHelper();
        $organs = $helper->getSourceOrgan();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 0;
        foreach( $organs as $organ ) {
            $list = new OrganList();
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( $organ );
            $list->setType('default');

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $count++;
        }


        return $count;
    }

    public function generateProcedures() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:ProcedureList')->findAll();

        if( $entities ) {

           return -1;
        }

        $helper = new FormHelper();
        $organs = $helper->getProcedure();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 0;
        foreach( $organs as $organ ) {
            $list = new ProcedureList();
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( $organ );
            $list->setType('default');

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $count++;
        }

        return $count;
    }

    public function generateStatusType() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:StatusType')->findAll();

        if( $entities ) {

            return -1;
        }

        $type1 = new StatusType();
        $type1->setName("Regular");
        $em->persist($type1);

        $type2 = new StatusType();
        $type2->setName("Filled");
        $em->persist($type2);

        $type3 = new StatusType();
        $type3->setName("On Hold");
        $em->persist($type3);

        $em->flush();

        return 3;
    }

    public function generateStatusGroups() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:StatusGroup')->findAll();

        if( $entities ) {

            return -1;
        }

        $group1 = new StatusGroup();
        $group1->setName("User");
        $em->persist($group1);

        $group2 = new StatusGroup();
        $group2->setName("Admin");
        $em->persist($group2);

        $em->flush();

        return 2;
    }


    public function generateStatuses() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:Status')->findAll();

        if( $entities ) {
            return -1;
        }

        $statuses = array(
            "Submitted", "Not Submitted", "Canceled", "Amended",
            "On Hold: Slides Received", "On Hold: Awaiting Slides",
            "Filled: Scanned", "Filled: Not Scanned", "Filled: Some Scanned", "Filled: Scanned & Returned",
            "Filled: Not Scanned & Returned", "Filled: Some Scanned & Returned",
        );

        $count = 0;
        foreach( $statuses as $statusStr ) {

            $status = new Status();

            //Regular
            switch( $statusStr )
            {

                case "Submitted":
                    $status->setName("Submitted");
                    $status->setAction("Submit");
                    break;
                case "Not Submitted":
                    $status->setName("Not Submitted");
                    $status->setAction("On Hold");
                    break;
                case "Canceled":
                    $status->setName("Canceled");
                    $status->setAction("Cancel");
                    break;
                case "Amended":
                    $status->setName("Amended");
                    $status->setAction("Amend");
                    break;
                default:
                    break;
            }

            $status->setType( $em->getRepository('OlegOrderformBundle:StatusType')->findOneByName('Regular') );
            $status->setGroup( $em->getRepository('OlegOrderformBundle:StatusGroup')->findOneByName('User') );

            //Filled
            if( strpos($statusStr,'Filled') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
                $status->setType( $em->getRepository('OlegOrderformBundle:StatusType')->findOneByName('Filled') );
                $status->setGroup( $em->getRepository('OlegOrderformBundle:StatusGroup')->findOneByName('Admin') );
            }

            //On Hold
            if( strpos($statusStr,'On Hold') !== false ) {
                $status->setName($statusStr);
                $status->setAction($statusStr);
                $status->setType( $em->getRepository('OlegOrderformBundle:StatusType')->findOneByName('On Hold') );
                $status->setGroup( $em->getRepository('OlegOrderformBundle:StatusGroup')->findOneByName('Admin') );
            }

            $em->persist($status);
            $em->flush();

            $count++;

        } //foreach

        return $count;
    }

    public function generatePathServices() {

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('OlegOrderformBundle:PathServiceList')->findAll();

        if( $entities ) {

            return -1;
        }

        $helper = new FormHelper();
        $services = $helper->getPathologyService();

        $username = $this->get('security.context')->getToken()->getUser();

        $count = 0;
        foreach( $services as $service ) {
            $list = new PathServiceList();
            $list->setCreator( $username );
            $list->setCreatedate( new \DateTime() );
            $list->setName( $service );
            $list->setType('default');

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $count++;
        }

        return $count;
    }


    //Users
//    /**
//     * @Route("/listusers", name="listusers")
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:Admin:users.html.twig")
//     */
//    public function indexUserAction()
//    {
//        $userManager = $this->container->get('fos_user.user_manager');
//        $users = $userManager->findUsers();
//
//        return array(
//            'entities' => $users,
//        );
//    }
//
//    /**
//     * @Route("/show_user/{id}", name="showuser", requirements={"id" = "\d+"})
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
//     */
//    public function showUserAction($id)
//    {
//
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);
//        $form = $this->createForm(new UserType(), $entity, array('disabled' => true));
//
////        if (!is_object($user) || !$user instanceof UserInterface) {
////            throw new AccessDeniedException('This user does not have access to this section.');
////        }
//
//        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
//        return array(
//            'entity' => $entity,
//            'form' => $form->createView(),
//            'cicle' => 'show'
//        );
//    }
//
//    /**
//     * @Route("/edit_user/{id}", name="user_edit", requirements={"id" = "\d+"})
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
//     */
//    public function editUserAction($id)
//    {
//
//        $em = $this->getDoctrine()->getManager();
//        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);
//
//        $form = $this->createForm(new UserType(), $entity, array(
//            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
//            'method' => 'PUT',
//        ));
//        $form->add('submit', 'submit', array('label' => 'Update','attr' => array('class' => 'btn btn-warning')));
//
////        $update_form = $this->createForm(new UserType(), $user, array(
////            'action' => $this->generateUrl('update_user', array('id' => $user->getId())),
////            'method' => 'PUT',
////        ));
//
////        $form->add('submit', 'submit', array('label' => 'Update'));
//
//        //return $this->container->get('templating')->renderResponse('FOSUserBundle:Profile:show.html.'.$this->container->getParameter('fos_user.template.engine'), array('user' => $user));
//        return array(
//            'entity' => $entity,
//            'form' => $form->createView(),
//            'cicle' => 'edit'
//        );
//    }
//
//    /**
//     * @Route("/{id}", name="user_update")
//     * @Method("PUT")
//     * @Template("OlegOrderformBundle:Profile:edit_user.html.twig")
//     */
//    public function updateUserAction(Request $request, $id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $entity = $em->getRepository('OlegOrderformBundle:User')->find($id);
//
//        if (!$entity) {
//            throw $this->createNotFoundException('Unable to find User entity.');
//        }
//
//        $form = $this->createForm(new UserType(), $entity, array(
//            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
//            'method' => 'PUT',
//        ));
//        $form->add('submit', 'submit', array('label' => 'Update'));
//
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $em->flush();
//
//            return $this->redirect($this->generateUrl('showuser', array('id' => $id)));
//        }
//
//        return array(
//            'entity' => $entity,
//            'form'   => $form->createView(),
////            'delete_form' => $deleteForm->createView(),
//        );
//    }
//
//    /**
//     * @Route("/genusers", name="generate_users")
//     * @Method("GET")
//     * @Template("OlegOrderformBundle:Admin:users.html.twig")
//     */
//    public function generateUsersAction()
//    {
//
//        $usersCount = $this->generateUsersExcel();
//
//        //exit();
//        return $this->redirect($this->generateUrl('listusers'));
//    }
//
//
//    public function generateUsersExcel() {
//        $inputFileName = __DIR__ . '/../Helper/users.xlsx';
//
//        try {
//            $inputFileType = \PHPExcel_IOFactory::identify($inputFileName);
//            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
//            $objPHPExcel = $objReader->load($inputFileName);
//        } catch(Exception $e) {
//            die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
//        }
//
//        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
//        //var_dump($sheetData);
//
//        $em = $this->getDoctrine()->getManager();
//
//        $sheet = $objPHPExcel->getSheet(0);
//        $highestRow = $sheet->getHighestRow();
//        $highestColumn = $sheet->getHighestColumn();
//
//        //for each user
//        $count = 0;
//        for ($row = 2; $row <= $highestRow; $row++){
//            //  Read a row of data into an array
//            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
//                NULL,
//                TRUE,
//                FALSE);
//
//            //  Insert row data array into the database
////            echo $row.": ";
////            var_dump($rowData);
////            echo "<br>";
//
//            $email = $rowData[0][11];
//            list($username, $extra) = explode("@", $email);
//            $phone = $rowData[0][8];
//            $fax = $rowData[0][12];
//            $firstName = $rowData[0][6];
//            $lastName = $rowData[0][5];
//            $title = $rowData[0][7];
//            $office = $rowData[0][10];
//            $pathlogyServices = explode("/",$rowData[0][2]);
//
//            $user = new User();
//            $user->setEmail($email);
//            $user->setEmailCanonical($email);
//            $user->setUsername($username);
//            $user->setUsernameCanonical($username);
//            $user->setFirstName($firstName);
//            $user->setLastName($lastName);
//            $user->setDisplayName($firstName." ".$lastName);
//            $user->setPhone($phone);
//            $user->setFax($fax);
//            $user->setTitle($title);
//            $user->setOffice($office);
//            $user->setPassword("");
//
//            $pathlogyServiceEntities = new ArrayCollection();
//            foreach( $pathlogyServices as $pathlogyService ) {
//                $pathlogyService = trim($pathlogyService);
//                if( $pathlogyService != "" ) {
//                    //echo $username.": service=(".$pathlogyService.")<br>";
//                    $pathlogyServiceEntity  = $em->getRepository('OlegOrderformBundle:PathServiceList')->findOneByName($pathlogyService);
//
//                    if( $pathlogyServiceEntity ) {
//                        //$pathlogyServiceEntities[] = $pathlogyServiceEntity;
//                    } else {
//                        $pathlogyServiceEntity = new PathServiceList();
//                        $pathlogyServiceEntity->setCreator( $this->get('security.context')->getToken()->getUser() );
//                        $pathlogyServiceEntity->setCreatedate( new \DateTime() );
//                        $pathlogyServiceEntity->setName( $pathlogyService );
//                        $pathlogyServiceEntity->setType('default');
//                        $em->persist($pathlogyServiceEntity);
//                        $em->flush();
//                    }
//                    $user->addPathologyServices($pathlogyServiceEntity);
//                }
//            }
////            if( count($pathlogyServiceEntities) > 0 ) {
////                $user->setPathologyServices($pathlogyServiceEntities);
////            }
//
//            $user->setEnabled(true);
//            $user->setLocked(false);
//            $user->setExpired(false);
//
//            $found_user = $em->getRepository('OlegOrderformBundle:User')->findOneByUsername($username);
//            if( $found_user ) {
//                //echo $username." found ";
//                //$user = $em->merge($user);
//                //$em->flush();
//            } else {
//                //echo $username." not found ";
//                $em->persist($user);
//                $em->flush();
//                $count++;
//            }
//
//        }//for each user
//
//        return $count;
//    }

}
