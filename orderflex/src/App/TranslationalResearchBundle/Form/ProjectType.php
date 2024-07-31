<?php

namespace App\TranslationalResearchBundle\Form;



use App\TranslationalResearchBundle\Entity\SpecialtyList; //process.py script: replaced namespace by ::class: added use line for classname=SpecialtyList


use App\TranslationalResearchBundle\Entity\PriceTypeList; //process.py script: replaced namespace by ::class: added use line for classname=PriceTypeList


use App\TranslationalResearchBundle\Entity\ProjectTypeList; //process.py script: replaced namespace by ::class: added use line for classname=ProjectTypeList


use App\TranslationalResearchBundle\Entity\IrbApprovalTypeList; //process.py script: replaced namespace by ::class: added use line for classname=IrbApprovalTypeList


use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User


use App\TranslationalResearchBundle\Entity\TissueProcessingServiceList; //process.py script: replaced namespace by ::class: added use line for classname=TissueProcessingServiceList


use App\TranslationalResearchBundle\Entity\CollLabList; //process.py script: replaced namespace by ::class: added use line for classname=CollLabList


use App\TranslationalResearchBundle\Entity\CollDivList; //process.py script: replaced namespace by ::class: added use line for classname=CollDivList


use App\TranslationalResearchBundle\Entity\IrbStatusList; //process.py script: replaced namespace by ::class: added use line for classname=IrbStatusList


use App\TranslationalResearchBundle\Entity\RequesterGroupList; //process.py script: replaced namespace by ::class: added use line for classname=RequesterGroupList


use App\TranslationalResearchBundle\Entity\CompCategoryList; //process.py script: replaced namespace by ::class: added use line for classname=CompCategoryList


use App\TranslationalResearchBundle\Entity\OtherRequestedServiceList; //process.py script: replaced namespace by ::class: added use line for classname=OtherRequestedServiceList


use App\OrderformBundle\Entity\MessageCategory; //process.py script: replaced namespace by ::class: added use line for classname=MessageCategory
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
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

        if ($this->params['cycle'] != 'new' && $this->params['cycle'] != 'pdf') {

            $builder->add('state', ChoiceType::class, array(
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
                'attr' => array('class' => 'textarea form-control')
            ));
        }

//        if( $this->params['cycle'] == 'review' ) {
//            $builder->add('irbExpirationDate',DateType::class,array(
//                'label' => false,
//                'attr' => array('class'=>'datepicker form-control transres-irbExpirationDate')
//            ));
//        }

        if (
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

            if (
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
                'class' => SpecialtyList::class,
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
        if (1) {
            if (
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_TECHNICIAN')
                $this->params['trpAdmin'] ||
                $this->params['trpTech']
            ) {
                //if( $this->params['cycle'] == "new" ) {
                if (1) {
                    $builder->add('priceList', EntityType::class, array(
                        'class' => PriceTypeList::class,
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
                                ));
                        },
                    ));
                } else {
                    $builder->add('priceList', EntityType::class, array(
                        'class' => PriceTypeList::class,
                        'choice_label' => 'name',
                        'label' => 'Utilize the following specific price list:',
                        'disabled' => ($this->params['admin'] ? false : true),
                        //'disabled' => true,
                        'required' => false,
                        'multiple' => false,
                        'attr' => array('class' => 'combobox combobox-width'),
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('list')
                                ->orderBy("list.orderinlist", "ASC");
                        },
                    ));
                }


            }
        }

        //////////// Project fields ////////////
        $builder->add('title', null, array(
            'required' => false,
            'label' => "Title:",
            'attr' => array('class' => 'textarea form-control')
        ));


        //For MISI project submission form only, make two fields:
        // “IRB Expiration Date:“ and “IRB Approval Status:”required IF “Not exempt” is selected.
        $irbNumberRequired = false;
//        if( $this->params['project']->getProjectSpecialtyStr() == 'MISI' ) {
//            $irbNumberRequired = true;
//        }
        $builder->add('irbNumber', null, array(
            'label' => $this->params['transresUtil']->getHumanName() . ' Number:',
            'required' => $irbNumberRequired, //false,
            'attr' => array('class' => 'form-control'),
        ));
        $builder->add('irbExpirationDate', DateType::class, array(
            'widget' => 'single_text',
            'label' => $this->params['transresUtil']->getHumanName() . " Expiration Date:",
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'required' => $irbNumberRequired, //false,
            //'view_timezone' => $user_tz,
            //'model_timezone' => $user_tz,
            'attr' => array('class' => 'datepicker form-control transres-project-irbExpirationDate')
        ));

