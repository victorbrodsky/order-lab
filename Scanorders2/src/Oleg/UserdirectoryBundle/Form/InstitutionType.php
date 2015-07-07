<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
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
        $this->institutionWithUserPositions = '';
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $builder->add('id', 'employees_custom_selector', array(
//            'label' => 'Institution:',
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
//            'classtype' => 'institution'
//        ));

        //hidden: set by js
//        $builder->add('id',null,array(
//            'label' => false,   //'ID:',   //'ID:'
//            'attr' => array('class' => 'tree-node-id'),
//        ));

        //hidden: set by js
//        $builder->add('parent',null,array(
//            'label' => false,   //'Parent:',
//            'attr' => array('class' => 'tree-node-parent'),
//        ));


        //////////////////////// Not mapped data providing fields ////////////////////////

        //breadcrumbs hidden: set by js
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $institution = $event->getData();
            $form = $event->getForm();

            echo "inst:".$institution."<br>";

            ////////////////// breadcrumbs //////////////////
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
            ////////////////// EOF breadcrumbs //////////////////

            $label = 'Institution:';
            if( $institution && $institution->getOrganizationalGroupType() ) {
                $label = $institution->getOrganizationalGroupType()->getName().":";
            }

            $form->add('id', 'employees_custom_selector', array(
                'label' => $label,   //'Institution:',
                'required' => false,
                'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
                'classtype' => 'institution'
            ));

            //add userPositions for 'label'=>'Administrative'
            if( array_key_exists('label', $this->params) && $this->params['label'] == 'Administrative' ) {
                //$institutionWithUserPositions = 'institution-with-userpositions';
//            $builder->add('userPositions',null,array(
//                'mapped' => false,
//                'label' => false,
//                'attr' => array('class' => 'ajax-combobox-userpositions'),
//            ));
                $this->params['treenode'] = $institution;
                $form->add('userPositions', 'collection', array(
                    'type' => new UserPositionType($this->params, null),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'by_reference' => false,
                    'prototype' => true,
                    'prototype_name' => '__userpositions__',
                ));

            } else {
                //$institutionWithUserPositions = '';
                //echo "no user pos<br>";
                $form->remove('userPositions');
            }

//            //visible as institution node combobox
//            //echo "user pos=".$institutionWithUserPositions."<br>";
//            $form->add('institutionnode', 'employees_custom_selector', array(
//                'mapped' => false,
//                'label' => 'Institution:',
//                'required' => false,
//                'attr' => array('class' => 'ajax-combobox-institution '.$institutionWithUserPositions, 'type' => 'hidden'),
//                'classtype' => 'institution'
//            ));

        });
        //////////////////////// EOF Not mapped data providing fields ////////////////////////


        //visible as institution node combobox
//        $builder->add('institutionnode', 'employees_custom_selector', array(
//            'mapped' => false,
//            'label' => 'Institution:',
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
//            'classtype' => 'institution'
//        ));


    }


    public function buildForm_ORIG(FormBuilderInterface $builder, array $options)
    {

        //hidden: set by js
        $builder->add('id',null,array(
            'label' => false,   //'ID:',   //'ID:'
            'attr' => array('class' => 'tree-node-id'),
        ));

        //hidden: set by js
        $builder->add('parent',null,array(
            'label' => false,   //'Parent:',
            'attr' => array('class' => 'tree-node-parent'),
        ));


        //////////////////////// Not mapped data providing fields ////////////////////////

        //breadcrumbs hidden: set by js
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $institution = $event->getData();
            $form = $event->getForm();

            $children = array();
            if( $institution ) {
                $children = $institution->getIdBreadcrumbs();
            }

            $form->add('breadcrumbs', 'hidden', array(
                'mapped' => false,
                'data' => implode(',',$children),
                'label' => false,
                'attr' => array('class' => 'tree-node-breadcrumbs'),
            ));

            //add userPositions for 'label'=>'Administrative'
            if( array_key_exists('label', $this->params) && $this->params['label'] == 'Administrative' ) {
                //$institutionWithUserPositions = 'institution-with-userpositions';
//            $builder->add('userPositions',null,array(
//                'mapped' => false,
//                'label' => false,
//                'attr' => array('class' => 'ajax-combobox-userpositions'),
//            ));
                $this->params['treenode'] = $institution;
                $form->add('userPositions', 'collection', array(
                    'type' => new UserPositionType($this->params, null),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'by_reference' => false,
                    'prototype' => true,
                    'prototype_name' => '__userpositions__',
                ));

            } else {
                //$institutionWithUserPositions = '';
                //echo "no user pos<br>";
                $form->remove('userPositions');
            }

//            //visible as institution node combobox
//            //echo "user pos=".$institutionWithUserPositions."<br>";
//            $form->add('institutionnode', 'employees_custom_selector', array(
//                'mapped' => false,
//                'label' => 'Institution:',
//                'required' => false,
//                'attr' => array('class' => 'ajax-combobox-institution '.$institutionWithUserPositions, 'type' => 'hidden'),
//                'classtype' => 'institution'
//            ));

        });
        //////////////////////// EOF Not mapped data providing fields ////////////////////////


        //visible as institution node combobox
        $builder->add('institutionnode', 'employees_custom_selector', array(
            'mapped' => false,
            'label' => 'Institution:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
            'classtype' => 'institution'
        ));


//        echo "with user pos=".$this->institutionWithUserPositions."<br>";
//        //visible as institution node combobox
//        $builder->add('institutionnode', 'employees_custom_selector', array(
//            'mapped' => false,
//            'label' => 'Institution:',
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-institution ' . $this->institutionWithUserPositions, 'type' => 'hidden'),
//            'classtype' => 'institution'
//        ));

//        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
//            $inst = $event->getData();
//            $form = $event->getForm();
//
//            if (!$inst) {
//                return;
//            }
//
//            //echo "inst=".$inst."<br>";
//            //print_r($inst);
//            $instId = $inst['id'];
//            //echo "inst id=".$instId."<br>";
//            //$newInst = $this->params['em']->getReference('OlegUserdirectoryBundle:Institution', $instId);
//            $newInst = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($instId);
//
//            if( !$newInst ) {
//                return;
//            }
//
//            $inst['id'] = $newInst->getId();
//            $inst['parent'] = $newInst->getParent()->getId();
//
//            $event->setData($inst);
//        });


    }

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
