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



use App\UserdirectoryBundle\Entity\SourceSystemList; //process.py script: replaced namespace by ::class: added use line for classname=SourceSystemList


use App\UserdirectoryBundle\Entity\Institution; //process.py script: replaced namespace by ::class: added use line for classname=Institution
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class UserRequestType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add( 'siteName', HiddenType::class, array(
            'label'=> false,    //'Site Name:',
            //'disabled' => true,
            'required'=> true,
            'attr' => array('class'=>'form-control', 'readonly'=>true),
        ));

        $builder->add( 'cwid', TextType::class, array(
                'label' => false,  
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif cwid'),
        ));

        //hascwid
        $builder->add( 'hascwid', ChoiceType::class, array( //flipped
            'label' => false, //'Do you (the person for whom the account is being requested) have a CWID username?',
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            //'choices_as_values' => true,
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type hascwid')
        ));

        //password RepeatedType::class
        $builder->add( 'password', PasswordType::class, array(
            'mapped' => false,
            'label'=>'Password:',
            'attr' => array('class' => 'form-control form-control-modif cwid-password')
        ));

        $builder->add( 'firstName', TextType::class, array(
            'label'=>'First Name:',
            'required'=> true,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'name', TextType::class, array(
                'label'=>'Last Name:',
                'required'=> true,
                'attr' => array('class'=>'form-control form-control-modif'),
        ));
        
        $builder->add( 'email', EmailType::class, array(
                'label'=>'Email:',
                'required'=> true, //does not work here
                'attr' => array('class'=>'form-control form-control-modif email-mask', 'required'=>'required'), //'required'=>'required' does not work here
        ));
        
        $builder->add( 'phone', TextType::class, array(
                'label'=>'Phone Number:',
                'required'=> false,
                'attr' => array('class'=>'form-control form-control-modif phone-mask'),
        ));

        $requireMobilePhone = false;
        if( $this->params['requireVerifyMobilePhone'] ) {
            $requireMobilePhone = true;
        }
        $builder->add( 'mobilePhone', TextType::class, array(
            'label'=>'Primary Mobile Phone Number (E. 164 format: +11234567890):',
            'required'=> $requireMobilePhone,
            'attr' => array('class'=>'form-control form-control-modif phone-mask'),
        ));
        
        $builder->add( 'job', TextType::class, array(
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
        $builder->add('requestedScanOrderInstitutionScope', EntityType::class, array(
            'label' => 'Organizational Group:',
            'required' => true, //false,
            'multiple' => false,
            //'empty_value' => false,
            'choice_label' => 'getNodeNameWithRoot',
            'class' => Institution::class,
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
                    $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels($institution) . ":";
                }
            }
            if( !$label ) {
                $label = $this->params['em']->getRepository(Institution::class)->getLevelLabels(null) . ":";
            }

            $form->add('requestedScanOrderInstitutionScope', CustomSelectorType::class, array(
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


        
        $builder->add('request', TextareaType::class, array(
            'label'=>'Reason for account request:',
            'required'=> false,
            'attr' => array('class'=>'textarea form-control form-control-modif'),
        ));


        //$refLabel = "For reference, please provide the name and contact information of your supervisor or of the person who can confirm the validity of your request below.\r\nAccess permissions similar to (user name):";
        $builder->add( 'similaruser', TextType::class, array(
            'label' => "Access permissions similar to (user name):",
            'required' => false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referencename', TextType::class, array(
            'label'=>'Reference Name:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referenceemail', TextType::class, array(
            'label'=>'Reference Email:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        $builder->add( 'referencephone', TextType::class, array(
            'label'=>'Reference Phone Number:',
            'required'=> false,
            'attr' => array('class'=>'form-control form-control-modif'),
        ));

        if(0) {
            $builder->add('systemAccountRequest', EntityType::class, array(
                'label' => 'System for which the account is being requested:',
                'required' => true,
                //'multiple' => true,
                //'empty_value' => false,
                'class' => SourceSystemList::class,
                'attr' => array('class' => 'combobox combobox-width')
            ));
        } else {
            //TODO: set the value of the “System for which the account is being requested:” field to the value that corresponds to the URL
            $fullDomain = trim($this->params['request']->getPathInfo());
            echo '$fullDomain='.$fullDomain.'<br>';
            $systemAccountRequestName = 'ORDER Employee Directory';
            $systemAccountRequest = $this->params['em']->getRepository(SourceSystemList::class)->findOneByName($systemAccountRequestName);
            //dump($systemAccountRequest);
            //exit('111');

            $builder->add('systemAccountRequest', EntityType::class, array(
                'class' => SourceSystemList::class,
                //'choice_label' => 'name',
                'label' => 'System for which the account is being requested:',
                'required' => false,
                'data' => $systemAccountRequest,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->andWhere("list.name LIKE '%ORDER%' OR list.name LIKE '%External Authentication%'")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\UserRequest',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_userrequesttype';
    }
}
