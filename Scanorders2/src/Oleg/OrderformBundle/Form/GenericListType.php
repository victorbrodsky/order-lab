<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GenericListType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $classEntity = "Oleg\\OrderformBundle\\Entity\\".$this->params['className'];

        if( $this->params['className'] == "Status" ) {
            $builder
                ->add('action',null,array(
                    'label'=>'Action:',
                    'attr' => array('class' => 'combobox combobox-width')
                ));
        }

        if( $this->params['className'] == "Roles" ) {
            $builder
                ->add('alias',null,array(
                    'label'=>'Alias:',
                    'attr' => array('class' => 'combobox combobox-width')
                ));
        }

        $builder->add('list', new ListType($this->params), array(
            'data_class' => $classEntity,
            'label' => false
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => "Oleg\\OrderformBundle\\Entity\\".$this->params['className']
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_'.strtolower($this->params['className']);
    }
}
