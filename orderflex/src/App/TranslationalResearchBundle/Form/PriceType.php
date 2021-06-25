<?php

namespace App\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceType extends AbstractType
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

        $builder->add('id',HiddenType::class,array(
            'label'=>false,
            'attr' => array('class'=>'user-object-id-field')
        ));

        $builder->add('fee', null, array(
            'label' => "Fee per unit for initial quantity ($):",
            'required' => false,
            'attr' => array('class' => 'form-control pricetype-fee currency-mask-without-prefix')
        ));

        $builder->add('feeAdditionalItem', null, array(
            'label' => "Fee per additional item ($):",
            'required' => false,
            'attr' => array('class' => 'form-control pricetype-feeadditionalitem currency-mask-without-prefix')
        ));

        $builder->add('initialQuantity', NumberType::class, array(
            'label' => "Initial Quantity:",
            'required' => false,
            'attr' => array('class' => 'form-control pricetype-initialQuantity digit-mask')
        ));


        $builder->add('priceList', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:PriceTypeList',
            'choice_label' => 'name',
            'label' => 'Utilize the following price list:',
            //'disabled' => ($this->params['admin'] ? false : true),
            //'disabled' => true,
            'required' => false,
            'multiple' => false,
            'attr' => array('class' => 'combobox combobox-width'),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("list.type = :typedef OR list.type = :typeadd")
                    ->orderBy("list.orderinlist", "ASC")
                    ->setParameters(array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));

        $builder->add( 'workQueues', EntityType::class, array(
            'class' => 'AppTranslationalResearchBundle:WorkQueueList',
            'label'=>'Work Queues:',
            'required'=> false,
            'multiple' => true,
            'attr' => array('class'=>'combobox'),
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('list')
                    ->where("(list.type = :typedef OR list.type = :typeadd)")
                    ->orderBy("list.orderinlist","ASC")
                    ->setParameters( array(
                        'typedef' => 'default',
                        'typeadd' => 'user-added',
                    ));
            },
        ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\TranslationalResearchBundle\Entity\Prices',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_translationalresearchbundle_prices';
    }


}
