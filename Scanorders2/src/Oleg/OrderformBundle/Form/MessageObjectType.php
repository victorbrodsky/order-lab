<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;

use Oleg\OrderformBundle\Entity\Message;
use Oleg\UserdirectoryBundle\Form\AttachmentContainerType;
use Oleg\OrderformBundle\Helper\FormHelper;

//This form includes only message object
class MessageObjectType extends AbstractType
{

    protected $entity;
    protected $params;
    protected $labels;

    public function __construct( $params=null, $entity=null )
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
        if( !array_key_exists('message.sources', $this->params) ) {
            $this->params['message.sources'] = true;
        }
        if( !array_key_exists('message.destinations', $this->params) ) {
            $this->params['message.destinations'] = true;
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
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        if( array_key_exists('message.idnumber', $this->params) &&  $this->params['message.idnumber'] == true ) {
            $builder->add('idnumber', null, array(
                'label' => "Identification Number:",
                'attr' => array('class' => 'form-control'),
                'required'=>false,
            ));
        }

        if( array_key_exists('message.orderdate', $this->params) &&  $this->params['message.orderdate'] == true ) {
            //echo "message.orderdate=".$this->params['message.orderdate']."<br>";
            $builder->add('orderdate','date',array(
                'widget' => 'single_text',
                'label' => "Generation Date:",
                //'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
                'format' => 'MM/dd/yyyy, H:mm:ss',
                'attr' => array('class' => 'datepicker form-control'),
                'required' => false,
            ));
        }

        //echo "provider show=".$this->params['message.provider']."<br>";
        //if( array_key_exists('message.provider', $this->params) &&  $this->params['message.provider'] == true ) {
            $builder->add('provider', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => $this->labels['provider'],
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->where('u.roles LIKE :roles OR u=:user')
                            ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
                    },
            ));
        //}

        if( array_key_exists('message.proxyuser', $this->params) &&  $this->params['message.proxyuser'] == true ) {
//            $builder->add('proxyuser', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:User',
//                'label' => $this->labels['proxyuser'],
//                'required' => false,
//                'multiple' => true,
//                'attr' => array('class' => 'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('u')
//                            ->where('u.roles LIKE :roles OR u=:user')
//                            ->setParameters(array('roles' => '%' . 'ROLE_SCANORDER_ORDERING_PROVIDER' . '%', 'user' => $this->params['user'] ));
//                    },
//            ));

            $builder->add('proxyuser', 'custom_selector', array(
                'label' => $this->labels['proxyuser'],
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                'required' => false,
                //'multiple' => true,
                'classtype' => 'userWrapper'
            ));
        }

        if( array_key_exists('message.orderRecipients', $this->params) &&  $this->params['message.orderRecipients'] == true ) {
            $builder->add('orderRecipients', 'custom_selector', array(
                'label' => $this->labels['orderRecipients'],
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                'required' => false,
                'classtype' => 'userWrapper'
            ));
        }

        if( array_key_exists('message.reportRecipients', $this->params) &&  $this->params['message.reportRecipients'] == true ) {
            $builder->add('reportRecipients', 'custom_selector', array(
                'label' => $this->labels['reportRecipients'],
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-proxyuser'),
                'required' => false,
                'classtype' => 'userWrapper'
            ));
        }

        if( array_key_exists('educational', $this->params) &&  $this->params['educational'] == true ) {
            $builder->add( 'educational', new EducationalType($this->params,$this->entity), array('label'=>$this->labels['educational']) );
        }

        if( array_key_exists('research', $this->params) &&  $this->params['research'] == true ) {
            $builder->add( 'research', new ResearchType($this->params,$this->entity), array('label'=>$this->labels['research']) );
        }

        //priority
        $helper = new FormHelper();
        $priorityArr = array(
            'label' => $this->labels['priority'],
            'choices' => $helper->getPriority(),
            'required' => true,
            'multiple' => false,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type', 'required'=>'required')
        );
        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create' ) {
            $priorityArr['data'] = 'Routine';    //new
        }
        $builder->add( 'priority', 'choice', $priorityArr);

        //deadline
        if( $this->params['cycle'] == 'new' ) {
            $deadline = date_modify(new \DateTime(), '+2 week');
        } else {
            $deadline = null;
        }

        if( $this->entity && $this->entity->getDeadline() != '' ) {
            $deadline = $this->entity->getDeadline();
        }

        $builder->add('deadline','date',array(
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control', 'style'=>'margin-top: 0;'),
            'required' => false,
            'data' => $deadline,
            'label'=>$this->labels['deadline'],
        ));

        $builder->add('returnoption', 'checkbox', array(
            'label'     => $this->labels['returnoption'],
            'required'  => false,
        ));

        //sources
        if( array_key_exists('message.sources', $this->params) &&  $this->params['message.sources'] == true ) {
            $this->params['endpoint.location.label'] = $this->labels['sources.location'];
            $this->params['endpoint.system.label'] = $this->labels['sources.system'];
            $this->addFormEndpoint('sources',$builder,$this->params);
        }

        //destinations
        if( array_key_exists('message.destinations', $this->params) && $this->params['message.destinations'] == true ) {
            //echo "show destination endpoint<br>";
            $this->params['endpoint.location.label'] = $this->labels['destinations.location'];
            $this->params['endpoint.system.label'] = $this->labels['destinations.system'];
            $this->addFormEndpoint('destinations',$builder,$this->params);
        }

        //Institution Tree
        if( array_key_exists('institutions', $this->params) ) {
            $institutions = $this->params['institutions'];
        } else {
            $institutions = null;
        }

        $builder->add('institution', 'entity', array(
            'label' => $this->labels['institution'],
            'required'=> true,
            'multiple' => false,
            'empty_value' => false,
            'class' => 'OlegUserdirectoryBundle:Institution',
            'choices' => $institutions,
            'attr' => array('class' => 'combobox combobox-width combobox-institution ajax-combobox-institution-preset')
        ));


        //message's slide
        if( array_key_exists('slide', $this->params) &&  $this->params['slide'] == true ) {
            $builder->add('slide', 'collection', array(
                'type' => new SlideSimpleType($this->params,$this->entity),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__slide__',
            ));
        }

