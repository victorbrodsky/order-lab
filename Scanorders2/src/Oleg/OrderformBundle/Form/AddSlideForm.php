<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Oleg\OrderformBundle\Helper as Helper;

class AddSlideForm extends AbstractType {
    /**
     * Builds the AddSlideForm form
     * @param  \Symfony\Component\Form\FormBuilder $builder
     * @param  array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $helper = new Helper\FormHelper();
        
        $builder->add('id', 'hidden');
        $builder->add('accession', 'text', array('required'=>true));
        //$builder->add('stain', 'text', array('required'=>false));
        $builder->add('stain', 'choice', array(                 
                'choices' => $helper->getStains()        
        ));
        $builder->add('mag', 'choice', array(        
            'choices' => $helper->getMags()
        ));       
        $builder->add('diagnosis', 'textarea', array('max_length'=>10000,'required'=>false));
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
