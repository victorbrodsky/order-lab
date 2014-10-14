<?php

namespace Oleg\UserdirectoryBundle\Form;


use Oleg\UserdirectoryBundle\Entity\PrivateComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class BaseCommentsType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'comment', 'textarea', array(
            'label'=>'Comment:',
            'read_only' => $this->params['read_only'],
            'required'=>false,
            'attr' => array('class' => 'textarea form-control')
        ));

        if( $this->params['fullClassName'] == "Oleg\UserdirectoryBundle\Entity\PrivateComment" ) {
            $baseAttr = new PrivateComment();
            $builder->add('status', 'choice', array(
                'disabled' => ($this->params['read_only'] ? true : false),
                'choices'   => array(
                    $baseAttr::STATUS_UNVERIFIED => $baseAttr->getStatusStrByStatus($baseAttr::STATUS_UNVERIFIED),
                    $baseAttr::STATUS_VERIFIED => $baseAttr->getStatusStrByStatus($baseAttr::STATUS_VERIFIED)
                ),
                'label' => "Status:",
                'required' => true,
                'attr' => array('class' => 'combobox combobox-width'),
            ));
        }


        $builder->add('commentType', 'employees_custom_selector', array(
            'label' => 'Comment Category:',
            'attr' => array('class' => 'ajax-combobox-commenttype', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'commentType'
        ));


        $builder->add('commentSubType', 'employees_custom_selector', array(
            'label' => 'Comment Name:',
            'attr' => array('class' => 'ajax-combobox-commentsubtype', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'commentSubType'
        ));


        //comment's category (type)
//        $builder->add('commentType', 'collection', array(
//            'type' => new CommentTypeType($this->params),
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'label' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__commenttype__',
//        ));

//        $builder->add('commentType', new CommentTypeType($this->params), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\CommentTypeList',
//            //'label' => false,
//            'required' => false
//        ));

//        $builder->add('commentSubType', new CommentSubtypeType($this->params), array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\CommentSubTypeList',
//            //'label' => false,
//            'required' => false
//        ));



    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->params['fullClassName'],
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_'.$this->params['formname'];
    }
}
