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

use Oleg\OrderformBundle\Form\CustomType\ScanCustomSelectorType;
use Oleg\UserdirectoryBundle\Form\InstitutionalWrapperType;
use Oleg\UserdirectoryBundle\Form\InstitutionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Entity\Message;
use Oleg\UserdirectoryBundle\Form\AttachmentContainerType;
//use Oleg\OrderformBundle\Helper\FormHelper;

//This form includes only message object
class MessageObjectType extends AbstractType
{

    protected $entity;
    protected $params;
    protected $labels;

    public function formConstructor( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;

        if( !array_key_exists('type', $this->params) ) {
            $this->params['type'] = 'Unknown Order';
        }

        //show by default
        if( !array_key_exists('message.provider', $this->params) ) {
            $this->params['message.provider'] = true;
        }
        if( !array_key_exists('message.proxyuser', $this->params) ) {
            $this->params['message.proxyuser'] = true;
        }
        if( !array_key_exists('message.orderRecipients', $this->params) ) {
            $this->params['message.orderRecipients'] = true;
        }
        if( !array_key_exists('message.reportRecipients', $this->params) ) {
            $this->params['message.reportRecipients'] = true;
        }
        if( !array_key_exists('message.organizationRecipients', $this->params) ) {
            $this->params['message.organizationRecipients'] = true;
        }
        if( !array_key_exists('message.sources', $this->params) ) {
            $this->params['message.sources'] = true;
        }
        if( !array_key_exists('message.destinations', $this->params) ) {
            $this->params['message.destinations'] = true;
        }
        if( !array_key_exists('message.externalIds', $this->params) ) {
            $this->params['message.externalIds'] = true;
        }

        if( !array_key_exists('message.associations', $this->params) ) {
            $this->params['message.associations'] = true;
        }
        if( !array_key_exists('message.backAssociations', $this->params) ) {
            $this->params['message.backAssociations'] = true;
        }

        //default labels
        $labels = array(
            'educational' => 'Educational:',
            'research' => 'Research:',
            'institution' => 'Institution:',

            'sources.location' => 'Source Location:',
            'sources.system' => 'Source System:',
            'destinations.location' => 'Destination Location:',
            'destinations.system' => 'Destination System:',

            'equipment' => 'Scanner:',
            'proxyuser' => 'Ordering Provider(s):',
            'orderRecipients' => 'Order Recipient(s):',
            'reportRecipients' => 'Report Recipient(s):',
            'organizationRecipients' => 'Recipient Organization(s):',
            'provider' => 'Submitter:',
            'returnoption' => 'Return slide(s) by this date even if not scanned:',
            'priority' => 'Priority:',
            'deadline' => 'Deadline:',

            'labelPrefix' => 'Image',
            'device.types' => array()
        );

        //over write labels
        if( array_key_exists('labels', $this->params) ) {
            $overLabels = $this->params['labels'];
            foreach($labels as $field=>$label) {
                //echo $field."=>".$label."<br>";
                if( array_key_exists($field, $overLabels) ) {
                    //echo $field." exists!<br>";
                    $labels[$field] = $overLabels[$field];
                }
            }
        }

        $this->labels = $labels;

    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $this->formConstructor($options['form_custom_value'],$options['form_custom_value_entity']);

        $this->buildForm_new($builder,$options);
    }

