<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class ListType extends AbstractType
{

    protected $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('orderinlist',null,array('label'=>'Order:'))
            ->add('name',null,array('label'=>'Name:'))
            ->add('type',null,array('label'=>'Type:'))
            ->add('creator',null,array('label'=>'Creator:'))
        ;

        $builder->add( 'createdate', 'date', array(
            'label'=>'Creation Date:',
            'widget' => 'single_text',
            'required'=>false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker'),
        ));

        if( $this->params['original'] ) {
            $builder->add('original', null, array('attr' => array('class' => 'combobox combobox-width')));
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_listtype';
    }
}
