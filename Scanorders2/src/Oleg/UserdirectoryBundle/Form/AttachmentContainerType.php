<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class AttachmentContainerType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('documentContainers', 'collection', array(
            'type' => new DocumentContainerType($this->params),
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentcontainer__',
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\AttachmentContainer',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_attachmentcontainertype';
    }
}
