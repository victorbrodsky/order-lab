<?php

namespace Oleg\TranslationalResearchBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\AdministrativeTitleType;
use Oleg\UserdirectoryBundle\Form\UserInfoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountConfirmationType extends AbstractType
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


        $builder->add('username', TextType::class, array(
            'label' => false,
            'required' => true,
            'attr' => array('class' => 'form-control'),
        ));

        $builder->add('infos', CollectionType::class, array(
            'entry_type' => UserInfoType::class,
            'label' => false,
            'required' => true,
            //'allow_add' => true,
            //'allow_delete' => true,
            //'by_reference' => true,
            //'prototype' => true,
            //'prototype_name' => '__infos__',
        ));

        $builder->add('update', SubmitType::class, array(
            'label' => "Confirm",
            'attr' => array('class' => 'btn btn-warning')
        ));


        $params = array(
            'disabled'=>false,
            'label'=>'Administrative',
            'fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle',
            'formname'=>'administrativetitletype',
            'cycle'=>$this->cycle
        );
        $params = array_merge($this->params, $params);
        $builder->add('administrativeTitles', CollectionType::class, array(
            'entry_type' => AdministrativeTitleType::class,
            'entry_options' => array(
                'form_custom_value' => $params
            ),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__administrativetitles__',
        ));

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'form_custom_value' => null,
            'csrf_protection' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_accountconfirmationtype';
    }


}