//        if( !$builder->has('attachmentContainer') ) {
//            $params = array('labelPrefix'=>'Image');
//            $params['device.types'] = array();
//            $builder->add('attachmentContainer', new AttachmentContainerType($params), array(
//                'required' => false,
//                'label' => false
//            ));
//        }
//
//        if( !$builder->has('equipment') ) {
//            $builder->add('equipment', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:Equipment',
//                'property' => 'name',
//                'label'=>$this->labels['equipment'],
//                'required'=> true,
//                'multiple' => false,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('i')
//                            ->leftJoin('i.keytype','keytype')
//                            ->where("keytype.name = :keytype AND i.type != :type")
//                            ->setParameters( array('keytype' => 'Whole Slide Scanner', 'type' => 'disabled') );
//                    },
//            ));
//        }


        //Associations
        //$this->params['message.associations'] = true;
        if( array_key_exists('message.associations', $this->params) &&  $this->params['message.associations'] == true ) {
            $builder->add('associations', 'entity', array(
                'class' => 'OlegOrderformBundle:Message',
                'property' => 'getFullName',
                'label' => "Association(s):",
                'attr' => array('class' => 'combobox combobox-width'),
                'required'=>false,
                'multiple' => true,
            ));
        }
        if( array_key_exists('message.backAssociations', $this->params) &&  $this->params['message.backAssociations'] == true ) {
            $builder->add('backAssociations', 'entity', array(
                'class' => 'OlegOrderformBundle:Message',
                'property' => 'getFullName',
                'label' => "Reciprocal Association(s):",
                'attr' => array('class' => 'combobox combobox-width'),
                'required'=>false,
                'multiple' => true,
            ));
                    }


        /////////////////////////// specific orders //////////////////////////

        //get message entity
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event)
        {

            $form = $event->getForm();
            $dataEntity = $event->getData();

            /* Check we're looking at the right data/form */
            if( $dataEntity && $dataEntity instanceof Message ) {

                //echo $dataEntity;

                //laborder
                if( $dataEntity->getLaborder() || (array_key_exists('message.laborder', $this->params) &&  $this->params['message.laborder'] == true) ) {
                    //echo "laborder:".$dataEntity->getLaborder()->getId()."<br>";
                    $form->add('laborder', new LabOrderType($this->params,$this->entity), array(
                        'required' => false,
                        'label' => false
                    ));

                    //overwrite laborder's attachmentContainer
                    $params = array('labelPrefix'=>'Requisition Form Image');
                    $equipmentTypes = array('Requisition Form Scanner');
                    $params['device.types'] = $equipmentTypes;
                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
                        'required' => false,
                        'label' => false
                    ));
                }

                //imageAnalysisOrder
                if( $dataEntity->getImageAnalysisOrder() || (array_key_exists('message.imageAnalysisOrder', $this->params) && $this->params['message.imageAnalysisOrder'] == true) ) {
                    //$params['device.types'] = $equipmentTypes;
                    //echo "show imageAnalysisOrder <br>";
                    $form->add('imageAnalysisOrder', new ImageAnalysisOrderType($this->params,$this->entity), array(
                        'required' => false,
                        'label' => false
                    ));

                    //$this->params['endpoint.location.label'] = $this->labels['destinations.location'];
                    $this->params['endpoint.location'] = false;
                    $this->params['endpoint.system.label'] = 'Image Analysis Software:';     //$this->labels['destinations.system'];
                    $this->addFormEndpoint('destinations',$form,$this->params);

                    //$this->params['endpoint.location.label'] = $this->labels['sources.location'];
                    $this->params['endpoint.location'] = false;
                    $this->params['endpoint.system.label'] = $this->labels['sources.system'];   //'Message Source:';
                    $this->addFormEndpoint('sources',$form,$this->params);
                }

                //report
                if( $dataEntity->getReport() || (array_key_exists('message.report', $this->params) &&  $this->params['message.report'] == true) ) {
                    //echo "Report:".$dataEntity->getReport()->getId()."<br>";
                    $form->add('report', new ReportType($this->params,$this->entity), array(
                        'required' => false,
                        'label' => false
                    ));

                    //overwrite report's attachmentContainer
                    $params = array('labelPrefix'=>'Reference Representation');
                    $equipmentTypes = array();
                    $params['device.types'] = $equipmentTypes;
                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
                        'required' => false,
                        'label' => false
                    ));
                }

                //report block
                if( $dataEntity->getReportBlock() || (array_key_exists('message.reportblock', $this->params) &&  $this->params['message.reportblock'] == true) ) {
                    echo "ReportBlock:".$dataEntity->getReportBlock()->getId()."<br>";
                    $form->add('reportBlock', new ReportBlockType($this->params,$this->entity), array(
                        'required' => false,
                        'label' => false
                    ));

                    //overwrite report's attachmentContainer
                    $params = array('labelPrefix'=>'Reference Representation');
                    $equipmentTypes = array();
                    $params['device.types'] = $equipmentTypes;
                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
                        'required' => false,
                        'label' => false
                    ));
                }

                //blockorder
                if( $dataEntity->getBlockorder() || (array_key_exists('message.blockorder', $this->params) &&  $this->params['message.blockorder'] == true) ) {
                    $form->add('blockorder', new BlockOrderType($this->params,$this->entity), array(
                        'required' => false,
                        'label' => false
                    ));

                    //overwrite blockorder's attachmentContainer
                    $params = array('labelPrefix'=>'Block Image');
                    $equipmentTypes = array('Xray Machine','Block Imaging Camera');
                    $params['device.types'] = $equipmentTypes;
                    $form->add('attachmentContainer', new AttachmentContainerType($params), array(
                        'required' => false,
                        'label' => false
                    ));
                }

                //slideorder
                if( $dataEntity->getSlideorder() || (array_key_exists('message.slideorder', $this->params) &&  $this->params['message.slideorder'] == true) ) {
                    $form->add('slideorder', new SlideOrderType($this->params,$this->entity), array(
                        'required' => false,
                        'label' => false
                    ));

                    $form->add('equipment', 'entity', array(
                        'class' => 'OlegUserdirectoryBundle:Equipment',
                        'property' => 'name',
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
                if( $dataEntity->getStainorder() || (array_key_exists('message.stainorder', $this->params) &&  $this->params['message.stainorder'] == true) ) {
                    //echo "stainorder:".$dataEntity->getStainorder()->getId()."<br>";
                    $form->add('stainorder', new StainOrderType($this->params,$this->entity), array(
                        'required' => false,
                        'label' => false
                    ));

                    $form->add('equipment', 'entity', array(
                        'class' => 'OlegUserdirectoryBundle:Equipment',
                        'property' => 'name',
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


                //////////////////// message exception fields such as orderRecipients, reportRecipients  ////////////////////
                $showOrderRecipients = true;
                $showReportRecipients = false;
                $catName = $dataEntity->getMessageCategory()->getName()."";

                if( $catName == "Image Analysis Order" ) {
                    $showOrderRecipients = true;
                    $showReportRecipients = true;
                }

                if( strpos($catName,'Report') !== false ) {
                    $showOrderRecipients = false;
                    $showReportRecipients = true;
                }

                //remove fields
                if( !$showOrderRecipients ) {
                    $form->remove('orderRecipients');
                }

                if( !$showReportRecipients ) {
                    $form->remove('reportRecipients');
                }
                //////////////////// EOF message exception fields such as orderRecipients, reportRecipients ////////////////////


            }//$dataEntity
        });
        /////////////////////////// specific orders //////////////////////////

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Message'
        ));
    }

    public function getName()
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
        $form->add($field, 'collection', array(
            'type' => new EndpointType($params,$this->entity),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__'.$field.'__',
        ));
    }

}