    //executes in 17 seconds
    public function buildForm_new(FormBuilderInterface $builder, array $options)
    {
        //return;

        if( $this->keyInArrayAndTrue($this->params,'message.idnumber') ) {
            $builder->add('idnumber', null, array(
                'label' => "Identification Number:",
                'attr' => array('class' => 'form-control'),
                'required'=>false,
            ));
        }

        if( $this->keyInArrayAndTrue($this->params,'message.orderdate') ) {
            //echo "add orderdate <br>";
            $builder->add('orderdate', DateType::class,array(
                'widget' => 'single_text',
                'label' => "Generation Date:",
                //'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
                'format' => 'MM/dd/yyyy, H:mm:ss',
                'attr' => array('class' => 'datepicker form-control'),
                'required' => false,
            ));
        }
        //exit("formtype after orderdate");

        $builder->add('provider', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => $this->labels['provider'],
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
//                        ->where('u.roles LIKE :roles OR u=:user')
//                        ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
                },
        ));

        if( $this->keyInArrayAndTrue($this->params,'message.proxyuser') ) {
            $builder->add('proxyuser', ScanCustomSelectorType::class, array(
                'label' => $this->labels['proxyuser'],
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                'required' => false,
                'classtype' => 'userWrapper'
            ));
        }

        if( $this->keyInArrayAndTrue($this->params,'message.orderRecipients') ) {
            $builder->add('orderRecipients', ScanCustomSelectorType::class, array(
                'label' => $this->labels['orderRecipients'],
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                'required' => false,
                'classtype' => 'userWrapper'
            ));
        }

        if( $this->keyInArrayAndTrue($this->params,'message.reportRecipients') ) {
            $builder->add('reportRecipients', ScanCustomSelectorType::class, array(
                'label' => $this->labels['reportRecipients'],
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                'required' => false,
                'classtype' => 'userWrapper'
            ));
        }

        if( $this->keyInArrayAndTrue($this->params,'message.organizationRecipients') ) {
            $this->params['label'] = $this->labels['organizationRecipients'];
            $this->addFormOrganizationRecipients('organizationRecipients',$builder,$this->params);
        }

        if( $this->keyInArrayAndTrue($this->params,'educational') ) {
            $builder->add('educational',EducationalType::class,array(
                'form_custom_value' => $this->params,
                'label'=>$this->labels['educational']
            ));
        }

        if( $this->keyInArrayAndTrue($this->params,'research') ) {
            $builder->add('research',ResearchType::class,array(
                'form_custom_value' => $this->params,
                'label'=>$this->labels['research']
            ));
        }

        //priority
        //$helper = new FormHelper();
        $priorityArr = array(
            'label' => $this->labels['priority'],
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
            'label'=>$this->labels['deadline'],
        ));

        $builder->add('returnoption', CheckboxType::class, array(
            'label'     => $this->labels['returnoption'],
            'required'  => false,
        ));

        //datastructure-patient
        //if( array_key_exists('datastructure',$this->params) && ($this->params['datastructure'] == 'datastructure-patient') ) {

            //Message Status
            $builder->add('messageStatus', EntityType::class, array(
                'class' => 'OlegOrderformBundle:MessageStatusList',
                //'choice_label' => 'name',
                'label'=>'Message Status:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));

        //}


        //sources
        if( $this->params['message.sources'] == true ) {
            $this->params['endpoint.location.label'] = $this->labels['sources.location'];
            $this->params['endpoint.system.label'] = $this->labels['sources.system'];
            $this->addFormEndpoint('sources',$builder,$this->params);
        }

        //destinations
        if( $this->params['message.destinations'] == true ) {
            $this->params['endpoint.location.label'] = $this->labels['destinations.location'];
            $this->params['endpoint.system.label'] = $this->labels['destinations.system'];
            $this->addFormEndpoint('destinations',$builder,$this->params);
        }

        //externalIds
        if( $this->params['message.externalIds'] == true ) {
            //echo "message.externalIds <br>";
            $this->addFormExternalIds('externalIds',$builder,$this->params);
        }

        //Institution Tree
        if( array_key_exists('institutions', $this->params) ) {
            $institutions = $this->params['institutions'];
        } else {
            $institutions = null;
        }
        $builder->add('institution', EntityType::class, array(
            'label' => 'Order data visible to members of (Institutional PHI Scope):',
            'required'=> true,
            'multiple' => false,
            //'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $institutions,
            'attr' => array('class' => 'combobox combobox-width combobox-institution')
        ));


        //message's slide
        if( $this->keyInArrayAndTrue($this->params,'slide') ) {
            $builder->add('slide', CollectionType::class, array(
                'entry_type' => SlideSimpleType::class,
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slide__',
            ));
        }


        //Associations
        if(  $this->keyInArrayAndTrue($this->params,'message.associations') ) {
            $builder->add('associations', EntityType::class, array(
                'class' => 'OlegOrderformBundle:Message',
                'choice_label' => 'getFullName',
                'label' => "Association(s):",
                'attr' => array('class' => 'combobox combobox-width'),
                'required'=>false,
                'multiple' => true,
            ));
        }
        if( $this->keyInArrayAndTrue($this->params,'message.backAssociations') ) {
            $builder->add('backAssociations', EntityType::class, array(
                'class' => 'OlegOrderformBundle:Message',
                'choice_label' => 'getFullName',
                'label' => "Reciprocal Association(s):",
                'attr' => array('class' => 'combobox combobox-width'),
                'required'=>false,
                'multiple' => true,
            ));
        }

        /////////////////////////// specific orders //////////////////////////
if( 1 ) {
        //get message entity
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {

            $form = $event->getForm();
            $dataEntity = $event->getData();

            /* Check we're looking at the right data/form */
            if( $dataEntity && $dataEntity instanceof Message ) {

                if( $dataEntity->getMessageCategory() ) {
                    $messageCategory = $dataEntity->getMessageCategory()->getName()."";
                } else {
                    $messageCategory = null;
                }
                $this->params['dataEntity.messageCategory'] = $messageCategory;

                //Encounter Note
                if( $messageCategory == "Encounter Note" ) {
                    $form->add('proxyuser', ScanCustomSelectorType::class, array(
                        'label' => $this->labels['proxyuser'],
                        'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                        'required' => false,
                        'classtype' => 'userWrapper'
                    ));
                    $form->add('comment', TextType::class, array(
                        'required' => false,
                        'label' => 'Note:',
                        'attr' => array('class' => 'textarea form-control'),
                    ));
                }

                //Procedure Order
                if( $messageCategory == "Procedure Order" ) {
                    $form->add('procedureorder', ProcedureOrderType::class, array(
                        'required' => false,
                        'label' => false
                    ));
                    $form->add('comment', TextType::class, array(
                        'required' => false,
                        'label' => 'Indication:',
                        'attr' => array('class' => 'textarea form-control'),
                    ));
                }

                //Referral Order
                if( $messageCategory == "Referral Order" ) {
                    //overwrite orderRecipients with new title
                    $form->add('orderRecipients', ScanCustomSelectorType::class, array(
                        'label' => 'Refer To Individual:',
                        'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                        'required' => false,
                        'classtype' => 'userWrapper'
                    ));

                    //overwrite organizationRecipients with title "Refer to Organization:"
                    $this->params['label'] = "Refer to Organization:";
                    $this->addFormOrganizationRecipients('organizationRecipients',$form,$this->params);

                    //show the "Note" field with the title of "Indication"
                    $form->add('comment', TextType::class, array(
                        'required' => false,
                        'label' => 'Indication:',
                        'attr' => array('class' => 'textarea form-control'),
                    ));
                }

                //Procedure Note
                if( $messageCategory == "Procedure Note" ) {
                    $form->add('comment', TextType::class, array(
                        'required' => false,
                        'label' => 'Note:',
                        'attr' => array('class' => 'textarea form-control'),
                    ));
                }

                //laborder
                if( $dataEntity->getLaborder() ) {
                    $form->add('laborder', LabOrderType::class, array(
                        'form_custom_value' => $this->params,
                        'required' => false,
                        'label' => false
                    ));

                    //overwrite organizationRecipients with title "Laboratory:"
                    if( $messageCategory == "Lab Order Requisition" ) {
                        $this->params['label'] = "Laboratory:";
                        $this->addFormOrganizationRecipients('organizationRecipients',$form,$this->params);
                    }

                    if( $messageCategory != "Lab Order Requisition" ) {
                        $params = array('labelPrefix'=>'Requisition Form Image');
                        $equipmentTypes = array('Requisition Form Scanner');
                        $params['device.types'] = $equipmentTypes;
                        $form->add('attachmentContainer', AttachmentContainerType::class, array(
                            'form_custom_value' => $params,
                            'required' => false,
                            'label' => false
                        ));
                    }
                }

                //imageAnalysisOrder
                if( $dataEntity->getImageAnalysisOrder() ) {
                    $form->add('imageAnalysisOrder', ImageAnalysisOrderType::class, array(
                        'form_custom_value' => $this->params,
                        'required' => false,
                        'label' => false
                    ));

                    $this->params['endpoint.location'] = false;
                    $this->params['endpoint.system.label'] = 'Image Analysis Software:';     //$this->labels['destinations.system'];
                    $this->addFormEndpoint('destinations',$form,$this->params);

                    $this->params['endpoint.location'] = false;
                    $this->params['endpoint.system.label'] = $this->labels['sources.system'];   //'Message Source:';
                    $this->addFormEndpoint('sources',$form,$this->params);
                }

                //Genaral Report
                if( $dataEntity->getReport() ) {
                    $form->add('report', ReportType::class, array(
                        'form_custom_value' => $this->params,
                        'required' => false,
                        'label' => false
                    ));

                    $form->add('comment', TextType::class, array(
                        'required' => false,
                        'label' => 'Comment:',
                        'attr' => array('class' => 'textarea form-control'),
                    ));

                    if( $messageCategory != "Slide Report" &&
                        $messageCategory != "Stain Report" &&
                        $messageCategory != "Scan Report"
                    ) {
                        $params = array('labelPrefix'=>'Reference Representation');
                        $equipmentTypes = array();
                        $params['device.types'] = $equipmentTypes;
                        $form->add('attachmentContainer', AttachmentContainerType::class, array(
                            'form_custom_value' => $params,
                            'required' => false,
                            'label' => false
                        ));
                    }


                }

                //report block
                if( $dataEntity->getReportBlock() ) {
                    $form->add('reportBlock', ReportBlockType::class, array(
                        'required' => false,
                        'label' => false
                    ));

                    $form->add('comment', TextType::class, array(
                        'required' => false,
                        'label' => 'Comment:',
                        'attr' => array('class' => 'textarea form-control'),
                    ));
                }

                //blockorder
                if( $dataEntity->getBlockorder() ) {
                    $form->add('blockorder', BlockOrderType::class, array(
                        'required' => false,
                        'label' => false
                    ));
                }

                //block images
//                if( $messageCategory == "Block Images" ) {
//                    //attachmentContainer
//                    $params = array('labelPrefix'=>'Block Image');
//                    $equipmentTypes = array('Xray Machine','Block Imaging Camera');
//                    $params['device.types'] = $equipmentTypes;
//                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//                }

                //slideorder
                if( $dataEntity->getSlideorder() ) {
                    $form->add('slideorder', SlideOrderType::class, array(
                        'required' => false,
                        'label' => false
                    ));

                    $form->add('equipment', EntityType::class, array(
                        'class' => 'OlegUserdirectoryBundle:Equipment',
                        'choice_label' => 'name',
                        'label' => 'Microtome Device:',
                        'required'=> true,
                        'multiple' => false,
                        'attr' => array('class'=>'combobox combobox-width'),
                        'query_builder' => function(EntityRepository $er) {

                                $equipmentTypes = array('Microtome','Centrifuge');
                                $whereArr = array();
                                foreach($equipmentTypes as $equipmentType) {
                                    $whereArr[] = "keytype.name = '" . $equipmentType . "'";
                                }
                                $where = implode(' OR ', $whereArr);

                                return $er->createQueryBuilder('i')
                                    ->leftJoin('i.keytype','keytype')
                                    ->where($where . " AND i.type != :type")
                                    ->setParameters( array('type' => 'disabled') );
                            },
                    ));
                }

                //stainorder
                if( $dataEntity->getStainorder() ) {
                    $form->add('stainorder', StainOrderType::class, array(
                        'required' => false,
                        'label' => false
                    ));

                    $form->add('equipment', EntityType::class, array(
                        'class' => 'OlegUserdirectoryBundle:Equipment',
                        'choice_label' => 'name',
                        'label' => 'Slide Stainer Device:',
                        'required'=> true,
                        'multiple' => false,
                        'attr' => array('class'=>'combobox combobox-width'),
                        'query_builder' => function(EntityRepository $er) {

                                $equipmentTypes = array('Slide Stainer');
                                $whereArr = array();
                                foreach($equipmentTypes as $equipmentType) {
                                    $whereArr[] = "keytype.name = '" . $equipmentType . "'";
                                }
                                $where = implode(' OR ', $whereArr);

                                return $er->createQueryBuilder('i')
                                    ->leftJoin('i.keytype','keytype')
                                    ->where($where . " AND i.type != :type")
                                    ->setParameters( array('type' => 'disabled') );
                            },
                    ));
                }


                //////////////////// message exception fields such as orderRecipients, reportRecipients etc  ////////////////////
                $showOrderRecipients = true;
                $showReportRecipients = false;

                if( $messageCategory == "Image Analysis Order" ) {
                    $showReportRecipients = true;
                }

                if( strpos($messageCategory,'Report') !== false ) {
                    $showOrderRecipients = false;
                    $showReportRecipients = true;
                    //echo $messageCategory.": showReportRecipients=".$showReportRecipients."<br>";
                }

                if( $messageCategory == 'Lab Order Requisition' ) {
                    $showReportRecipients = true;
                }

                //echo "showReportRecipients=".$showReportRecipients."<br>";

                //remove fields
                if( !$showOrderRecipients ) {
                    //$form->remove('orderRecipients');
                }

                if( !$showReportRecipients ) {
                    //$form->remove('reportRecipients');
                }
                //////////////////// EOF message exception fields such as orderRecipients, reportRecipients ////////////////////


            }//$dataEntity
        });
}
        /////////////////////////// specific orders //////////////////////////

    }

