<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HistoryType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentid')
            ->add('newid')
            ->add('changedate')
            ->add('roles')
            ->add('note')
            ->add('provider')
            ->add('currentstatus')
            ->add('newstatus')
            ->add('viewed')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\History'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_history';
    }
}
