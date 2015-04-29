<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\UserdirectoryBundle\Entity\Training;

class TrainingType extends AbstractType
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

        $builder->add('id','hidden',array(
            'label'=>false,
            'attr' => array('class'=>'user-object-id-field')
        ));

        //status
        $baseUserAttr = new Training();
        $builder->add('status', 'choice', array(
            'disabled' => ($this->params['read_only'] ? true : false),
            'choices' => array(
                $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
                $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
            ),
            'label' => "Status:",
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width'),
        ));


        $builder->add('startDate', 'date', array(
            'label' => 'Start Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        //If value ="Graduated" display the title of the field "Completion Date" as "Graduation Date" and hide this field and title
        if( $this->params['cycle'] == 'show' ) {

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

                $training = $event->getData();
                $form = $event->getForm();

                $completionReason = NULL;

                if( $training ) {
                    $completionReason = $training->getCompletionReason();
                }

                if( $completionReason == "Graduated" ) {

                    $form->add('completionDate', 'date', array(
                        'label' => 'Graduation Date:',
                        'widget' => 'single_text',
                        'required' => false,
                        'format' => 'MM/dd/yyyy',
                        'attr' => array('class' => 'datepicker form-control'),
                    ));

                } else {

                    $form->add('completionDate', 'date', array(
                        'label' => 'Completion Date:',
                        'widget' => 'single_text',
                        'required' => false,
                        'format' => 'MM/dd/yyyy',
                        'attr' => array('class' => 'datepicker form-control'),
                    ));

                    $form->add('completionReason', null, array(
                        'label' => 'Completion Reason:',
                        'attr' => array('class'=>'combobox combobox-width')
                    ));

                }

            });

        } else {

            $builder->add('completionDate', 'date', array(
                'label' => 'Completion Date:',
                'widget' => 'single_text',
                'required' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control'),
            ));

            $builder->add('completionReason', null, array(
                'label' => 'Completion Reason:',
                'attr' => array('class'=>'combobox combobox-width')
            ));

        }

        $builder->add('degree', null, array(
            'label' => 'Degree:',
            'attr' => array('class'=>'combobox combobox-width ajax-combobox-trainingdegree')
        ));

        $builder->add('appendDegreeToName', 'checkbox', array(
            'label'     => 'Append degree to name:',
            'attr' => array('class'=>'training-field-appenddegreetoname'),
            'required'  => false,
        ));

        $builder->add('majors', 'employees_custom_selector', array(
            'label' => 'Major:',
            'attr' => array('class' => 'ajax-combobox-trainingmajors', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'trainingmajors'
        ));

        $builder->add('minors', 'employees_custom_selector', array(
            'label' => 'Minor:',
            'attr' => array('class' => 'ajax-combobox-trainingminors', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'trainingminors'
        ));

        $builder->add('honors', 'employees_custom_selector', array(
            'label' => 'Honors:',
            'attr' => array('class' => 'ajax-combobox-traininghonors', 'type' => 'hidden'),
            'required' => false,
            //'multiple' => true,
            'classtype' => 'traininghonors'
        ));

        $builder->add('fellowshipTitle', 'employees_custom_selector', array(
            'label' => 'Professional Fellowship Title:',
            'attr' => array('class' => 'ajax-combobox-trainingfellowshiptitle', 'type' => 'hidden'),
            'required' => false,
            'classtype' => 'trainingfellowshiptitle'
        ));

        $builder->add('appendFellowshipTitleToName', 'checkbox', array(
            'label'     => 'Append professional fellowship to name:',
            'required'  => false,
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
        //fellowshipSubspecialty
        $builder->add('fellowshipSubspecialty', 'employees_custom_selector', array(
            'label' => "Fellowship Subspecialty:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width ajax-combobox-fellowshipsubspecialty', 'type' => 'hidden'),
            'classtype' => 'fellowshipsubspecialty'
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