//    //executes in 17 seconds
//    public function buildForm_old(FormBuilderInterface $builder, array $options)
//    {
//
//        if( array_key_exists('message.idnumber', $this->params) && $this->params['message.idnumber'] == true ) {
//            $builder->add('idnumber', null, array(
//                'label' => "Identification Number:",
//                'attr' => array('class' => 'form-control'),
//                'required'=>false,
//            ));
//        }
//
//        if( array_key_exists('message.orderdate', $this->params) && $this->params['message.orderdate'] == true ) {
//            $builder->add('orderdate','date',array(
//                'widget' => 'single_text',
//                'label' => "Generation Date:",
//                //'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
//                'format' => 'MM/dd/yyyy, H:mm:ss',
//                'attr' => array('class' => 'datepicker form-control'),
//                'required' => false,
//            ));
//        }
//
//        //echo "provider show=".$this->params['message.provider']."<br>";
//        $builder->add('provider', EntityType::class, array(
//            'class' => 'OlegUserdirectoryBundle:User',
//            'label' => $this->labels['provider'],
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('u')
//                        ->where('u.roles LIKE :roles OR u=:user')
//                        ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
//                },
//        ));
//
//        if( array_key_exists('message.proxyuser', $this->params) && $this->params['message.proxyuser'] == true ) {
//            $builder->add('proxyuser', ScanCustomSelectorType::class, array(
//                'label' => $this->labels['proxyuser'],
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
//                'required' => false,
//                //'multiple' => true,
//                'classtype' => 'userWrapper'
//            ));
//        }
//
//        if( array_key_exists('message.orderRecipients', $this->params) && $this->params['message.orderRecipients'] == true ) {
//            $builder->add('orderRecipients', ScanCustomSelectorType::class, array(
//                'label' => $this->labels['orderRecipients'],
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
//                'required' => false,
//                'classtype' => 'userWrapper'
//            ));
//        }
//
//        if( array_key_exists('message.reportRecipients', $this->params) && $this->params['message.reportRecipients'] == true ) {
//            $builder->add('reportRecipients', ScanCustomSelectorType::class, array(
//                'label' => $this->labels['reportRecipients'],
//                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
//                'required' => false,
//                'classtype' => 'userWrapper'
//            ));
//        }
//
//        if( array_key_exists('educational', $this->params) && $this->params['educational'] == true ) {
//            $builder->add( 'educational', new EducationalType($this->params,$this->entity), array('label'=>$this->labels['educational']) );
//        }
//
//        if( array_key_exists('research', $this->params) && $this->params['research'] == true ) {
//            $builder->add( 'research', new ResearchType($this->params,$this->entity), array('label'=>$this->labels['research']) );
//        }
//
//        //priority
//        $helper = new FormHelper();
//        $priorityArr = array(
//            'label' => $this->labels['priority'],
//            'choices' => $helper->getPriority(),
//            'required' => true,
//            'multiple' => false,
//            'expanded' => true,
//            'attr' => array('class' => 'horizontal_type', 'required'=>'required')
//        );
//        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create' ) {
//            $priorityArr['data'] = 'Routine';    //new
//        }
//        $builder->add( 'priority', 'choice', $priorityArr);
//
//        //deadline
//        if( $this->params['cycle'] == 'new' ) {
//            $deadline = date_modify(new \DateTime(), '+2 week');
//        } else {
//            $deadline = null;
//        }
//
//        if( $this->entity && $this->entity->getDeadline() != '' ) {
//            $deadline = $this->entity->getDeadline();
//        }
//
//        $builder->add('deadline','date',array(
//            'widget' => 'single_text',
//            'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
//            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
//            'required' => false,
//            'data' => $deadline,
//            'label'=>$this->labels['deadline'],
//        ));
//
//        $builder->add('returnoption', 'checkbox', array(
//            'label'     => $this->labels['returnoption'],
//            'required'  => false,
//        ));
//
//        //sources
//        if( array_key_exists('message.sources', $this->params) && $this->params['message.sources'] == true ) {
//            $this->params['endpoint.location.label'] = $this->labels['sources.location'];
//            $this->params['endpoint.system.label'] = $this->labels['sources.system'];
//            $this->addFormEndpoint('sources',$builder,$this->params);
//        }
//
//        //destinations
//        if( array_key_exists('message.destinations', $this->params) && $this->params['message.destinations'] == true ) {
//            $this->params['endpoint.location.label'] = $this->labels['destinations.location'];
//            $this->params['endpoint.system.label'] = $this->labels['destinations.system'];
//            $this->addFormEndpoint('destinations',$builder,$this->params);
//        }
//
//        //Institution Tree
//        if( array_key_exists('institutions', $this->params) ) {
//            $institutions = $this->params['institutions'];
//        } else {
//            $institutions = null;
//        }
//
//        $builder->add('institution', EntityType::class, array(
//            'label' => $this->labels['institution'],
//            'required'=> true,
//            'multiple' => false,
//            'empty_value' => false,
//            'class' => 'OlegUserdirectoryBundle:Institution',
//            'choices' => $institutions,
//            'attr' => array('class' => 'combobox combobox-width combobox-institution ajax-combobox-institution-preset')
//        ));
//
//
//        //message's slide
//        if( array_key_exists('slide', $this->params) && $this->params['slide'] == true ) {
//            $builder->add('slide', CollectionType::class, array(
//                'type' => new SlideSimpleType($this->params,$this->entity),
//                'label' => false,
//                'required' => false,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'by_reference' => false,
//                'prototype' => true,
//                'prototype_name' => '__slide__',
//            ));
//        }
//
//        //Associations
//        if( array_key_exists('message.associations', $this->params) && $this->params['message.associations'] == true ) {
//            $builder->add('associations', EntityType::class, array(
//                'class' => 'OlegOrderformBundle:Message',
//                'choice_label' => 'getFullName',
//                'label' => "Association(s):",
//                'attr' => array('class' => 'combobox combobox-width'),
//                'required'=>false,
//                'multiple' => true,
//            ));
//        }
//        if( array_key_exists('message.backAssociations', $this->params) && $this->params['message.backAssociations'] == true ) {
//            $builder->add('backAssociations', EntityType::class, array(
//                'class' => 'OlegOrderformBundle:Message',
//                'choice_label' => 'getFullName',
//                'label' => "Reciprocal Association(s):",
//                'attr' => array('class' => 'combobox combobox-width'),
//                'required'=>false,
//                'multiple' => true,
//            ));
//        }
//
//
//        /////////////////////////// specific orders //////////////////////////
//
//        //get message entity
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
//        {
//
//            $form = $event->getForm();
//            $dataEntity = $event->getData();
//
//            /* Check we're looking at the right data/form */
//            if( $dataEntity && $dataEntity instanceof Message ) {
//
//                //$this->params['dataEntity'] = $dataEntity;
//                if( $dataEntity->getMessageCategory() ) {
//                    $messageCategory = $dataEntity->getMessageCategory()->getName()."";
//                } else {
//                    $messageCategory = null;
//                }
//                $this->params['dataEntity.messageCategory'] = $messageCategory;
//                //echo $dataEntity;
//
//                //Encounter Note
//                if( $dataEntity->getMessageCategory()->getName() == "Encounter Note" ) {
//                    $form->add('comment', TextType::class, array(
//                        'required' => false,
//                        'label' => 'Note:',
//                        'attr' => array('class' => 'textarea form-control'),
//                    ));
//                }
//
//                //Procedure Note
//                if( $dataEntity->getMessageCategory()->getName() == "Procedure Note" ) {
//                    $form->add('comment', TextType::class, array(
//                        'required' => false,
//                        'label' => 'Note:',
//                        'attr' => array('class' => 'textarea form-control'),
//                    ));
//                }
//
//                //laborder
//                if( $dataEntity->getLaborder() || (array_key_exists('message.laborder', $this->params) && $this->params['message.laborder'] == true) ) {
//                    //echo "laborder:".$dataEntity->getLaborder()->getId()."<br>";
//                    $form->add('laborder', new LabOrderType($this->params,$this->entity), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//
//                    //overwrite laborder's attachmentContainer
//                    $params = array('labelPrefix'=>'Requisition Form Image');
//                    $equipmentTypes = array('Requisition Form Scanner');
//                    $params['device.types'] = $equipmentTypes;
//                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//                }
//
//                //imageAnalysisOrder
//                if( $dataEntity->getImageAnalysisOrder() || (array_key_exists('message.imageAnalysisOrder', $this->params) && $this->params['message.imageAnalysisOrder'] == true) ) {
//                    $form->add('imageAnalysisOrder', new ImageAnalysisOrderType($this->params,$this->entity), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//
//                    $this->params['endpoint.location'] = false;
//                    $this->params['endpoint.system.label'] = 'Image Analysis Software:';     //$this->labels['destinations.system'];
//                    $this->addFormEndpoint('destinations',$form,$this->params);
//
//                    $this->params['endpoint.location'] = false;
//                    $this->params['endpoint.system.label'] = $this->labels['sources.system'];   //'Message Source:';
//                    $this->addFormEndpoint('sources',$form,$this->params);
//                }
//
//                //report
//                if( $dataEntity->getReport() || (array_key_exists('message.report', $this->params) && $this->params['message.report'] == true) ) {
//                    $form->add('report', new ReportType($this->params,$this->entity), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//
//                    //overwrite report's attachmentContainer
//                    $params = array('labelPrefix'=>'Reference Representation');
//                    $equipmentTypes = array();
//                    $params['device.types'] = $equipmentTypes;
//                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//                }
//
//                //report block
//                if( $dataEntity->getReportBlock() || (array_key_exists('message.reportblock', $this->params) && $this->params['message.reportblock'] == true) ) {
//                    $form->add('reportBlock', new ReportBlockType($this->params,$this->entity), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//                }
//
//                //blockorder
//                if( $dataEntity->getBlockorder() || (array_key_exists('message.blockorder', $this->params) && $this->params['message.blockorder'] == true) ) {
//                    $form->add('blockorder', new BlockOrderType($this->params,$this->entity), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//                }
//
//                //block images
//                if( $dataEntity->getMessageCategory()->getName() == "Block Images" ) {
//                    //attachmentContainer
//                    $params = array('labelPrefix'=>'Block Image');
//                    $equipmentTypes = array('Xray Machine','Block Imaging Camera');
//                    $params['device.types'] = $equipmentTypes;
//                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//                }
//
//                //slideorder
//                if( $dataEntity->getSlideorder() || (array_key_exists('message.slideorder', $this->params) && $this->params['message.slideorder'] == true) ) {
//                    $form->add('slideorder', new SlideOrderType($this->params,$this->entity), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//
//                    $form->add('equipment', EntityType::class, array(
//                        'class' => 'OlegUserdirectoryBundle:Equipment',
//                        'choice_label' => 'name',
//                        'label' => 'Microtome Device:',
//                        'required'=> true,
//                        'multiple' => false,
//                        'attr' => array('class'=>'combobox combobox-width'),
//                        'query_builder' => function(EntityRepository $er) {
//
//                                $equipmentTypes = array('Microtome','Centrifuge');
//                                $whereArr = array();
//                                foreach($equipmentTypes as $equipmentType) {
//                                    $whereArr[] = "keytype.name = '" . $equipmentType . "'";
//                                }
//                                $where = implode(' OR ', $whereArr);
//
//                                return $er->createQueryBuilder('i')
//                                    ->leftJoin('i.keytype','keytype')
//                                    ->where($where . " AND i.type != :type")
//                                    ->setParameters( array('type' => 'disabled') );
//                            },
//                    ));
//                }
//
//                //stainorder
//                if( $dataEntity->getStainorder() || (array_key_exists('message.stainorder', $this->params) && $this->params['message.stainorder'] == true) ) {
//                    $form->add('stainorder', new StainOrderType($this->params,$this->entity), array(
//                        'required' => false,
//                        'label' => false
//                    ));
//
//                    $form->add('equipment', EntityType::class, array(
//                        'class' => 'OlegUserdirectoryBundle:Equipment',
//                        'choice_label' => 'name',
//                        'label' => 'Slide Stainer Device:',
//                        'required'=> true,
//                        'multiple' => false,
//                        'attr' => array('class'=>'combobox combobox-width'),
//                        'query_builder' => function(EntityRepository $er) {
//
//                                $equipmentTypes = array('Slide Stainer');
//                                $whereArr = array();
//                                foreach($equipmentTypes as $equipmentType) {
//                                    $whereArr[] = "keytype.name = '" . $equipmentType . "'";
//                                }
//                                $where = implode(' OR ', $whereArr);
//
//                                return $er->createQueryBuilder('i')
//                                    ->leftJoin('i.keytype','keytype')
//                                    ->where($where . " AND i.type != :type")
//                                    ->setParameters( array('type' => 'disabled') );
//                            },
//                    ));
//                }
//
//
//                //////////////////// message exception fields such as orderRecipients, reportRecipients  ////////////////////
//                $showOrderRecipients = true;
//                $showReportRecipients = false;
//
//                if( $messageCategory == "Image Analysis Order" ) {
//                    $showOrderRecipients = true;
//                    $showReportRecipients = true;
//                }
//
//                if( strpos($messageCategory,'Report') !== false ) {
//                    $showOrderRecipients = false;
//                    $showReportRecipients = true;
//                }
//
//                //remove fields
//                if( !$showOrderRecipients ) {
//                    $form->remove('orderRecipients');
//                }
//
//                if( !$showReportRecipients ) {
//                    $form->remove('reportRecipients');
//                }
//                //////////////////// EOF message exception fields such as orderRecipients, reportRecipients ////////////////////
//
//
//            }//$dataEntity
//        });
//        /////////////////////////// specific orders //////////////////////////
//
//    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Message',
            'form_custom_value' => null,
            'form_custom_value_entity' => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oleg_orderformbundle_messageobjecttype';
    }

    //return true if substring is found: 'Scan Order', 'Lab Order' ...
    public function hasSpecificOrders( $message, $substring ) {
        $category = $message->getType();
        //echo "category=".$category."<br>";
        if( strpos($category,$substring) !== false ) {
            //echo "has <br>";
            return true;
        }
        //echo "no has <br>";
        return false;
    }


    public function addFormEndpoint( $field, $form, $params ) {
        $form->add($field, CollectionType::class, array(
            'entry_type' => EndpointType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__'.$field.'__',
        ));
    }

    public function addFormExternalIds( $field, $form, $params ) {
        $form->add($field, CollectionType::class, array(
            'entry_type' => ExternalIdType::class,
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__'.$field.'__',
        ));
    }

    public function addFormOrganizationRecipients( $field, $form, $params ) {
        $form->add($field, CollectionType::class, array(
            'entry_type' => InstitutionalWrapperType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,   //$this->params['label'],
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__'.$field.'__',
        ));
    }

    public function keyInArrayAndTrue( $array, $key ) {
        //return false;
        if( $this->keyInArray($array,$key) && $array[$key] == true ) {
            return true;
        }
        return false;
    }
    public function keyInArray( $array, $key ) {
        //return false;
        if( array_key_exists($key, $array) ) {
            return true;
        }
        return false;
    }

}
