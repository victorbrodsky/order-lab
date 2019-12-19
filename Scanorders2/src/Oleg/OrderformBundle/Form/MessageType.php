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

namespace Oleg\OrderformBundle\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use Oleg\UserdirectoryBundle\Form\DataTransformer\UserWrapperTransformer;
use Oleg\UserdirectoryBundle\Form\InstitutionalWrapperType;
use Oleg\UserdirectoryBundle\Form\InstitutionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

//use Oleg\OrderformBundle\Helper\FormHelper;


//This form type is used strictly only for scan order: message (message) form has scan order
//This form includes patient hierarchy form.
//Originally it was made the way that message has scanorder.
//All other order's form should have aggregated message type form: order form has message form.
class MessageType extends AbstractType
{

    protected $entity;
    protected $params;
    
//    public function __construct( $type = null, $service = null, $entity = null )
    //params: type: single or clinical, educational, research
    //params: cycle: new, edit, show
    //params: service: pathology service
    //params: entity: entity itself
    public function formConstructor( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;

        if( !array_key_exists('type', $this->params) ) {
            $this->params['type'] = 'Unknown Order';
        }

        if( !array_key_exists('message.proxyuser.label', $this->params) ) {
            $this->params['message.proxyuser.label'] = 'Ordering Provider(s):';
        }

    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

//        echo "message params=";
        //echo "type=".$this->params['type']."<br>";
        //echo "cycle=".$this->params['cycle']."<br>";
//        echo "<br>";

        //$helper = new FormHelper();

        $builder->add( 'oid' , HiddenType::class, array('attr'=>array('class'=>'message-id')) );

        //unmapped data quality form to record the MRN-Accession conflicts
        $builder->add('conflicts', CollectionType::class, array(
            'entry_type' => DataQualityMrnAccType::class,
            'mapped' => false,
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__dataqualitymrnacc__',
        ));

        //add children
        if( $this->params['type'] != 'Table-View Scan Order' ) {

            //echo "message type: show patient <br>";
            $builder->add('patient', CollectionType::class, array(
                'entry_type' => PatientType::class,
                'entry_options' => array(
                    'form_custom_value' => $this->params,
                    'form_custom_value_entity' => $this->entity
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__patient__',
            ));

        } else {

            //echo "message type: show datalocker <br>";

            $builder->add('datalocker',HiddenType::class, array(
                "mapped" => false
            ));

            $builder->add('clickedbtn',HiddenType::class, array(
                "mapped" => false
            ));

        }

        //echo "<br>type=".$this->type."<br>";

        $builder->add('educational',EducationalType::class,array(
            'form_custom_value' => $this->params,
            'label'=>'Educational:'
        ));

        $builder->add('research',ResearchType::class,array(
            'form_custom_value' => $this->params,
            'label'=>'Research:'
        ));

        //priority
        $priorityArr = array(
            'label' => 'Priority:',
            'choices' => array( 'Routine'=>'Routine', 'Stat'=>'Stat' ), //$helper->getPriority(),
            //'choices_as_values' => true,
            'required' => true,
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type', 'required'=>'required')
        );
        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create' ) {
            $priorityArr['data'] = 'Routine';    //new
        }
        $builder->add( 'priority', ChoiceType::class, $priorityArr); //flipped

//        //delivery
//        $attr = array('class' => 'ajax-combobox-delivery', 'type' => 'hidden');
//        $builder->add('delivery', 'custom_selector', array(
//            'label' => 'Slide Delivery:',
//            'attr' => $attr,
//            'required'=>true,
//            'classtype' => 'delivery'
//        ));

        //deadline
        if( $this->params['cycle'] == 'new' ) {
            $deadline = date_modify(new \DateTime(), '+2 week');
        } else {
            $deadline = null;
        }

        if( $this->entity && $this->entity->getDeadline() != '' ) {
            $deadline = $this->entity->getDeadline();
        }

        $builder->add('deadline', DateType::class,array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'data' => $deadline,
            'label' => 'Scan Deadline:',
        ));

        $builder->add('returnoption', CheckboxType::class, array(
            'label'     => 'Return slide(s) by this date even if not scanned:',
            'required'  => false,
        ));

        if( array_key_exists('message.provider', $this->params) &&  $this->params['message.provider'] == true ) {
            $builder->add('provider', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => 'Submitter:',
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->leftJoin("u.infos", "infos")
                        ->leftJoin("u.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("(employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL)")
                        ->andWhere("(u.testingAccount = false OR u.testingAccount IS NULL)")
                        ->andWhere("(u.keytype IS NOT NULL AND u.primaryPublicUserId != 'system')")
                        ->orderBy("infos.displayName","ASC");
//                        return $er->createQueryBuilder('u')
//                            ->where('u.roles LIKE :roles OR u=:user')
//                            ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
                    },
            ));
        }


//        $builder->add('proxyuser', EntityType::class, array(
//            'class' => 'OlegUserdirectoryBundle:User',
//            'label'=>'Ordering Provider:',
//            'required' => false,
//            //'multiple' => true,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('u')
//                    ->where('u.roles LIKE :roles OR u=:user')
//                    ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
//            },
//        ));

