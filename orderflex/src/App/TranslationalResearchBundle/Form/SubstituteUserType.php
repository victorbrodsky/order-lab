<?php

namespace App\TranslationalResearchBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SubstituteUserType extends AbstractType
{

    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        //$builder->add('createDate')->add('updateDate')->add('state')->add('creator')->add('updateUser')->add('reviewer')->add('reviewerDelegate');

//        if( $this->params['showPrimaryReview'] ) {
//            //echo "show primaryReview <br>";
//            $builder->add('primaryReview', CheckboxType::class, array(
//                'label' => 'Primary Review:',
//                'required' => false,
//                'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
//            ));
//        }

        $builder->add( 'projectSpecialty', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:SpecialtyList',
            'choice_label' => 'name',
            'label'=>'Project Specialty:',
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

        $builder->add( 'substituteUser', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "The user name to be substituted:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder()
        ));

        //Preloaded (must test a user generation with roles according to the selected project specialty)
        $sitename = "'translationalresearch'";
        $otherUserParam = "'hematopathology_ap-cp'";
        $addUserOnFly = ' (<a href="javascript:void(0)" onclick="constructNewUserModal(this,' . $sitename . ','.$otherUserParam.');">Add New</a>)';
        $builder->add( 'replaceUser', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            //'label'=> "Replace with the following user name (Add New):",
            'label'=> "Replace with the following user name$addUserOnFly:",
            //'label'=> "Replace with the following user name:",
            'required'=> false,
            'multiple' => false,
            //'attr' => array('class'=>'combobox combobox-width'),
            'attr' => array('class'=>'combobox combobox-width add-new-user-on-enter', 'data-otheruserparam'=>$otherUserParam),
            'query_builder' => $this->params['transresUtil']->userQueryBuilder()
        ));

        $builder->add('substitute', SubmitType::class, array(
            'label' => 'Substitute',
            'attr' => array('class'=>'btn btn-success')
        ));

        //Project
        $builder->add('excludedProjectCompleted', CheckboxType::class, array(
            'label' => 'Completed project requests:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('excludedProjectCanceled', CheckboxType::class, array(
            'label' => 'Canceled project requests:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('excludedProjectDraft', CheckboxType::class, array(
            'label' => 'Draft project requests:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        //Request
        $builder->add('excludedRequestCompleted', CheckboxType::class, array(
            'label' => 'Completed work requests:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('excludedRequestCanceled', CheckboxType::class, array(
            'label' => 'Canceled project requests:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        //Invoice
        $builder->add('excludedInvoicePaid', CheckboxType::class, array(
            'label' => 'Paid invoices:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('excludedInvoicePartiallyPaid', CheckboxType::class, array(
            'label' => 'Partially paid invoices:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('excludedInvoiceCanceled', CheckboxType::class, array(
            'label' => 'Canceled invoices:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));

        //Perform substitution in the following project request fields:
        $builder->add('projectPis', CheckboxType::class, array(
            'label' => 'Principal Investigator(s) for the project:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectPisIrb', CheckboxType::class, array(
            'label' => 'Principal Investigator listed on the IRB application:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectPathologists', CheckboxType::class, array(
            'label' => $this->params['institutionName'].' Pathologist(s) Involved:', //NYP/WCM
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectCoInvestigators', CheckboxType::class, array(
            'label' => 'Co-Investigator(s):',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectContacts', CheckboxType::class, array(
            'label' => 'Contact(s):',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectBillingContact', CheckboxType::class, array(
            'label' => 'Billing Contact:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        //IRB Reviewer
        $builder->add('projectReviewerIrb', CheckboxType::class, array(
            'label' => 'IRB Reviewer:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectReviewerIrbDelegate', CheckboxType::class, array(
            'label' => 'IRB Reviewer Delegate:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        //Admin Reviewer
        $builder->add('projectReviewerAdmin', CheckboxType::class, array(
            'label' => 'Administrative Reviewer:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectReviewerAdminDelegate', CheckboxType::class, array(
            'label' => 'Administrative Reviewer Delegate:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        //Committee Reviewer
        $builder->add('projectReviewerCommittee', CheckboxType::class, array(
            'label' => 'Committee Reviewer:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectReviewerCommitteeDelegate', CheckboxType::class, array(
            'label' => 'Committee Reviewer Delegate:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        //Primary Committee Reviewer
        $builder->add('projectReviewerPrimaryCommittee', CheckboxType::class, array(
            'label' => 'Primary Committee Reviewer:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectReviewerPrimaryCommitteeDelegate', CheckboxType::class, array(
            'label' => 'Primary Committee Reviewer Delegate:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        //Final Reviewer
        $builder->add('projectReviewerFinal', CheckboxType::class, array(
            'label' => 'Final Reviewer:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('projectReviewerFinalDelegate', CheckboxType::class, array(
            'label' => 'Final Reviewer Delegate:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));

        //Perform substitution in the following work request fields:
        $builder->add('requestPis', CheckboxType::class, array(
            'label' => 'Principal Investigator(s) for the project:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('requestBillingContact', CheckboxType::class, array(
            'label' => 'Billing Contact:',
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));

        //Perform substitution in the following invoice fields:
//        $builder->add('invoicePi', CheckboxType::class, array(
//            'label' => 'Principal Investigator for the project:',
//            'required' => false,
//            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
//        ));
        $builder->add('invoiceBillingContact', CheckboxType::class, array(
            'label' => "PI's Billing Contact:",
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));
        $builder->add('invoiceSalesperson', CheckboxType::class, array(
            'label' => "Salesperson:",
            'required' => false,
            'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
        ));

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_substituteuser';
    }


}
