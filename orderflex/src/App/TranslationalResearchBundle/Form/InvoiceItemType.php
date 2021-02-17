<?php

namespace App\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceItemType extends AbstractType
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

        $invoiceItem = $builder->getData();

        $builder->add('quantity', null, array(
            'label' => "Quantity",
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-quantity')
        ));

        $builder->add('additionalQuantity', null, array(
            'label' => "Additional Quantity",
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-additionalQuantity')
        ));

        $builder->add('itemCode', null, array(
            'label' => "Item Code",
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-itemCode')
        ));

//        $itemCode = NULL;
//        if( $invoiceItem ) {
//            $itemCode = $invoiceItem->getItemCode();
//            echo "itemCode exists <br>";
//        }
//        echo "itemCode=$itemCode <br>";
//        $builder->add('itemCodeNotMapped', null, array(
//            'label' => false, //"Item Code",
//            'required' => false,
//            'mapped' => false,
//            "data" => $itemCode,
//            'attr' => array('class' => 'form-control invoiceitem-itemCodeNotMapped')
//        ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $invoiceItem = $event->getData();
            $form = $event->getForm();

            $itemCode = NULL;
            if( $invoiceItem ) {
                $itemCode = $invoiceItem->getItemCode();
                //echo "itemCode exists <br>";
            }
            //echo "itemCode=$itemCode <br>";

            $form->add('itemCodeNotMapped', null, array(
                'label' => false, //"Item Code",
                'required' => false,
                'mapped' => false,
                "data" => $itemCode,
                'attr' => array('class' => 'form-control invoiceitem-itemCodeNotMapped')
            ));

        });

        $builder->add('description', null, array(
            'label' => "Description",
            'required' => false,
            'attr' => array('class' => 'textarea form-control', 'style' => 'min-height: 80px;')
        ));

        $builder->add('unitPrice', NumberType::class, array(
            'label' => "Unit Price ($)",
            'scale' => 2,
            //'divisor' => 100,
            //'currency' => false,
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-unitPrice')
        ));

        $builder->add('additionalUnitPrice', NumberType::class, array(
            'label' => "Additional Unit Price ($)",
            'scale' => 2,
            //'divisor' => 100,
            //'currency' => false,
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-additionalUnitPrice')
        ));

//        $builder->add('total', NumberType::class, array(
//            'label' => "Total ($)",
//            'scale' => 2,
//            //'divisor' => 100,
//            //'currency' => false,
//            'required' => false,
//            'attr' => array('class' => 'form-control invoiceitem-total')
//        ));
        $builder->add('total', HiddenType::class, array(
            'label' => "Total ($)",
            //'scale' => 2,
            //'divisor' => 100,
            //'currency' => false,
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-total')
        ));

        $builder->add('total1', NumberType::class, array(
            'label' => false, //"Total ($)",
            'scale' => 2,
            'mapped' => false,
            'required' => false,
            'disabled' => true,
            'attr' => array('class' => 'form-control invoiceitem-total1')
        ));
        $builder->add('total2', NumberType::class, array(
            'label' => false, //"Total ($)",
            'scale' => 2,
            'mapped' => false,
            'required' => false,
            'disabled' => true,
            'attr' => array('class' => 'form-control invoiceitem-total2')
        ));


        //Buttons
//        if( $this->params['cycle'] === "new" ) {
//            $builder->add('save', SubmitType::class, array(
//                'label' => 'Save',
//                'attr' => array('class' => 'btn btn-warning')
//            ));
//        }
//        if( $this->params['cycle'] === "edit" ) {
//            $builder->add('edit', SubmitType::class, array(
//                'label' => 'Update',
//                'attr' => array('class' => 'btn btn-warning')
//            ));
//        }

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\InvoiceItem',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_invoiceitem';
    }


}
