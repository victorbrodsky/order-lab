<?php

namespace App\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        //$user_tz = $this->params['user']->getPreferences()->getTimezone();

        if( $this->params['cycle'] != 'new' ) {

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
                //'disabled' => true,
                'format' => 'MM/dd/yyyy',
                'html5' => false, //Solution to the exception: Using a custom format when the "html5" option is enabled is deprecated since Symfony 4.3 and will lead to an exception in 5.0
                'attr' => array('class' => 'datepicker form-control'),
                'required' => false,
            ));

            $builder->add('stateComment', null, array(
                'label' => "Status Comment:",
                'attr' => array('class'=>'textarea form-control')
            ));
        }

//        if( $this->params['cycle'] == 'review' ) {
//            $builder->add('irbExpirationDate',DateType::class,array(
//                'label' => false,
//                'attr' => array('class'=>'datepicker form-control transres-irbExpirationDate')
//            ));
//        }

        if(
            $this->params['cycle'] != 'new' &&
            //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') &&
            $this->params['trpAdmin'] && 
            $this->project->getCreateDate()
        ) {
            $builder->add('createDate', DateType::class, array(
                'widget' => 'single_text',
                'label' => "Submission Date:",
                'disabled' => true,
                'format' => 'MM/dd/yyyy',
                'html5' => false,
                'attr' => array('class' => 'datepicker form-control', 'readonly' => true),
                'required' => false,
            ));

            if(
                $this->params['cycle'] == 'edit' &&
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN')
                $this->params['trpAdmin']
            ) {
                //enable edit submitter for admin
                $builder->add('submitter', null, array(
                    'label' => "Submitted By:",
                    //'disabled' => true,
                    'attr' => array('class' => 'combobox combobox-width')
                ));
            } else {
                $builder->add('submitter', null, array(
                    'label' => "Submitted By:", 
                    'disabled' => true,
                    'attr' => array('class' => 'combobox combobox-width', 'readonly' => true)
                ));
            }


            $builder->add('projectSpecialty', EntityType::class, array(
                'class' => 'AppTranslationalResearchBundle:SpecialtyList',
                'choice_label' => 'name',
                'label' => 'Project Specialty:',
                'disabled' => ($this->params['admin'] ? false : true),
                //'disabled' => true,
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width'),
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist", "ASC")
                        ->setParameters(array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
            ));
        }

        //visible only to TRP Admin, TRP Tech, Deputy Platform Admin, and Platform Admin
        if( 1 ) {
            if (
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_TECHNICIAN')
                $this->params['trpAdmin'] ||
                $this->params['trpTech']
            ) {
                //if( $this->params['cycle'] == "new" ) {
                if( 1 ) {
                    $builder->add('priceList', EntityType::class, array(
                        'class' => 'AppTranslationalResearchBundle:PriceTypeList',
                        'choice_label' => 'name',
                        'label' => 'Utilize the following specific price list:',
                        'disabled' => ($this->params['admin'] ? false : true),
                        //'disabled' => true,
                        'required' => false,
                        'multiple' => false,
                        'attr' => array('class' => 'combobox combobox-width transres-project-priceList'),
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('list')
                                ->where("list.type = :typedef OR list.type = :typeadd")
                                ->orderBy("list.orderinlist", "ASC")
                                ->setParameters(array(
                                    'typedef' => 'default',
                                    'typeadd' => 'user-added',
                                ))
                                ;
                        },
                    ));
                } else {
                    $builder->add('priceList', EntityType::class, array(
                        'class' => 'AppTranslationalResearchBundle:PriceTypeList',
                        'choice_label' => 'name',
                        'label' => 'Utilize the following specific price list:',
                        'disabled' => ($this->params['admin'] ? false : true),
                        //'disabled' => true,
                        'required' => false,
                        'multiple' => false,
                        'attr' => array('class' => 'combobox combobox-width'),
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('list')
                                ->orderBy("list.orderinlist", "ASC")
                                ;
                        },
                    ));
                }


            }
        }

        //////////// Project fields ////////////
        $builder->add('title',null,array(
            'required' => false,
            'label'=>"Title:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('irbNumber',null, array(
            'label' => $this->params['transresUtil']->getHumanName().' Number:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('funded',CheckboxType::class,array(
            'required' => false,
            'label'=>"Funded:",
            'attr' => array('class'=>'form-control transres-funded')
        ));

        $builder->add('fundedAccountNumber',null, array(
            'label' => 'If funded, please provide account number:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('irbExpirationDate',DateType::class,array(
            'widget' => 'single_text',
            'label' => $this->params['transresUtil']->getHumanName()." Expiration Date:",
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            //'view_timezone' => $user_tz,
            //'model_timezone' => $user_tz,
            'attr' => array('class' => 'datepicker form-control transres-project-irbExpirationDate'),
            'required' => false,
        ));

        if( $this->params['cycle'] != 'new' && $this->params['admin'] ) {
            $builder->add('expectedExpirationDate', DateType::class, array(
                'widget' => 'single_text',
                'label' => "Expected Expiration Date (for non-funded project only):",
                'format' => 'MM/dd/yyyy',
                'html5' => false,
                //'view_timezone' => $user_tz,
                //'model_timezone' => $user_tz,
                'attr' => array('class' => 'datepicker form-control transres-project-expectedExpirationDate'),
                'required' => false,
            ));

            //visible only to the TRP admin role (or the relevant Admin role per project type - CTP,  MISI) and only on the Edit/View pages
            //$this->params['transresUtil']
            if( $this->params['admin'] ) {
                $builder->add('reasonForStatusChange', null, array(
                    'label' => "Reason for status change or closure:",
                    'attr' => array('class' => 'textarea form-control')
                ));
            }
        }

        ///////////////// Hide 7 fields (from $budgetSummary to $expectedCompletionDate) ///////////////////
        if(0) {
            $builder->add('budgetSummary', null, array(
                'label' => "Provide a Detailed Budget Outline/Summary:",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('hypothesis',null,array(
                'label' => "Hypothesis:",
                'attr' => array('class'=>'textarea form-control')
            ));

            $builder->add('objective',null,array(
                'label' => "Objective:",
                'attr' => array('class'=>'textarea form-control')
            ));

            $builder->add('numberOfCases',TextType::class,array(
                'label' => "Number of Cases:",
                'required' => false,
                'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
            ));

            $builder->add('numberOfCohorts',TextType::class,array(
                'label' => "Number of Cohorts:",
                'required' => false,
                'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
            ));

            $builder->add('expectedResults',null,array(
                'label' => "Expected Results:",
                'attr' => array('class'=>'textarea form-control')
            ));

            $builder->add('expectedCompletionDate',null,array(
                'label' => "Expected Completion Date:",
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'html5' => false,
                'attr' => array('class'=>'datepicker form-control')
            ));
        }
        ///////////////// EOF Hide the fields (from $budgetSummary to $expectedCompletionDate) ///////////////////

        $builder->add('description',null,array(
            'label' => "Brief Description:",
            'attr' => array('class'=>'textarea form-control')
        ));

        $builder->add('totalCost', null, array(
            'label' => 'Estimated Total Costs ($) ('.$this->params['feeScheduleLink'].'):',
            'required' => true,
            //'attr' => array('class' => 'form-control', 'data-inputmask' => "'alias': 'currency'", 'style'=>'text-align: left !important;' )
            //'attr' => array('class' => 'form-control currency-mask mask-text-align-left'),
            'attr' => array('class' => 'form-control currency-mask-without-prefix'),
        ));


        if( $this->params['cycle'] != "new" ) {
            $builder->add('approvedProjectBudget', null, array(
                'label' => 'Approved Project Budget ($):',
                'required' => false,
                'attr' => array('class' => 'form-control currency-mask-without-prefix transres-project-approvedProjectBudget'),
            ));

            if(
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_COMMITTEE_REVIEWER')
                $this->params['trpAdmin'] ||
                $this->params['trpCommitteeReviewer']
            ) {
                $builder->add('noBudgetLimit', CheckboxType::class, array(
                    'label' => 'No Budget Limit:',
                    'required' => false,
                    'attr' => array('class' => 'form-control transres-project-noBudgetLimit'),
                ));
            }
        }

        $builder->add('projectType', CustomSelectorType::class, array(
            'label' => 'Project Type:',
            'required' => false,
            'attr' => array('class' => 'ajax-combobox-transresprojecttypes', 'type' => 'hidden'),
            'classtype' => 'transresprojecttypes'
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist", "ASC")
//                    ->setParameters(array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
        ));

        $builder->add('exemptIrbApproval', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:IrbApprovalTypeList',
            'label' => 'Is this project exempt from '.$this->params['transresUtil']->getHumanName().' approval?:',
            'required' => true,
            'attr' => array('class' => 'combobox transres-project-exemptIrbApproval'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));


        $builder->add('exemptIACUCApproval', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:IrbApprovalTypeList',
            'label' => 'Is this project exempt from '.$this->params['transresUtil']->getAnimalName().' approval?:',
            'required' => true,
            'attr' => array('class' => 'combobox transres-project-exemptIACUCApproval'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        $builder->add('iacucNumber',null, array(
            'label' => $this->params['transresUtil']->getAnimalName().' Number:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('iacucExpirationDate',DateType::class,array(
            'widget' => 'single_text',
            'label' => $this->params['transresUtil']->getAnimalName()." Expiration Date:",
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            //'view_timezone' => $user_tz,
            //'model_timezone' => $user_tz,
            'attr' => array('class' => 'datepicker form-control transres-project-iacucExpirationDate'),
            'required' => false,
        ));
        //////////// EOF Project fields ////////////


        $addUserOnFly = "";
        if( $this->params['cycle'] == "new" || $this->params['cycle'] == "edit" ) {
            $sitename = "'translationalresearch'";
            $otherUserParam = "'".$this->params['otherUserParam']."'";

            //Original
            //$addUserOnFly = ' (<a href="javascript:void(0)" onclick="addNewUserOnFly(this,' . $sitename . ','.$otherUserParam.');">Add New</a>)';

            //Original modal with "Loading..."
            //$addUserOnFly = ' (<a href="javascript:void(0)" data-toggle="modal" data-target="#new-user-temp-modal" onclick="addNewUserOnFly(this,' . $sitename . ','.$otherUserParam.');">Add New</a>)';

            //$addUserOnFly = ' (<a href="javascript:void(0)" data-toggle="modal" data-target="#user-add-new-user">Add New</a>)';

            //Preloaded
            $addUserOnFly = ' (<a href="javascript:void(0)" onclick="constructNewUserModal(this,' . $sitename . ','.$otherUserParam.');">Add New</a>)';
        }

        $builder->add( 'principalInvestigators', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "Principal Investigator(s) for the project$addUserOnFly:",
            'required'=> true,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam'=>$this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add( 'principalIrbInvestigator', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "Principal Investigator listed on the ".$this->params['transresUtil']->getHumanName()." application$addUserOnFly:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam'=>$this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add( 'coInvestigators', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "Co-Investigator(s)$addUserOnFly:",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam'=>$this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add( 'pathologists', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> $this->params['institutionName']." Pathologist(s) Involved$addUserOnFly:",
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam'=>$this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add( 'contacts', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "Contact(s)$addUserOnFly:",
            'required'=> true,
            'multiple' => true,
            'attr' => array('class'=>'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam'=>$this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add( 'billingContact', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "Billing Contact$addUserOnFly:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam'=>$this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
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
                    'data_class' => 'App\TranslationalResearchBundle\Entity\IrbReview',
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
                    'data_class' => 'App\TranslationalResearchBundle\Entity\AdminReview',
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
                    'data_class' => 'App\TranslationalResearchBundle\Entity\CommitteeReview',
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
                    'data_class' => 'App\TranslationalResearchBundle\Entity\FinalReview',
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


        if( 0 && $this->params['cycle'] != 'show' && $this->params['cycle'] != 'review' ) { //
            /////////////////////////////////////// messageCategory ///////////////////////////////////////
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $message = $event->getData();
                $form = $event->getForm();
                $messageCategory = null;

                $label = null;
                $mapper = array(
                    'prefix' => "App",
                    'className' => "MessageCategory",
                    'bundleName' => "OrderformBundle",
                    'organizationalGroupType' => "MessageTypeClassifiers"
                );
                if ($message) {
                    $messageCategory = $message->getMessageCategory();
                    if ($messageCategory) {
                        $label = $this->params['em']->getRepository('AppOrderformBundle:MessageCategory')->getLevelLabels($messageCategory, $mapper);
                    }
                }
                if (!$label) {
                    $label = $this->params['em']->getRepository('AppOrderformBundle:MessageCategory')->getLevelLabels(null, $mapper);
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

        if( $this->params['cycle'] == 'new' || $this->params['cycle'] == 'edit' ) {
            $builder->add('irbApprovalLetters', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'label' => $this->params['transresUtil']->getHumanName().' Approval Letter:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));
        }

        $builder->add('humanTissueForms', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Human Tissue Form:',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        //involveHumanTissue
        $builder->add('involveHumanTissue', ChoiceType::class, array( //flipped
            'label' => 'Will this project involve human tissue?',
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            //'choices_as_values' => true,
            'multiple' => false,
            'required' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type involveHumanTissue')
        ));


        //Histology Tissue Procurement/Processing
        $builder->add('requireTissueProcessing',ChoiceType::class,array(
            'label' => "Will this project require tissue procurement/processing?:",
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            'multiple' => false,
            'required' => true,
            'expanded' => true,
            'attr' => array('class'=>'horizontal_type requireTissueProcessing')
        ));
        $builder->add('totalNumberOfPatientsProcessing',TextType::class,array(
            'label' => "Total number of patients:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('totalNumberOfSpecimensProcessing',TextType::class,array(
            'label' => "Total number of patient cases:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('tissueNumberOfBlocksPerCase',TextType::class,array(
            'label' => "Number of blocks per case:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add( 'tissueProcessingServices', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:TissueProcessingServiceList',
            'label'=>'Services:',
            'required'=>false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type tissueProcessingServices'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            }
        ));

        //Archival Specimens
        $builder->add('requireArchivalProcessing',ChoiceType::class,array(
            'label' => "Will this project require archival specimens?:",
            'choices' => array("Yes"=>"Yes", "No"=>"No"),
            'multiple' => false,
            'required' => true,
            'expanded' => true,
            'attr' => array('class'=>'horizontal_type requireArchivalProcessing')
        ));
        $builder->add('totalNumberOfPatientsArchival',TextType::class,array(
            'label' => "Total number of patients:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('totalNumberOfSpecimensArchival',TextType::class,array(
            'label' => "Total number of patient cases:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('totalNumberOfBlocksPerCase',TextType::class,array(
            'label' => "Number of blocks per case:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('quantityOfSlidesPerBlockStained',TextType::class,array(
            'label' => "Quantity of slides per block - stained:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('quantityOfSlidesPerBlockUnstained',TextType::class,array(
            'label' => "Quantity of slides per block - unstained:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('quantityOfSlidesPerBlockUnstainedIHC',TextType::class,array(
            'label' => "Quantity of slides per block - unstained for IHC:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('quantityOfSpecialStainsPerBlock',TextType::class,array(
            'label' => "Quantity of special stains per block:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('quantityOfParaffinSectionsRnaDnaPerBlock',TextType::class,array(
            'label' => "Quantity of paraffin sections for RNA/DNA (Tube) per block:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('quantityOfTmaCoresRnaDnaAnalysisPerBlock',TextType::class,array(
            'label' => "Quantity of TMA cores for RNA/DNA analysis (Tube) per block:",
            'required' => false,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left')
        ));
        $builder->add( 'restrictedServices', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:OtherRequestedServiceList',
            'label'=>'Other Requested Services:',
            'required'=>false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type restrictedServices'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            }
        ));

        $builder->add('tissueFormComment',null,array(
            'label' => "Comment:",
            'required' => false,
            'attr' => array('class'=>'form-control textarea')
        ));


        if( $this->params['saveAsDraft'] === true ) {
            $builder->add('saveAsDraft', SubmitType::class, array(
                'label' => 'Save Draft Project Request',
                //'attr' => array('class' => 'btn btn-warning', 'onclick'=>'transresValidateProjectForm();')
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
                'label' => 'Submit for Review', //'Submit Irb Review',
                //'attr' => array('class'=>'btn btn-warning', 'onclick'=>'transresValidateProjectForm();')
                'attr' => array('class'=>'btn btn-success')
            ));
        }
        if( $this->params['reSubmitReview'] === true ) {
            $builder->add('reSubmitReview', SubmitType::class, array(
                'label' => 'Save Changes and Resubmit Project',
                'attr' => array('class'=>'btn btn-success')
            ));

            $builder->add('reSubmitReviewComment',TextareaType::class,array(
                'label' => "Submitter's comment associated with resubmitting:",
                'required' => false,
                'mapped' => false,
                'attr' => array('class'=>'form-control textarea')
            ));
        }
        if( $this->params['updateProject'] === true ) {
            $builder->add('updateProject', SubmitType::class, array(
                'label' => 'Save Changes',  //'Update Project',
                //'attr' => array('class'=>'btn btn-warning', 'onclick'=>'transresValidateProjectForm();')
                'attr' => array('class'=>'btn btn-warning', 'onclick'=>'transresSubmitBtnRegister("updateProject");')
            ));
        }

//        //////////////////// Project Closure/Reactivation ////////////////////
//        if( $this->params['cycle'] != 'new' ) {
//            $builder->add('closureReason', null, array(
//                'label' => 'Reason for project closure:',
//                'required' => false,
//                'attr' => array('class' => 'form-control'),
//            ));
//
//            $builder->add('reactivationReason', null, array(
//                'label' => 'Reason for project reactivation:',
//                'required' => false,
//                'attr' => array('class' => 'form-control'),
//            ));
//
//            if( $this->params['SecurityAuthChecker']->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
//                $builder->add('targetState',ChoiceType::class, array(
//                    'label' => 'Target Status:',
//                    'required' => false,
//                    'disabled' => $this->params['disabledState'],
//                    'choices' => $this->params['stateChoiceArr'],
//                    'attr' => array('class' => 'combobox'),
//                ));
//
//                $builder->add( 'targetStateRequester', EntityType::class, array(
//                    'class' => 'AppUserdirectoryBundle:User',
//                    'label'=> "Target Status Requester:",
//                    'required'=> false,
//                    'multiple' => false,
//                    'attr' => array('class'=>'combobox combobox-width'),
//                    'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
//                ));
//            }
//        }
//        //////////////////// EOF Project Closure/Reactivation ////////////////////

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\Project',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_translationalresearchbundle_project';
    }

}
