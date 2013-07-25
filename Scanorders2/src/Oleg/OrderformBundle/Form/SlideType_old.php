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
        
        $builder->add( 'id', 'hidden' );       
        
        $builder->add( 'accession', new AccessionType(), array('label'=>' ') );
        
        $builder->add( 'part', new PartType(), array('label'=>' ') );
        
        $builder->add( 'block', new BlockType(), array('label'=>' ') );             
        
        $builder->add( 'stain', new StainType(), array('label'=>' ') ); 
                    
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
       
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\Slide'
        ));
    }
  
    /**
     * Mandatory in Symfony2
     * Gets the unique name of this form.
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_slidetype';
    }
    
}

?>
