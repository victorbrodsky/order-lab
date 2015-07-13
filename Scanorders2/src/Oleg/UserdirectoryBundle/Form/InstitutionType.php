<?php

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Entity\UserPosition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class InstitutionType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $institution = $event->getData();
            $form = $event->getForm();

            echo "PRE_SET_DATA inst:".$institution."<br>";

            $label = 'Institution:';
            if( $institution && $institution->getOrganizationalGroupType() ) {
                $label = $institution->getOrganizationalGroupType()->getName().":";
            }

            $form->add('id', 'employees_custom_selector', array(
                'label' => $label,
                'required' => false,
                'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
                'classtype' => 'institution'
            ));

            //add userPositions for 'label'=>'Administrative'
//            if( array_key_exists('label', $this->params) && $this->params['label'] == 'Administrative' ) {
//                //$institutionWithUserPositions = 'institution-with-userpositions';
//
//                if( !$institution ) {
//                    return;
//                }
//
//                //unmapped field: institution-userpositiontype
//                $positions = $this->params['em']->getRepository('OlegUserdirectoryBundle:PositionTypeList')->findAll();
//                //$positionIds = array();
//                //$positionNames = array();
//                $positionIdName = array();
//                foreach( $positions as $position ) {
//                    //$positionIds[] = $position->getId();
//                    //$positionNames[] = $position->getName();
//                    $positionIdName[] = $position->getId() . '-' . $position->getName();
//                }
//
//
//                $institutionsPositiontypes = array();
//                $dataPositions = array();
//
//                foreach( $institution->getEntityBreadcrumbs() as $institution ) {
//
//                    if( !$institution->getOrganizationalGroupType() ) {
//                        //continue;
//                    }
//
//                    $name = $institution->getOrganizationalGroupType()->getName();
//                    $keyInst = $name.'-'.$institution->getId(); //full key: Division-nodeid-positiontype
//
//                    foreach( $positions as $position ) {
//                        $key = $keyInst.'-'.$position->getId();
//                        $institutionsPositiontypes[$key] = $position->getName() . ' of ' . $name; //Head of Department
//                    }
//
//                    //create position data
//                    $positiontypes = $institution->getUserPositionsByUseridAndNodeid($this->params['user'],$institution);
//                    foreach( $positiontypes as $nodeUserPosition ) {
//                        foreach( $nodeUserPosition->getPositionTypes() as $posType ) {
//                            $key = $keyInst.'-'.$posType->getId();
//                            $dataPositions[] = $key;
//                        }
//                    }
//
//                }
//
//                $form->add('institutionspositiontypes', 'choice', array(
//                    //'mapped' => false,
//                    'label' => 'Position Type:',
//                    'choices' => $institutionsPositiontypes,
//                    'multiple' => true,
//                    'required' => false,
//                    'data' => $dataPositions,
//                    'attr' => array('class' => 'combobox institutionspositiontypes', 'data-positions-idname' => implode(",", $positionIdName) ),
//                ));
//
//            }

        });


        //////////////////////// PRE_SUBMIT: set node by id ////////////////////////
