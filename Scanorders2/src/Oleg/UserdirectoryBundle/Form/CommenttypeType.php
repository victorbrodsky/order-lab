<?php

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Entity\CommentSubTypeList;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class CommentTypeType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $attr = array('class' => 'ajax-combobox-commenttype', 'type' => 'hidden');
        if( $this->params['read_only'] ) {
            $attr['readonly'] = 'readonly';
        }
        $builder->add('name', 'employees_custom_selector', array(
            'label' => 'Comment Category:',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'commentType'
        ));

//        //category (or type name)
//        $builder->add('name','entity',array(
//            'class' => 'OlegUserdirectoryBundle:CommentTypeList',
//            'label'=>"Comment Category:",
//            'attr' => array('class'=>'combobox combobox-width'),
//            'required' => false
//        ));

//        $builder->add('name', null, array(
//            'label' => "Comment Category:",
//            'required' => false,
//            'attr' => array('class'=>'combobox combobox-width')
//        ));


//        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') {
//            $options['data'] = 1; //new
//        }

//        //comment category
//        $builder->add('commentSubTypes', 'collection', array(
//            'type' => new CommentSubtypeType($this->params),
//            'data' => array( new CommentSubTypeList() ),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => true,
//            'label' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__commentsubtype__',
//        ));



//        $builder->addEventListener(
//
//            FormEvents::PRE_SET_DATA,
//
//            function (FormEvent $event) {
//
//                $form = $event->getForm();
//
//                // this would be your entity, i.e. SportMeetup
//                $data = $event->getData();
//
//                $options = array(
//                    'type' => new CommentSubtypeType($this->params),
//                    'allow_add' => true,
//                    'allow_delete' => true,
//                    'required' => true,
//                    'label' => false,
//                    'by_reference' => false,
//                    'prototype' => true,
//                    'prototype_name' => '__commentsubtype__',
//                );
//
//                if( !$data ) {
//                    //echo "subtypes count=".count($data->getChildren())."<br>";
//                    $options['data'] = array( new CommentSubTypeList() );
//                }
//
//                //$sport = $data->getSport();
//                //$positions = null === $sport ? array() : $sport->getAvailablePositions();
//
//                //comment category
//                $form->add('commentSubTypes', 'collection', $options);
//
//            }
//
//        ); //listener




    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\CommentTypeList',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_commenttype';
    }
}
