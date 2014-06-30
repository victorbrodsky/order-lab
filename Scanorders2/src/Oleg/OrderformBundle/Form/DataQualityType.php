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

//        $builder->add( 'description', null, array(
//            'label'=>"MRN-ACCESSION CONFLICT",
//            'attr'=>array('class'=>'dataquality-description-class textarea form-control')
//        ));

        $builder->add( 'accession', null, array(
            'label'=>false
        ));

        $builder->add( 'newaccession', null, array(
            'label'=>false
        ));

if(0) {
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
                'required' => true,
                //'attr' => array('required'=>'required')
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

        //accession
        $builder->add( 'accession', $type, array(
            'label'=>$labelAccession,
            'attr'=>array('class'=>'dataquality-accession-class form-control form-control-modif')
        ));

        //accession type
//        $builder->add( 'accessiontype', $type, array(
//            'label'=>$labelAccessiontype,
//            'attr'=>array('class'=>'dataquality-accessiontype-class')
//        ));
        $attr = array('class' => 'dataquality-accessiontype-class', 'style'=>'display:none;'); //ajax-combobox combobox combobox-width accessiontype-combobox
        $options = array(
            'label' => false,
            'required' => true,
            'attr' => $attr,
            'classtype' => 'accessiontype',
        );
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') {
            $options['data'] = 1; //new
        }
        $builder->add('accessiontype', 'custom_selector', $options);


        //mrn
        $builder->add( 'mrn', $type, array(
            'label'=>$labelMrn,
            'attr'=>array('class'=>'dataquality-mrn-class form-control form-control-modif')
        ));


        //mrn types
//        $builder->add( 'mrntype', $type, array(
//            'label'=>$labelMrntype,
//            'attr'=>array('class'=>'dataquality-mrntype-class')
//        ));
        $attr = array('class' => 'dataquality-mrntype-class', 'style'=>'display:none;'); //ajax-combobox combobox combobox-width mrntype-combobox
        $options = array(
            'label' => false,
            'required' => true,
            'attr' => $attr,
            'classtype' => 'mrntype',
        );
        if($this->params['cicle'] == "" || $this->params['cicle'] == 'new' || $this->params['cicle'] == 'create') {
            $options['data'] = 1; //new
        }
        $builder->add('mrntype', 'custom_selector', $options);
}

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
