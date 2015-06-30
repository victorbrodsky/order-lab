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
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id',null,array(
            'label' => false,   //'ID:',   //'ID:'
            'attr' => array('class' => 'tree-node-id'),
        ));

        $builder->add('parent',null,array(
            'label' => false,   //'Parent:',
            'attr' => array('class' => 'tree-node-parent'),
        ));


        //////////////////////// Not mapped data providing fields ////////////////////////
        $builder->add('institutionnode', 'employees_custom_selector', array(
            'mapped' => false,
            'label' => 'Institution:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
            'classtype' => 'institution'
        ));


        ///// ids breadcrumbs /////
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

        });
        ///// EOF breadcrumbs /////

        //////////////////////// EOF Not mapped data providing fields ////////////////////////

        //add userPositions for 'label'=>'Administrative'
        if( array_key_exists('label', $this->params) && $this->params['label'] == 'Administrative' ) {
            $builder->add('userPositions',null,array(
                'mapped' => false,
                'label' => false,
                'attr' => array('class' => 'ajax-combobox-userpositions'),
            ));
        }


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
