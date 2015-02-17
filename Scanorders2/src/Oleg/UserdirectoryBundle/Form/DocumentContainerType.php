<?php

namespace Oleg\UserdirectoryBundle\Form;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class DocumentContainerType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {

        if( !$params || !array_key_exists('labelPrefix',$params) || !$params['labelPrefix'] ) {
            $params['labelPrefix'] = 'Image';
        }

        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id', 'hidden', array(
            'attr' => array('class' => 'documentcontainer-field-id'),
        ));

        $builder->add('documents', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => $this->params['labelPrefix'] . '(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__document__',
        ));

        $builder->add('title', null, array(
            'label' => $this->params['labelPrefix'] . ' Title:',
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('comments', 'collection', array(
            'type' => new DocumentCommentType($this->params),
            'label' => $this->params['labelPrefix'] . ' Comment(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__comments__',
        ));

        $builder->add('device', null, array(
            'label' => $this->params['labelPrefix'] . ' Device:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));

        $builder->add('datetime','date',array(
            'widget' => 'single_text',
            'format' => 'MM-dd-yyyy',   //used for day dateline (no hours), so we don't need to set view_timezone
            'attr' => array('class' => 'datepicker form-control scandeadline-mask', 'style'=>'margin-top: 0;'),
            'required' => false,
            'label'=>$this->params['labelPrefix'] . ' Date & Time:',
        ));

        $builder->add('provider', null, array(
            'label' => $this->params['labelPrefix'] . ' Scanned By:',
            'attr' => array('class' => 'combobox combobox-width'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\DocumentContainer',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_documentcontainertype';
    }
}
