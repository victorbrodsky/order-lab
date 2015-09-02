<?php

namespace Oleg\FellAppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class FellAppTrainingType extends AbstractType
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


        $builder->add('startDate', 'date', array(
            'label' => 'Start Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('completionDate', 'date', array(
            'label' => 'Completion Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));


        $builder->add('degree', null, array(
            'label' => 'Degree:',
            'attr' => array('class'=>'combobox combobox-width ajax-combobox-trainingdegree')
        ));

        $builder->add('majors', 'employees_custom_selector', array(
            'label' => 'Major:',
            'attr' => array('class' => 'ajax-combobox-trainingmajors', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'trainingmajors'
        ));

        $builder->add('institution', 'employees_custom_selector', array(
            'label' => 'Educational Institution:',
            'attr' => array('class' => 'ajax-combobox-traininginstitution', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'traininginstitution'
        ));

        //residencySpecialty
        $builder->add('residencySpecialty', 'employees_custom_selector', array(
            'label' => 'Residency Specialty:',
            'attr' => array('class' => 'ajax-combobox-residencyspecialty', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'residencyspecialty'
        ));

        //jobTitle
        $builder->add('jobTitle', 'employees_custom_selector', array(
            'label' => 'Job or Experience Title:',
            'attr' => array('class' => 'ajax-combobox-jobtitle', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'jobTitle'
        ));



    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Training',
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_training';
    }
}
