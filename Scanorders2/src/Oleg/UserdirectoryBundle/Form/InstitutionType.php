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
            'label' => 'ID:',   //'ID:'
            'attr' => array('class' => 'tree-node-id'),
        ));

        $builder->add('parent',null,array(
            'label' => 'Parent:',
            'attr' => array('class' => 'tree-node-parent'),
        ));

        $builder->add('institutionnode', 'employees_custom_selector', array(
            'mapped' => false,
            'label' => 'Institution:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
            'classtype' => 'institution'
        ));


        ///////////// ids breadcrumbs /////////////
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $institution = $event->getData();
            $form = $event->getForm();

            $children = array();
            if( $institution ) {
                $children = $institution->getIdBreadcrumbs();
            }

            $form->add('breadcrumbs', null, array(
                'mapped' => false,
                'data' => implode(',',$children),
                'label' => false,
                'attr' => array('class' => 'tree-node-breadcrumbs'),
            ));

        });
        ///////////// EOF mag /////////////


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
