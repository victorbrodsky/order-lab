<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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

        $builder->add('createDate', DateType::class, array(
            'widget' => 'single_text',
            'label' => "Create Date:",
            'disabled' => true,
            'format' => 'MM/dd/yyyy',
            'attr' => array('class' => 'datepicker form-control'),
            'required' => false,
        ));

        $builder->add('submitter', EntityType::class, array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label'=> "Submitter:",
            'disabled' => true,
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->leftJoin("list.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->where("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    //->andWhere("list.roles LIKE '%ROLE_TRANSRES_%'")
                    ->leftJoin("list.infos", "infos")
                    ->orderBy("infos.displayName","ASC");
            },
        ));

        $builder->add('oid', null, array(
            'label' => "Invoice Number:",
            //'disabled' => true,
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

        $builder->add('from', null, array(
            'label' => "From:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('to', null, array(
            'label' => "To:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        $builder->add('discountNumeric', null, array(
            'label' => "Discount ($):",
            //'disabled' => true,
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('discountPercent', null, array(
            'label' => "Discount (%):",
            //'disabled' => true,
            'required' => false,
            'attr' => array('class' => 'form-control')
        ));

        $builder->add('footer', null, array(
            'label' => "Footer:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        //InvoiceItems
        $builder->add('invoiceItems', CollectionType::class, array(
            'entry_type' => InvoiceItemType::class,
            'entry_options' => array(
                //'data_class' => 'Oleg\TranslationalResearchBundle\Entity\AdminReview',
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

        //logo
        $builder->add('documents', CollectionType::class, array(
            'entry_type' => DocumentType::class,
            'label' => 'Logo:',
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__logo__',
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
            'data_class' => 'Oleg\TranslationalResearchBundle\Entity\Invoice',
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


}