if(0) {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {

            $institution = $event->getData();
            $form = $event->getForm();

            echo "PRE_SUBMIT institution:<br>";
            print_r($institution);
            echo "<br>";

            if( !$institution ) {
                return;
            }

            $submittedInstitutionId = $institution['id'];
            if( !$submittedInstitutionId ) {
                return;
            }

            //save institutionspositiontypes to DB
//            $instArr = array();
//            if( array_key_exists('institutionspositiontypes', $institution) ) {
//                foreach( $institution['institutionspositiontypes'] as $institutionspositiontypes ) {
//                    echo "institutionspositiontypes=".$institutionspositiontypes."<br>";
//                    //Division-149-2
//                    $arr = explode("-",$institutionspositiontypes);
//                    $instId = $arr[1];
//                    $posId = $arr[2];
//                    if( $instId && $posId ) {
//                        $instArr[$instId][] = $posId;
//                    }
//                }
//            }

//            echo "newPositions <br>";
//            print_r($instArr);
//            echo "<br>";

//            foreach( $instArr as $instId => $newPositions ) {
//
//                $nodeUserPositions = $this->params['em']->getRepository('OlegUserdirectoryBundle:UserPosition')->findBy(
//                    array(
//                        'user' => $this->params['user']->getId(),
//                        'institution' => $instId
//                    )
//                );
//
//                if( count($nodeUserPositions) > 1 ) {
//                    $error = 'Logical Error: More than one UserPosition found for user ' . $this->params['user'] . ' and institution ID ' . $instId . '. Found ' . count($nodeUserPositions) . ' UserPositions';
//                    throw new LogicException($error);
//                }
//
//                $nodeUserPosition = null;
//                if( count($nodeUserPositions) > 0 ) {
//                    $nodeUserPosition = $nodeUserPositions[0];
//                }
//
//                if( !$nodeUserPosition ) {
//                    //echo 'create new UserPosition<br>';
//                    $nodeUserPosition = new UserPosition();
//                    $nodeUserPosition->setUser($this->params['user']);
//                    $instRef = $this->params['em']->getReference('OlegUserdirectoryBundle:Institution', $instId);
//                    $nodeUserPosition->setInstitution($instRef);
//                }
//
//                $nodeUserPosition->clearPositionTypes();
//
//                foreach( $newPositions as $positionId ) {
//                    $positionRef = $this->params['em']->getReference('OlegUserdirectoryBundle:PositionTypeList', $positionId);
//                    $nodeUserPosition->addPositionType($positionRef);
//                }
//
//                $this->params['em']->persist($nodeUserPosition);
//
//            }

            //set node by id
            $newInst = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($submittedInstitutionId);
            //$newInst = $this->params['em']->getReference('OlegUserdirectoryBundle:Institution', $submittedInstitutionId);

            if( $newInst ) {
                $titleForm = $form->getParent();
                $title = $titleForm->getData();

                echo "title <br>";
                print_r($title);
                echo "<br>";

//                if( !$title ) {
//                    echo "title <br>";
//                    print_r($title);
//                    echo "<br>";
//                    //return;
//                }

//                if( !$title->getInstitution() ) {
//                    //return;
//                }

                //echo "user=".$this->params['user']."<br>";
//                if( !$this->params['user'] ) {
//                    //return;
//                }

                $titleForm->add('institution', null, array(
                    'required' => false,
                    'label' => false,
                    'data' => $newInst   //$newInst->getId()
                ));

//                //remove old userPosition from institution node
//                $newIdBreadcrumbs = $newInst->getIdBreadcrumbs();
//
//                $originalInstitutionId = $title->getInstitution()->getId();
//                $originalInstitution = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($originalInstitutionId);
//                $originalIdBreadcrumbs = $originalInstitution->getIdBreadcrumbs();
//
//                $this->removeUserPositionFromInstitution($this->params['user']->getId(),$originalIdBreadcrumbs,$newIdBreadcrumbs);

                //echo "PRE_SUBMIT set newInst=".$newInst."<br>";
                $title->setInstitution($newInst);
                //$title['institution'] = $newInst;

            }

        });
}
        //////////////////////// EOF PRE_SUBMIT: set node by id ////////////////////////


    }


//    public function removeUserPositionFromInstitution( $userid, $originalIdBreadcrumbs, $newIdBreadcrumbs ) {
//
////        echo "originalIdBreadcrumbs:<br>";
////        print_r($originalIdBreadcrumbs);
////        echo "<br>";
////        echo "newIdBreadcrumbs:<br>";
////        print_r($newIdBreadcrumbs);
////        echo "<br>";
//
//        $mergedArr = array_merge( $originalIdBreadcrumbs, $newIdBreadcrumbs );
//        $diffIds = array_diff($mergedArr, $newIdBreadcrumbs);
//
////        echo "diffIds:<br>";
////        print_r($diffIds);
////        echo "<br>";
//
//        foreach( $diffIds as $instId ) {
//
//            if( !in_array($instId, $newIdBreadcrumbs) ) {
//                $this->removeUserPositionFromSingleInstitution($userid,$instId);
//            }
//
//        }
//    }

//    public function removeUserPositionFromSingleInstitution( $userid, $instid ) {
//
//        $originalInstitution = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($instid);
//
//        $originalUserPositions = $this->params['em']->getRepository('OlegUserdirectoryBundle:UserPosition')->findBy(
//            array(
//                'user' => $userid,
//                'institution' => $instid
//            )
//        );
//
//        if( !$originalInstitution && (!$originalUserPositions || count($originalUserPositions) == 0) ) {
//            return;
//        }
//
//        foreach( $originalUserPositions as $originalUserPosition ) {
//            //echo "!!!PRE_SUBMIT remove userPosition=".$originalUserPosition." from inst=".$originalInstitution."<br>";
//            $originalInstitution->removeUserPosition($originalUserPosition);
//
//            $this->params['em']->remove($originalUserPosition);
//            $this->params['em']->flush($originalUserPosition);
//
//            $this->params['em']->persist($originalInstitution);
//        }
//
//    }









