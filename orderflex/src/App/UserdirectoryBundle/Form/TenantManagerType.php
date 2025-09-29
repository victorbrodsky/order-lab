<?php
/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 2/5/2024
 * Time: 10:52 AM
 */

namespace App\UserdirectoryBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class TenantManagerType extends AbstractType
{
    protected $params;
    protected $btnName;

    public function formConstructor( $params=null )
    {
        $this->params = $params;
        $this->btnName = "Update Tenants in DB";
    }

    /**
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        if( $this->params['tenantRole'] == 'homepagemanager' ) {
            $builder->add('logos', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'label' => 'Header Image:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));

            $builder->add('greeting', null, array(
                'label' => 'Greeting text:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));

            $builder->add('maintext', null, array(
                'label' => 'Main text:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));

            $builder->add('footer', null, array(
                'label' => 'Footer:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));

            //About us
            $builder->add('aboutusLogos', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'label' => 'About Us Page Logos:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));

            $builder->add('aboutusText', null, array(
                'label' => 'About Us Page Text:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));

            $builder->add('aboutusFooter', null, array(
                'label' => 'About Us Page Footer:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));


            $builder->add('servicestext', null, array(
                'label' => 'Consolidated Services Homepage Section Content:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));
            $builder->add('servicesShow', null, array(
                'label' => 'Show ‘Consolidated Services’ section on the homepage:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));
            $builder->add('width', null, array(
                'label' => 'Logo width:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));
            $builder->add('height', null, array(
                'label' => 'Logo height:',
                'required' => false,
                'attr' => array('class' => 'form-control textarea')
            ));
            //High Resolution Logo (2x)
            $builder->add('highResLogos', CollectionType::class, array(
                'entry_type' => DocumentType::class,
                'label' => 'High Resolution Header Image:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__documentsid__',
            ));


            $this->btnName = "Update Homepage";
        }

        if( $this->params['tenantRole'] == 'tenantmanager' ) {
            $builder->add('tenants', CollectionType::class, array(
                'entry_type' => TenantType::class,
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__tenants__',
            ));
        }

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

        if( $this->params['cycle'] == 'edit' ) {
            $builder->add('submit', SubmitType::class, array(
                'label' => $this->btnName, //'Update Tenants in DB',
                'attr' => array('class' => 'btn btn-primary')
            ));
        }
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