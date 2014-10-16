<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class DocumentType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id', 'hidden', array(
            'label' => false,
            'attr' => array('class' => 'file-upload-id')
        ));
//
//        $builder->add('uniquename','hidden',array(
//            'attr' => array('class' => 'file-upload-uniquename')
//        ));
//
//        $builder->add('uploadDirectory','hidden',array(
//            'attr' => array('class' => 'file-upload-uploaddirectory')
//        ));
//
//        $builder->add('size','hidden',array(
//            'attr' => array('class' => 'file-upload-size')
//        ));

//        $builder->add('originalname', null, array(
//            'label' => 'File:',
//            'attr' => array('class' => 'form-control'),
//            'required' => false
//        ));

//        $builder->add('newdocument', 'hidden', array(
//            'mapped' => false,
//            'label' => false,
//            'required' => false
//        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Document',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_document';
    }
}