//    public function buildForm_ORIG(FormBuilderInterface $builder, array $options)
//    {
//
//        //hidden: set by js
//        $builder->add('id',null,array(
//            'label' => false,   //'ID:',   //'ID:'
//            'attr' => array('class' => 'tree-node-id'),
//        ));
//
//        //hidden: set by js
//        $builder->add('parent',null,array(
//            'label' => false,   //'Parent:',
//            'attr' => array('class' => 'tree-node-parent'),
//        ));
//
//
//        //////////////////////// Not mapped data providing fields ////////////////////////
//
//        //breadcrumbs hidden: set by js
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//
//            $institution = $event->getData();
//            $form = $event->getForm();
//
//            $children = array();
//            if( $institution ) {
//                $children = $institution->getIdBreadcrumbs();
//            }
//
//            $form->add('breadcrumbs', 'hidden', array(
//                'mapped' => false,
//                'data' => implode(',',$children),
//                'label' => false,
//                'attr' => array('class' => 'tree-node-breadcrumbs'),
//            ));
//
//            //add userPositions for 'label'=>'Administrative'
//            if( array_key_exists('label', $this->params) && $this->params['label'] == 'Administrative' ) {
//                //$institutionWithUserPositions = 'institution-with-userpositions';
////            $builder->add('userPositions',null,array(
////                'mapped' => false,
////                'label' => false,
////                'attr' => array('class' => 'ajax-combobox-userpositions'),
////            ));
//                $this->params['treenode'] = $institution;
//                $form->add('userPositions', 'collection', array(
//                    'type' => new UserPositionType($this->params, null),
//                    'allow_add' => true,
//                    'allow_delete' => true,
//                    'required' => false,
//                    'by_reference' => false,
//                    'prototype' => true,
//                    'prototype_name' => '__userpositions__',
//                ));
//
//            } else {
//                //$institutionWithUserPositions = '';
//                //echo "no user pos<br>";
//                $form->remove('userPositions');
//            }
//
////            //visible as institution node combobox
////            //echo "user pos=".$institutionWithUserPositions."<br>";
////            $form->add('institutionnode', 'employees_custom_selector', array(
////                'mapped' => false,
////                'label' => 'Institution:',
////                'required' => false,
////                'attr' => array('class' => 'ajax-combobox-institution '.$institutionWithUserPositions, 'type' => 'hidden'),
////                'classtype' => 'institution'
////            ));
//
//        });
//        //////////////////////// EOF Not mapped data providing fields ////////////////////////
//
//
//        //visible as institution node combobox
//        $builder->add('institutionnode', 'employees_custom_selector', array(
//            'mapped' => false,
//            'label' => 'Institution:',
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
//            'classtype' => 'institution'
//        ));
//
//
////        echo "with user pos=".$this->institutionWithUserPositions."<br>";
////        //visible as institution node combobox
////        $builder->add('institutionnode', 'employees_custom_selector', array(
////            'mapped' => false,
////            'label' => 'Institution:',
////            'required' => false,
////            'attr' => array('class' => 'ajax-combobox-institution ' . $this->institutionWithUserPositions, 'type' => 'hidden'),
////            'classtype' => 'institution'
////        ));
//
////        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
////            $inst = $event->getData();
////            $form = $event->getForm();
////
////            if (!$inst) {
////                return;
////            }
////
////            //echo "inst=".$inst."<br>";
////            //print_r($inst);
////            $instId = $inst['id'];
////            //echo "inst id=".$instId."<br>";
////            //$newInst = $this->params['em']->getReference('OlegUserdirectoryBundle:Institution', $instId);
////            $newInst = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($instId);
////
////            if( !$newInst ) {
////                return;
////            }
////
////            $inst['id'] = $newInst->getId();
////            $inst['parent'] = $newInst->getParent()->getId();
////
////            $event->setData($inst);
////        });
//
//
//    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Institution',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_institutiontype';
    }
}
