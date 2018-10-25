<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\DocumentType;
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

    public function formConstructor( $params ) {
        $this->params = $params;
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
            'class' => 'OlegUserdirectoryBundle:User',
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
            'label' => "Translational Research Reminder Email - Send From the Following Address:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('invoiceReminderSchedule', null, array(
            'label' => "Translational Research Unpaid Invoice Reminder Schedule in Months:",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('invoiceReminderSubject', null, array(
            'label' => "Translational Research Unpaid Invoice Reminder Email Subject:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('invoiceReminderBody', null, array(
            'label' => "Translational Research Unpaid Invoice Reminder Email Body:",
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
            'class' => 'OlegOrderformBundle:AccessionType',
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
            'label' => "Specimen Details Comment (The answers you provide must reflect what has been requested in the approved IRB and the approved tissue request form.):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('institutionName', null, array(
            'label' => "Institution Name (NYP/WCM):",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('fromEmail', null, array(
            'label' => "Emails sent by this site will appear to come from the following address (trp-admin@med.cornell.edu):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));
        $builder->add('notifyEmail', null, array(
            'label' => 'Cc for email notification when Work Request\' status change to "Completed" and "Completed and Notified" (trp@med.cornell.edu):',
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('emailNoteConcern', null, array(
            'label' => "Translational Research Email Notification Asking To Contact With Concerns:",
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

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\TranslationalResearchBundle\Entity\TransResSiteParameters',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_siteparameter';
    }


}
