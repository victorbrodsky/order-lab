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

        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create' || $this->params['cicle'] == 'amend' ) {

            $type = 'hidden';
            $labelDescr = false;
            $labelAccession = false;
            $labelAccessiontype = false;
            //$labelNewaccession = false;
            $labelMrn = false;
            $labelMrntype = false;

            $builder->add( 'btnoption', 'choice', array(
                'label'=>'MRN-ACCESSION CONFLICT',
                'choices' => array("OPTION1"=>"TEXT1", "OPTION2"=>"TEXT2", "OPTION3"=>"TEXT3"),
                'multiple' => false,
                'expanded' => true,
                'mapped' => false,
                'attr' => array('required'=>'required')
            ));

        } else {

            $type = null;
            $labelDescr = "MRN-ACCESSION CONFLICT";
            $labelAccession = "Conflict Accession#";
            $labelAccessiontype = "Conflict Accession Type";
            //$labelNewaccession = "New Assigned Accession#";
            $labelMrn = "Conflict MRN";
            $labelMrntype = "Conflict MRN Type";

        }

        $builder->add( 'description', $type, array(
            'label'=>$labelDescr,
            'attr'=>array('class'=>'dataquality-description-class textarea form-control')
        ));

        $builder->add( 'accession', $type, array(
            'label'=>$labelAccession,
            'attr'=>array('class'=>'dataquality-accession-class form-control form-control-modif')
        ));

        $builder->add( 'accessiontype', $type, array(
            'label'=>$labelAccessiontype,
            'attr'=>array('class'=>'dataquality-accessiontype-class')
        ));

//        $builder->add( 'newaccession', $type, array(
//            'label'=>$labelNewaccession,
//            'attr'=>array('class'=>'dataquality-accession-class form-control form-control-modif')
//        ));

        $builder->add( 'mrn', $type, array(
            'label'=>$labelMrn,
            'attr'=>array('class'=>'dataquality-mrn-class form-control form-control-modif')
        ));

        $builder->add( 'mrntype', $type, array(
            'label'=>$labelMrntype,
            'attr'=>array('class'=>'dataquality-mrntype-class')
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