//        $builder->add('funded', CheckboxType::class, array(
//            'required' => false,
//            'label' => "Has this project been funded?:", //"Funded:",
//            //'attr' => array('class'=>'form-control transres-funded')
//            'attr' => array('class' => 'transres-funded')
//        ));
        //Hide funded for External (requestor group = External) MISI projects:
        //Hide if (MISI && external)
        //Show if (!MISI || !external)
        //RequesterGroupList abbreviation 'External' 
        if( $this->params['project']->getProjectSpecialtyStr() != 'MISI' ||
            $this->params['project']->getRequesterGroupAbbreviation() != 'external'
        ) {
            $builder->add('funded', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No, I am requesting all or some funding from the ' . $this->params['institutionName'] . ' Pathology department' => false
                ),
                'label' => 'Has this project been funded?:',
                'multiple' => false,
                'required' => false,
                'expanded' => true,
                'placeholder' => false, //to remove 'Null' set placeholder to false
                'attr' => array('class' => 'horizontal_type transres-funded')
            ));
        }

        $builder->add('fundedAccountNumber', null, array(
            'label' => 'If funded, please provide account number:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        if ($this->params['cycle'] != 'new' && $this->params['admin']) {
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
            if ($this->params['admin']) {
                $builder->add('reasonForStatusChange', null, array(
                    'label' => "Reason for status change or closure:",
                    'attr' => array('class' => 'textarea form-control')
                ));
            }
        }

        ///////////////// Hide 7 fields (from $budgetSummary to $expectedCompletionDate) ///////////////////
        if (0) {
            $builder->add('budgetSummary', null, array(
                'label' => "Provide a Detailed Budget Outline/Summary:",
                'attr' => array('class' => 'textarea form-control')
            ));

//            $builder->add('hypothesis',null,array(
//                'label' => "Hypothesis:",
//                'attr' => array('class'=>'textarea form-control')
//            ));

//            $builder->add('objective',null,array(
//                'label' => "Objective:",
//                'attr' => array('class'=>'textarea form-control')
//            ));

            $builder->add('numberOfCases', TextType::class, array(
                'label' => "Number of Cases:",
                'required' => false,
                'attr' => array('class' => 'form-control digit-mask mask-text-align-left')
            ));

            $builder->add('numberOfCohorts', TextType::class, array(
                'label' => "Number of Cohorts:",
                'required' => false,
                'attr' => array('class' => 'form-control digit-mask mask-text-align-left')
            ));

//            $builder->add('expectedResults',null,array(
//                'label' => "Expected Results:",
//                'attr' => array('class'=>'textarea form-control')
//            ));

            $builder->add('expectedCompletionDate', null, array(
                'label' => "Expected Completion Date:",
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'html5' => false,
                'attr' => array('class' => 'datepicker form-control')
            ));
        }
        ///////////////// EOF Hide the fields (from $budgetSummary to $expectedCompletionDate) ///////////////////

        $descriptionLabel = "Brief Description:";
        if( $this->params['project']->getProjectSpecialtyStr() == 'MISI' ) {
            $descriptionLabel = "Brief description (include basic information regarding your project, ".
                "approximate number of markers you want to analyze, and number and type of samples, ".
                "e.g. human or mouse, whole-slide or TMAs, neoplastic or non-neoplastic, disease model, etc.):";
        }
        $builder->add('description', null, array(
            'label' => $descriptionLabel,
            'attr' => array('class' => 'textarea form-control')
        ));

        if ($this->params['cycle'] == "new") {
            $totalCostLabel = 'Project budget amount ($) (' . $this->params['feeScheduleLink'] . '):';
        } else {
            $totalCostLabel = 'Estimated total cost / Project budget amount ($) (' . $this->params['feeScheduleLink'] . '):';
        }

        $builder->add('totalCost', null, array(
            //'label' => 'Estimated total cost / Project budget amount ($) (' . $this->params['feeScheduleLink'] . '):', //Estimated Total Costs ($)
            'label' => $totalCostLabel,
            'required' => true,
            //'attr' => array('class' => 'form-control', 'data-inputmask' => "'alias': 'currency'", 'style'=>'text-align: left !important;' )
            //'attr' => array('class' => 'form-control currency-mask mask-text-align-left'),
            'attr' => array('class' => 'form-control currency-mask-without-prefix'),
        ));


        if ($this->params['cycle'] != "new") {
            $builder->add('approvedProjectBudget', null, array(
                'label' => 'Approved Project Budget ($):',
                'required' => false,
                'attr' => array('class' => 'form-control currency-mask-without-prefix transres-project-approvedProjectBudget'),
            ));

            if (
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ||
                //$this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_COMMITTEE_REVIEWER')
                $this->params['trpAdmin'] ||
                $this->params['trpCommitteeReviewer']
            ) {
                $builder->add('noBudgetLimit', CheckboxType::class, array(
                    'label' => 'No budget limit:',
                    'required' => false,
                    'attr' => array('class' => 'transres-project-noBudgetLimit'),
                ));
            }
        }

        if ($this->params['cycle'] == "show" || $this->params['cycle'] == "review" || $this->params['cycle'] == "pdf") {
            $builder->add('projectType', EntityType::class, array(
                //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:ProjectTypeList'] by [ProjectTypeList::class]
                'class' => ProjectTypeList::class,
                'label' => 'Project Type:',
                'required' => false,
                'attr' => array('class' => 'combobox transres-project-projectType'),
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
        } else {
            //Allow to type in new project type
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
        }

        $builder->add('exemptIrbApproval', EntityType::class, array(
            'class' => IrbApprovalTypeList::class,
            'label' => 'Is this project exempt from ' . $this->params['transresUtil']->getHumanName() . ' approval?:',
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
            'class' => IrbApprovalTypeList::class,
            'label' => 'Is this project exempt from ' . $this->params['transresUtil']->getAnimalName() . ' approval?:',
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

        $builder->add('iacucNumber', null, array(
            'label' => $this->params['transresUtil']->getAnimalName() . ' Number:',
            'required' => false,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('iacucExpirationDate', DateType::class, array(
            'widget' => 'single_text',
            'label' => $this->params['transresUtil']->getAnimalName() . " Expiration Date:",
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            //'view_timezone' => $user_tz,
            //'model_timezone' => $user_tz,
            'attr' => array('class' => 'datepicker form-control transres-project-iacucExpirationDate'),
            'required' => false,
        ));
        //////////// EOF Project fields ////////////


        $addUserOnFly = "";
        if ($this->params['cycle'] == "new" || $this->params['cycle'] == "edit") {
            $sitename = "'translationalresearch'";
            $otherUserParam = "'" . $this->params['otherUserParam'] . "'";

            //Original
            //$addUserOnFly = ' (<a href="javascript:void(0)" onclick="addNewUserOnFly(this,' . $sitename . ','.$otherUserParam.');">Add New</a>)';

            //Original modal with "Loading..."
            //$addUserOnFly = ' (<a href="javascript:void(0)" data-toggle="modal" data-target="#new-user-temp-modal" onclick="addNewUserOnFly(this,' . $sitename . ','.$otherUserParam.');">Add New</a>)';

            //$addUserOnFly = ' (<a href="javascript:void(0)" data-toggle="modal" data-target="#user-add-new-user">Add New</a>)';

            //Preloaded
            $addUserOnFly = ' (<a href="javascript:void(0)" onclick="constructNewUserModal(this,' . $sitename . ',' . $otherUserParam . ');">Add New</a>)';
        }

        $builder->add('principalInvestigators', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Principal Investigator(s) for the project$addUserOnFly:",
            'required' => true,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam' => $this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add('principalIrbInvestigator', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Principal Investigator listed on the " . $this->params['transresUtil']->getHumanName() . " application$addUserOnFly:",
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam' => $this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        //Add submitInvestigators similar to coInvestigators
        $builder->add('submitInvestigators', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Submitting Investigator, if different from Principal Investigator above$addUserOnFly:",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam' => $this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add('coInvestigators', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Co-Investigator(s)$addUserOnFly:",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam' => $this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add('pathologists', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => $this->params['institutionName'] . " Pathologist(s) Involved$addUserOnFly:",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam' => $this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add('contacts', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Contact(s)$addUserOnFly:",
            'required' => true,
            'multiple' => true,
            'attr' => array('class' => 'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam' => $this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        $builder->add('billingContact', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Billing Contact$addUserOnFly:",
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam' => $this->params['otherUserParam']),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder($this->params['cycle'])
        ));

        //Reviews
        //echo "showIrbReviewer=".$this->params['showIrbReviewer']."<br>";
        if ($this->params['showIrbReviewer']) {
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

        if ($this->params['showAdminReviewer']) {
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

        if ($this->params['showCommitteeReviewer']) {
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

        if ($this->params['showFinalReviewer']) {
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


        if (0 && $this->params['cycle'] != 'show' && $this->params['cycle'] != 'review') { //
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
                    'organizationalGroupType' => "MessageTypeClassifiers",
                    'fullClassName' => "App\\OrderformBundle\\Entity\\MessageCategory",
                    'entityNamespace' => "App\\OrderformBundle\\Entity"
                );
                if ($message) {
                    $messageCategory = $message->getMessageCategory();
                    if ($messageCategory) {
                        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
                        $label = $this->params['em']->getRepository(MessageCategory::class)->getLevelLabels($messageCategory, $mapper);
                    }
                }
                if (!$label) {
                    //process.py script: replaced namespace by ::class: ['AppOrderformBundle:MessageCategory'] by [MessageCategory::class]
                    $label = $this->params['em']->getRepository(MessageCategory::class)->getLevelLabels(null, $mapper);
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

        //don't show attached document's links on pdf, these docs will be embedded on the project's pdf.
        if ($this->params['cycle'] != 'pdf') {
            //hide if Project Documents does not exists
            if ($this->params['cycle'] != 'new') {
                $projectDocuments = $this->project->getDocuments();
                if ($projectDocuments && count($projectDocuments) > 0) {
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
                }
            }

            if ($this->params['cycle'] == 'new' || $this->params['cycle'] == 'edit') {
                $builder->add('irbApprovalLetters', CollectionType::class, array(
                    'entry_type' => DocumentType::class,
                    'label' => $this->params['transresUtil']->getHumanName() . ' Approval Letter:',
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
        }

        //involveHumanTissue
        //ONLY for CP: For the following question: Will this project involve human tissue?
        //a) Change the answer “No” to “No (this project will only involve human fluid specimens or no human tissue at all)”
        if ($this->params['specialProjectSpecialty'] == true) {
            //CP project
            $requireArchivalProcessingLabel = array(
                "Yes" => "Yes",
                "No (this project will only involve human fluid specimens or no human tissue at all)" => "No"
            );
        } else {
            //All other projects
            $requireArchivalProcessingLabel = array("Yes" => "Yes", "No" => "No");
        }
        $builder->add('involveHumanTissue', ChoiceType::class, array( //flipped
            'label' => 'Will this project involve human tissue?',
            'choices' => $requireArchivalProcessingLabel, //array("Yes"=>"Yes", "No"=>"No"),
            //'choices_as_values' => true,
            'multiple' => false,
            'required' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type involveHumanTissue')
        ));


        //Histology Tissue Procurement/Processing
        $builder->add('requireTissueProcessing', ChoiceType::class, array(
            'label' => "Will this project require tissue procurement/processing?:",
            'choices' => array("Yes" => "Yes", "No" => "No"),
            'multiple' => false,
            'required' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type requireTissueProcessing')
        ));
        $builder->add('totalNumberOfPatientsProcessing', TextType::class, array(
            'label' => "Total number of patients:",
            'required' => false,
            'attr' => array('class' => 'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('totalNumberOfSpecimensProcessing', TextType::class, array(
            'label' => "Total number of patient cases:",
            'required' => false,
            'attr' => array('class' => 'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('tissueNumberOfBlocksPerCase', TextType::class, array(
            'label' => "Number of blocks per case:",
            'required' => false,
            'attr' => array('class' => 'form-control digit-mask mask-text-align-left')
        ));
        $builder->add('tissueProcessingServices', EntityType::class, array(
            'class' => TissueProcessingServiceList::class,
            'label' => 'Services:',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type tissueProcessingServices'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            }
        ));

        ////////////// Additional fields from #294 //////////////
        //ONLY for CP and AP/CP
        //echo "specialExtraProjectSpecialty=".$this->params['specialExtraProjectSpecialty']."<br>";
//        if( $this->params['specialExtraProjectSpecialty'] == true ) {
        $builder->add('collLabs', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:CollLabList'] by [CollLabList::class]
            'class' => CollLabList::class,
            'label' => 'Which labs within Clinical Pathology are you collaborating with, if any?:',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type collLabs'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            }
        ));
//        }

        //For ALL “New Project Request Forms” (not just for CP)
        $builder->add('collDivs', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:CollDivList'] by [CollDivList::class]
            'class' => CollDivList::class,
            'label' => 'Which division(s) are you collaborating with?:',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type collDivs'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            }
        ));

        $builder->add('hypothesis', null, array(
            'label' => "Hypothesis:",
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('irbStatusList', EntityType::class, array(
            //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:IrbStatusList'] by [IrbStatusList::class]
            'class' => IrbStatusList::class,
            'label' => 'IRB Approval Status:',
            'required' => false,
            'attr' => array('class' => 'combobox transres-project-irbStatusList'),
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
        $builder->add('irbStatusExplain', null, array(
            'label' => "Please explain why the IRB submission is not applicable:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control transres-project-irbStatusExplain')
        ));

        //https://stackoverflow.com/questions/39272733/boolean-values-and-choice-symfony-type
        $builder->add('needStatSupport', ChoiceType::class, array(
            'choices' => array(
                'Yes' => true,
                'No' => false
            ),
            'label' => 'Will you need departmental statistical or informatics support from the computational pathology team?:',
            'multiple' => false,
            'required' => false,
            'expanded' => true,
            'placeholder' => false, //to remove 'Null' set placeholder to false
            'attr' => array('class' => 'horizontal_type needStatSupport')
        ));
        $builder->add('amountStatSupport', null, array(
            'label' => "What is the estimated quantity of needed statistical or informatics support hours?:",
            'attr' => array('class' => 'textarea form-control')
        ));

        //F- Hide “Will you need informatics support?” question for ALL project types by default
//        $builder->add('needInfSupport', ChoiceType::class, array(
//            'choices' => array(
//                'Yes' => true,
//                'No' => false
//            ),
//            'label' => 'Will you need informatics support?:',
//            'multiple' => false,
//            'required' => false,
//            'expanded' => true,
//            'placeholder' => false, //to remove 'Null' set placeholder to false
//            'attr' => array('class' => 'horizontal_type needInfSupport')
//        ));
//        $builder->add('amountInfSupport', null, array(
//            'label' => "Please describe the data and the needed analysis:",
//            'attr' => array('class' => 'textarea form-control')
//        ));

        $builder->add('studyPopulation', null, array(
            'label' => "Study population (include a brief description such as health status or primary diagnosis):",
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('numberPatient', null, array(
            'label' => "Number of involved patients:",
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('numberLabReport', null, array(
            'label' => "Number of involved lab result reports:",
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('studyDuration', null, array(
            'label' => "Projected grant or other closest deadline date for completion of this project:",
            'widget' => 'single_text',
            'format' => 'MM/dd/yyyy',
            'html5' => false,
            'attr' => array('class' => 'datepicker form-control')
        ));

        //For MISI project submission form ONLY
        if( $this->params['project']->getProjectSpecialtyStr() == 'MISI' ) {
            $builder->add('timeframe', null, array(
                'label' => "Timeframe description (expected deadline dates for the receipt of the initial" .
                    " requested data set, completed data analysis, manuscript submission, grant milestones, etc):",
                'attr' => array('class' => 'textarea form-control')
            ));
        }

        //Add submitInvestigators similar to coInvestigators. Added above

        //////////// Additonal Details (8) ////////////
        //always show if new, edit
        //hide if show, review and empty
        $hideAdditionalDetails = false;
        if ($this->params['cycle'] == 'show' || $this->params['cycle'] == 'review' || $this->params['cycle'] == 'pdf') {
            if ($this->params['project']->hasAdditionalDetails() === false) {
                $hideAdditionalDetails = true;
            }
        }
        if ($this->params['cycle'] == 'new' || $this->params['cycle'] == 'edit') {
            $hideAdditionalDetails = false;
        }
        if ($hideAdditionalDetails === false) {
            $builder->add('collDepartment', null, array(
                'label' => "Which department(s) outside of pathology are you collaborating with?:",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('collInst', null, array(
                'label' => "Which outside institution(s) are you planning to collaborate with?:",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('collInstPi', ChoiceType::class, array(
                'label' => "If collaborations with outside institutions are planned, will you (or the principal investigator listed above) be the PI for the entire study?:",
                'choices' => array(
                    'Yes' => true,
                    'No' => false
                ),
                'multiple' => false,
                'required' => false,
                'expanded' => true,
                'placeholder' => false, //to remove 'Null' set placeholder to false
                'attr' => array('class' => 'horizontal_type collInstPi')
            ));

            $builder->add('essentialInfo', null, array(
                'label' => "Background (essential information related to the project):",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('objective', null, array(
                'label' => "Specific aims (please provide 2 to 3):",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('strategy', null, array(
                'label' => "Research strategy (provide a description of the study design, approach, and statistical methods including sample size calculation):",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('expectedResults', null, array(
                'label' => "Expected results (2 to 3 sentences):",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('fundByPath', ChoiceType::class, array(
                'label' => "Is funding for this project requested from the Pathology Department?:",
                'choices' => array(
                    'Yes' => true,
                    'No' => false
                ),
                'multiple' => false,
                'required' => false,
                'expanded' => true,
                'placeholder' => false, //to remove 'Null' set placeholder to false
                'attr' => array('class' => 'horizontal_type fundByPath')
            ));

            /// Additional fields from #295 ///
            $builder->add('fundDescription', null, array(
                'label' => "Please describe the planned expenses that comprise the budget for this project:",
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('otherResource', null, array(
                'label' => "Other departmental resources requested:",
                'attr' => array('class' => 'textarea form-control')
            ));
            /// EOF Additional fields from #295 ///
        }
        //////////// EOF Additonal Details ////////////
        ////////////// EOF Additional fields from #294 //////////////

        ////////////// Additional fields from #294 //////////////
        if ($this->params['cycle'] != 'new') {
            $builder->add('progressUpdate', null, array(
                'label' => "Progress Updates:",
                'attr' => array('class' => 'textarea form-control')
            ));
        }
        ////////////// EOF Additional fields from #294 //////////////

        //(!$this->params['admin'] && $this->params['project']->getProjectSpecialtyStr() != 'MISI' && $this->params['cycle'] == 'new')
        if( $this->params['admin'] ) {
            $builder->add('requesterGroup', EntityType::class, array(
                'class' => RequesterGroupList::class,
                'label' => 'Requester group:',
                'required' => false,
                'attr' => array('class' => 'combobox transres-project-requesterGroup'),
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

        $builder->add('compTypes', EntityType::class, array(
            'class' => CompCategoryList::class,
            'label' => 'Computational data analysis service category:',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'attr' => array('class' => 'horizontal_type compTypes'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            }
        ));

        if( $this->params['cycle'] == 'show' || $this->params['cycle'] == 'review'  ) {
            $builder->add('projectPdfs', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'label' => 'Project summary to a PDF:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));
        }

        //Archival Specimens
        //ONLY for CP change “Will this project require archival specimens?:” to “Will this project require archival tissue specimens?:”
        //echo "otherUserParam=".$this->params['otherUserParam']."<br>";
        //if( $this->params['otherUserParam'] == 'cp' ) {
        if( $this->params['specialProjectSpecialty'] == true ) {
            $requireArchivalProcessingLabel = "Will this project require archival tissue specimens?:";
        } else {
            $requireArchivalProcessingLabel = "Will this project require archival specimens?:";
        }
        $builder->add('requireArchivalProcessing',ChoiceType::class,array(
            'label' => $requireArchivalProcessingLabel, //"Will this project require archival specimens?:",
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
        //process.py script: replaced namespace by ::class: ['AppTranslationalResearchBundle:OtherRequestedServiceList'] by [OtherRequestedServiceList::class]
            'class' => OtherRequestedServiceList::class,
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


        if( $this->params['project']->getProjectSpecialtyStr() == 'MISI' ) {
            $builder->add('dataAnalysis', null, array(
                'label' => "Is there a bioinformatician on your team to analyze your data?" .
                    " Please describe your plan for downstream data analysis:",
                'required' => false,
                'attr' => array('class' => 'textarea form-control')
            ));

            $builder->add('softwareTool', null, array(
                'label' => "Do you have access to software tools for data visualization and/or analysis of your images?" .
                    " Please describe the tools you plan to use:",
                'required' => false,
                'attr' => array('class' => 'textarea form-control')
            ));
        }


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
                'attr' => array('class'=>'btn btn-success transres-reSubmitReview')
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
