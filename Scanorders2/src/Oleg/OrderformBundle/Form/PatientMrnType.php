<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



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

        $builder->add('id','hidden');

        if( $this->params['cicle'] == "show" ) {
            $builder->add('creationdate');
            $builder->add('provider');
        }

        $builder->add('field', null, array(
            'label' => "MRN",
            'required' => false,
            'attr' => array('class'=>'form-control'),
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\PatientMrn'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_mrntype'; //generic field type
    }
}
