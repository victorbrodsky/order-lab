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

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;
//use Doctrine\ORM\EntityRepository;
//use Symfony\Component\Form\FormEvents;
//use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AccessRequestType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'firstName', TextType::class, array(
            'label'=>'First Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'lastName', TextType::class, array(
            'label'=>'Last Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));
        
        $builder->add( 'email', EmailType::class, array(
                'label'=>'Email:',
                'required'=> true, //does not work here
                'attr' => array('class'=>'form-control email-mask', 'required'=>'required'), //'required'=>'required' does not work here
        ));
        
        $builder->add( 'phone', TextType::class, array(
                'label'=>'Phone Number:',
                'required'=> false,
                'attr' => array('class'=>'form-control phone-mask'),
        ));

        $requireMobilePhone = false;
        if( $this->params['requireVerifyMobilePhone'] ) {
            $requireMobilePhone = true;
        }
        $builder->add( 'mobilePhone', TextType::class, array(
            'label'=>'Primary Mobile Phone Number (E. 164 format: +11234567890):',
            'required'=> $requireMobilePhone,
            //'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif phone-mask'),
        ));
        
        $builder->add( 'job', TextType::class, array(
                'label'=>'Job title:',
                'required'=> false,
                'attr' => array('class'=>'form-control'),
        ));


        if (array_key_exists('requestedScanOrderInstitutionScope', $this->params)) {
            $requestedScanOrderInstitutionScope = $this->params['requestedScanOrderInstitutionScope'];
        } else {
            $requestedScanOrderInstitutionScope = null;
        }
        $builder->add('organizationalGroup', EntityType::class, array(
            'label' => 'Organizational Group:',
            'required' => false,
            'multiple' => false,
            'choice_label' => 'getNodeNameWithRoot',
            'class' => 'AppUserdirectoryBundle:Institution',
            'choices' => $requestedScanOrderInstitutionScope,
            'invalid_message' => 'organizationalGroup invalid value',
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));


//        //requestedScanOrderInstitutionScope
//    if(1) {
//        if (array_key_exists('requestedScanOrderInstitutionScope', $this->params)) {
//            $requestedScanOrderInstitutionScope = $this->params['requestedScanOrderInstitutionScope'];
//        } else {
//            $requestedScanOrderInstitutionScope = null;
//        }
//        //echo "choices=".count($requestedScanOrderInstitutionScope)."<br>";
//        $builder->add('organizationalGroup', 'entity', array(
//            'label' => '* Organizational Group:',
//            'required' => false,
//            'multiple' => false,
//            //'empty_value' => false,
//            'choice_label' => 'getNodeNameWithRoot',
//            'class' => 'AppUserdirectoryBundle:Institution',
//            'choices' => $requestedScanOrderInstitutionScope,
//            'attr' => array('class' => 'combobox combobox-width combobox-institution')
//        ));
//    } else {
//        ///////////////////////// tree node /////////////////////////
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $title = $event->getData();
//            $form = $event->getForm();
//
//            $label = null;
//            if( $title ) {
//                $institution = $title->getOrganizationalGroup();
//                if( $institution ) {
//                    $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
//                }
//            }
//            if( !$label ) {
//                $label = $this->params['em']->getRepository('AppUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
//            }
//
//            $form->add('organizationalGroup', 'employees_custom_selector', array(
//                'label' => "Organizational Group (".$label."):",
//                'required' => true,
//                'attr' => array(
//                    'class' => 'ajax-combobox-compositetree',
//                    'type' => 'hidden',
//                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
//                    'data-compositetree-classname' => 'Institution'
//                ),
//                'classtype' => 'institution'
//            ));
//        });
//        ///////////////////////// EOF tree node /////////////////////////
//    }


        
        $builder->add('reason', TextareaType::class, array(
            'label'=>'Reason for access request:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control'),
        ));


        //$refLabel = "For reference, please provide the name and contact information of your supervisor or of the person who can confirm the validity of your request below.\r\nAccess permissions similar to (user name):";
        $builder->add( 'similaruser', TextType::class, array(
            'label' => "Access permissions similar to (user name):",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'referencename', null, array(
            'label'=>'Reference Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'referenceemail', null, array(
            'label'=>'Reference Email:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'referencephone', null, array(
            'label'=>'Reference Phone Number:',
            'required'=> false,
            'attr' => array('class'=>'form-control'),
        ));

    }

    //public function configureOptions(OptionsResolver $resolver)
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\AccessRequest',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_accessrequesttype';
    }
}
