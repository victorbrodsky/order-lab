<?php

namespace Oleg\VacReqBundle\Form;


use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Oleg\VacReqBundle\Form\VacReqRequestBusinessType;


class VacReqRequestType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


//        ///////////////////////// tree node /////////////////////////
//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
//            $title = $event->getData();
//            $form = $event->getForm();
//
//            $label = null;
//            if( $title ) {
//                $institution = $title->getInstitution();
//                if( $institution ) {
//                    $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels($institution) . ":";
//                }
//            }
//            if( !$label ) {
//                $label = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->getLevelLabels(null) . ":";
//            }
//            //echo "label=".$label."<br>";
//
//            $form->add('institution', 'employees_custom_selector', array(
//                'label' => $label,
//                'required' => false,
//                //'attr' => array('class' => 'ajax-combobox-institution', 'type' => 'hidden'),
//                'attr' => array(
//                    'class' => 'ajax-combobox-compositetree',
//                    'type' => 'hidden',
//                    'data-compositetree-bundlename' => 'UserdirectoryBundle',
//                    'data-compositetree-classname' => 'Institution'
//                ),
//                'classtype' => 'institution'
//            ));
//        });
//        ///////////////////////// EOF tree node /////////////////////////

//        if( $this->params['cycle'] != 'new' ) {
//            $builder->add('status', 'choice', array(
//                'disabled' => ($this->params['roleAdmin'] ? true : false),
//                'choices' => array(
//                    'pending' => 'pending',
//                    'approved' => 'approved',
//                    'declined' => 'declined'
//                ),
//                'label' => "Status:",
//                'required' => true,
//                'attr' => array('class' => 'combobox combobox-width'),
//            ));
//        }

        $builder->add('availabilities', null, array(
            'label' => "Availability(s):",
            'attr' => array('class' => 'combobox combobox-width'),
        ));

//        $builder->add('emergencyCellPhone', null, array(
//            'label' => "Cell Phone:",
//            'attr' => array('class' => 'form-control'),
//        ));
//
//        $builder->add('emergencyOther', null, array(
//            'label' => "Other:",
//            'attr' => array('class' => 'form-control'),
//        ));

        $builder->add('emergencyComment', null, array(
            'label' => "Emergency Comment:",
            'attr' => array('class' => 'form-control'),
        ));

        //Business Travel
        $builder->add('requestBusiness', new VacReqRequestBusinessType($this->params), array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestBusiness',
            'label' => false,
            'required' => false,
        ));

        //Business Travel
        $builder->add('requestVacation', new VacReqRequestVacationType($this->params), array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequestVacation',
            'label' => false,
            'required' => false,
        ));

        if( $this->params['cycle'] != 'show' ) {
//            $builder->add('user', null, array(
//                //'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
//                'read_only' => ($this->params['roleAdmin'] ? false : true),
//                'label' => "Requester:",
//                'required' => true,
//                'attr' => array('class' => 'combobox combobox-width vacreq-user')
//            ));

            $builder->add('user', 'entity', array(
                'class' => 'OlegUserdirectoryBundle:User',
                'label' => "Requester:",
                'required' => true,
                'multiple' => false,
                //'property' => 'name',
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->leftJoin("user.infos","infos")
                        ->leftJoin("user.employmentStatus", "employmentStatus")
                        ->leftJoin("employmentStatus.employmentType", "employmentType")
                        ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                        ->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                        ->orderBy("infos.lastName","ASC");
                },
            ));
        }

        //add organizational group <-> institution
//        $builder->add('institution', 'choice', array(
//            'data_class' => 'Oleg\UserdirectoryBundle\Entity\Institution',
//            //'class' => 'OlegUserdirectoryBundle:Institution',
//            //'property' => 'getUserNameStr',
//            'label' => "Organizational Group",
//            'required' => false,
//            //'multiple' => false,
//            'attr' => array('class' => 'combobox combobox-width vacreq-institution', 'placeholder' => 'Organizational Group'),
//            'choices' => $this->params['organizationalInstitution'],
//        ));
//        $builder->add('organizationalInstitution', 'choice', array(
//            //'class' => 'OlegUserdirectoryBundle:Institution',
//            //'property' => 'getUserNameStr',
//            'mapped' => false,
//            'label' => "Organizational Group",
//            'required' => true,
//            //'read_only' => ($this->params['roleAdmin'] ? false : true),
//            //'multiple' => false,
//            'attr' => array('class' => 'combobox combobox-width vacreq-institution', 'placeholder' => 'Organizational Group'),
//            'choices' => $this->params['organizationalInstitution'],
//        ));
        $builder->add('institution', 'choice', array(
            'label' => "Organizational Group:",
            'required' => true,
            'attr' => array('class' => 'combobox combobox-width vacreq-institution', 'placeholder' => 'Organizational Group'),
            'choices' => $this->params['organizationalInstitution'],
        ));
        $builder->get('institution')
            ->addModelTransformer(new CallbackTransformer(
                //original from DB to form: institutionObject to institutionId
                function($originalInstitution) {
                    //echo "originalInstitution=".$originalInstitution."<br>";
                    if( is_object($originalInstitution) && $originalInstitution->getId() ) { //object
                        return $originalInstitution->getId();
                    }
                    return $originalInstitution; //id
//                    if( $originalInstitution ) { //id
//                        $institutionObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($originalInstitution);
//                        return $institutionObject;
//                    }
//                    return null;
                },
                //reverse from form to DB: institutionId to institutionObject
                function($submittedInstitutionObject) {
                    echo "submittedInstitutionObject=".$submittedInstitutionObject."<br>";
                    if( $submittedInstitutionObject ) { //id
                        $institutionObject = $this->params['em']->getRepository('OlegUserdirectoryBundle:Institution')->find($submittedInstitutionObject);
                        return $institutionObject;
                    }
                    return null;
//                    if( is_object($submittedInstitutionObject) && $submittedInstitutionObject->getId() ) { //object
//                        return $submittedInstitutionObject->getId();
//                    }
//                    return $submittedInstitutionObject; //id
                    //return $submittedInstitutionObject->getId();
                }
            ))
        ;

//        $builder->add('approver', null, array(
//            //'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
//            'label' => "Approver:",
//            'required' => false,
//            'attr' => array('class' => 'combobox combobox-width vacreq-approver')
//        ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\VacReqBundle\Entity\VacReqRequest',
        ));
    }

    public function getName()
    {
        return 'oleg_vacreqbundle_request';
    }
}
