<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SlideTypeType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('type')
            ->add('creator')
            ->add('original', null, array('attr' => array('class' => 'combobox combobox-width')))
        ;

        $builder->add( 'createdate', 'date', array(
            'label'=>'Creation Date:',
            'widget' => 'single_text',
            'required'=>false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker'),
        ));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\SlideType'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_slidetype';
    }
}
