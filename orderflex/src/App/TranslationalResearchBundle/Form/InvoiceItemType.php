<?php

namespace App\TranslationalResearchBundle\Form;


use App\UserdirectoryBundle\Form\CustomType\CustomSelectorType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceItemType extends AbstractType
{

    protected $params;
    //protected $itemCodeString = true; //item code as a string
    protected $itemCodeString = false; //item code will be represented as select box with js to auto populate description and prices

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

        //$invoiceItem = $builder->getData();

        $builder->add('id', HiddenType::class, array(
            'attr' => array('class'=>'invoiceitem-id'),
        ));
        
        $builder->add('product', null, array(
            'attr' => array('class'=>'invoiceitem-product', 'style'=>'display:none;'),
        ));

        $builder->add('quantity', NumberType::class, array(
            'label' => "Quantity",
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-quantity digit-mask', 'min'=>'0', 'data-toggle'=>'tooltip', 'title' => 'Initial')
        ));

        $builder->add('additionalQuantity', NumberType::class, array(
            'label' => "Additional Quantity",
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-additionalQuantity digit-mask', 'min'=>'0', 'data-toggle'=>'tooltip', 'title' => 'Additional')
        ));

        ///////////// Item Code /////////////////
//        if(0) {
//            if ($this->itemCodeString) {
//                $builder->add('itemCode', null, array(
//                    'label' => "Item Code",
//                    'required' => false,
//                    'attr' => array('class' => 'form-control invoiceitem-itemCode')
//                ));
//            } else {
//                //InvoiceItem -> itemCode (String) <=> product (Product) -> category (RequestCategoryTypeList) -> productId (String)
//                $builder->add('itemCode', CustomSelectorType::class, array(
//                    'label' => "Item Code",
//                    'attr' => array('class' => 'combobox ajax-combobox-transresitemcodes invoiceitem-itemCode', 'type' => 'hidden'),
//                    'required' => false,
//                    'classtype' => 'transresitemcodes'
//                ));
//            }
//        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $invoiceItem = $event->getData();
            $form = $event->getForm();

            $itemCode = NULL;
            $categoryId = NULL;
            $total1 = NULL;
            $total2 = NULL;
            if( $invoiceItem ) {

//                $product = $invoiceItem->getProduct();
//                if( $product ) {
//                    $category = $product->getCategory();
//                    if( $category ) {
//                        $categoryId = $category->getId();
//                    }
//                }
//
//                if( !$categoryId ) {
//                    $categoryId = $invoiceItem->getItemCode();
//                }
//
//                if( !$invoiceItem->getItemCode() ) {
//                    $categoryId = $invoiceItem->getItemCode();
//                }

                //$invoiceItemId = $invoiceItem->getId();
                //$itemCode = $invoiceItem->getItemCode();
                $product = NULL;
                if( $invoiceItem->getItemCode() ) {
                    $product = $invoiceItem->getProduct();
                    //echo "invoiceitem form product=".$product."<br>";
                    if( $product ) {
                        $category = $product->getCategory();
                        if( $category ) {
                            $categoryId = $category->getId();
                        }
                    }
                } else {
                    $categoryId = NULL;
                }
                if( !$categoryId ) {
                    //echo "itemCode=".$invoiceItem->getItemCode()."<br>";
                    $categoryId = $invoiceItem->getItemCode();
                    //echo "categoryId=".$categoryId."<br>";
                }
                //echo $invoiceItemId.": invoiceitem form categoryId=".$categoryId.", itemCode=".$itemCode.", product=[".$product."]<br>";

                $itemCode = $invoiceItem->getItemCode();
                //echo "itemCode exists <br>";
                $total1 = $invoiceItem->getTotal1();
                $total2 = $invoiceItem->getTotal2();
            }
            //$itemCode = "delivery fee";
            //echo "itemCode=[$itemCode]<br>";

            if($this->itemCodeString) {
                $form->add('itemCode', null, array(
                    'label' => "Item Code",
                    'required' => false,
                    "data" => $itemCode,
                    'attr' => array('class' => 'form-control invoiceitem-itemCode')
                ));

                $form->add('itemCodeNotMapped', null, array(
                    'label' => false, //"Item Code",
                    'required' => false,
                    'mapped' => false,
                    "data" => $itemCode,
                    'attr' => array('class' => 'form-control invoiceitem-itemCodeNotMapped')
                ));
            } else {
                $form->add('itemCode', CustomSelectorType::class, array(
                    'label' => "Item Code",
                    'attr' => array('class' => 'combobox ajax-combobox-transresitemcodes invoiceitem-itemCode', 'type' => 'hidden', 'data-toggle'=>'tooltip', 'title' => 'Initial'),
                    'required' => false,
                    "data" => $categoryId,
                    'classtype' => 'transresitemcodes'
                ));

//                $form->add('itemCodeNotMapped', CustomSelectorType::class, array(
//                    'label' => false, //"Item Code",
//                    'required' => false,
//                    'mapped' => false,
//                    'disabled' => true,
//                    'attr' => array('class' => 'combobox ajax-combobox-transresitemcodes invoiceitem-itemCodeNotMapped', 'type' => 'hidden', 'data-toggle'=>'tooltip', 'title' => 'Additional'),
//                    "data" => $categoryId,
//                    'classtype' => 'transresitemcodes'
//                ));
            }

            $form->add('total1', NumberType::class, array(
                'label' => false, //"Total ($)",
                'scale' => 2,
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'data' => $total1,
                'attr' => array('class' => 'form-control invoiceitem-total1', 'data-toggle'=>'tooltip', 'title' => 'Initial')
            ));
            $form->add('total2', NumberType::class, array(
                'label' => false, //"Total ($)",
                'scale' => 2,
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'data' => $total2,
                'attr' => array('class' => 'form-control invoiceitem-total2', 'data-toggle'=>'tooltip', 'title' => 'Additional')
            ));

        });
        ///////////// Items /////////////////

        $builder->add('description', null, array(
            'label' => "Description",
            'required' => false,
            'attr' => array('class' => 'textarea form-control invoiceitem-description', 'style' => 'min-height: 80px;')
        ));

        $builder->add('unitPrice', null, array(
            'label' => "Unit Price ($)",
            'scale' => 2,
            //'divisor' => 100,
            //'currency' => false,
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-unitPrice currency-mask-without-prefix', 'data-toggle'=>'tooltip', 'title' => 'Initial')
        ));

        $builder->add('additionalUnitPrice', null, array(
            'label' => "Additional Unit Price ($)",
            'scale' => 2,
            //'divisor' => 100,
            //'currency' => false,
            'required' => false,
            'attr' => array('class' => 'form-control invoiceitem-additionalUnitPrice currency-mask-without-prefix', 'data-toggle'=>'tooltip', 'title' => 'Additional')
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
    public function getBlockPrefix(): string
    {
        return 'oleg_translationalresearchbundle_invoiceitem';
    }


}
