<?php

namespace App\TranslationalResearchBundle\Form;

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

class InvoiceType extends AbstractType
{

    protected $invoice;
    protected $params;

    public function formConstructor( $params )
    {
        $this->params = $params;
        $this->invoice = $params['invoice'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        //$builder->add('createDate')->add('updateDate')->add('oid')->add('invoiceNumber')->add('dueDate')->add('status')->add('to')->add('discountNumeric')->add('discountPercent')->add('submitter')->add('updateUser')->add('transresRequests')->add('salesperson');

//        $builder->add('createDate', DateType::class, array(
//            'widget' => 'single_text',
//            'label' => "Create Date:",
//            'disabled' => true,
//            'format' => 'MM/dd/yyyy',
//            'attr' => array('class' => 'datepicker form-control'),
//            'required' => false,
//        ));
        
        $builder->add('status', ChoiceType::class, array( //flipped
            'label' => 'Status:',
            'choices' => $this->params['statuses'],
            'multiple' => false,
            'required' => true,
            'attr' => array('class' => 'combobox')
        ));
        
        //Request's PI
        if( $this->params['principalInvestigators'] && count($this->params['principalInvestigators']) > 0 ) {
            //show only request's the first PI user
            $builder->add('principalInvestigator', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Principal Investigator for the project:",
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox transres-invoice-principalInvestigator'), //, 'readonly'=>'readonly'
                'choices' => $this->params['principalInvestigators'],
            ));
        } else {
            //show all users with set invoice's PI
            $builder->add('principalInvestigator', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                'label' => "Principal Investigator for the project:",
                'required' => true,
                'multiple' => false,
                'attr' => array('class' => 'combobox transres-invoice-principalInvestigator'),
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
        }

        $builder->add('billingContact', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label'=> "PI's Billing Contact:",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox transres-invoice-billingContact'),
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

        $builder->add('salesperson', EntityType::class, array(
            'class' => 'AppUserdirectoryBundle:User',
            'label' => "Salesperson:",
            //'disabled' => true,
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox'),
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

        if(1) {
            $builder->add('submitter', EntityType::class, array(
                'class' => 'AppUserdirectoryBundle:User',
                //'label' => "Submitter (will be shown in the invoice PDF as Requester):",
                'label' => "Invoice's Submitter:",
                'disabled' => true,
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox'),
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
        }

        //if( $this->params['cycle'] != 'new' ) {
            $builder->add('oid', null, array(
                'label' => "Invoice Number:",
                'disabled' => true,
                'required' => false,
                'attr' => array('class' => 'form-control')
            ));
        //}

        $builder->add('fundedAccountNumber', null, array(
            'label' => "Fund Number:",
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('dueDate', DateType::class, array(
            'widget' => 'single_text',
            'label' => "Due Date:",
            //'disabled' => true,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('paidDate', DateType::class, array(
            'widget' => 'single_text',
            'label' => "Paid Date:",
            //'disabled' => true,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('invoiceFrom', null, array(
            'label' => "From:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('invoiceTo', null, array(
            'label' => "To:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control transres-invoice-invoiceTo')
        ));

        $builder->add('discountNumeric', null, array(
            'label' => "Discount ($):",
            //'disabled' => true,
            'required' => false,
            'attr' => array('class' => 'form-control invoice-discountNumeric currency-mask-without-prefix')
        ));

        $builder->add('discountPercent', null, array(
            'label' => "Discount (%):",
            //'disabled' => true,
            'required' => false,
            'attr' => array('class' => 'form-control invoice-discountPercent digit-mask')
        ));

        $builder->add('administrativeFee', null, array(
            'label' => "Administrative Fee ($):",
            'required' => false,
            'attr' => array('class' => 'form-control invoice-administrativeFee digit-mask')
        ));

        $builder->add('footer', null, array(
            'label' => "Footer:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('footer2', null, array(
            'label' => "Footer 2 (In Bold):",
            'required' => false,
            'attr' => array('class' => 'textarea form-control', 'style'=>"font-weight: bold")
        ));

//        $builder->add('footer3', null, array(
//            'label' => "Footer 3:",
//            'required' => false,
//            'attr' => array('class' => 'textarea form-control')
//        ));

        //InvoiceItems
        $builder->add('invoiceItems', CollectionType::class, array(
            'entry_type' => InvoiceItemType::class,
            'entry_options' => array(
                //'data_class' => 'App\TranslationalResearchBundle\Entity\AdminReview',
                'form_custom_value' => $this->params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__invoiceitems__',
        ));

//        $builder->add('invoiceAddItems', CollectionType::class, array(
//            'entry_type' => InvoiceAddItemType::class,
//            'entry_options' => array(
//                'form_custom_value' => $this->params
//            ),
//            'label' => false,
//            'required' => false,
//            'allow_add' => true,
//            'allow_delete' => true,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__invoiceadditems__',
//        ));

        //Generated Invoices
//        $builder->add('documents', CollectionType::class, array(
//            'entry_type' => DocumentType::class,
//            'label' => false,
//            'allow_add' => true,
//            'allow_delete' => true,
//            'required' => false,
//            'by_reference' => false,
//            'prototype' => true,
//            'prototype_name' => '__logo__',
//        ));


        $builder->add('subTotal', NumberType::class, array(
            'label' => "Subtotal ($):",
            'scale' => 2,
            'required' => false,
            'attr' => array('class' => 'form-control invoice-subTotal currency-mask-without-prefix') //'onclick'=>'transresUpdateSubTotal()'
        ));

        $builder->add('total', NumberType::class, array(
            'label' => "Total ($):",
            'scale' => 2,
            'required' => false,
            'attr' => array('class' => 'form-control invoice-total currency-mask-without-prefix')
        ));

        if( $this->params['cycle'] != 'new' ) {
            $builder->add('paid', NumberType::class, array(
                'label' => "Paid ($):",
                'scale' => 2,
                'required' => false,
                'attr' => array('class' => 'form-control invoice-paid')
            ));
        }

        $builder->add('due', NumberType::class, array(
            'label' => "Balance Due ($):",
            'scale' => 2,
            'required' => false,
            'attr' => array('class' => 'form-control invoice-due', 'readonly'=>'readonly')
        ));

//        $builder->add('subsidy', NumberType::class, array(
//            'label' => "Subsidy ($):",
//            'scale' => 2,
//            'required' => false,
//            'attr' => array('class' => 'form-control invoice-subsidy', 'readonly'=>'readonly')
//        ));

        $builder->add('documents', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'PDF(s):',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__documentsid__',
        ));

        $builder->add('comment', null, array(
            'label' => "Comment:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control transres-invoice-comment')
        ));


        //data-toggle="modal" data-target="#exampleModal"
        //"data-toggle"=>"modal", "data-target"=>"#pleaseWaitModal"

        //Buttons
        $pis = $this->getInvoicePis($builder->getData());
        if( $this->params['cycle'] === "new" ) {
//            $builder->add('save', SubmitType::class, array(
//                'label' => 'Generate Invoice',
//                'attr' => array('class' => 'btn btn-primary btn-with-wait')
//            ));
            $builder->add('saveAndGeneratePdf', SubmitType::class, array(
                'label' => 'Save and Generate PDF Invoice',
                'attr' => array('class' => 'btn btn-primary btn-with-wait', "data-toggle"=>"modal", "data-target"=>"#pleaseWaitModal")
            ));
            $builder->add('saveAndGeneratePdfAndSendByEmail', SubmitType::class, array(
                'label' => 'Save, Generate PDF Invoice and Send PDF Invoice by Email to PI'.$pis,
                'attr' => array('class' => 'btn btn-success btn-with-wait', "data-toggle"=>"modal", "data-target"=>"#pleaseWaitModal")
            ));
        }
        if( $this->params['invoice']->getLatestVersion() === true ) {
            if( $this->params['cycle'] == "edit" ) {

                if (count($this->params['invoice']->getDocuments()) > 0) {
                    $generatePrefix = "Regenerate";
                } else {
                    $generatePrefix = "Generate";
                }

//                $builder->add('edit', SubmitType::class, array(
//                    'label' => 'Update Invoice',
//                    'attr' => array('class' => 'btn btn-primary btn-with-wait')
//                ));
                $builder->add('saveAndGeneratePdf', SubmitType::class, array(
                    'label' => "Update and $generatePrefix PDF Invoice",
                    'attr' => array('class' => 'btn btn-primary btn-with-wait', "data-toggle"=>"modal", "data-target"=>"#pleaseWaitModal") //'onClick'=>"this.disabled=true; this.value = 'Please Wait!';"
                ));
                $builder->add('saveAndGeneratePdfAndSendByEmail', SubmitType::class, array(
                    'label' => "Update, $generatePrefix PDF Invoice and Send PDF Invoice by Email to PI".$pis,
                    'attr' => array('class' => 'btn btn-warning btn-with-wait', "data-toggle"=>"modal", "data-target"=>"#pleaseWaitModal")
                ));

//                if (count($this->params['invoice']->getDocuments()) > 0) {
//                    $builder->add('sendByEmail', SubmitType::class, array(
//                        'label' => 'Send the Most Recent Invoice PDF by Email',
//                        'attr' => array('class' => 'btn btn-warning')
//                    ));
//                }

            }

//            if( $this->params['cycle'] == "show" ) {
//                $builder->add('sendByEmail', SubmitType::class, array(
//                    'label' => 'Send the Most Recent Invoice PDF by Email',
//                    'disabled' => false,
//                    'attr' => array('class' => 'btn btn-warning')
//                ));
//            }
        }

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\Invoice',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_invoice';
    }

    public function getInvoicePis($invoice) {
        $transresRequestUtil = $this->params['transres_request_util'];
        return $transresRequestUtil->getInvoicePisStr($invoice);
    }

}
