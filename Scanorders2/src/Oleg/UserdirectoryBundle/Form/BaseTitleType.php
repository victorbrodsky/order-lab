<?php

namespace Oleg\UserdirectoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


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

//        $builder->add( 'name', 'text', array(
//            'label'=>$this->params['label'].' Title:',   //'Admnistrative Title:',
//            'required'=>false,
//            'attr' => array('class' => 'form-control')
//        ));
        $builder->add('name', 'employees_custom_selector', array(
            'label'=>$this->params['label'].' Title:',
            'attr' => array('class' => 'ajax-combobox-'.$this->params['formname'], 'type' => 'hidden'),
            'required' => false,
            'classtype' => $this->params['formname']
        ));

        $builder->add('startDate', 'date', array(
            'label' => $this->params['label']." Title Start Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control allow-future-date'),
        ));

        $builder->add('endDate', 'date', array(
            'label' => $this->params['label']." Title End Date:",
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control allow-future-date user-expired-end-date'),
        ));

        $baseUserAttr = new $this->params['fullClassName']();
        $builder->add('status', 'choice', array(
            'disabled' => ($this->params['read_only'] ? true : false),
            'choices'   => array(
                $baseUserAttr::STATUS_UNVERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_UNVERIFIED),
                $baseUserAttr::STATUS_VERIFIED => $baseUserAttr->getStatusStrByStatus($baseUserAttr::STATUS_VERIFIED)
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
            'label' => $this->params['label']." Title Type:",
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width'),
        ));


        ///////////////////////// tree node /////////////////////////
//        echo "base title user=".$this->params['user']."<br>";
//        $builder->add('institution', new InstitutionType($this->params), array(
//            'required' => false,
//            'label' => false    //'Institution:'
//        ));
//        $label = 'Institution:';
//        $builder->add('institution', 'employees_custom_selector', array(
//            'label' => $label,
//            'required' => false,
//            'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
//            'classtype' => 'institution'
//        ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $title = $event->getData();
            $form = $event->getForm();

            $label = 'Institution:';
            if( $title ) {
                $institution = $title->getInstitution();
                if( $institution ) {
                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
                }

            }

            $form->add('institution', 'employees_custom_selector', array(
                'label' => $label,
                'required' => false,
                //'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
                'attr' => array(
                    'class' => 'ajax-combobox-compositetree',
                    'type' => 'hidden',
                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
                    'data-compositetree-classname' => 'Institution'
                ),
                'classtype' => 'institution'
            ));
        });
        ///////////////////////// EOF tree node /////////////////////////

        $builder->add('effort', 'employees_custom_selector', array(
            'label' => 'Percent Effort:',
            'attr' => array('class' => 'ajax-combobox-effort', 'type' => 'hidden', "data-inputmask"=>"'mask': '[o]', 'repeat': 10, 'greedy' : false"),
            'required' => false,
            'classtype' => 'effort'
        ));


        if( $this->params['cycle'] != "show" ) {
            $builder->add('orderinlist',null,array(
                'label'=>'Display Order:',
                'required' => false,
                'attr' => array('class'=>'form-control')
            ));
        }

        //position, residencyTrack, fellowshipType, pgy for AppointmentTitle (Academic Appointment Title)
        if( $this->params['fullClassName'] == "Oleg\UserdirectoryBundle\Entity\AppointmentTitle" ) {

            $builder->add('position', 'choice', array(
                'choices'   => array(
                    'Resident'   => 'Resident',
                    'Fellow' => 'Fellow',
                    'Clinical Faculty' => 'Clinical Faculty',
                    'Research Faculty' => 'Research Faculty'
                ),
                'label' => "Position Track Type:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width appointmenttitle-position-field', 'onchange'=>'positionTypeAction(this)'),
            ));

            $builder->add( 'residencyTrack', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:ResidencyTrackList',
                'property' => 'name',
                'label'=>'Residency Track:',
                'required'=> false,
                'multiple' => false,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->where("list.type = :typedef OR list.type = :typeadd")
                            ->orderBy("list.orderinlist","ASC")
                            ->setParameters( array(
                                'typedef' => 'default',
                                'typeadd' => 'user-added',
                            ));
                    },
            ));

            $builder->add('fellowshipType', 'employees_custom_selector', array(
                'label' => "Fellowship Type:",
                'required' => false,
                'attr' => array('class' => 'combobox combobox-width ajax-combobox-fellowshiptype', 'type' => 'hidden'),
                'classtype' => 'fellowshiptype'
            ));

            $builder->add('pgystart', 'date', array(
                'label' => "During academic year that started on:",
                'widget' => 'single_text',
                'required' => false,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control pgystart-field', 'style'=>'margin-top: 0;'),
            ));

            $builder->add('pgylevel',null,array(
                'label'=>'The Post Graduate Year (PGY) level was:',
                'required' => false,
                'attr' => array('class'=>'form-control pgylevel-field')
            ));

            $builder->add('pgylevelexpected','integer',array(
                'label' => 'Expected Current Post Graduate Year (PGY) level:',
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'attr' => array('class'=>'form-control pgylevelexpected-field')
            ));

        }


        //boss
        if( $this->params['fullClassName'] == "Oleg\UserdirectoryBundle\Entity\AdministrativeTitle" ) {

            $builder->add('boss','entity',array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Reports to:",
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false
            ));

            $builder->add('userPositions','entity',array(
                'class' => 'OlegUserdirectoryBundle:PositionTypeList',
                'label' => "Position Type:",
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'required' => false
            ));

//            $builder->add( 'supervisorInstitution', null, array(
//                'label'=>'Head of this institution:',
//                'required'=>false,
//                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
//            ));
//
//            $builder->add( 'supervisorDepartment', null, array(
//                'label'=>'Head of this department:',
//                'required'=>false,
//                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
//            ));
//
//            $builder->add( 'supervisorDivision', null, array(
//                'label'=>'Head of this division:',
//                'required'=>false,
//                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
//            ));
//
//            $builder->add( 'supervisorService', null, array(
//                'label'=>'Head of this service:',
//                'required'=>false,
//                'attr' => array('class'=>'form-control', 'style'=>'margin:0')
//            ));

        }


        //specialties for Medical Appointment Title)
        if( $this->params['fullClassName'] == "Oleg\UserdirectoryBundle\Entity\MedicalTitle" ) {

            $builder->add( 'specialties', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:MedicalSpecialties',
                'property' => 'name',
                'label'=>'Specialty(s):',
                'required'=> false,
                'multiple' => true,
                'attr' => array('class'=>'combobox combobox-width'),
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('list')
                            ->where("list.type = :typedef OR list.type = :typeadd")
                            ->orderBy("list.orderinlist","ASC")
                            ->setParameters( array(
                                'typedef' => 'default',
                                'typeadd' => 'user-added',
                            ));
                    },
            ));

        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->params['fullClassName'],
            //'csrf_protection' => false,
            'allow_extra_fields' => true
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_'.$this->params['formname'];
    }
}
