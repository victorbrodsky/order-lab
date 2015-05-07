<?php

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EncounterPatmiddlenameType extends AbstractType
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
            'label'=>"Patient's Middle Name (at the time of encounter):",
            'required' => false,
            'attr' => array('class' => 'form-control form-control-modif encounter-middleName')
        ));

        $builder->add('alias', 'checkbox', array(
            'required' => false,
            'label' => 'Alias',
        ));

        $builder->add('others', new ArrayFieldType(), array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterPatmiddlename',
            'label' => false,
			'attr' => array('style'=>'display:none;')
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterPatmiddlename',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounterpatmiddlename';
    }
}
