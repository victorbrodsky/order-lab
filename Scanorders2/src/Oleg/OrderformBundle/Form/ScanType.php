<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('mag', 'text', array('max_length'=>100,'required'=>true));
        $builder->add('scanregion', 'text', array('max_length'=>200,'required'=>false));
        
        $builder->add( 'slide', new SlideType(), array(
                //'data_class' => null,
        ));
        
        $builder->add('note', 'textarea', array(
                'max_length'=>5000,
                'required'=>false,
                'label'=>'Reason for Scan/Note:',
                //'attr'=>array('readonly'=>true)
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Scan'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_scantype';
    }
}
