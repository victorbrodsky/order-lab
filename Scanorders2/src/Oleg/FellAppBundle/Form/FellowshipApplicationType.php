<?php

namespace Oleg\FellAppBundle\Form;


use Oleg\UserdirectoryBundle\Form\BoardCertificationType;
use Oleg\UserdirectoryBundle\Form\CitizenshipType;
use Oleg\UserdirectoryBundle\Form\DataTransformer\StringToBooleanTransformer;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Oleg\UserdirectoryBundle\Form\ExaminationType;
use Oleg\UserdirectoryBundle\Form\LocationType;
use Oleg\UserdirectoryBundle\Form\StateLicenseType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class FellowshipApplicationType extends AbstractType
{

    protected $params;
    protected $entity;

    public function __construct( $params=null, $entity = null )
    {
        $this->params = $params;
        $this->entity = $entity;

        //print_r($this->params);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('fellowshipSubspecialty',null, array(
            'label' => '* Fellowship Type:',
            'required' => false,
            'attr' => array('class' => 'combobox combobox-width fellapp-fellowshipSubspecialty'),
        ));

        if( $this->params['cycle'] == "new" ) {
            $builder->add('timestamp','date',array(
                'widget' => 'single_text',
                'label' => "Application Receipt Date:",
                //'format' => 'MM/dd/yyyy, H:mm:ss',
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control'),
                'required' => false,
            ));
        }

        $builder->add('startDate','date',array(
            'widget' => 'single_text',
            'label' => "Start Date:",
            'format' => 'MM/dd/yyyy',  //'MM/dd/yyyy, H:mm:ss',
            'attr' => array('class' => 'datepicker form-control fellapp-startDate'),
            'required' => false,
        ));

        $builder->add('endDate','date',array(
            'widget' => 'single_text',
            'label' => "End Date:",
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control fellapp-endDate'),
            'required' => false,
        ));

        $builder->add('user', new FellAppUserType($this->params), array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'label' => false,
            'required' => false,
        ));


        $builder->add('coverLetters', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Cover Letter(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));

        $builder->add('cvs', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Curriculum Vitae (CV):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));


    //        $builder->add('reprimand','choice', array(
    //            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
    //            'required' => false,
    //            'choices' => array('Yes'=>'Yes','No'=>'No'),
    //            'attr' => array('class' => 'combobox'),
    //        ));
        $builder->add('reprimand', 'checkbox', array(
            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
            'required' => false,
            'attr' => array('class' => 'form-control fellapp-reprimand-field', 'onclick' => 'showHideWell(this)'),
        ));
        $builder->get('reprimand')->addModelTransformer(new StringToBooleanTransformer());
        $builder->add('reprimandDocuments', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Upload Reprimand Explanation(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));

    //        $builder->add('lawsuit','choice', array(
    //            'label' => 'Have you ever been reprimanded, or had your license suspended or revoked in any of these states?',
    //            'required' => false,
    //            'choices' => array('Yes'=>'Yes','No'=>'No'),
    //            'attr' => array('class' => 'combobox'),
    //        ));
        $builder->add('lawsuit', 'checkbox', array(
            'label' => 'Have you ever been named in (and/or had a judgment against you) in a medical malpractice legal suit?',
            'required' => false,
            'attr' => array('class' => 'form-control fellapp-lawsuit-field', 'onclick' => 'showHideWell(this)'),
        ));
        $builder->get('lawsuit')->addModelTransformer(new StringToBooleanTransformer());
        $builder->add('lawsuitDocuments', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Upload Legal Explanation(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));


        $builder->add('references', 'collection', array(
            'type' => new ReferenceType($this->params),
            'label' => 'Reference(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__references__',
        ));


        $builder->add('honors',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('publications',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('memberships',null,array(
            'required' => false,
            'label'=>false,
            'attr' => array('class'=>'textarea form-control')
        ));



        $builder->add('signatureName',null, array(
            'label' => 'Signature:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('signatureDate', null, array(
            'label' => 'Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));


        $builder->add('reports', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Report(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));

        $builder->add('formReports', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Form Report(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));


        $builder->add('oldReports', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Old Report(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));


        //other documents
        $builder->add('documents', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Other Document(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));

        $builder->add('itinerarys', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Itinerary / Interview Schedule(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));


        $builder->add('interviewDate', null, array(
            'label' => 'Interview Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('interviews', 'collection', array(
            'type' => new InterviewType($this->params),
            'label' => 'Interview(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__interviews__',
        ));

        $builder->add( 'observers', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Observer(s):",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));


        /////////////////// user objects ////////////////////////////

        $builder->add('avatars', 'collection', array(
            'type' => new DocumentType($this->params),
            'label' => 'Applicant Photo(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documents__',
        ));

        $builder->add('trainings', 'collection', array(
            'type' => new FellAppTrainingType($this->params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__trainings__',
        ));

        $this->userLocations($builder);

        $builder->add('citizenships', 'collection', array(
            'type' => new CitizenshipType($this->params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__citizenships__',
        ));

        $builder->add('examinations', 'collection', array(
            'type' => new ExaminationType($this->params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__examinations__',
        ));

        $builder->add('stateLicenses', 'collection', array(
            'type' => new StateLicenseType($this->params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__statelicenses__',
        ));

        $builder->add('boardCertifications', 'collection', array(
            'type' => new BoardCertificationType(),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__boardcertifications__',
        ));

        //////////////////////////////////////////////////////////////


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\FellAppBundle\Entity\FellowshipApplication',
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'oleg_fellappbundle_fellowshipapplication';
    }



    public function userLocations($builder) {


        if( $this->params['sc']->isGranted('ROLE_FELLAPP_COORDINATOR') ) {
            $roleAdmin = true;
            $readonly = true;
        } else {
            $roleAdmin = false;
            $readonly = false;
        }
        //echo "readonly=".$readonly."<br>";
        $readonly = false;

        $currentUser = false;
        $user = $this->params['sc']->getToken()->getUser();
        if( $user->getId() === $this->params['user']->getId() ) {
            $currentUser = true;
        }
        //echo "currentUser=".$currentUser."<br>";


        $params = array('read_only'=>$readonly,'admin'=>$roleAdmin,'currentUser'=>$currentUser,'cycle'=>$this->params['cycle'],'em'=>$this->params['em'],'subjectUser'=>$this->params['user']);

        $builder->add('locations', 'collection', array(
            'type' => new FellAppLocationType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__locations__',
        ));

        return $builder;
    }

}
