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


        $builder->add('documents', 'collection', array(
            'type' => new DocumentType($this->params),
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__document__',
        ));


//        $builder->add('newdocuments', 'hidden', array(
//            'mapped' => false,
//            'label' => false,
//            'required' => false
//        ));

//        $builder->add('documents', null, array(
//            'label' => 'Uploads:',
//            'attr' => array('class' => 'form-control'),
//            'required' => false
//        ));

//        $classNameArr = explode("\\",$this->params['fullClassName']);
//        $len = count($classNameArr);
//        $shortClassName = $classNameArr[$len-1];
//        //echo "shortClassName=".$shortClassName."<br>";

//        $builder->add( 'documents', 'entity', array(
//            'disabled' => ($this->params['read_only'] ? true : false),
//            'class' => 'OlegUserdirectoryBundle:Document',
//            'property' => 'originalname',
//            'label'=>'Uploads:',
//            'required'=> false,
//            //'multiple' => false,
//            'attr' => array('class'=>'form-control')
//        ));

//        $builder->add( 'documents', 'entity', array(
//            'disabled' => ($this->params['read_only'] ? true : false),
//            'class' => 'OlegUserdirectoryBundle:'.$shortClassName,
//            //'property' => 'originalname',
//            'label'=>'Uploads:',
//            'required'=> false,
//            //'multiple' => false,
//            'attr' => array('class'=>'form-control'),
//            'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('comm')
//                        ->innerJoin('comm.documents','documents');
//                        //->where("documents = comm.documents");
//                },
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
