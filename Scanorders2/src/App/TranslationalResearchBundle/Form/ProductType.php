<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\DocumentType;
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

    public function formConstructor( $params )
    {
        $this->params = $params;
        //$this->$product = $params['product'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->formConstructor($options['form_custom_value']);

        $builder->add('id', HiddenType::class);


        $builder->add('category', EntityType::class, array(
            'class' => 'OlegTranslationalResearchBundle:RequestCategoryTypeList',
            'choice_label' => 'getOptimalAbbreviationName',
            'label'=>"Product or Service".$this->params['categoryListLink'].":",
            'required'=> false,
            'multiple' => false,
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

//        $builder->add('requested', IntegerType::class, array(
//            'label' => "Requested Quantity:",
//            'required' => false,
//            //'attr' => array('class' => 'form-control')
//        ));
//        $builder->add('requested', null, array(
//            'label' => "Requested Quantity:",
//            'required' => false,
//            'attr' => array('class' => 'product-requested-quantity')
//        ));
        $builder->add('requested',TextType::class,array(
            'label' => "Requested Quantity:",
            'required' => true,
            'attr' => array('class'=>'form-control digit-mask mask-text-align-left product-requested-quantity')
        ));

        if( $this->params["cycle"] != "new" ) {
            $builder->add('completed', null, array(
                'label' => "Completed Quantity:",
                'required' => false,
                'attr' => array('class' => 'form-control')
            ));
        }

        $builder->add('comment', null, array(
            'label' => "Comment:",
            'required' => false,
            'attr' => array('class' => 'textarea form-control')
        ));

        if( $this->params['cycle'] != "new" ) {
            $builder->add('note', null, array(
                'label' => "Note (TRP tech):",
                'required' => false,
                'attr' => array('class' => 'textarea form-control')
            ));
        }

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\TranslationalResearchBundle\Entity\Product',
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
