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



use App\UserdirectoryBundle\Controller\OrderAbstractController;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Fabiang\Sasl\Sasl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Ldap\Adapter\ExtLdap\ConnectionOptions;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends OrderAbstractController
{

    /**
     * @Route("/thanks-for-downloading/{id}/{sitename}", name="common_thankfordownloading", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Default/thanksfordownloading.html.twig")
     */
    public function thankfordownloadingAction(Request $request, $id, $sitename) {
        return array(
            'fileid' => $id,
            'sitename' => $sitename
        );
    }


    /**
     * @Route("/show-system-log", name="employees_show_system_log", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Default/show-system-log.html.twig")
     */
    public function showSystemLogAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $logDir = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "log";

        $systemLogFile = $logDir . DIRECTORY_SEPARATOR . "prod.log";

        //echo file_get_contents( $systemLogFile );

        //$orig = file_get_contents($systemLogFile);
        //$a = htmlentities($orig);

        echo '<code>';
        echo '<pre>';

        //echo $a;
        echo file_get_contents( $systemLogFile );

        echo '</pre>';
        echo '</code>';

        exit();
        return array();
    }

    /**
     * @Route("/show-system-test-error-log", name="employees_show_system_test_error_log", methods={"GET"})
     * @Template("AppUserdirectoryBundle/Default/show-system-log.html.twig")
     */
    public function showSystemTestLogAction(Request $request) {

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $logDir = $this->container->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "log";

        $systemLogFile = $logDir . DIRECTORY_SEPARATOR . "test_ERROR.log";

        //echo file_get_contents( $systemLogFile );

        //$orig = file_get_contents($systemLogFile);
        //$a = htmlentities($orig);

        echo '<code>';
        echo '<pre>';

        //echo $a;
        echo file_get_contents( $systemLogFile );

        echo '</pre>';
        echo '</code>';

        exit();
        return array();
    }


//    /**
//     * @Route("/", name="employees_home")
//     * @Template("AppUserdirectoryBundle/Default/home.html.twig")
//     */
//    public function indexAction()
//    {
//
//        if(
//            false == $this->get('security.authorization_checker')->isGranted('ROLE_USER') ||              // authenticated (might be anonymous)
//            false == $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')    // authenticated (NON anonymous)
//        ){
//            return $this->redirect( $this->generateUrl('login') );
//        }
//
//        //$form = $this->createForm(new SearchType(),null);
//
//        //$form->submit($request);  //use bind instead of handleRequest. handleRequest does not get filter data
//        //$search = $form->get('search')->getData();
//
//        //check for active access requests
//        $accessreqs = $this->getActiveAccessReq();
//
//
//        return array(
//            'accessreqs' => count($accessreqs)
//            //'form' => $form->createView(),
//        );
//    }
//
//    //check for active access requests
//    public function getActiveAccessReq() {
//        if( !$this->get('security.authorization_checker')->isGranted('ROLE_USERDIRECTORY_ADMIN') ) {
//            return null;
//        }
//        $userSecUtil = $this->get('user_security_utility');
//        $accessreqs = $userSecUtil->getUserAccessRequestsByStatus($this->getParameter('employees.sitename'),AccessRequest::STATUS_ACTIVE);
//        return $accessreqs;
//    }


