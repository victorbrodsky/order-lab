<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class StainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $helper = new FormHelper();      
        
        $builder->add('name', 'choice', array(                 
                'choices' => $helper->getStains(),
                'data' => 0,
                'max_length' => 200,
                'required' => true,
                'label' => '* Stain:',
        ));
        
        $builder->add('stainer', 'text', array(
            'label'=>'Stainer:',
            'max_length'=>200,
            'required'=>false
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Stain'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_staintype';
    }
}
