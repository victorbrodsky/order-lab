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

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class UserRequestType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
        //$helper = new FormHelper();
        
        $builder->add( 'cwid', 'text', array(
                'label'=>'WCMC CWID:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif cwid'),
        ));

        //hascwid
        $builder->add( 'hascwid', 'choice', array(
            'label' => 'Do you (the person for whom the account is being requested) have a CWID username?',
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type hascwid')
        ));

        //password
        $builder->add( 'password', 'password', array(
            'mapped' => false,
            'label'=>'Password:',
            'attr' => array('class' => 'form-control form-control-modif cwid-password')
        ));

        $builder->add( 'firstName', 'text', array(
            'label'=>'* First Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'name', 'text', array(
                'label'=>'* Last Name:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'email', 'email', array(
                'label'=>'* Email:',
                'required'=> true, //does not work here
                'attr' => array('class'=>'form-control form-control-modif email-mask', 'required'=>'required'), //'required'=>'required' does not work here
        ));
        
        $builder->add( 'phone', 'text', array(
                'label'=>'Phone Number:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif phone-mask'),
        ));
        
        $builder->add( 'job', 'text', array(
                'label'=>'Job title:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));


        //requestedScanOrderInstitutionScope
    if(1) {
        if (array_key_exists('requestedScanOrderInstitutionScope', $this->params)) {
            $requestedScanOrderInstitutionScope = $this->params['requestedScanOrderInstitutionScope'];
        } else {
            $requestedScanOrderInstitutionScope = null;
        }
        //echo "choices=".count($requestedScanOrderInstitutionScope)."<br>";
        $builder->add('requestedScanOrderInstitutionScope', 'entity', array(
            'label' => '* Organizational Group:',
            'required' => false,
            'multiple' => false,
            //'empty_value' => false,
            'property' => 'getNodeNameWithRoot',
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $requestedScanOrderInstitutionScope,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));
    } else {
        ///////////////////////// tree node /////////////////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = null;
            if( $title ) {
                $institution = $title->getRequestedScanOrderInstitutionScope();
                if( $institution ) {
                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                }
            }
            if( !$label ) {
                $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
            }

            $form->add('requestedScanOrderInstitutionScope', 'employees_custom_selector', array(
                'label' => "Organizational Group (".$label."):",
                'required' => true,
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution'
                ),
                'classtype' => 'institution'
            ));
        });
        ///////////////////////// EOF tree node /////////////////////////
    }


        
        $builder->add('request', 'textarea', array(
            'label'=>'Reason for account request:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control form-control-modif'),
        ));


        //$refLabel = "For reference, please provide the name and contact information of your supervisor or of the person who can confirm the validity of your request below.\r\nAccess permissions similar to (user name):";
        $builder->add( 'similaruser', 'text', array(
            'label' => "Access permissions similar to (user name):",
            'required' => false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referencename', 'text', array(
            'label'=>'Reference Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referenceemail', 'text', array(
            'label'=>'Reference Email:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referencephone', 'text', array(
            'label'=>'Reference Phone Number:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add('systemAccountRequest', 'entity', array(
            'label' => 'System for which the account is being requested:',
            'required'=> true,
            //'multiple' => true,
            //'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'attr' => array('class' => 'combobox combobox-width')
        ));
        $builder->add( 'systemAccountRequest', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            //'property' => 'name',
            'label' => 'System for which the account is being requested:',
            'required'=> false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->andWhere("list.name LIKE '%ORDER%' OR list.name LIKE '%Aperio eSlide Manager%'")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserRequest',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_userrequesttype';
    }
}
