<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DataQualityType extends AbstractType
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

        $builder->add( 'description', 'hidden', array(
            'label'=>false,
            'attr'=>array('class'=>'dataquality-description-class')
        ));

        $builder->add( 'accession', 'hidden', array(
            'label'=>false,
            'attr'=>array('class'=>'dataquality-accession-class')
        ));

        $builder->add( 'mrn', 'hidden', array(
            'label'=>false,
            'attr'=>array('class'=>'dataquality-mrn-class')
        ));

        $builder->add( 'mrntype', 'hidden', array(
            'label'=>false,
            'attr'=>array('class'=>'dataquality-mrntype-class')
        ));


        $builder->add( 'btnoption', 'choice', array(
            'label'=>'MRN-ACCESSION CONFLICT',
            'choices' => array("OPTION1"=>"TEXT1", "OPTION2"=>"TEXT2", "OPTION3"=>"TEXT3"),
            'multiple' => false,
            'expanded' => true,
            'mapped' => false,
            'attr' => array('required'=>'required')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\DataQuality',
        ));
    }

    public function getName()
    {
        return 'dataquality';
    }
}
