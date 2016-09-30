<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class ExternalIdType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        if( $params ) $this->params = $params;
        if( $entity ) $this->entity = $entity;
    }
        
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('sourceSystem', 'entity', array(
            'label' => "External ID Source System:",
            'required'=> false,
            'multiple' => false,
            'class' => 'OlegUserdirectoryBundle:SourceSystemList',
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add('externalId', null, array(
            'label' => "External ID:",
            'required'=> false,
            'attr' => array('class' => 'form-control')
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ExternalId'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_externalidtype';
    }
}
