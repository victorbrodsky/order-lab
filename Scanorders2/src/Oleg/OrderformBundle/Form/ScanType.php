<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\FormHelper;

class ScanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $helper = new FormHelper();
        
        //$builder->add('mag', 'text', array('max_length'=>100,'required'=>true));
//        $builder->add('mag', 'choice', array(                 
//                'choices' => $helper->getMags(),
//                'data' => '20X',
//                'max_length' => 50,
//                'required' => true,
//                'label' => '* Magnification:',
//        ));
        $builder->add( 'mag', 
                'choice', array(  
                'label'=>'* Magnification:',
                'max_length'=>200,
                'choices' => $helper->getMags(),
                'required'=>true,
                'data' => '20X',              
        ));
        
        $builder->add('scanregion', 'text', array('max_length'=>200,'required'=>false));
        
        $builder->add( 'slide', new SlideType(), array(
                //'data_class' => null,
        ));
        
        $builder->add('note', 'textarea', array(
                'max_length'=>5000,
                'required'=>false,
                'label'=>'Reason for Scan/Note:',
                'data' => 'Interesting case',
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
