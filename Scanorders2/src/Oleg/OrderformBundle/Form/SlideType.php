<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oleg\OrderformBundle\Helper as Helper;

class SlideType extends AbstractType {
    /**
     * Builds the SlideType form
     * @param  \Symfony\Component\Form\FormBuilder $builder
     * @param  array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $helper = new Helper\FormHelper();
        
        $builder->add('id', 'hidden');
        
//        $builder->add('accession', 'text', array('max_length'=>100,'required'=>true));
        
        $builder->add( 'accession', new AccessionType(), array('label'=>' ') );
        
        $builder->add( 'part', new PartType(), array('label'=>' ') );
        
        $builder->add( 'block', new BlockType(), array('label'=>' ') );
        
        $builder->add( 'orderinfo', new OrderInfoType(), array('label'=>' ') );              
        
        $builder->add('stain', 'choice', array(                 
                'choices' => $helper->getStains(),
                'required'=>false,
                'label'=>'Stain:',
                //'empty_value'=>'H&E',
                //'attr' => array('id' => 'stain_label_div'),
        ));
        $builder->add('mag', 'choice', array(        
            'choices' => $helper->getMags(),
            'required'=>true,
            'label'=>'* Magnification:',
            //'empty_value'=>'20X',
            //'expanded'=>true,
            //'multiple'=>false,
            //'extra_fields_message'=>'true',
        ));       
        $builder->add('diagnosis', 'textarea', array(
                'max_length'=>10000,
                'required'=>false,
                'label'=>'Diagnosis / Reason for scans:',
                //'attr'=>array('readonly'=>true)
        ));
        $builder->add('microscopicdescr', 'textarea', array('max_length'=>10000,'required'=>false));
        $builder->add('specialstain', 'text', array('max_length'=>100,'required'=>false));
        $builder->add('relevantscan', 'text', array('max_length'=>100,'required'=>false));
        $builder->add('scanregion', 'text', array('max_length'=>100,'required'=>false));       
        $builder->add('note', 'textarea', array('max_length'=>10000,'required'=>false));
    }

    /**
     * Returns the default options/class for this form.
     * @param array $options
     * @return array The default options
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Slide'
        );
    }

    /**
     * Mandatory in Symfony2
     * Gets the unique name of this form.
     * @return string
     */
    public function getName()
    {
        return 'add_slide';
    }
    
}

?>
