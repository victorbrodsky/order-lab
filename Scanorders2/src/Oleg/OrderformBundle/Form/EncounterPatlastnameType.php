<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EncounterPatlastnameType extends AbstractType
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

        $builder->add( 'field', 'text', array(
            'label'=>"Patient's Last Name (at the time of encounter)",
            'required' => false,
            'attr' => array('class' => 'form-control form-control-modif encounter-lastName')
        ));

        $builder->add('alias', 'checkbox', array(
            'required' => false,
            'label' => 'Alias',
            //'attr' => array('style'=>'margin:0')
        ));

        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterPatlastname',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterPatlastname',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounterpatlastname';
    }
}