//        $builder->add('proxyuser', 'custom_selector', array(
//            'label' => 'Ordering Provider:',
//            'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
//            'required' => false,
//            'classtype' => 'userWrapper'
//        ));

        //$transformer = new UserWrapperTransformer($this->params['em'], $this->params['serviceContainer']);
//        $builder->add(
//            $builder->create('proxyuser', null, array(
//                'attr' => array('class'=>'combobox combobox-width'),
//                'multiple' => false,
//                'label' => 'Ordering Provider(s):',
//            ))
//                ->addModelTransformer($transformer)
//        );
//        $builder->add(
//            $builder->create('proxyuser', EntityType::class, array(
//                'class' => 'OlegUserdirectoryBundle:UserWrapper',
//                //'choices' => array(1,2,3),
//                'multiple' => true,
//                'expanded' => true,
//                'label' => 'Ordering Provider(s):',
//                'attr' => array('class' => 'combobox combobox-width'),
//                //'classtype' => 'userWrapper'
//            ))
//                ->addModelTransformer($transformer)
//        );

        if( $this->params['cycle'] == 'show' ) {

            //$builder->add( 'proxyuser', null);

            $builder->add( 'proxyuser', EntityType::class, array(
                'class' => 'OlegUserdirectoryBundle:UserWrapper',
                //'choice_label' => 'getEntity',
                'label' => $this->params['message.proxyuser.label'],
                'required' => false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width')
            ));

        } else {

            $builder->add('proxyuser', ScanCustomSelectorType::class, array(
                'label' => $this->params['message.proxyuser.label'],
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                'required' => false,
                //'multiple' => true,
                'classtype' => 'userWrapper'
            ));

        }


        $builder->add( 'equipment', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:Equipment',
            'choice_label' => 'name',
            'label' => 'Scanner:',
            'required'=> true,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                ));
            },
        ));

        $builder->add( 'purpose', ChoiceType::class, array( //flipped
            'label' => 'Purpose:',
            'required' => true,
            'choices' => array(
                "For Internal Use by the Department of Pathology"=>"For Internal Use by the Department of Pathology",
                "For External Use (Invoice Fund Number)"=>"For External Use (Invoice Fund Number)"
            ),
            //'choices_as_values' => true,
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type')
        ));

        $attr = array('class' => 'ajax-combobox-account', 'type' => 'hidden');
        $builder->add('account', ScanCustomSelectorType::class, array(
            'label' => 'Debit Fund WBS Account Number:',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'account'
        ));



        //Endpoint object: destination - location
        //$this->params['label'] = 'Return Slides to:';
        $this->params['endpoint.system'] = false;
        $this->params['endpoint.location.label'] = 'Return Slides to:';
        $builder->add('destinations', CollectionType::class, array(
            'entry_type' => EndpointType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__destinations__',
        ));

        //Institutional PHI Scope
        if( array_key_exists('institutions', $this->params) ) {
            $institutions = $this->params['institutions'];
        } else {
            $institutions = null;
        }
        //foreach( $institutions as $inst ) {
        //    echo "form inst=".$inst."<br>";
        //}
        $builder->add('institution', EntityType::class, array(
            'label' => 'Order data visible to members of (Institutional PHI Scope):',
            'choice_label' => 'getNodeNameWithRoot',
            'required' => true,
            'multiple' => false,
            //'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $institutions,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));

        //Performing organization
        $builder->add('organizationRecipients', CollectionType::class, array(
            'entry_type' => InstitutionalWrapperType::class,
            'entry_options' => array(
                'form_custom_value' => $this->params
            ),
            'label' => "Organization Recipient",
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__organizationRecipients__',
        ));


        ////////////////////////// Specific Orders //////////////////////////

        //Exception for scan order form, to avoid complications of changing html twig view
        if( $this->hasSpecificOrders($this->entity,'Scan Order') ) {
            $builder->add('scanorder', ScanOrderType::class, array(
                'data_class' => 'Oleg\OrderformBundle\Entity\ScanOrder',
                'form_custom_value' => $this->params,
                'label' => false
            ));
        }

//        $builder->add('laborder', new LabOrderType($this->params), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\LabOrder',
//            'label' => false
//        ));
//
//        $builder->add('slideReturnRequest', new SlideReturnRequestType($this->params), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\SlideReturnRequest',
//            'label' => false
//        ));

        ////////////////////////// EOF Specific Orders //////////////////////////
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Message',
            'form_custom_value' => null,
            'form_custom_value_entity' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_messagetype';
    }

    //return true if substring is found: 'Scan Order', 'Lab Order' ...
    public function hasSpecificOrders( $message, $substring ) {
        if( !$message ) {
            return false;
        }

        $category = $message->getMessageCategory();
        //echo "category=".$category."<br>";
        if( strpos($category,$substring) !== false ) {
            //echo "has <br>";
            return true;
        }
        //echo "no has <br>";
        return false;
    }

}
