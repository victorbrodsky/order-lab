<?php

namespace App\TranslationalResearchBundle\Form;

use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList;
use App\TranslationalResearchBundle\Util\TransResUtil;
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{

    protected $params;
    protected $priceList;
    protected $disabled;

    public function formConstructor( $params )
    {
        $this->params = $params;

//        if( isset($params['transresUtil']) ) {
//            $this->trpBusinessNameAbbreviation = $params['transresUtil']->getBusinessEntityAbbreviation();
//        }

        $this->priceList = NULL;
        if (isset($this->params['transresRequest'])) {
            $workRequest = $this->params['transresRequest'];
            $project = $workRequest->getProject();
            //echo "project=".$project."<br>";
            if ($project) {
                $this->priceList = $project->getPriceList();
            }
        }
        //echo "priceList=".$this->priceList."<br>";

//        $disabled = false;
//        if( $this->params['SecurityAuthChecker']->isGranted('ROLE_TRANSRES_ADMIN') ) {
//            $disabled = true;
//        }
//        //$disabled = true;
//        $this->disabled = $disabled;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('id', HiddenType::class, array(
            'attr' => array('class'=>'product-id'),
        ));

        //wrap all of the fields in addEventListener
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $product = $event->getData();
            $form = $event->getForm();

            $this->setProductPermission($product);

            $this->getForm($form);

        });

    }

    public function getRequestCategoryQueryBuilder(EntityRepository $er) {

        //'class' => 'AppTranslationalResearchBundle:RequestCategoryTypeList',
        $workRequest = NULL;
        $projectSpecialtyId = array();
        $projectSpecialtyIdsArr = array();
        if( isset($this->params['transresRequest']) ) {
            $workRequest = $this->params['transresRequest'];
            //echo "workRequest=".$workRequest->getId()."<br>";
            if( $workRequest ) {
                $projectSpecialty = $workRequest->getProjectSpecialty();
                if( $projectSpecialty ) {
                    $projectSpecialtyId = $projectSpecialty->getId();
                    $projectSpecialtyIdsArr[] = $projectSpecialtyId;
                }
            }
        }
        //echo "projectSpecialtyIdsArr ids=".implode(",",$projectSpecialtyIdsArr)."<br>";
        //echo "projectSpecialtyId = ".$projectSpecialtyId."<br>";
        //dump($projectSpecialtyIdsArr);
        //exit('111');

        //do not show if fee is zero using $this->priceList
        //$feeRestriction = "(list.fee IS NOT NULL AND list.fee <> '0')";
        $feeRestriction = "(list.fee IS NOT NULL)";
        if( $this->priceList ) {
            $priceListId = $this->priceList->getId();
            if( $priceListId ) {
                //$specificFeeRestriction = "(priceList.id = $priceListId AND prices.fee IS NOT NULL AND prices.fee <> '0')";
                $specificFeeRestriction = "(priceList.id = $priceListId AND prices.fee IS NOT NULL)";
                $feeRestriction = $feeRestriction . " OR ";
                $feeRestriction = $feeRestriction . $specificFeeRestriction;
                //echo $this->priceList.": feeRestriction = $feeRestriction<br>";
            }
        }

        //if( $workRequest && count($projectSpecialtyIdsArr) > 0 ) {
        if( $workRequest && $projectSpecialtyId ) {
            //AppTranslationalResearchBundle:RequestCategoryTypeList
            $queryBuilder = $er->createQueryBuilder('list')
                ->leftJoin('list.projectSpecialties','projectSpecialties')
                ->leftJoin('list.prices','prices')
                ->leftJoin('prices.priceList','priceList')
                ->where("list.type = :typedef OR list.type = :typeadd")
                //->andWhere("projectSpecialties.id IN (:projectSpecialtyIdsArr)") //show categories with this specialty only
                //->andWhere("projectSpecialties.id NOT IN (:projectSpecialtyIdsArr)") //do show categories with this specialty only
                //->andWhere("projectSpecialties.id IS NULL")
                ->andWhere("(projectSpecialties.id IS NULL OR projectSpecialties.id NOT IN (:projectSpecialtyIdsArr))")
                //->andWhere("(projectSpecialties.id IS NULL OR projectSpecialties != :projectSpecialtyIdsArr)")
                ->andWhere($feeRestriction)
                ->orderBy("list.orderinlist","ASC")
                //->setMaxResults( 1 )
                ->setParameters( array(
                    'typedef' => 'default',
                    'typeadd' => 'user-added',
                    'projectSpecialtyIdsArr' => $projectSpecialtyIdsArr
                ));
        } else {
            $queryBuilder = $er->createQueryBuilder('list')
                ->leftJoin('list.prices','prices')
                ->leftJoin('prices.priceList','priceList')
                ->where("list.type = :typedef OR list.type = :typeadd")
                ->andWhere($feeRestriction)
                ->orderBy("list.orderinlist","ASC")
                ->setParameters( array(
                    'typedef' => 'default',
                    'typeadd' => 'user-added',
                ));
        }

        return $queryBuilder;
    }

    public function setProductPermission($product) {

            if( !$product ) {
                return false;
            }

            //testing
            $productId = "";
            if( $product ) {
                $category = $product->getCategory();
                if ($category) {
                    $productId = $category->getProductId();
                }
            }

            $action = $this->params['cycle'];

            $transresPermissionUtil = $this->params['transresPermissionUtil'];

            $productPermission = $transresPermissionUtil->hasProductPermission($action,$product);
            if( $productPermission ) {
                //echo $productId.": enables <br>";
                $this->disabled = false;
                //$this->disabled = true; //testing
            } else {
                //echo $productId.": disabled <br>";
                $this->disabled = true;
            }
    }

    public function getForm($builder) {
        //dynamically get label and price according to the priceList
        if(0) {
            $builder->add('category', EntityType::class, array(
                'class' => 'AppTranslationalResearchBundle:RequestCategoryTypeList',
                //'choice_label' => 'getOptimalAbbreviationName',
                //            'choice_value' => function ($entity) {
                //                //return "111";
                //                if( $entity ) {
                //                    return $entity->getOptimalAbbreviationName($this->priceList);
                //                }
                //                return '';
                //            },
                'choice_label' => function (RequestCategoryTypeList $entity) {
                    if ($entity) {
                        return $entity->getOptimalAbbreviationName($this->priceList);
                    }
                    return '';
                },
                'label' => "Product or Service" . $this->params['categoryListLink'] . ":",
                'required' => false,
                'multiple' => false,
                'attr' => array('class' => 'combobox combobox-width product-category-combobox'),
                'query_builder' => function (EntityRepository $er) {
                    return $this->getRequestCategoryQueryBuilder($er);
                },
            ));
        } else {

            $builder->add('category', EntityType::class, array(
                'class' => 'AppTranslationalResearchBundle:RequestCategoryTypeList',
                'choice_label' => function (RequestCategoryTypeList $entity) {
                    if ($entity) {
                        return $entity->getOptimalAbbreviationName($this->priceList);
                    }
                    return '';
                },
                'label' => "Product or Service" . $this->params['categoryListLink'] . ":",
                'required' => false,
                'multiple' => false,
                'disabled' => $this->disabled,
                'attr' => array('class' => 'combobox combobox-width product-category-combobox'),
                'choices' => $this->params['projectSpecialties']
            ));
        }

        $builder->add('requested',TextType::class,array(
            'label' => "Requested Quantity:",
            'required' => true,
            'disabled' => $this->disabled,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left product-requested-quantity')
        ));

        if( $this->params["cycle"] != "new" ) {
            $builder->add('completed', TextType::class, array(
                'label' => "Completed Quantity:",
                'required' => false,
                'disabled' => $this->disabled,
                'attr' => array('class' => 'form-control digit-mask mask-text-align-left product-completed-quantity')
            ));
        }

        $builder->add('comment', null, array(
            'label' => "Comment:",
            'required' => false,
            'disabled' => $this->disabled,
            'attr' => array('class' => 'textarea form-control product-comment')
        ));

        if( $this->params['cycle'] != "new" ) {
            $trpBusinessNameAbbreviation = "TRP";
            if( isset($this->params['transresUtil']) ) {
                $trpBusinessNameAbbreviation = $this->params['transresUtil']->getBusinessEntityAbbreviation();
            }
            $builder->add('note', null, array(
                //'label' => "Note (TRP tech):",
                'label' => "Note ($trpBusinessNameAbbreviation tech):", //$this->trpBusinessNameAbbreviation
                //'label' => "Note (".$this->trpBusinessNameAbbreviation." tech):", //$this->trpBusinessNameAbbreviation
                //'label' => "Note (".$this->params['trpBusinessNameAbbreviation']." tech):", //$this->trpBusinessNameAbbreviation
                'required' => false,
                'disabled' => $this->disabled,
                'attr' => array('class' => 'textarea form-control product-note')
            ));

            //            $builder->add('notInInvoice', CheckboxType::class, array(
            //                'label' => 'Not In Invoice:',
            //                'required' => false,
            //                'attr' => array('class' => 'form-control', 'style' => 'margin:0'),
            //            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\Product',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_product';
    }


}
