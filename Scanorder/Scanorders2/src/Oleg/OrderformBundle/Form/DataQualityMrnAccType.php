<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DataQualityMrnAccType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $type = 'text'; //'hidden';

        $builder->add( 'btnoption', 'choice', array(
            'label'=>'MRN-ACCESSION CONFLICT',
            'choices' => array("OPTION1"=>"TEXT1", "OPTION2"=>"TEXT2", "OPTION3"=>"TEXT3"),
            'multiple' => false,
            'expanded' => true,
            'mapped' => false,
            'required' => true,
            'attr' => array('required'=>'required')
        ));

        //description
        $builder->add( 'description', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));

        //accession
        $builder->add( 'accession', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));

        //accession type
        $builder->add( 'accessiontype', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));

        //mrn
        $builder->add( 'mrn', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));


        //mrn types
        $builder->add( 'mrntype', $type, array(
            'label'=>false,
            'required' => false,
            'attr' => array('style'=>'display:none;')
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
//        $resolver->setDefaults(array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\DataQualityMrnAcc',
//        ));
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'dataqualitymrnacc';
    }
}
