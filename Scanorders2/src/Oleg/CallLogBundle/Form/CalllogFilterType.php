<?php

namespace Oleg\CallLogBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CalllogFilterType extends AbstractType
{

    private $params;

    public function __construct( $params=null )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('startDate', 'datetime', array(
            'label' => false, //'Start Date',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control', 'placeholder'=>'Start Date'), //'title'=>'Start Year', 'data-toggle'=>'tooltip',
        ));

        $builder->add('endDate', 'datetime', array(
            'label' => false, //'Start Date',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class'=>'datepicker form-control', 'placeholder'=>'End Date'), //'title'=>'End Year', 'data-toggle'=>'tooltip',
        ));

//        $builder->add('filter', 'choice', array(
//            'label' => false,
//            'required'=> false,
//            //'multiple' => false,
//            'choices' => $this->params['fellTypes'],
//            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty-filter'),
//        ));
        
//        $builder->add('search', 'text', array(
//            //'placeholder' => 'Search',
//            'max_length'=>200,
//            'required'=>false,
//            'label' => false,
//            'attr' => array('class'=>'form-control form-control-modif limit-font-size submit-on-enter-field'),
//        ));
        
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'filter';
    }
}