//    /**
//     * @Route("/admin", name="employees_admin")
//     * @Template("AppUserdirectoryBundle/Default/index.html.twig")
//     */
//    public function adminAction()
//    {
//        $name = "This is an Employee Directory Admin Page!!!";
//        return array('name' => $name);
//    }
//
//
//    /**
//     * @Route("/hello/{name}", name="employees_hello")
//     * @Template()
//     */
//    public function helloAction($name)
//    {
//        return array('name' => $name);
//    }


    /**
     * https://collage.med.cornell.edu/order/directory/fix-author-generated-users/
     *
     * @Route("/fix-author-generated-users/", name="employees_fix-author-generated-users")
     */
    public function fixAuthorGeneratedUsersAction()
    {
        exit("Not allowed. This is one time run script to fix added by for already generated users.");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        //get generated users by createdby
        //$createdBy = "manual-".$sitename;
        $repository = $em->getRepository('AppUserdirectoryBundle:User');
        $dql = $repository->createQueryBuilder("user");
        $dql->where("user.createdby LIKE '%manual-%'");
        $query = $em->createQuery($dql);
        $users = $query->getResult();
        echo "Generated users count=".count($users)."<br>";

        foreach($users as $user) {
            echo "user=".$user.": ";

            $author = $this->getAuthorFromLogger($user);
            if( $author ) {
                $user->setAuthor($author);
                $em->flush();
                echo "Update author=".$author."<br>";
            } else {
                echo "Author is not found in logger<br>";
            }
        }

        exit("EOF generated users");
    }
    public function getAuthorFromLogger($user) {
        $em = $this->getDoctrine()->getManager();

        //get the date from event log
        $repository = $em->getRepository('AppUserdirectoryBundle:Logger');
        $dql = $repository->createQueryBuilder("logger");


        $dql->where("logger.entityName = 'User' AND logger.entityId = '".$user->getId()."'");

        //$dql->andWhere("logger.event LIKE '%"."status changed to '/Unpaid/Issued"."%'"); //status changed to 'Unpaid/Issued'
        //$dql->andWhere("logger.event LIKE :eventStr OR logger.event LIKE :eventStr2");
        $dql->andWhere("logger.event LIKE :eventStr OR logger.event LIKE :eventStr2");

        $dql->orderBy("logger.id","DESC");
        $query = $em->createQuery($dql);

        $search = "User account for ";
        $search2 = "has been created by";

        $query->setParameters(
            array(
                'eventStr' => '%'.$search.'%',
                'eventStr2' => '%'.$search2.'%'
            )
        );

        $loggers = $query->getResult();

        if( count($loggers) > 0 ) {
            $logger = $loggers[0];

            $author = $logger->getUser();
            return $author;
        }

        return NULL;
    }

    /**
     * https://collage.med.cornell.edu/order/directory/fix-author-generated-users/
     *
     * @Route("/some-testing/", name="employees_some_testing")
     */
    public function someTestingAction() {

        exit("disabled");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        //Test 1
        $em = $this->getDoctrine()->getManager();
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        $letterOne = $em->getRepository('AppUserdirectoryBundle:Document')->findOneById(877);
        $letterOnePath = $letterOne->getServerPath();
        $letterTwo = $em->getRepository('AppUserdirectoryBundle:Document')->findOneById(875);
        $letterTwoPath = $letterTwo->getServerPath();
        $identical = $fellappRecLetterUtil->checkIfFilesIdentical($letterOnePath,$letterTwoPath,$fileTwoHash=null);
        if( $identical ) {
            echo "Files are identical <br>";
        } else {
            echo "Files are diiferent <br>";
        }

        exit("EOF Test 1");

        //Test 2
        $fellappRecLetterUtil = $this->container->get('fellapp_rec_letter_util');
        $googlesheetmanagement = $this->container->get('fellapp_googlesheetmanagement');
        $service = $googlesheetmanagement->getGoogleService();

        $folderId = "1ex5Yh8nJia8WUQ7eTkSnM1OS9Z18J2Oz"; //created 12:48 PM Jul 16
        $files = $googlesheetmanagement->retrieveFilesByFolderId($folderId,$service);

        $count = 0;
        foreach($files as $file) {
            $goolgeDateTime = $fellappRecLetterUtil->getGoogleFileCreationDatetime($service, $file->getId());
            if( $count++ > 3 ) {
                break;
            }
        }

        exit("EOF Test 2");

    }


    /**
     * @Route("/login-testing/{username}/{password}/", name="employees_login_testing")
     */
    public function loginTestingAction( Request $request, $username, $password ) {

        //exit("disabled");

        if( false === $this->get('security.authorization_checker')->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            return $this->redirect($this->generateUrl('employees-nopermission'));
        }

        $authUtil = $this->container->get('authenticator_utility');
        $authUtil->laminasBind($username,$password);

        //$this->loginLaminasTest($username,$password);

        //$this->loginTest($username,$password);
        //$this->loginSymfonyTest($username,$password);


        exit("EOF Login Testing");

    }

    //It might work
    //remove: fabiang/sasl symfony/ldap
    public function loginLaminasTest( $thisUser, $password ) {

        echo "username=[$thisUser], password=[$password] <br>";

        $host = 'a.wcmc-ad.net';

        $options = [
            'host'              => $host,
            //'username'          => 'xxx',
            //'password'          => 'xxx',
            //'bindRequiresDn'    => false,
            'accountDomainName' => $host,
            'baseDn'            => 'dc=a,dc=wcmc-ad,dc=net',
            //'useSsl'            => true,
            //'useStartTls'      => true
        ];

        $ldap = new \Laminas\Ldap\Ldap($options);
        $ldap->bind($thisUser, $password);

        dump($ldap);
        //exit('EOF');

        //$acctname = $ldap->getCanonicalAccountName($thisUser, \Laminas\Ldap\Ldap::ACCTNAME_FORM_DN);

        $acctname = $ldap->getCanonicalAccountName($thisUser, \Laminas\Ldap\Ldap::ACCTNAME_FORM_DN);
        echo "acctname=[$acctname] <br>";

        //dump($acctname);

        echo "EOF loginLaminasTest <br>";
        exit('EOF');
    }

    public function loginFabiangTest( $thisUser, $password ) {

        echo "username=[$thisUser], password=[$password] <br>";

        $host = 'a.wcmc-ad.net';

        $factory = new Sasl();

        $mechanism = $factory->factory('SCRAM-SHA-1', array(
            'authcid'  => $thisUser,
            'secret'   => $password,
            //'authzid'  => 'authzid', // optional. Username to proxy as
            //'service'  => 'servicename', // optional. Name of the service
            'hostname' => $host, // optional. Hostname of the service
        ));

        $response = $mechanism->createResponse();

        dump($response);

        exit('EOF');
    }

    public function loginSymfonyTest( $thisUser, $password ) {
        $host = 'a.wcmc-ad.net';

        //$options = 'X_SASL_MECH'; //array(X_SASL_MECH);

        $encryption = 'none';
        //$encryption = 'ssl';
        //$encryption = 'tls';

        $ldap = Ldap::create('ext_ldap', [
            'host' => $host,
            'port' => 389,
            'version' => 3,
            //'encryption' => $encryption,
            //'options' => $options
            //'options' => array(x_sasl_mech)
            //'x_sasl_mech'
        ]);

        //$ldap = Ldap::create('ext_ldap', ['connection_string' => 'ldaps://$host:636']);

        //$dn = "OU=NYP Users,OU=External,DC=a,DC=wcmc-ad,DC=net";
        $dn = "cn=Users,DC=a,DC=wcmc-ad,DC=net";
        //$dn = "DC=a,DC=wcmc-ad,DC=net";

        $dn = "cn=".$thisUser.",".$dn;

        //$dn = $thisUser;
        echo "dn: [$dn]<br>";
        echo "password=[$password]<br>";

        //$dn = "CN=xxx,OU=NYP Users,OU=External,DC=a,DC=wcmc-ad,DC=net";
        //$dn = "CN=xxx";
        //$dn = "CN=xxx,DC=a,DC=wcmc-ad,DC=net";
        //$password = "xxx";

        //$dn = "CN=xxx,OU=NYP Users,OU=External,DC=a,DC=wcmc-ad,DC=net";
        //$password = "xxx";

        $r = $ldap->bind($dn, $password);

        dump($r);

//        if( $r ) {
//            exit('OK');
//        } else {
//            exit('NOT OK');
//        }

        echo "EOF loginSymfonyTest <br>";
        //exit('EOF loginSymfonyTest');
    }


    public function loginTest_php( $thisUser, $password ) {

        //$thisUser = $_SERVER['REMOTE_USER'];
        //$thisUser = "nyptestuser1";
        $thisServer = 'a.wcmc-ad.net';

        # Bind to the directory Server
        $ldap = ldap_connect("ldap://$thisServer");
        if($ldap) {
            $r = ldap_bind($ldap);
        } else {
            echo "Unable to connect to $thisServer!";
        }

        # Set an option
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

        echo "<h1>Kerberos Credentials</h1>\n";
        echo "<pre>\n";
        system('klist');
        echo "</pre>\n";

        $dn = "DC=a,DC=wcmc-ad,DC=net";

        $r=ldap_sasl_bind ( $ldap, NULL, $password, 'DIGEST-MD5', NULL, $thisUser, $dn);

        if( $r ) {
            exit('OK');
        } else {
            exit('NOT OK');
        }

        exit('111');

        # Bind using the default Kerberos credentials
        //if (ldap_sasl_bind($ldap,"","","GSSAPI")) {
        if (ldap_sasl_bind($ldap,$thisUser,$password,"GSSAPI")) {

            # Search the Directory
            //$dn = "cn=people,dc=stanford,dc=edu";
            //$filter = "(|(uid=$thisUser)(mail=$thisUser@*))";
            //DistinguishedName="DC=a,DC=wcmc-ad,DC=net" SearchFilter="(objectClass=user)"
            //AttributeNameUUID="objectGuid" AttributeNameUser="sAMAccountName" PageSize="10">
            $dn = "DC=a,DC=wcmc-ad,DC=net";
            $filter = "(objectClass=user)";
            echo "<h1>LDAP Search</h1>\n";
            echo "Host: $thisServer<br />\n";
            echo "Base DN: $dn<br />\n";
            echo "Filter: $filter<br />\n";
            echo "REMOTE_USER: $thisUser<br />\n";

            $result = ldap_search($ldap, $dn, $filter);
            if ($result) {
                echo "<blockquote>\n";
                $cnt = ldap_count_entries($ldap, $result);
                echo "Number of entries returned is $cnt<br />\n";
                $info = ldap_get_entries($ldap,$result);
                echo "Data for " . $info["count"] . " items returned:<p>";
                print("\n");
                for($i=0;$i<$info["count"];$i++) {
                    echo "dn is: " . $info[$i]["dn"] . "<br />";
                    print("\n");
                    echo "first cn entry is: " . $info[$i]["cn"][0] . "<br />";
                    print("\n");
                    echo "first email is: " . $info[$i]["mail"][0] . "<br /> <hr />";
                    print("\n");
                }
                echo "</blockquote>\n";
            }
        } else {
            echo '<font color="red">Bind to the directory failed.</font>'."\n";
        }

        ldap_close($ldap);

    }


}
