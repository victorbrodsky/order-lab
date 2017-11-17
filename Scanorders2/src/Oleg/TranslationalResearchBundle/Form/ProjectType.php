<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{

    protected $project;
    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;

        $this->project = $params['project'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //$builder->add('createDate')->add('updateDate')->add('state')->add('title')->add('irbNumber')->add('startDate')->add('expirationDate')
        //->add('funded')->add('fundedAccountNumber')->add('description')->add('budgetSummary')->add('totalCost')->add('projectType')
        //->add('biostatisticalComment')->add('administratorComment')->add('primaryReviewerComment')->add('submitter')->add('updateUser')
        //->add('principalInvestigators')->add('coInvestigators')->add('pathologists')->add('irbSubmitter')->add('contact');

        //TODO: disable all fields if routeName == "translationalresearch_project_review"

        if( $this->params['cycle'] != 'new' ) {

//            $builder->add('primaryReviewerComment',null,array(
//                'label' => "Primary Reviewer Comment:",
//                'attr' => array('class'=>'textarea form-control')
//            ));

//            $builder->add('state',null, array(
//                'label' => 'State:',
//                'disabled' => $this->params['disabledState'],
//                'required' => false,
//                'attr' => array('class' => 'form-control'),
//            ));
            $builder->add('state',ChoiceType::class, array(
                'label' => 'Status:',
                'required' => false,
                'disabled' => $this->params['disabledState'],
                'choices' => $this->params['stateChoiceArr'],
                'attr' => array('class' => 'combobox'),
            ));

            $builder->add('approvalDate', DateType::class, array(
                'widget' => 'single_text',
                'label' => "Approval Date:",
                'disabled' => true,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control'),
                'required' => false,
            ));
        }

//        if( $this->params['cycle'] == 'review' ) {
//            $builder->add('irbExpirationDate',DateType::class,array(
//                'label' => false,
//                'attr' => array('class'=>'datepicker form-control transres-irbExpirationDate')
//            ));
//        }

        if( $this->project->getCreateDate() ) {
            $builder->add('createDate', DateType::class, array(
                'widget' => 'single_text',
                'label' => "Create Date:",
                'disabled' => true,
                'format' => 'MM/dd/yyyy',
                'attr' => array('class' => 'datepicker form-control', 'readonly'=>true),
                'required' => false,
            ));

            $builder->add('submitter', null, array(
                'label' => "Created By:",
                'disabled' => true,
                'attr' => array('class'=>'combobox combobox-width', 'readonly'=>true)
            ));
        }

        $builder->add( 'projectSpecialty', EntityType::class, array(
            'class' => 'OlegTranslationalResearchBundle:SpecialtyList',
            'choice_label' => 'name',
            'label'=>'Project Specialty:',
            //'disabled' => ($this->params['admin'] ? false : true),
            'disabled' => true,
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

//        $builder->add('oid', null, array(
//            'label' => "Project ID:",
//            'disabled' => ($this->params['admin'] ? false : true),
//            'attr' => array('class'=>'form-control')
//        ));

//        if( $this->project->getUpdateDate() ) {
//            $builder->add('updateDatCreate Date:e', 'date', array(
//                'widget' => 'single_text',
//                'label' => "Update Date:",
//                'disabled' => true,
//                'format' => 'MM/dd/yyyy',
//                'attr' => array('class' => 'datepicker form-control'),
//                'required' => false,
//            ));
//        }
//        if( $this->project->getUpdateUser() ) {
//            $builder->add('updateUser', null, array(
//                'label' => "Updated By:",
//                'disabled' => true,
//            ));
//        }

//        $builder->add('title',null,array(
//            'required' => false,
//            'label'=>"Project Title:",
//            'attr' => array('class'=>'textarea form-control')
//        ));
//
//        $builder->add('irbNumber',null, array(
//            'label' => 'IRB Number:',
//            'required' => false,
//            'attr' => array('class' => 'form-control'),
//        ));

//        $builder->add('startDate','date',array(
//            'widget' => 'single_text',
//            'label' => "Project Start Date:",
//            'format' => 'MM/dd/yyyy',
//            'attr' => array('class' => 'datepicker form-control'),
//            'required' => false,
//        ));
//
//        $builder->add('expirationDate','date',array(
//            'widget' => 'single_text',
//            'label' => "Project Expiration Date:",
//            'format' => 'MM/dd/yyyy',
//            'attr' => array('class' => 'datepicker form-control'),
//            'required' => false,
//        ));

//        $builder->add('funded',CheckboxType::class,array(
//            'required' => false,
//            'label'=>"Is this Research Project Funded:",
//            'attr' => array('class'=>'form-control')
//        ));
//
//        $builder->add('fundedAccountNumber',null, array(
//            'label' => 'If funded, please provide account number:',
//            'required' => false,
//            'attr' => array('class' => 'form-control'),
//        ));

//        $descriptionLabel =
//            "Please provide a brief description of the project to include background information,
//            purpose and objective, and a methodology section stating a justification for
//            the size and scope of the project. The breadth of information
//            should be adequate for a scientific committee to understand and assess the value of the research.";
//        $descriptionLabel = "Brief Description";
//        $builder->add('description',null,array(
//            'label' => $descriptionLabel,
//            'attr' => array('class'=>'textarea form-control') //,'style'=>'height:300px'
//        ));

//        $builder->add('budgetSummary',null,array(
//            'label' => "Provide a Detailed Budget Outline/Summary:",
//            'attr' => array('class'=>'textarea form-control')
//        ));

//        $builder->add('totalCost',null, array(
//            'label' => 'Estimated Total Costs ($):',
//            'required' => false,
//            //'attr' => array('class' => 'form-control', 'data-inputmask' => "'alias': 'currency'", 'style'=>'text-align: left !important;' )
//            'attr' => array('class' => 'form-control currency-mask mask-text-align-left'),
//        ));

//        $builder->add('projectType',null, array(
//            'label' => 'Project Type:',
//            'required' => false,
//            'attr' => array('class' => 'form-control'),
//        ));

//        $builder->add('biostatisticalComment',null,array(
//            'label' => "Biostatistical Comment:",
//            'attr' => array('class'=>'textarea form-control')
//        ));
//
//        $builder->add('administratorComment',null,array(
//            'label' => "Administrator Comment:",
//            'attr' => array('class'=>'textarea form-control')
//        ));

//        $builder->add('readyForReview', CheckboxType::class, array(
//            'required' => false,
//            'label' => "Please check the box if this project is ready for committee to review:",
//            'attr' => array('class' => 'form-control')
//        ));

        $builder->add( 'principalInvestigators', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Principal Investigator(s):",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add( 'coInvestigators', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Co-Investigator(s):",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

//        $builder->add( 'irbSubmitter', EntityType::class, array(
//            'class' => 'OlegUserdirectoryBundle:User',
//            'label'=> "Name of PI Who Submitted the IRB:",
//            'required'=> false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->leftJoin("list.employmentStatus", "employmentStatus")
//                    ->leftJoin("employmentStatus.employmentType", "employmentType")
//                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
//                    ->leftJoin("list.infos", "infos")
//                    ->orderBy("infos.displayName","ASC");
//            },
//        ));

        $builder->add( 'pathologists', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "WCMC Pathologist(s) Involved:",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add( 'contacts', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Contact(s):",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add( 'billingContacts', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Billing Contact(s):",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        //Reviews
        //echo "showIrbReviewer=".$this->params['showIrbReviewer']."<br>";
        if( $this->params['showIrbReviewer'] ) {
            //echo "show irb_review<br>";
            $this->params['stateStr'] = "irb_review";
            $this->params['standAlone'] = false;
            $builder->add('irbReviews', CollectionType::class, array(
                'entry_type' => ReviewBaseType::class,
                'entry_options' => array(
                    'data_class' => 'Oleg\TranslationalResearchBundle\Entity\IrbReview',
                    'form_custom_value' => $this->params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__irbreviews__',
            ));
        }

        if( $this->params['showAdminReviewer'] ) {
            //echo "show admin_review<br>";
            $this->params['stateStr'] = "admin_review";
            $this->params['standAlone'] = false;
            $builder->add('adminReviews', CollectionType::class, array(
                'entry_type' => ReviewBaseType::class,
                'entry_options' => array(
                    'data_class' => 'Oleg\TranslationalResearchBundle\Entity\AdminReview',
                    'form_custom_value' => $this->params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__adminreviews__',
            ));
        }

        if( $this->params['showCommitteeReviewer'] ) {
            //echo "show committee_review<br>";
            $this->params['stateStr'] = "committee_review";
            $this->params['standAlone'] = false;
            $builder->add('committeeReviews', CollectionType::class, array(
                'entry_type' => ReviewBaseType::class,
                'entry_options' => array(
                    'data_class' => 'Oleg\TranslationalResearchBundle\Entity\CommitteeReview',
                    'form_custom_value' => $this->params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__committeereviews__',
            ));
        }

        if( $this->params['showFinalReviewer'] ) {
            //echo "show final_review<br>";
            $this->params['stateStr'] = "final_review";
            $this->params['standAlone'] = false;
            $builder->add('finalReviews', CollectionType::class, array(
                'entry_type' => ReviewBaseType::class,
                'entry_options' => array(
                    'data_class' => 'Oleg\TranslationalResearchBundle\Entity\FinalReview',
                    'form_custom_value' => $this->params
                ),
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__finalreviews__',
            ));
        }


        if( $this->params['cycle'] != 'show' && $this->params['cycle'] != 'review' ) { //
            /////////////////////////////////////// messageCategory ///////////////////////////////////////
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $message = $event->getData();
                $form = $event->getForm();
                $messageCategory = null;

                $label = null;
                $mapper = array(
                    'prefix' => "Oleg",
                    'className' => "MessageCategory",
                    'bundleName' => "OrderformBundle",
                    'organizationalGroupType' => "MessageTypeClassifiers"
                );
                if ($message) {
                    $messageCategory = $message->getMessageCategory();
                    if ($messageCategory) {
                        $label = $this->params['em']->getRepository('OlegOrderformBundle:MessageCategory')->getLevelLabels($messageCategory, $mapper);
                    }
                }
                if (!$label) {
                    $label = $this->params['em']->getRepository('OlegOrderformBundle:MessageCategory')->getLevelLabels(null, $mapper);
                }

                if ($label) {
                    $label = $label . ":";
                }

                //echo "show defaultInstitution label=".$label."<br>";

                $form->add('messageCategory', CustomSelectorType::class, array(
                    'label' => $label,
                    'required' => false,
                    //'read_only' => true, //this depracted and replaced by readonly in attr
                    //'disabled' => true, //this disabled all children
                    'attr' => array(
                        'readonly' => true,
                        'class' => 'ajax-combobox-compositetree combobox-without-add combobox-compositetree-postfix-level combobox-compositetree-read-only-exclusion ajax-combobox-messageCategory', //combobox-compositetree-readonly-parent
                        'type' => 'hidden',
                        'data-compositetree-bundlename' => 'OrderformBundle',
                        'data-compositetree-classname' => 'MessageCategory',
                        'data-label-prefix' => '',
                        //'data-readonly-parent-level' => '2', //readonly all children from level 2 up (including this level)
                        'data-read-only-exclusion-after-level' => '2', //readonly will be disable for all levels after indicated level
                        'data-label-postfix-value-level' => '<span style="color:red">*</span>', //postfix after level
                        'data-label-postfix-level' => '4', //postfix after level "Issue"
                    ),
                    'classtype' => 'messageCategory'
                ));


                //add form node fields
                //$form = $this->addFormNodes($form,$messageCategory,$this->params);

            });
            /////////////////////////////////////// EOF messageCategory ///////////////////////////////////////
        }//if


        $builder->add('documents', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Document(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));


        if( $this->params['saveAsDraft'] === true ) {
            $builder->add('saveAsDraft', SubmitType::class, array(
                'label' => 'Save Project as Draft',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }
//        if( $this->params['saveAsComplete'] === true ) {
//            $builder->add('saveAsComplete', SubmitType::class, array(
//                'label' => 'Complete Submission',
//                'attr' => array('class'=>'btn btn-warning')
//            ));
//        }
        if( $this->params['submitIrbReview'] === true ) {
            $builder->add('submitIrbReview', SubmitType::class, array(
                'label' => 'Submit Irb Review',
                'attr' => array('class'=>'btn btn-warning')
            ));
        }
        if( $this->params['updateProject'] === true ) {
            $builder->add('updateProject', SubmitType::class, array(
                'label' => 'Update Project',
                'attr' => array('class'=>'btn btn-warning')
            ));
        }

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\TranslationalResearchBundle\Entity\Project',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_project';
    }


}
