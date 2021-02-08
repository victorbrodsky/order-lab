<?php

namespace App\TranslationalResearchBundle\Form;

use App\TranslationalResearchBundle\Entity\RequestCategoryTypeList;
use App\TranslationalResearchBundle\Util\TransResUtil;
use Doctrine\ORM\EntityRepository;
use App\UserdirectoryBundle\Form\DocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{

    //protected $product;
    protected $params;
    protected $priceList;
    //protected $trpBusinessNameAbbreviation;

//    public function __construct(TransResUtil $transresUtil)
//    {
//        $trpBusinessNameAbbreviation = $transresUtil->getBusinessEntityAbbreviation();
//        $this->trpBusinessNameAbbreviation = $trpBusinessNameAbbreviation;
//    }

    public function formConstructor( $params )
    {
        $this->params = $params;
        //$this->$product = $params['product'];

//        if( isset($params['transresUtil']) ) {
//            $this->trpBusinessNameAbbreviation = $params['transresUtil']->getBusinessEntityAbbreviation();
//        }

        $this->priceList = NULL;
        if( isset($this->params['transresRequest']) ) {
            $workRequest = $this->params['transresRequest'];
            $project = $workRequest->getProject();
            //echo "project=".$project."<br>";
            if( $project ) {
                $this->priceList = $project->getPriceList();
            }
        }
        //echo "priceList=".$this->priceList."<br>";
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('id', HiddenType::class);

//        $builder->add('category', EntityType::class, array(
//            'class' => 'AppTranslationalResearchBundle:RequestCategoryTypeList',
//            'choice_label' => 'getOptimalAbbreviationName',
//            'label'=>"Product or Service".$this->params['categoryListLink'].":",
//            'required'=> false,
//            'multiple' => false,
//            'attr' => array('class'=>'combobox combobox-width'),
//            'query_builder' => function(EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist","ASC")
//                    ->setParameters( array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));
        //dynamically get label and price according to the priceList
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
            'choice_label' => function(RequestCategoryTypeList $entity) {
                if( $entity ) {
                    return $entity->getOptimalAbbreviationName($this->priceList);
                }
                return '';
            },
            'label'=>"Product or Service".$this->params['categoryListLink'].":",
            'required'=> false,
            'multiple' => false,
            'attr' => array('class'=>'combobox combobox-width product-category-combobox'),
            'query_builder' => function(EntityRepository $er) {
                return $this->getRequestCategoryQueryBuilder($er);
            },
        ));

        $builder->add('requested',TextType::class,array(
            'label' => "Requested Quantity:",
            'required' => true,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left product-requested-quantity')
        ));

        if( $this->params["cycle"] != "new" ) {
            $builder->add('completed', TextType::class, array(
                'label' => "Completed Quantity:",
                'required' => false,
                'attr' => array('class' => 'form-control digit-mask mask-text-align-left')
            ));
        }

        $builder->add('comment', null, array(
            'label' => "Comment:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
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
                'attr' => array('class' => 'textarea form-control')
            ));
        }

    }

    public function getRequestCategoryQueryBuilder(EntityRepository $er) {

        //'class' => 'AppTranslationalResearchBundle:RequestCategoryTypeList',
        $workRequest = NULL;
        $projectSpecialtyIdsArr = array();
        if( isset($this->params['transresRequest']) ) {
            $workRequest = $this->params['transresRequest'];
            //echo "workRequest=".$workRequest->getId()."<br>";
            if( $workRequest ) {
                $projectSpecialty = $workRequest->getProjectSpecialty();
                if( $projectSpecialty ) {
                    $projectSpecialtyIdsArr[] = $projectSpecialty->getId();
                }
            }
        }
        //dump($projectSpecialtyIdsArr);
        //exit('111');

        //TODO: do not show if fee is zero using $this->priceList
        //$feeRestriction = "";
        $feeRestriction = "(list.fee IS NOT NULL AND list.fee <> '0')";
        if( 0 && $this->priceList ) {
            $priceListId = $this->priceList->getId();
            if( $priceListId ) {
                $specificFeeRestriction = "(prices.id = $priceListId AND prices.fee IS NOT NULL)";
                $specificFeeRestriction = "(prices.id = $priceListId)";
                //$feeRestriction = $feeRestriction . " OR ";
                $feeRestriction = $feeRestriction . $specificFeeRestriction;
                //echo $this->priceList.": feeRestriction = $feeRestriction<br>";
            }
        }

        if( $workRequest && count($projectSpecialtyIdsArr) > 0 ) {
            //AppTranslationalResearchBundle:RequestCategoryTypeList

            $queryBuilder = $er->createQueryBuilder('list')
                ->leftJoin('list.projectSpecialties','projectSpecialties')
                ->leftJoin('list.prices','prices')
                ->where("list.type = :typedef OR list.type = :typeadd")
                ->andWhere("projectSpecialties.id IN (:projectSpecialtyIdsArr)") //show categories with this specialty only
                //->andWhere("projectSpecialties.id IN (:projectSpecialtyIdsArr) OR projectSpecialties.id IS NULL") //show categories with this specialty only OR categories without specialty
                ->andWhere($feeRestriction)
                ->orderBy("list.orderinlist","ASC")
                ->setParameters( array(
                    'typedef' => 'default',
                    'typeadd' => 'user-added',
                    'projectSpecialtyIdsArr' => $projectSpecialtyIdsArr
                ));
        } else {
            $queryBuilder = $er->createQueryBuilder('list')
                ->leftJoin('list.prices','prices')
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
