<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class BaseTitleType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('id','hidden',array('label'=>false));

        $builder->add( 'name', 'text', array(
            'label'=>$this->params['label'].':',   //'Admnistrative Title:',
            'required'=>false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('startDate', 'date', array(
            'label' => $this->params['label']." Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask allow-future-date'),
        ));

        $builder->add('endDate', 'date', array(
            'label' => $this->params['label']." End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control patientdob-mask allow-future-date'),
        ));

        $builder->add('status', 'choice', array(
            'disabled' => ($this->params['read_only'] ? true : false),
            'choices'   => array(
                '0'   => 'Unverified',
                '1' => 'Verified'
            ),
            'label' => "Status:",
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width'),
        ));

        //priority
        $builder->add('priority', 'choice', array(
            'choices'   => array(
                '0'   => 'Primary',
                '1' => 'Secondary'
            ),
            'label' => "Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));

//        $builder->add('institution', null, array(
//            'label' => "Institution:",
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//        ));
        $attr = array('class' => 'ajax-combobox-institution', 'type' => 'hidden');    //new
        $builder->add('institution', 'custom_selector', array(
            'label' => 'Institution(s):',
            'attr' => $attr,
            'required' => false,
            'classtype' => 'institution'
        ));

        $builder->add('department', null, array(
            'label' => "Department:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));
        $builder->add('division', null, array(
            'label' => "Division:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));
        $builder->add('service', null, array(
            'label' => "Service:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->params['fullClassName'],
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_'.$this->params['formname'];
    }
}
