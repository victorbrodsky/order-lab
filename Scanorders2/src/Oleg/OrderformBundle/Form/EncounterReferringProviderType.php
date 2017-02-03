<?php

namespace Oleg\OrderformBundle\Form;

use Oleg\OrderformBundle\Form\ArrayFieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EncounterReferringProviderType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;


        if( !array_key_exists('referringProviders-readonly', $this->params) ) {
            $this->params['referringProviders-readonly'] = true;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

//        $builder->add('field', 'date', array(
//            'label' => "Encounter Date:",
//            'widget' => 'single_text',
//            'required' => false,
//            'format' => 'MM/dd/yyyy',   //used for birth day only (no hours), so we don't need to set view_timezone
//            'attr' => array('class' => 'datepicker form-control encounter-date', 'style'=>'margin-top: 0;'),
//        ));

//        $builder->add('others', new ArrayFieldType($this->params), array(
//            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterReferringProvider',
//            'label' => false,
//			'attr' => array('style'=>'display:none;')
//        ));

//        //extra data-structure fields
//        //echo "datastructure time <br>";
//        $builder->add('time', 'time', array(
//            'input'  => 'datetime',
//            'widget' => 'choice',
//            'label'=>'Encounter Time:'
//        ));


        $builder->add('field', 'custom_selector', array(
            'label' => 'Referring Provider Name:',
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-encounterReferringProvider'),
            'read_only' => $this->params['referringProviders-readonly'],
            'required' => false,
            'classtype' => 'singleUserWrapper'
            //'classtype' => 'userWrapper'
        ));

        $builder->add('referringProviderSpecialty', 'custom_selector', array(
            'label' => 'Referring Provider Specialty:',
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-referringProviderSpecialty'),
            'read_only' => $this->params['referringProviders-readonly'],
            'required' => false,
            'classtype' => 'referringProviderSpecialty'
        ));

        $builder->add('referringProviderPhone', null, array(
            'label' => 'Referring Provider Phone Number:',
            'read_only' => $this->params['referringProviders-readonly'],
            'attr' => array('class'=>'form-control')
        ));

        $builder->add('referringProviderEmail', null, array(
            'label' => 'Referring Provider E-Mail:',
            'attr' => array('class'=>'form-control'),
            'read_only' => $this->params['referringProviders-readonly'],
        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\EncounterReferringProvider',
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_encounterreferringprovidertype';
    }
}
