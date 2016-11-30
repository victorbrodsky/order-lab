<?php

namespace Oleg\CallLogBundle\Form;

use Oleg\OrderformBundle\Form\ArrayFieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EncounterAttendingPhysicianType extends AbstractType
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

        $builder->add('field', 'custom_selector', array(
            'label' => 'Attending Physician:',
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-encounterAttendingPhysician'),
            'required' => false,
            'classtype' => 'singleUserWrapper'
            //'classtype' => 'userWrapper'
        ));

//        $builder->add('attendingPhysicianSpecialty', 'custom_selector', array(
//            'label' => 'Referring Provider Specialty:',
//            'attr' => array('class' => 'combobox combobox-width ajax-combobox-attendingPhysicianSpecialty'),
//            'required' => false,
//            'classtype' => 'attendingPhysicianSpecialty'
//        ));
//
//        $builder->add('attendingPhysicianPhone', null, array(
//            'label' => 'Referring Provider Phone Number:',
//            'attr' => array('class'=>'form-control')
//        ));
//
//        $builder->add('attendingPhysicianEmail', null, array(
//            'label' => 'Referring Provider E-Mail:',
//            'attr' => array('class'=>'form-control')
//        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterAttendingPhysician',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounterattendingphysiciantype';
    }
}
