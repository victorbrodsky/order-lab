<?php

namespace App\TranslationalResearchBundle\Form;



use App\OrderformBundle\Entity\AccessionType;
use App\UserdirectoryBundle\Entity\User; //process.py script: replaced namespace by ::class: added use line for classname=User
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteParameterType extends AbstractType
{

    protected $params;
    protected $booleanChoices;
    protected $booleanRequired;

    public function formConstructor( $params ) {
        $this->params = $params;

        //Use this choices for boolean. If Default is set than use the value from the default TRP site settings
        //default TRP site settings -> use "Yes" by default
        //specific TRP site settings -> use "Default" by default
        //[Yes/No] and default to “Yes” by default
        if( $this->params["projectSpecialty"] ) {
            //If specialty is set => Yes, No, Default
            $booleanChoices = array(
                'Default' => NULL,
                'Yes' => true,
                'No' => false
            );
            //$booleanRequired = false;
        } else {
            //If specialty is not set (default) => Yes, No
            $booleanChoices = array(
                'Not set' => NULL,
                'Yes' => true,
                'No' => false,
            );
            //$booleanRequired = true;
        }
        $this->booleanChoices = $booleanChoices;
        $this->booleanRequired = true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('transresFromHeader', null, array(
            'label' => "Invoice 'From' Address:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresFooter', null, array(
            'label' => "Invoice Footer:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresNotificationEmail', null, array(
            'label' => "Email Notification Body when Invoice PDF is sent to PI:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresNotificationEmailSubject', null, array(
            'label' => "Email Notification Subject when Invoice PDF is sent to PI:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('invoiceSalesperson', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppUserdirectoryBundle:User'] by [User::class]
            'class' => User::class,
            'label' => "Invoice Salesperson:",
            //'disabled' => true,
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName", "ASC");
            },
        ));

        ////////// Invoice reminder email ////////////
        $builder->add('invoiceReminderEmail', null, array(
            'label' => "Reminder Email - Send From the Following Address:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('invoiceReminderSchedule', null, array(
            'label' => "Unpaid Invoice Reminder Schedule 
            (overdue in months,reminder interval in months,max reminder count. 
            For example, '6,3,5' will send reminder emails after 6 months overdue every 3 months for 5 times):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('invoiceReminderSubject', null, array(
            'label' => "Unpaid Invoice Reminder Email Subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('invoiceReminderBody', null, array(
            'label' => "Unpaid Invoice Reminder Email Body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        ////////// EOF Invoice reminder email ////////////

        $builder->add('transresLogos', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Invoice Logo(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('requestCompletedNotifiedEmail', null, array(
            'label' => "Email Notification Body to the Request's PI when Request status is changed to 'Completed and Notified':",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('requestCompletedNotifiedEmailSubject', null, array(
            'label' => "Email Notification Subject to the Request's PI when Request status is changed to 'Completed and Notified':",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('accessionType', EntityType::class, array(
        //process.py script: replaced namespace by ::class: ['AppOrderformBundle:AccessionType'] by [AccessionType::class]
            'class' => AccessionType::class,
            'label' => "Default Source System for Work Request Deliverables:",
            'required' => false,
            'multiple' => false,
            'choice_label' => 'getOptimalName',
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        //Packing Slip
        $builder->add('transresPackingSlipLogos', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Packing Slip Logo(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('transresPackingSlipTitle', null, array(
            'label' => "Title (i.e. 'Packing Slip'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipHeadline1', null, array(
            'label' => "Heading Line 1 (i.e. 'Department of Pathology and Laboratory Medicine'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipHeadline2', null, array(
            'label' => "Heading Line 2 (i.e. 'Translational Research Program'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipHeadlineColor', null, array(
            'label' => "Heading Font Color (Blue #1E90FF, HTML color value):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipHighlightedColor', null, array(
            'label' => "Heading Font Color (Red #FF0000, HTML color value):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipSubHeading1', null, array(
            'label' => "Sub-heading 1 (i.e. 'COMMENT FOR REQUEST'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipSubHeading2', null, array(
            'label' => "Sub-heading 2 (i.e. 'LIST OF DELIVERABLE(S)'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipFooter1', null, array(
            'label' => "Footer Line 1 (i.e. 'Please contact us for more information about this slip.'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('transresPackingSlipFooter2', null, array(
            'label' => "Footer Line 2 (i.e. 'Translational Research Program * 1300 York Ave., F512, New York, NY 10065 * Tel (212) 746-62255'):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('barcodeSize', null, array(
            'label' => "Packing Slip Barcode size (i.e. 54px):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('transresPackingSlipFontSize', null, array(
            'label' => "Packing Slip Font size (i.e. 14px):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('specimenDetailsComment', null, array(
            'label' => "Specimen Details Comment (The answers you provide must reflect what has been requested in the approved ".$this->params['humanName']." and the approved tissue request form.):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('institutionName', null, array(
            'label' => "Institution Name (i.e. NYP/WCM):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('fromEmail', null, array(
            'label' => "Emails sent by this site will appear to come from the following address (ctp-admin):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('notifyEmail', null, array(
            'label' => 'Cc for email notification when Work Request\' status change to "Completed" and "Completed and Notified" (ctp@med.cornell.edu):',
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('showMessageToUsers', ChoiceType::class, array(
            'label' => 'Show TRP Message to Users:',
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('messageToUsers', null, array(
            'label' => 'TRP Message to Users:',
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('humanTissueFormNote', null, array(
            'label' => 'Human Tissue Form Note to Users:',
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        //Disable/Enable new project
        $builder->add('enableNewProjectOnSelector', ChoiceType::class, array(
            'label' => 'Enable the display the button (project specialty) on the "New Project Request" selector page (translational-research/project/select-new-project-type):',
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('enableNewProjectOnNavbar', ChoiceType::class, array(
            'label' => 'Enable the display the "New Project Request" link in the top Navbar:',
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('enableNewProjectAccessPage', ChoiceType::class, array(
            'label' => 'Enable access the "New Project Request" page URL (this is for users who might bookmark this page and try to return to it):',
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        //ticket 295(28)
        $builder->add('enableProjectOnNavbar', ChoiceType::class, array(
            'label' => 'Show this project specialty in the Project Requests By Type top navigation bar menu (Project Requests->By type):',
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('enableProjectOnWorkReqNavbar', ChoiceType::class, array(
            'label' => 'Show this project specialty in the Work Requests top navigation bar menu (Work Requests->* by type):',
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('enableProjectOnConfig', ChoiceType::class, array(
            'label' => 'Show this project specialty in the Reviewer Configuration top navigation bar menu (Configuration->Reviewers):',
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('compEmailUsers', EntityType::class, array(
            'class' => User::class,
            'label' => "Send notification emails regarding projects involving Computational Pathology to:",
            'required' => false,
            'multiple' => true,
            'attr' => array('class' => 'combobox'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName", "ASC");
            }
        ));
        $builder->add('compEmailSubject', null, array(
            'label' => "Subject line for notification emails regarding projects involving Computational Pathology:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('compEmailBody', null, array(
            'label' => "Body of the notification emails regarding projects involving Computational Pathology:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('emailNoteConcern', null, array(
            'label' => "Email Notification Asking To Contact With Concerns:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        //Buttons
        if( $this->params['cycle'] === "new" ) {
            $builder->add('save', SubmitType::class, array(
                'label' => 'Save',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }
        if( $this->params['cycle'] === "edit" ) {
            $builder->add('edit', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array('class' => 'btn btn-warning')
            ));
        }



        ////////// Project reminder email ////////////
        $builder->add('projectReminderDelay_irb_review', null, array(
            'label' => "Pending project request reminder email delay for IRB review (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectReminderDelay_admin_review', null, array(
            'label' => "Pending project request reminder email delay for Admin review (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectReminderDelay_committee_review', null, array(
            'label' => "Pending project request reminder email delay for Scientific Committee review (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectReminderDelay_final_review', null, array(
            'label' => "Pending project request reminder email delay for Financial and Programmatic review (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectReminderDelay_irb_missinginfo', null, array(
            'label' => "Pending project request reminder email delay for IRB Missing Info (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectReminderDelay_admin_missinginfo', null, array(
            'label' => "Pending project request reminder email delay for Admin Missing Info (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('projectReminderSubject_review', null, array(
            'label' => "Project request review reminder email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('projectReminderBody_review', null, array(
            'label' => "Project request review reminder email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('projectReminderSubject_missinginfo', null, array(
            'label' => "Project request reminder missing info email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('projectReminderBody_missinginfo', null, array(
            'label' => "Project request reminder missing info email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        ////////// EOF Project reminder email ////////////

        ////////// Pending work request reminder email ////////////
        $builder->add('pendingRequestReminderDelay', null, array(
            'label' => "Delayed pending work request reminder email delay (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('pendingRequestReminderSubject', null, array(
            'label' => "Delayed pending work request reminder email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('pendingRequestReminderBody', null, array(
            'label' => "Delayed pending work request reminder email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        ////////// EOF Pending work request reminder email ////////////

        ////////// Completed work request reminder email ////////////
        $builder->add('completedRequestReminderDelay', null, array(
            'label' => "Delayed completed work request reminder email delay (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('completedRequestReminderSubject', null, array(
            'label' => "Delayed completed work request reminder email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('completedRequestReminderBody', null, array(
            'label' => "Delayed completed work request reminder email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        ////////// EOF Completed work request reminder email ////////////

        ////////// Completed and Notified, without issued invoice work request reminder email ////////////
        $builder->add('completedNoInvoiceRequestReminderDelay', null, array(
            'label' => "Delayed completed and notified, without issued invoices work request reminder email delay (in days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('completedNoInvoiceRequestReminderSubject', null, array(
            'label' => "Delayed completed and notified, without issued invoices work request reminder email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('completedNoInvoiceRequestReminderBody', null, array(
            'label' => "Delayed completed and notified, without issued invoices work request reminder email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        ////////// EOF Completed and Notified, without issued invoice work request reminder email ////////////

        $builder->add('showRemittance', ChoiceType::class, array(
            'label' => "Show Remittance section in invoice PDF:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('updateProjectFundNumber', ChoiceType::class, array(
            'label' => "Update parent Project Request’s Fund Number when New Work request’s number is submitted:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('transresIntakeForms', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Intake Form(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        ////////////// Budget Related Parameters /////////////////////
        $builder->add('overBudgetFromEmail', null, array(
            'label' => "Over budget notification from (default ctp-admin):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('overBudgetSubject', null, array(
            'label' => "Over budget notification subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('overBudgetBody', null, array(
            'label' => "Over budget notification body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('overBudgetSendEmail', ChoiceType::class, array(
            'label' => "Send over budget notifications:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('approvedBudgetSendEmail', ChoiceType::class, array(
            'label' => "Send 'approved project budget' update notifications:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('approvedBudgetFromEmail', null, array(
            'label' => "Approved budget amount update notification from (default ctp-admin):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('approvedBudgetSubject', null, array(
            'label' => "Approved budget amount update notification email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('approvedBudgetBody', null, array(
            'label' => "Approved budget update notification email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('budgetLimitRemovalSubject', null, array(
            'label' => "Approved budget limit removal notification email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('budgetLimitRemovalBody', null, array(
            'label' => "Approved budget limit removal notification email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('overBudgetCalculation', null, array(
            'label' => "Base the notification regarding exceeding the budget on whether the following value exceeds the project budget [Total (Charge and Subsidy) / Charge (without Subsidy)]:",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        ////////////// EOF Budget Related Parameters /////////////////////

        $builder->add('projectExprDuration', null, array(
            'label' => "Default duration of a non-funded project request before expiration in months (default is 12 months):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectExprDurationEmail', null, array(
            'label' => "Default number of months in advance of the non-funded project request expiration".
                " date when the automatic notification requesting a progress report should".
                " be sent (default is 6 months):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectExprDurationChangeStatus', null, array(
            'label' => "Default number of days after the non-funded project request expiration date when".
                " the project request status should be set to 'Closed' (default is 90 days):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        //We don't need projectExprApply, since we can use sendExpriringProjectEmail and sendExpiredProjectEmail
//        $builder->add('projectExprApply', ChoiceType::class, array(
//            'label' => "Apply project request expiration notification rule to this project request type:",
//            'choices' => $this->booleanChoices,
//            'required' => $this->booleanRequired,
//            'attr' => array('class' => 'form-control')
//        ));
        $builder->add('projectExprApplyChangeStatus', ChoiceType::class, array(
            'label' => "Apply project request auto-closure after expiration rule to this non-funded project request type:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));

        //8 fields
        //1
        $builder->add('sendExpriringProjectEmail', ChoiceType::class, array(
            'label' => "Automatically send a reminder email to submit non-funded project progress report for expiring projects:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        //2
        $builder->add('expiringProjectEmailFrom', null, array(
            'label' => "Non-Funded Project Request Upcoming Expiration Notification E-Mail sent from (default ctp-admin):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('expiringProjectEmailSubject', null, array(
            'label' => "Non-Funded Project Request Upcoming Expiration Notification E-Mail Subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('expiringProjectEmailBody', null, array(
            'label' => "Non-Funded Project Request Upcoming Expiration Notification E-Mail Body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        //5
        $builder->add('sendExpiredProjectEmail', ChoiceType::class, array(
            'label' => "Automatically send a reminder email to the [TRP] system administrator for expired non-funded projects:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('expiredProjectEmailFrom', null, array(
            'label' => "Non-Funded Project Request Expiration Notification E-Mail sent from (default ctp-admin):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('expiredProjectEmailSubject', null, array(
            'label' => "Non-Funded Project Request Expiration Notification E-Mail Subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('expiredProjectEmailBody', null, array(
            'label' => "Non-Funded Project Request Expiration Notification E-Mail Body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        //////////////////// Project Closure/Reactivation ////////////////////
        $builder->add('sendProjectReactivationRequest', ChoiceType::class, array(
            'label' => "Send project reactivation approval requests:",
            'choices' => $this->booleanChoices,
            'required' => $this->booleanRequired,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectReactivationFromEmail', null, array(
            'label' => "Project reactivation approval request from (default to trpadminMailingListEmail):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('projectReactivationSubject', null, array(
            'label' => "Project reactivation approval request email subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('projectReactivationBody', null, array(
            'label' => "Project reactivation approval request email body:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        //////////////////// EOF Project Closure/Reactivation ////////////////////

        //"Recipient Fund Number used in JV (61211820 for all specialty, except for MISI 87000819):"
        $builder->add('recipientFundNumber', null, array(
            'label' => "Recipient Fund Number for the generated Unpaid Billing Summary:", 
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\TransResSiteParameters',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'oleg_translationalresearchbundle_siteparameter';
    }


}
