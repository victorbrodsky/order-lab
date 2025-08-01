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

namespace App\UserdirectoryBundle\Form;


use App\OrderformBundle\Entity\AccessionType;
use App\OrderformBundle\Entity\MrnType; //process.py script: replaced namespace by ::class: added use line for classname=MrnType
use App\OrderformBundle\Entity\OrderDelivery; //process.py script: replaced namespace by ::class: added use line for classname=OrderDelivery
use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User
use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteParametersType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;

        if( !isset($this->params['param']) ) {
            $this->params['param'] = null;
        }
    }


    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

//        $always_empty = true;
//
//        if( $this->params['cycle'] != 'show' ) {
//            $always_empty = false;
//        }

        //echo "$always_empty=".$always_empty."<br>";
        //echo "param=".$this->params['param']."<br>";

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'connectionChannel' ) {
//            $builder->add('connectionChannel',null,array(
//                'label'=>'Connection Channel (blank, http or https):',
//                'attr' => array('class'=>'form-control textarea')
//            ));
            $builder->add('connectionChannel',ChoiceType::class,array( //flipped
                'label'=>'Internal Connection Channel (http or https; Clearing Cache is required):',
                'choices' => array("http"=>"http", "https"=>"https"),
                //'choices_as_values' => true,
                'attr' => array('class'=>'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'urlConnectionChannel' ) {
            $builder->add('urlConnectionChannel',ChoiceType::class,array( //flipped
                //'label'=>'Url Connection Channel,  (required in case of using HaProxy, http or https; Clearing Cache is required):',
                'label'=>'External Connection Channel'.
                    " (if using HaProxy, internal connection channel should be 'http' and external connection channel should be 'https'".
                    '; http or https; Clearing Cache is required):',
                'choices' => array("http"=>"http", "https"=>"https"),
                //'choices_as_values' => true,
                'attr' => array('class'=>'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maxIdleTime' )
        $builder->add('maxIdleTime',null,array(
            'label'=>'Max Idle Time (min):',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'environment' )
        $builder->add('environment',ChoiceType::class,array( //flipped
            'label'=>'Environment:',
            'choices' => array('live'=>'live', 'test'=>'test', 'dev'=>'dev', 'demo'=>'demo'),
            //'choices_as_values' => true,
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'version' )
            $builder->add('version',null,array(
                'label'=>'Version:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'siteEmail' )
        $builder->add('siteEmail',EmailType::class,array(
            'label'=>'Site Email:',
            'attr' => array('class'=>'form-control')
        ));

        //smtp
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'smtpServerAddress' ) {
            $builder->add('smtpServerAddress', null, array(
                'label' => 'SMTP Server Address:',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerPort' ) {
            $builder->add('mailerPort', null, array(
                'label' => 'Mailer Port (i.e. 25, 465, 587):',
                'attr' => array('class' => 'form-control')
            ));
        }
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerTransport' ) {
//            $builder->add('mailerTransport', null, array(
//                'label' => 'Mailer Transport (i.e. smtp or gmail):',
//                'attr' => array('class' => 'form-control')
//            ));
//        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerAuthMode' ) {
            $builder->add('mailerAuthMode', null, array(
                'label' => 'Mailer Authentication Mode (i.e. oauth or login):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerUseSecureConnection' ) {
            $builder->add('mailerUseSecureConnection', null, array(
                'label' => 'Mailer Use Security Connection (i.e. tls or ssl):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerUser' ) {
            $builder->add('mailerUser', null, array(
                'label' => 'Mailer Username:',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerPassword' ) {
            $builder->add('mailerPassword', null, array(
                'label' => 'Mailer Password:',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerSpool' ) {
            $builder->add('mailerSpool', null, array(
                'label' => 'Use email spooling (Instead of sending every email directly to the SMTP server individually, add outgoing emails to a queue and then periodically send the queued emails. This makes form submission appear faster.):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerDeliveryAddresses' ) {
            $builder->add('mailerDeliveryAddresses', null, array(
                'label' => 'Reroute all outgoing emails only to the following email address(es) listed in the "email1@example.com,email2@example.com,email3@example.com" format separated by commas. (This is useful for a non-live server environment to avoid sending emails to users. Leaving this field empty will result in emails being sent normally.):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mailerFlushQueueFrequency' ) {
            $builder->add('mailerFlushQueueFrequency', null, array(
                'label' => 'Frequency of sending emails in the queue (in minutes between eruptions):',
                'attr' => array('class' => 'form-control')
            ));
        }

        //scan order DB
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerAddress' )
        $builder->add('dbServerAddress',null,array(
            'label'=>'ScanOrder DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerPort' )
        $builder->add('dbServerPort',null,array(
            'label'=>'ScanOrder DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerAccountUserName' )
        $builder->add('dbServerAccountUserName',null,array(
            'label'=>'ScanOrder DB Server Account User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbServerAccountPassword' )
        $builder->add('dbServerAccountPassword',null,array(
            'label'=>'ScanOrder DB Server Account Password:',
            //'always_empty' => $always_empty,
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbDatabaseName' )
        $builder->add('dbDatabaseName',null,array(
            'label'=>'ScanOrder Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //Send request to both authentication Active Directory/LDAP servers when the first is selected for a single log in attempt

        //////////////// LDAP 1 ////////////////////
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultPrimaryPublicUserIdType' )
            $builder->add('defaultPrimaryPublicUserIdType',null,array(
                'label'=>'Default Primary Public User ID Type:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapAll' )
            $builder->add('ldapAll',null,array(
                'label'=>'Send request to both authentication Active Directory/LDAP servers when the first is selected for a single log in attempt:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAddress' )
        $builder->add('aDLDAPServerAddress',null,array(
            'label'=>'AD/LDAP Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerPort' )
        $builder->add('aDLDAPServerPort',null,array(
            'label'=>'AD/LDAP Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerOu' )
        $builder->add('aDLDAPServerOu',null,array(
            'label'=>"AD/LDAP Bind DN for ldap search or simple authentication (cn=read-only-admin,dc=example,dc=com;ou=group1,dc=example,dc=com):",
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountUserName' )
        $builder->add('aDLDAPServerAccountUserName',null,array(
            'label'=>'AD/LDAP Server Account User Name (for ldap search):',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountPassword' )
        $builder->add('aDLDAPServerAccountPassword',null,array(
            'label'=>'AD/LDAP Server Account Password (for ldap search):',
            //'always_empty' => $always_empty,
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapExePath' )
            $builder->add('ldapExePath',null,array(
                'label'=>'LDAP/AD Authenticator Path - relevant for Windows-based servers only (Default: "../src/App/UserdirectoryBundle/Util/" ):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapExeFilename' )
            $builder->add('ldapExeFilename',null,array(
                'label'=>'LDAP/AD Authenticator File Name - relevant for Windows-based servers only (Default: "LdapSaslCustom.exe"):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapMapperEmail' )
            $builder->add('ldapMapperEmail',null,array(
                'label'=>'LDAP/AD Mapper Email Postfix (med.cornell.edu):',
                'attr' => array('class'=>'form-control')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapMapperPrimaryPublicUserIdType' )
            $builder->add('ldapMapperPrimaryPublicUserIdType',null,array(
                'label'=>'LDAP/AD Mapper Primary Public User ID Type:',
                'attr' => array('class'=>'combobox')
            ));
        //////////////// EOF LDAP 1 ////////////////////


        //////////////// LDAP 2 ////////////////////
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAddress2' )
            $builder->add('aDLDAPServerAddress2',null,array(
                'label'=>'AD/LDAP Server Address:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerPort2' )
            $builder->add('aDLDAPServerPort2',null,array(
                'label'=>'AD/LDAP Server Port:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerOu2' )
            $builder->add('aDLDAPServerOu2',null,array(
                'label'=>"AD/LDAP Bind DN for ldap search or simple authentication (cn=read-only-admin,dc=example,dc=com;ou=group1,dc=example,dc=com):",
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountUserName2' )
            $builder->add('aDLDAPServerAccountUserName2',null,array(
                'label'=>'AD/LDAP Server Account User Name (for ldap search):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'aDLDAPServerAccountPassword2' )
            $builder->add('aDLDAPServerAccountPassword2',null,array(
                'label'=>'AD/LDAP Server Account Password (for ldap search):',
                //'always_empty' => $always_empty,
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapExePath2' )
            $builder->add('ldapExePath2',null,array(
                'label'=>'LDAP/AD Authenticator Path - relevant for Windows-based servers only (Default: "../src/App/UserdirectoryBundle/Util/" ):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapExeFilename2' )
            $builder->add('ldapExeFilename2',null,array(
                'label'=>'LDAP/AD Authenticator File Name - relevant for Windows-based servers only (Default: "LdapSaslCustom.exe"):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapMapperEmail2' )
            $builder->add('ldapMapperEmail2',null,array(
                'label'=>'LDAP/AD Mapper Email Postfix (nyp.org):',
                'attr' => array('class'=>'form-control')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'ldapMapperPrimaryPublicUserIdType2' )
            $builder->add('ldapMapperPrimaryPublicUserIdType2',null,array(
                'label'=>'LDAP/AD Mapper Primary Public User ID Type:',
                'attr' => array('class'=>'combobox')
            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultPrimaryPublicUserIdType2' )
//            $builder->add('defaultPrimaryPublicUserIdType2',null,array(
//                'label'=>'Default Primary Public User ID Type:',
//                'attr' => array('class'=>'form-control')
//            ));
        //////////////// EOF LDAP 2 ////////////////////

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'autoAssignInstitution' )
//            $builder->add('autoAssignInstitution',null,array(
//                'label'=>'Auto-Assign Institution name:',
//                'attr' => array('class'=>'form-control')
//            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'enableAutoAssignmentInstitutionalScope' )
            $builder->add('enableAutoAssignmentInstitutionalScope',null,array(
                'label'=>'Enable auto-assignment of Institutional (PHI) Scope:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));


        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'autoAssignInstitution' ) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $institution = $event->getData()->getAutoAssignInstitution();
                $form = $event->getForm();

                $label = null;
                if( $institution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels($institution) . ":";
                }
                if( !$label ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels(null) . ":";
                }

                $form->add('autoAssignInstitution', CustomSelectorType::class, array(
                    'label' => "Auto-Assign Institution name - ".$label,
                    'required' => false,
                    'attr' => array(
                        'class' => 'ajax-combobox-compositetree',
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
                        'data-compositetree-classname' => 'Institution',
                        'data-label-prefix' => 'Auto-Assign Institution name -',  //'Originating Organizational Group',
                        'data-label-postfix' => ''
                    ),
                    'classtype' => 'institution'
                ));
            });
        }


        //pacsvendor DB
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pacsvendorSlideManagerDBServerAddress' )
        $builder->add('pacsvendorSlideManagerDBServerAddress',null,array(
            'label'=>'PACS DB Server Address:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pacsvendorSlideManagerDBServerPort' )
        $builder->add('pacsvendorSlideManagerDBServerPort',null,array(
            'label'=>'PACS DB Server Port:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pacsvendorSlideManagerDBUserName' )
        $builder->add('pacsvendorSlideManagerDBUserName',null,array(
            'label'=>'PACS DB Server User Name:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pacsvendorSlideManagerDBPassword' )
        $builder->add('pacsvendorSlideManagerDBPassword',null,array(
            'label'=>'PACS DB Server Password:',
            'attr' => array('class'=>'form-control')
        ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pacsvendorSlideManagerDBName' )
        $builder->add('pacsvendorSlideManagerDBName',null,array(
            'label'=>'PACS Database Name:',
            'attr' => array('class'=>'form-control')
        ));

        //footer
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'institutionurl' )
            $builder->add('institutionurl',null,array(
                'label'=>'Institution URL:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'institutionname' )
            $builder->add('institutionname',null,array(
                'label'=>'Institution Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'subinstitutionurl' )
            $builder->add('subinstitutionurl',null,array(
                'label'=>'Institution URL (Instance Owner Link in Footer):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'subinstitutionname' )
            $builder->add('subinstitutionname',null,array(
                'label'=>'Institution Name (Instance Owner in Footer):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'departmenturl' )
            $builder->add('departmenturl',null,array(
                'label'=>'Department URL:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'departmentname' )
            $builder->add('departmentname',null,array(
                'label'=>'Department Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'showCopyrightOnFooter' )
            $builder->add('showCopyrightOnFooter',null,array(
                'label'=>'Show copyright line on every footer:',
                'attr' => array('class'=>'form-control')
            ));

        //maintenance
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenance' )
            $builder->add('maintenance',null,array(
                'label'=>'Maintenance:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenanceenddate' )
            $builder->add('maintenanceenddate',null,array(
                'label'=>'Maintenance Until:',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy H:m',
                'html5' => false,
                'attr' => array('class'=>'form-control datetimepicker')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenanceloginmsg' )
            $builder->add('maintenanceloginmsg',null,array(
                'label'=>'Maintenance Login Message:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'maintenancelogoutmsg' )
            $builder->add('maintenancelogoutmsg',null,array(
                'label'=>'Maintenance Logout Message:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'globalNoteLogin' )
            $builder->add('globalNoteLogin',null,array(
                'label'=>'Global note for all login page:',
                'attr' => array('class'=>'form-control')
            ));

        //uploads
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'employeesuploadpath' )
            $builder->add('employeesuploadpath',null,array(
                'label'=>'Employee Directory Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'scanuploadpath' )
            $builder->add('scanuploadpath',null,array(
                'label'=>'Scan Orders Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'avataruploadpath' )
            $builder->add('avataruploadpath',null,array(
                'label'=>'Employee Directory Avatar Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'fellappuploadpath' )
            $builder->add('fellappuploadpath',null,array(
                'label'=>'Fellowship Application Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'resappuploadpath' )
            $builder->add('resappuploadpath',null,array(
                'label'=>'Residency Application Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'transresuploadpath' )
            $builder->add('transresuploadpath',null,array(
                'label'=>'Translational Research Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'callloguploadpath' )
            $builder->add('callloguploadpath',null,array(
                'label'=>'Call Log Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'crnuploadpath' )
            $builder->add('crnuploadpath',null,array(
                'label'=>'Critical Result Notification Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));

        //vacrequploadpath
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'vacrequploadpath' )
            $builder->add('vacrequploadpath',null,array(
                'label'=>'Vacation Request Upload Folder:',
                'attr' => array('class'=>'form-control')
            ));


        //titles and messages
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'mainHomeTitle' )
            $builder->add('mainHomeTitle',null,array(
                'label'=>'Main Home Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'listManagerTitle' )
            $builder->add('listManagerTitle',null,array(
                'label'=>'List Manager Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'eventLogTitle' )
            $builder->add('eventLogTitle',null,array(
                'label'=>'Event Log Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'siteSettingsTitle' )
            $builder->add('siteSettingsTitle',null,array(
                'label'=>'Site Settings Title:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'contentAboutPage' )
            $builder->add('contentAboutPage',null,array(
                'label'=>'About Page Content:',
                'attr' => array('class'=>'form-control textarea')
            ));

//        //Fellowship Application parameters
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'recLetterSaltFellApp' ) {
//            $builder->add('recLetterSaltFellApp', null, array(
//                'label' => 'Recommendation Letter Salt:',
//                'attr' => array('class' => 'form-control')
//            ));
//        }

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'codeGoogleFormFellApp' )
//            $builder->add('codeGoogleFormFellApp',null,array(
//                'label'=>'Path to the local copy of the fellowship application form Code.gs file (https://script.google.com/a/macros/pathologysystems.org/d/14jgVkEBCAFrwuW5Zqiq8jsw37rc4JieHkKrkYz1jyBp_DFFyTjRGKgHj/edit):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'allowPopulateFellApp' )
//            $builder->add('allowPopulateFellApp',null,array(
//                'label' => 'Periodically import fellowship applications and reference letters submitted via the Google form:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'sendEmailUploadLetterFellApp' ) {
//            $builder->add('sendEmailUploadLetterFellApp', null, array(
//                'label'=>'Automatically send invitation emails to upload recommendation letters:',
//                'attr' => array('class' => 'form-control form-control-modif', 'style' => 'margin:0')
//            ));
//        }

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'confirmationSubjectFellApp' )
//            $builder->add('confirmationSubjectFellApp',null,array(
//                'label'=>'Email subject for confirmation of application submission:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));
        
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'confirmationBodyFellApp' )
//            $builder->add('confirmationBodyFellApp',null,array(
//                'label'=>'Email body for confirmation of application submission:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));
        
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'confirmationEmailFellApp' )
//            $builder->add('confirmationEmailFellApp',null,array(
//                'label'=>'Email address for confirmation of application submission:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'clientEmailFellApp' )
//            $builder->add('clientEmailFellApp',null,array(
//                'label'=>'Client Email for accessing the Google Drive API (1040591934373-1sjcosdt66bmani0kdrr5qmc5fibmvk5@developer.gserviceaccount.com):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'p12KeyPathFellApp' )
//            $builder->add('p12KeyPathFellApp',null,array(
//                'label'=>'Full Path to p12 key or service account credentials.json file for accessing the Google Drive API (E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\src\App\FellAppBundle\Util\FellowshipApplication-f1d9f98353e5.p12):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'googleDriveApiUrlFellApp' )
//            $builder->add('googleDriveApiUrlFellApp',null,array(
//                'label'=>'Google Drive API URL (https://www.googleapis.com/auth/drive https://spreadsheets.google.com/feeds):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'userImpersonateEmailFellApp' )
//            $builder->add('userImpersonateEmailFellApp',null,array(
//                'label'=>'Impersonate the following user email address for accessing the Google Drive API (olegivanov@pathologysystems.org):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'templateIdFellApp' )
//            $builder->add('templateIdFellApp',null,array(
//                'label'=>'Template Google Spreadsheet ID (1ITacytsUV2yChbfOSVjuBoW4aObSr_xBfpt6m_vab48):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'backupFileIdFellApp' )
//            $builder->add('backupFileIdFellApp',null,array(
//                'label'=>'Backup Google Spreadsheet ID (19KlO1oCC88M436JzCa89xGO08MJ1txQNgLeJI0BpNGo):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'folderIdFellApp' )
//            $builder->add('folderIdFellApp',null,array(
//                'label'=>'Application Google Drive Folder ID (where the response spreadsheets are saved):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        //Not used anymore
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'configFileFolderIdFellApp' ) {
//            $builder->add('configFileFolderIdFellApp', null, array(
//                'label' => 'Google Drive Folder ID where config file is located:',
//                'attr' => array('class' => 'form-control form-control-modif', 'style' => 'margin:0')
//            ));
//        }

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'backupUpdateDatetimeFellApp' )
//            $builder->add('backupUpdateDatetimeFellApp',null,array(
//                'label'=>'Backup Sheet Last Modified Date:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'localInstitutionFellApp' )
//            $builder->add('localInstitutionFellApp',null,array(
//                'label'=>'Local Organizational Group for imported fellowship applications (Pathology Fellowship Programs (WCM)):',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'identificationUploadLetterFellApp' ) {
//            $builder->add('identificationUploadLetterFellApp', null, array(
//                'label' => 'Fellowship identification string to download recommendation letters (55555):',
//                'attr' => array('class' => 'form-control form-control-modif', 'style' => 'margin:0')
//            ));
//        }

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'deleteImportedAplicationsFellApp' )
//            $builder->add('deleteImportedAplicationsFellApp',null,array(
//                'label'=>"Delete successfully imported applications from Google Drive:",
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'deleteOldAplicationsFellApp' )
//            $builder->add('deleteOldAplicationsFellApp',null,array(
//                'label'=>'Delete downloaded spreadsheets with fellowship applications after successful import into the database:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'yearsOldAplicationsFellApp' )
//            $builder->add('yearsOldAplicationsFellApp',null,array(
//                'label'=>'Number of years to keep downloaded spreadsheets with fellowship applications as backup:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'spreadsheetsPathFellApp' )
//            $builder->add('spreadsheetsPathFellApp',null,array(
//                'label'=>'Path to the downloaded spreadsheets with fellowship applications (fellapp/Spreadsheets):',
//                'attr' => array(
//                    'class'=>'form-control form-control-modif',
//                    'style'=>'margin:0',
//                )
//            ));
//
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'applicantsUploadPathFellApp' )
//            $builder->add('applicantsUploadPathFellApp',null,array(
//                'label'=>'Path to the downloaded attached documents (fellapp/FellowshipApplicantUploads):',
//                'attr' => array(
//                    'class'=>'form-control form-control-modif',
//                    'style'=>'margin:0',
//                )
//            ));
//
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'reportsUploadPathFellApp' )
//            $builder->add('reportsUploadPathFellApp',null,array(
//                'label'=>'Path to the generated fellowship applications in PDF format (fellapp/Reports):',
//                'attr' => array(
//                    'class'=>'form-control form-control-modif',
//                    'style'=>'margin:0',
//                )
//            ));


//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'applicationPageLinkFellApp' )
//            $builder->add('applicationPageLinkFellApp',null,array(
//                'label'=>'Link to the Application Page:',
//                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'academicYearStart' )
            $builder->add('academicYearStart',null,array(
                'label'=>'Academic Year Start Day and Month (July 1st):',
                //'attr' => array('class'=>'datepicker form-control datepicker-day-month')
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'academicYearEnd' )
            $builder->add('academicYearEnd',null,array(
                'label'=>'Academic Year End Day and Month (June 30th):',
                'attr' => array('class'=>'form-control')
            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'holidaysUrl' )
//            $builder->add('holidaysUrl',null,array(
//                'label'=>'Link to list of holidays (http://intranet.med.cornell.edu/hr/):',
//                'attr' => array('class'=>'form-control')
//            ));

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'vacationAccruedDaysPerMonth' )
//            $builder->add('vacationAccruedDaysPerMonth',null,array(
//                'label'=>'Vacation days accrued per month by faculty (2):',
//                'attr' => array('class'=>'form-control')
//            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'enableMetaphone' )
            $builder->add('enableMetaphone',null,array(
                'label'=>'Enable use of Metaphone:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pathMetaphone' )
            $builder->add('pathMetaphone',null,array(
                'label'=>'Path to Metaphone:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        $this->addLis($builder);

        $this->addThirdPartySoftware($builder);

        if( !array_key_exists('singleField', $this->params) ) {
            $this->params['singleField'] = true;
        }
        if( $this->params['singleField'] == false ) {
        //if(1) {
            $builder->add('organizationalGroupDefaults', CollectionType::class, array(
                //'type' => new OrganizationalGroupDefaultType($this->params),
                'entry_type' => OrganizationalGroupDefaultType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__organizationalgroupdefaults__',
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'calllogResources' )
            $builder->add('calllogResources',null,array(
                'label'=>'Call Log Book Resources:',
                'attr' => array('class'=>'form-control textarea')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultDeidentifierAccessionType' ) {
            $builder->add('defaultDeidentifierAccessionType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
                'class' => AccessionType::class,
                //'choice_label' => 'name',
                //'choice_label' => 'getTreeName',
                'label' => 'Default Deidentifier Accession Type:',
                'required' => true,
                'multiple' => false,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultScanAccessionType' ) {
            $builder->add('defaultScanAccessionType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
                'class' => AccessionType::class,
                //'choice_label' => 'name',
                //'choice_label' => 'getTreeName',
                'label' => 'Default Scan Order Accession Type:',
                'required' => false,
                'multiple' => false,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultScanMrnType' ) {
            $builder->add('defaultScanMrnType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MrnType'] by [MrnType::class]
                'class' => MrnType::class,
                //'choice_label' => 'name',
                //'choice_label' => 'getTreeName',
                'label' => 'Default Scan Order Mrn Type:',
                'required' => false,
                'multiple' => false,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultScanDelivery' ) {
            $builder->add('defaultScanDelivery', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:OrderDelivery'] by [OrderDelivery::class]
                'class' => OrderDelivery::class,
                'label' => 'Default Slide Delivery:',
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultInstitutionalPHIScope' ) {
//            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//                $institution = $event->getData()->getDefaultInstitutionalPHIScope();
//                $form = $event->getForm();
//
//                $label = null;
//                if ($institution) {
//                    $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
//                }
//                if (!$label) {
//                    $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
//                }
//
//                $form->add('defaultInstitutionalPHIScope', CustomSelectorType::class, array(
//                    'label' => "Default Institutional PHI Scope - ".$label,
//                    'required' => false,
//                    'attr' => array(
//                        'class' => 'ajax-combobox-compositetree',
//                        'type' => 'hidden',
//                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
//                        'data-compositetree-classname' => 'Institution',
//                        'data-label-prefix' => 'Default Institutional PHI Scope -',  //'Originating Organizational Group',
//                        'data-label-postfix' => ''
//                    ),
//                    'classtype' => 'institution'
//                ));
//            });
//        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultOrganizationRecipient' ) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $institution = $event->getData()->getDefaultOrganizationRecipient();
                $form = $event->getForm();

                $label = null;
                if( $institution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels($institution) . ":";
                }
                if( !$label ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels(null) . ":";
                }

                $form->add('defaultOrganizationRecipient', CustomSelectorType::class, array(
                    'label' => "Default Organization Recipient - ".$label,
                    'required' => false,
                    'attr' => array(
                        'class' => 'ajax-combobox-compositetree',
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
                        'data-compositetree-classname' => 'Institution',
                        'data-label-prefix' => 'Default Organization Recipient -',  //'Originating Organizational Group',
                        'data-label-postfix' => ''
                    ),
                    'classtype' => 'institution'
                ));
            });
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'defaultScanner' ) {
            $builder->add('defaultScanner', EntityType::class, array(
                'class' => 'App\UserdirectoryBundle\Entity\Equipment',
                'label' => 'Default Scanner:',
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }


        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'permittedFailedLoginAttempt' ) {
            $builder->add('permittedFailedLoginAttempt',null,array(
                'label'=>'Permitted failed log in attempts:',
                'attr' => array('class'=>'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'captchaEnabled' ) {
            $builder->add('captchaEnabled',null,array(
                'label'=>'Captcha Enabled:',
                'attr' => array('class'=>'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'captchaSiteKey' ) {
            $builder->add('captchaSiteKey',null,array(
                'label'=>'Captcha Site Key (required when Captcha is enabled):',
                'attr' => array('class'=>'form-control textarea')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'captchaSecretKey' ) {
            $builder->add('captchaSecretKey',null,array(
                'label'=>'Captcha Secret Key (required when Captcha is enabled):',
                'attr' => array('class'=>'form-control textarea')
            ));
        }

        ////////////////////////// LDAP notice messages /////////////////////////
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'noticeAttemptingPasswordResetLDAP' ) {
            $builder->add('noticeAttemptingPasswordResetLDAP',null,array(
                'label'=>'Notice for attempting to reset password for an LDAP-authenticated account:',
                'attr' => array('class'=>'form-control textarea')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'loginInstruction' ) {
            $builder->add('loginInstruction',null,array(
                'label'=>'Notice to prompt user to use Active Directory account to log in (Please use your CWID to log in):',
                'attr' => array('class'=>'form-control textarea')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'noticeSignUpNoCwid' ) {
            $builder->add('noticeSignUpNoCwid',null,array(
                'label'=>'Notice to prompt user with no Active Directory account to sign up for a new account:',
                'attr' => array('class'=>'form-control textarea')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'noticeHasLdapAccount' ) {
            $builder->add('noticeHasLdapAccount',null,array(
                'label'=>'Account request question asking whether applicant has an Active Directory account:',
                'attr' => array('class'=>'form-control textarea')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'noticeLdapName' ) {
            $builder->add('noticeLdapName',null,array(
                'label'=>'Full local name for active directory account:',
                'attr' => array('class'=>'form-control textarea')
            ));
        }
        ////////////////////////// EOF LDAP notice messages /////////////////////////

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'navbarFilterInstitution1' ) {
            $builder->add('navbarFilterInstitution1', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                'class' => Institution::class,
                //'choice_label' => 'name',
                'choice_label' => 'getTreeName',
                'label' => 'Navbar Employee List Filter Institution #1:',
                'required' => true,
                'multiple' => false,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.organizationalGroupType","organizationalGroupType")
                        ->where("(list.type = :typedef OR list.type = :typeadd) AND list.level = :level AND organizationalGroupType.name = :inst")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            'level' => 0,
                            'inst' => 'Institution'
                        ));
                },
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'navbarFilterInstitution2' ) {
            $builder->add('navbarFilterInstitution2', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                'class' => Institution::class,
                //'choice_label' => 'name',
                'choice_label' => 'getTreeName',
                'label' => 'Navbar Employee List Filter Institution #2:',
                'required' => false,
                'multiple' => false,
                //'empty_value' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->leftJoin("list.organizationalGroupType","organizationalGroupType")
                        ->where("(list.type = :typedef OR list.type = :typeadd) AND list.level = :level AND organizationalGroupType.name = :inst")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            'level' => 0,
                            'inst' => 'Institution'
                        ));
                },
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'platformLogos' ) {
            $builder->add('platformLogos', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'label' => 'Platform Logo Image:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'transresProjectSelectionNote' ) {
            $builder->add('transresProjectSelectionNote', null, array(
                'label' => 'Translational Research Project Request Specialty Selection Note:',
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'transresHumanSubjectName' ) {
            $builder->add('transresHumanSubjectName', null, array(
                'label' => 'Name of the group that approves research projects involving human subjects (IRB):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'transresAnimalSubjectName' ) {
            $builder->add('transresAnimalSubjectName', null, array(
                'label' => 'Name of the group that approves research projects involving animal subjects (IACUC):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'transresBusinessEntityName' ) {
            $builder->add('transresBusinessEntityName', null, array(
                'label' => 'Name of the business entity responsible for the translational research site (Center for Translational Pathology):',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'transresBusinessEntityAbbreviation' ) {
            $builder->add('transresBusinessEntityAbbreviation', null, array(
                'label' => 'Abbreviated name of the business entity responsible for the translational research site (CTP):',
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'transresDashboardInstitution' ) {
//            $builder->add('transresDashboardInstitution', null, array(
//                'label' => 'Pathology Department for Translational Research Dashboard:',
//                'attr' => array('class' => 'form-control')
//            ));
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $institution = $event->getData()->getTransresDashboardInstitution();
                $form = $event->getForm();

                $label = null;
                if( $institution ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels($institution) . ":";
                }
                if( !$label ) {
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:Institution'] by [Institution::class]
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels(null) . ":";
                }

                $form->add('transresDashboardInstitution', CustomSelectorType::class, array(
                    'label' => "Pathology Department for Translational Research Dashboard - ".$label,
                    'required' => false,
                    'attr' => array(
                        'class' => 'ajax-combobox-compositetree',
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'UserdirectoryBundle',
                        'data-compositetree-classname' => 'Institution',
                        'data-label-prefix' => 'Auto-Assign Institution name -',  //'Originating Organizational Group',
                        'data-label-postfix' => ''
                    ),
                    'classtype' => 'institution'
                ));
            });
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'emailCriticalError' ) {
            $builder->add('emailCriticalError', null, array(
                'label' => 'E-Mail Platform Administrator in case of critical errors:',
                'attr' => array('class' => 'form-control')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'restartServerErrorCounter' ) {
            $builder->add('restartServerErrorCounter', null, array(
                'label' => 'Restart Apache in case of critical this many errors over the course of 10 minutes:',
                'attr' => array('class' => 'form-control')
            ));
        }
        
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'remoteAccessUrl' ) {
            $builder->add('remoteAccessUrl', null, array(
                'label' => 'Note Regarding Remote Access (https://its.weill.cornell.edu/services/wifi-networks/remote-access):',
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'externalMonitorUrl' ) {
            $builder->add('externalMonitorUrl', null, array(
                'label' => 'External server monitor url: view-med checks if view is running (i.e. http://view-test.med.cornell.edu):',
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'monitorScript' ) {
            $builder->add('monitorScript', null, array(
                'label' => "Monitor independent of Symfony, PHP, Postgresql 
                    script to monitor if url on this server is running; Help: webmonitor.py -H 
                    (python3 path/to/webmonitor.py 
                    -l 'url1,url2...' -h mailerhost -o mailerport -u mailerusername -p mailerpassword
                    -s 'sender email' -r 'receiver email1, email2 ...' 
                    -c 'sudo systemctl restart postgresql-17, sudo systemctl restart haproxy, sudo systemctl restart php-fpm' 
                    -U 'http://view-test.med.cornell.edu' 
                    -e 'server environment or description)'
                    :",
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'monitorCheckInterval' ) {
            $builder->add('monitorCheckInterval', null, array(
                'label' => 'Monitor check interval in minutes:',
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'dbBackupConfig' ) {
            $builder->add('dbBackupConfig', null, array(
                'label' => 'Configuration json file for DB backup. Unique \'idname\' must be included somewhere in the command.',
                'attr' => array('class' => 'form-control textarea')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'filesBackupConfig' ) {
            $builder->add('filesBackupConfig', null, array(
                'label' => 'Configuration json file for backup uploaded folder (filesBackup cron job). Unique \'idname\' must be included somewhere in the command.',
                'attr' => array('class' => 'form-control textarea')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'emailCriticalErrorExceptionUsers' ) {
//            $builder->add('emailCriticalErrorExceptionUsers', null, array(
//                'label' => 'Do not send critical error notifications to the following users:',
//                'attr' => array('class' => 'combobox')
//            ));

            $builder->add( 'emailCriticalErrorExceptionUsers', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
                'class' => User::class,
                'label'=>'Do not send critical error notifications to the following users:',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->where("user.roles LIKE '%ROLE_PLATFORM_ADMIN%' OR user.roles LIKE '%ROLE_PLATFORM_DEPUTY_ADMIN%'")
                        ->orderBy("user.primaryPublicUserId","ASC");
                },
            ));

        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'sendEmailUserAdded' ) {
            $builder->add('sendEmailUserAdded', null, array(
                'label' => 'Send email notifications to platform administrators when new user records are created: [Yes/No]:',
                'attr' => array('class' => 'form-control')
            ));
        }

        //////// fields for Server Instance connection ////////
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'authUserGroup' ) {
            $builder->add('authUserGroup', EntityType::class, array(
                'class' => 'App\UserdirectoryBundle\Entity\AuthUserGroupList',
                'label' => 'User Group:',
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'authServerNetwork' ) {
            $builder->add('authServerNetwork', EntityType::class, array(
                'class' => 'App\UserdirectoryBundle\Entity\AuthServerNetworkList',
                'label' => 'Server Network Accessibility and Role:',
                'required' => false, //true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'authPartnerServer' ) {
            $builder->add('authPartnerServer', EntityType::class, array(
                'class' => 'App\UserdirectoryBundle\Entity\AuthPartnerServerList',
                'label' => 'Tandem Partner Server URL:',
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }
        //////// EOF fields for Server Instance connection ////////

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'secretKey' ) {
            $builder->add('secretKey', null, array(
                'label' => 'Secret Key:',
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'instanceId' ) {
            $builder->add('instanceId', null, array(
                'label' => 'Instance ID [6 letters]:',
                'attr' => array('class' => 'form-control')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'showTenantsHomepage' ) {
            $builder->add('showTenantsHomepage', null, array(
                'label' => 'Show the section with the list of tenants on the homepage (for a primary tenant) [Yes/No]:',
                'attr' => array('class' => 'form-control')
            ));
        }

//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'hostedUserGroups' ) {
//            $builder->add('hostedUserGroups', EntityType::class, array(
//                'class' => 'App\UserdirectoryBundle\Entity\HostedUserGroupList',
//                'label' => 'Hosted User Groups:',
//                'required' => true,
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width'),
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('list')
//                        ->where("list.type = :typedef OR list.type = :typeadd")
//                        ->orderBy("list.orderinlist", "ASC")
//                        ->setParameters(array(
//                            'typedef' => 'default',
//                            'typeadd' => 'user-added',
//                        ));
//                },
//            ));
//        }
        
//        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'tenantPrefixUrlSlug' ) {
//            $builder->add('tenantPrefixUrlSlug', null, array(
//                'label' => 'Tenant prefix URL Slug:',
//                'attr' => array('class' => 'form-control')
//            ));
//        }
        
    }


    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\SiteParameters',
            'form_custom_value' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_siteparameters';
    }


    //Co-Path DB
    public function addLis($builder) {

        //Production
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBServerAddress' )
            $builder->add('lisDBServerAddress',null,array(
                'label'=>'LIS DB Server Address:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBServerPort' )
            $builder->add('lisDBServerPort',null,array(
                'label'=>'LIS DB Server Port:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBAccountUserName' )
            $builder->add('lisDBAccountUserName',null,array(
                'label'=>'LIS DB Server Account User Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBAccountPassword' )
            $builder->add('lisDBAccountPassword',null,array(
                'label'=>'LIS DB Server Account Password:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBName' )
            $builder->add('lisDBName',null,array(
                'label'=>'LIS Database Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISName' )
            $builder->add('LISName',null,array(
                'label'=>'LIS Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISVersion' )
            $builder->add('LISVersion',null,array(
                'label'=>'LIS Version:',
                'attr' => array('class'=>'form-control')
            ));


        //Test
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBServerAddressTest' )
            $builder->add('lisDBServerAddressTest',null,array(
                'label'=>'Test LIS DB Server Address:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBServerPortTest' )
            $builder->add('lisDBServerPortTest',null,array(
                'label'=>'Test LIS DB Server Port:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBAccountUserNameTest' )
            $builder->add('lisDBAccountUserNameTest',null,array(
                'label'=>'Test LIS DB Server Account User Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBAccountPasswordTest' )
            $builder->add('lisDBAccountPasswordTest',null,array(
                'label'=>'Test LIS DB Server Account Password:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBNameTest' )
            $builder->add('lisDBNameTest',null,array(
                'label'=>'Test LIS Database Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISNameTest' )
            $builder->add('LISNameTest',null,array(
                'label'=>'Test LIS Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISVersionTest' )
            $builder->add('LISVersionTest',null,array(
                'label'=>'Test LIS Version:',
                'attr' => array('class'=>'form-control')
            ));


        //Development
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBServerAddressDevelopment' )
            $builder->add('lisDBServerAddressDevelopment',null,array(
                'label'=>'Development LIS DB Server Address:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBServerPortDevelopment' )
            $builder->add('lisDBServerPortDevelopment',null,array(
                'label'=>'Development LIS DB Server Port:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBAccountUserNameDevelopment' )
            $builder->add('lisDBAccountUserNameDevelopment',null,array(
                'label'=>'Development LIS DB Server Account User Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBAccountPasswordDevelopment' )
            $builder->add('lisDBAccountPasswordDevelopment',null,array(
                'label'=>'Development LIS DB Server Account Password:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'lisDBNameDevelopment' )
            $builder->add('lisDBNameDevelopment',null,array(
                'label'=>'Development LIS Database Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISNameDevelopment' )
            $builder->add('LISNameDevelopment',null,array(
                'label'=>'Development LIS Name:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'LISVersionDevelopment' )
            $builder->add('LISVersionDevelopment',null,array(
                'label'=>'Development LIS Version:',
                'attr' => array('class'=>'form-control')
            ));


        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'liveSiteRootUrl' )
            $builder->add('liveSiteRootUrl',null,array(
                'label'=>'Live Site Root URL (http://c.med.cornell.edu/):',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'initialConfigurationCompleted' )
            $builder->add('initialConfigurationCompleted',null,array(
                'label'=>'Initial Configuration Completed:',
                'attr' => array('class'=>'form-control')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'networkDrivePath' )
            $builder->add('networkDrivePath',null,array(
                'label'=>'Network drive absolute path to store DB backup files (my/backup/path/):',
                'attr' => array('class'=>'form-control')
            ));

    }


    public function addThirdPartySoftware( $builder ) {
        //3 libreOffice
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFPathFellApp' ) {
            //echo "edit libreOfficeConvertToPDFPathFellApp <br>";
            $builder->add('libreOfficeConvertToPDFPathFellApp', null, array(
                'label' => 'Path to LibreOffice for converting a file to pdf (C:\Program Files (x86)\LibreOffice 5\program):',
                'attr' => array('class' => 'form-control form-control-modif', 'style' => 'margin:0')
            ));
        }
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFPathFellAppLinux' ) {
            $builder->add('libreOfficeConvertToPDFPathFellAppLinux', null, array(
                'label' => 'Path to LibreOffice for converting a file to pdf (path\LibreOffice 5\program) - Linux:',
                'attr' => array('class' => 'form-control form-control-modif', 'style' => 'margin:0')
            ));
        }

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFFilenameFellApp' )
            $builder->add('libreOfficeConvertToPDFFilenameFellApp',null,array(
                'label'=>'LibreOffice executable file (soffice):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFFilenameFellAppLinux' )
            $builder->add('libreOfficeConvertToPDFFilenameFellAppLinux',null,array(
                'label'=>'LibreOffice executable file (soffice) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFArgumentsdFellApp' )
            $builder->add('libreOfficeConvertToPDFArgumentsdFellApp',null,array(
                'label'=>'LibreOffice arguments (--headless -convert-to pdf -outdir):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'libreOfficeConvertToPDFArgumentsdFellAppLinux' )
            $builder->add('libreOfficeConvertToPDFArgumentsdFellAppLinux',null,array(
                'label'=>'LibreOffice arguments (--headless -convert-to pdf -outdir) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        //3 pdftk
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkPathFellApp' )
            $builder->add('pdftkPathFellApp',null,array(
                'label'=>'Path to pdftk for PDF concatenation (E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkPathFellAppLinux' )
            $builder->add('pdftkPathFellAppLinux',null,array(
                'label'=>'Path to pdftk for PDF concatenation (path\order\scanorder\Scanorders2\vendor\olegutil\PDFTKBuilderPortable\App\pdftkbuilder) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkFilenameFellApp' )
            $builder->add('pdftkFilenameFellApp',null,array(
                'label'=>'pdftk executable file (pdftk):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkFilenameFellAppLinux' )
            $builder->add('pdftkFilenameFellAppLinux',null,array(
                'label'=>'pdftk executable file (pdftk) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkArgumentsFellApp' )
            $builder->add('pdftkArgumentsFellApp',null,array(
                'label'=>'pdftk arguments (###inputFiles### cat output ###outputFile### dont_ask):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'pdftkArgumentsFellAppLinux' )
            $builder->add('pdftkArgumentsFellAppLinux',null,array(
                'label'=>'pdftk arguments (###inputFiles### cat output ###outputFile### dont_ask) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        //3 gs
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsPathFellApp' )
            $builder->add('gsPathFellApp',null,array(
                'label'=>'Path to Ghostscript for stripping PDF password protection (E:\Program Files (x86)\pacsvendor\pacsname\htdocs\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsPathFellAppLinux' )
            $builder->add('gsPathFellAppLinux',null,array(
                'label'=>'Path to Ghostscript for stripping PDF password protection (path\order\scanorder\Scanorders2\vendor\olegutil\Ghostscript\bin) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsFilenameFellApp' )
            $builder->add('gsFilenameFellApp',null,array(
                'label'=>'Ghostscript executable file (gswin64c.exe):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsFilenameFellAppLinux' )
            $builder->add('gsFilenameFellAppLinux',null,array(
                'label'=>'Ghostscript executable file (gswin64c) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsArgumentsFellApp' )
            $builder->add('gsArgumentsFellApp',null,array(
                'label'=>'Ghostscript arguments (-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=###outputFile###  -c .setpdfwrite -f ###inputFiles###):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'gsArgumentsFellAppLinux' )
            $builder->add('gsArgumentsFellAppLinux',null,array(
                'label'=>'Ghostscript arguments (-q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=###outputFile###  -c .setpdfwrite -f ###inputFiles###) - Linux:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'wkhtmltopdfpath' )
            $builder->add('wkhtmltopdfpath',null,array(
                'label'=>'Path to wkhtmltopdf.exe (with double quotation marks for path with space i.e. "C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe", update system cache is required):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'wkhtmltopdfpathLinux' )
            $builder->add('wkhtmltopdfpathLinux',null,array(
                'label'=>'Path to wkhtmltopdf binary - Linux (update system cache is required):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));


        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'phantomjs' )
            $builder->add('phantomjs',null,array(
                'label'=>'Path to phantomjs.exe (i.e. "C:\Program Files\phantomjs\bin\phantomjs.exe):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'phantomjsLinux' )
            $builder->add('phantomjsLinux',null,array(
                'label'=>'Path to phantomjs binary:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));

        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'rasterize' )
            $builder->add('rasterize',null,array(
                'label'=>'Path to rasterize.js (i.e. C:\Program Files\phantomjs\example\rasterize.js):',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
        if( $this->params['cycle'] == 'show' || $this->params['param'] == 'rasterizeLinux' )
            $builder->add('rasterizeLinux',null,array(
                'label'=>'Path to rasterize.js:',
                'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
            ));
    }

}
