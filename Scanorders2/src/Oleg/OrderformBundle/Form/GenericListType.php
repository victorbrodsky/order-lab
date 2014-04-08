<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GenericListType extends AbstractType
{

    protected $params;
    protected $className;

    public function __construct( $className, $params )
    {
        $this->params = $params;
        $this->className = $className;
    }

        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $classEntity = "Oleg\\OrderformBundle\\Entity\\".$this->className;
        $attr = array('class' => 'combobox combobox-width');

        if( $this->className == "Status" ) {
            $builder
                ->add('action',null,array(
                    'label'=>'Action:',
                    'attr' => array('class' => 'combobox combobox-width')
                ));
        }

        if( $this->className == "Roles" ) {
            $builder
                ->add('alias',null,array(
                    'label'=>'Alias:',
                    'attr' => array('class' => 'combobox combobox-width')
                ));
        }

        if( array_key_exists('synonyms', $this->params)) {
            $builder
                ->add('synonyms',null,array(
                    'label'=>'Synonyms:',
                    //'multiple' => false,
                    'required' => false,
                    'attr' => array('class' => 'combobox combobox-width')
                ));
        }

        $builder->add('list', new ListType(), array(
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
            'data_class' => "Oleg\\OrderformBundle\\Entity\\".$this->className
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_'.strtolower($this->className);
    }
}
