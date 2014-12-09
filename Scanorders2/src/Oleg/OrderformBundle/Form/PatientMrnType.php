<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PatientMrnType extends AbstractType
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

        if( $this->params['type'] == 'One-Slide Scan Order') {
            $attr['style'] = 'width:100%';
            $mrnTypeLabel = "MRN Type:";
            //$gen_attr = array('label'=>false,'class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        } else {
            $mrnTypeLabel = false;
            //$gen_attr = array('label'=>'Accession Number [or Label]','class'=>'Oleg\OrderformBundle\Entity\AccessionAccession','type'=>null);
        }

        $builder->add( 'field', 'text', array(
            'label'=>'MRN',
            'required'=>false,
            'attr' => array('class' => 'form-control keyfield patientmrn-mask')
        ));

//        $attr = array('class' => 'combobox combobox-width mrntype-combobox');
//        $builder->add('keytype', 'entity', array(
//            'class' => 'OlegOrderformBundle:MrnType',
//            'label'=>false, //'MRN Type',
//            'required' => true,
//            'attr' => $attr,
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('s')
//                    ->orderBy('s.id', 'ASC');
//            },
//        ));

        //mrn types
        $attr = array('class' => 'ajax-combobox combobox combobox-width mrntype-combobox', 'type' => 'hidden'); //combobox
        $options = array(
            'label' => $mrnTypeLabel,
            'required' => true,
            'attr' => $attr,
            'classtype' => 'mrntype',
        );

        if($this->params['cycle'] == "" || $this->params['cycle'] == 'new' || $this->params['cycle'] == 'create') {
            $options['data'] = 1; //new
        }

        $builder->add('keytype', 'custom_selector', $options);

        //other fields from abstract
        $builder->add('mrnothers', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_mrntype';
    }
}
