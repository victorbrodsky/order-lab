<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class ClinicalHistoryType extends AbstractType
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

        if( $this->entity ) {
            $label = 'Clinical History (Created by '.$this->entity->getPatient()[0]->getClinicalHistory()[0]->getCreator()." on ". $this->entity->getPatient()[0]->getClinicalHistory()[0]->getCreationdate()."):";
        } else {
            $label = 'Clinical History:';
        }


        $builder->add('clinicalHistory', 'textarea', array(
            'label' => $label,  //'Clinical History:',
            'max_length'=>10000,
            'required' => false,
            'attr' => array('class'=>'textarea form-control form-control-modif'),
        ));
        //$builder->add('creationdate');
        //$builder->add('creator');

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\ClinicalHistory',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_clinicalhistorytype';
    }
}
