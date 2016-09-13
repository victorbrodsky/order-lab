<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class ArrayFieldType extends AbstractType
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
        $builder->add('id', 'hidden');

        if( $this->params && $this->params['cycle'] == "show") {
            $builder->add('creationdate');
            $builder->add('provider');
        }

        if( $this->params && array_key_exists('datastructure', $this->params) && $this->params['datastructure'] == 'datastructure-patient') {

            $builder->add('provider','hidden');

            $builder->add('source','hidden');

            $builder->add('status', 'choice', array(
                'choices'   => array(
                    'valid' => 'valid',
                    'invalid' => 'invalid'
                ),
                'label' => "Status:",
                'required' => true,
                'attr' => array('class' => 'combobox combobox-no-width other-status'),
            ));
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
        return 'oleg_orderformbundle_arrayfieldtype';
    }
}
