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



use App\UserdirectoryBundle\Entity\User;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class InitialConfigurationType extends AbstractType
{

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('save', SubmitType::class, array(
            'label' => 'Save',
            'attr' => array('class'=>'btn btn-primary')
        ));

        $builder->add('environment',ChoiceType::class,array( //flipped
            'label'=>'Environment:',
            'choices' => array('live'=>'live', 'test'=>'test', 'dev'=>'dev', 'demo'=>'demo'),
            //'choices_as_values' => true,
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('connectionChannel',ChoiceType::class,array( //flipped
            'label'=>'Routing Connection Channel (http or https; Clearing Cache is required):',
            'choices' => array("http"=>"http", "https"=>"https"),
            //'choices_as_values' => true,
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('urlConnectionChannel',ChoiceType::class,array( //flipped
            'label'=>'Url Connection Channel (http or https; Clearing Cache is required):',
            'choices' => array("http"=>"http", "https"=>"https"),
            //'choices_as_values' => true,
            'attr' => array('class'=>'form-control')
        ));

        //Name of Parent Organization (if applicable): [ ]
        $builder->add('institutionurl',null,array(
            'label'=>'Institution URL (Copyright Link in Footer):',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('institutionname',null,array(
            'label'=>'Institution Name (Copyright Link in Footer):',
            'attr' => array('class'=>'form-control')
        ));

        //Name of Institution: [ ]
        $builder->add('subinstitutionurl',null,array(
            'label'=>'Institution URL (Instance Owner Link in Footer):',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('subinstitutionname',null,array(
            'label'=>'Institution Name (Instance Owner in Footer):',
            'attr' => array('class'=>'form-control')
        ));

        //Name of Department or Group: [ ]
        $builder->add('departmenturl',null,array(
            'label'=>'Department URL:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('departmentname',null,array(
            'label'=>'Department or Group Name:',
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('showCopyrightOnFooter',null,array(
            'label'=>'Show copyright line on every footer:',
            'attr' => array('class'=>'form-control')
        ));

        //New password for the Administrator account: [ ]
        $builder->add('password', RepeatedType::class, array(
            'invalid_message' => 'Please make sure the passwords match',
            'options' => array('attr' => array('class' => 'password-field form-control')),
            'required' => true,
            'mapped' => false,
            'type' => PasswordType::class,
            'first_options'  => array('label' => 'New password for the Administrator account:'),
            'second_options' => array('label' => 'Repeat password:'),
        ));

        //E-Mail address for the Administrator account: [ ]
        $builder->add('siteEmail',EmailType::class,array(
            'label'=>'E-Mail address for the Administrator account:',
            'attr' => array('class'=>'form-control user-email')
        ));

        //Live Site Root URL (such as "http://my.server.com/order"): [ ]
        $builder->add('liveSiteRootUrl',null,array(
            'label'=>'Live Site Root URL (such as "http://my.server.com/order"):',
            'attr' => array('class'=>'form-control')
        ));

        //email
        $builder->add('smtpServerAddress', null, array(
            'label' => 'SMTP Server Address:',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerPort', null, array(
            'label' => 'Mailer Port (i.e. 25, 465, 587):',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerAuthMode', null, array(
            'label' => 'Mailer Authentication Mode (i.e. oauth or login):',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerUseSecureConnection', null, array(
            'label' => 'Mailer Use Security Connection (i.e. tls or ssl):',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerUser', null, array(
            'label' => 'Mailer Username:',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerPassword', null, array(
            'label' => 'Mailer Password:',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerSpool', null, array(
            'label' => 'Use email spooling (Instead of sending every email directly to the SMTP server individually, add outgoing emails to a queue and then periodically send the queued emails. This makes form submission appear faster.):',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerDeliveryAddresses', null, array(
            'label' => 'Reroute all outgoing emails only to the following email address(es) listed in the "email1@example.com,email2@example.com,email3@example.com" format separated by commas. (This is useful for a non-live server environment to avoid sending emails to users. Leaving this field empty will result in emails being sent normally.):',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('mailerFlushQueueFrequency', null, array(
            'label' => 'Frequency of sending emails in the queue (in minutes between eruptions):',
            'attr' => array('class' => 'form-control')
        ));

        //ldap
        $builder->add('aDLDAPServerAddress',null,array(
            'label'=>'AD/LDAP Server Address:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('aDLDAPServerPort',null,array(
            'label'=>'AD/LDAP Server Port:',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('aDLDAPServerOu',null,array(
            'label'=>"AD/LDAP Bind DN for ldap search or simple authentication. Use ';' for multiple binds (cn=read-only-admin,dc=example,dc=com;ou=group1,dc=example,dc=com):",
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('aDLDAPServerAccountUserName',null,array(
            'label'=>'AD/LDAP Server Account User Name (for ldap search):',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('aDLDAPServerAccountPassword',null,array(
            'label'=>'AD/LDAP Server Account Password (for ldap search):',
            //'always_empty' => $always_empty,
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('ldapExePath',null,array(
            'label'=>'LDAP/AD Authenticator Path - relevant for Windows-based servers only (Default: "../src/App/UserdirectoryBundle/Util/" ):',
            'attr' => array('class'=>'form-control')
        ));
        $builder->add('ldapExeFilename',null,array(
            'label'=>'LDAP/AD Authenticator File Name - relevant for Windows-based servers only (Default: "LdapSaslCustom.exe"):',
            'attr' => array('class'=>'form-control')
        ));

        //////////////// Notices for LDAP and CWID ///////////////////
        $builder->add('noticeAttemptingPasswordResetLDAP',null,array(
            'label'=>'Notice for attempting to reset password for an LDAP-authenticated account:',
            'attr' => array('class'=>'form-control textarea')
        ));

        $builder->add('loginInstruction',null,array(
            'label'=>'Notice to prompt user to use Active Directory account to log in (Please use your CWID to log in):',
            'attr' => array('class'=>'form-control textarea')
        ));

        $builder->add('noticeSignUpNoCwid',null,array(
            'label'=>'Notice to prompt user with no Active Directory account to sign up for a new account:',
            'attr' => array('class'=>'form-control textarea')
        ));

        $builder->add('noticeHasLdapAccount',null,array(
            'label'=>'Account request question asking whether applicant has an Active Directory account:',
            'attr' => array('class'=>'form-control textarea')
        ));

        $builder->add('noticeLdapName',null,array(
            'label'=>'Full local name for active directory account:',
            'attr' => array('class'=>'form-control textarea')
        ));
        //////////////// EOF Notices for LDAP and CWID ///////////////////

        $builder->add('defaultPrimaryPublicUserIdType',null,array(
            'label'=>'Default Primary Public User ID Type:',
            'attr' => array('class'=>'combobox')
        ));
//        $builder->add('defaultPrimaryPublicUserIdType', CustomSelectorType::class, array(
//            'label'=>'Default Primary Public User ID Type:',
//            'attr' => array('class' => 'ajax-combobox-usernametype', 'type' => 'hidden'),
//            'required' => false,
//            'classtype' => 'usernametype'
//        ));

//        $builder->add('holidaysUrl',null,array(
//            'label'=>'Link to list of holidays (http://intranet.med.cornell.edu/hr/):',
//            'attr' => array('class'=>'form-control')
//        ));

        //Add
        //“Server Role and Network Access”  - authServerNetwork
        $builder->add('authServerNetwork', EntityType::class, array(
            'class' => 'App\UserdirectoryBundle\Entity\AuthServerNetworkList',
            'label' => 'Server Network Accessibility and Role:',
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
        //“User Group”                      - authUserGroup
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

        //“Hosted User Groups”              - hostedUserGroups
        //“Tandem Partner Server URL“       - REMOVED



    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\SiteParameters',
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_initialconfigurationtype';
    }
}
