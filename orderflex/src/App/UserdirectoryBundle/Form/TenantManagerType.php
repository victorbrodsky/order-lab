<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/5/2024
 * Time: 10:52 AM
 */

namespace App\UserdirectoryBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class TenantManagerType extends AbstractType
{
    protected $params;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        $builder->add('greeting',null,array(
            'label' => 'Greeting text:',
            'required' => false,
            'attr' => array('class'=>'form-control textarea')
        ));

        $builder->add('maintext',null,array(
            'label' => 'Main text:',
            'required' => false,
            'attr' => array('class'=>'form-control textarea')
        ));

        $builder->add('footer',null,array(
            'label' => 'Footer:',
            'required' => false,
            'attr' => array('class'=>'form-control textarea')
        ));

//        $builder->add('authServerNetwork', EntityType::class, array(
//            'class' => 'App\UserdirectoryBundle\Entity\AuthServerNetworkList',
//            'label' => "Server Network Accessibility and Role ('Internet (Hub)' option will enable multi-tenancy):",
//            'required' => false,
//            'multiple' => false,
//            'attr' => array('class' => 'combobox combobox-width'),
//            'query_builder' => function (EntityRepository $er) {
//                return $er->createQueryBuilder('list')
//                    ->where("list.type = :typedef OR list.type = :typeadd")
//                    ->orderBy("list.orderinlist", "ASC")
//                    ->setParameters(array(
//                        'typedef' => 'default',
//                        'typeadd' => 'user-added',
//                    ));
//            },
//        ));

        $builder->add('submit', SubmitType::class, array(
            'label' => 'Save',
            'attr' => array('class'=>'btn btn-primary')
        ));
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\TenantManager',
            'form_custom_value' => null
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'oleg_userdirectorybundle_tenantmanager';
    }
}