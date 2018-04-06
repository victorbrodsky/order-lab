<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

//NOT USED. TODEL
class SignUpConfirmationType extends AbstractType
{

    protected $params;

    public function formConstructor( $params=null, $entity = null )
    {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->formConstructor($options['form_custom_value']);

        //$builder->add('createdate')->add('updatedate')->add('userName')->add('email')->add('firstName')->add('lastName')->add('phone')->add('salt')->add('hashPassword')->add('registrationLinkID')->add('registrationStatus')->add('ip')->add('useragent')->add('width')->add('height')->add('user')->add('site')->add('updatedby')->add('institution')->add('administrativeTitle');

        $builder->add( 'userName', TextType::class, array(
            'label'=>'User Name:',
            'disabled' => true,
            //'required'=> true,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'firstName', TextType::class, array(
            'label'=>'First Name:',
            //'required'=> true,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'lastName', TextType::class, array(
            'label'=>'Last Name:',
            //'required'=> true,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'phone', TextType::class, array(
            'label'=>'Phone Number:',
            //'required'=> true,
            'attr' => array('class'=>'form-control'),
        ));

        $builder->add( 'email', EmailType::class, array(
            'label'=>'Email:',
            'disabled' => true,
            //'required'=> true, //does not work here
            'attr' => array('class'=>'form-control'), //form-control-modif email-mask
        ));

        $builder->add('activate', SubmitType::class, array(
            'label' => 'Activate Account',
            'attr' => array('class' => 'btn btn-primary') //'onclick'=>'transresValidateHandsonTable();'
        ));

        $this->titlesSections($builder);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\SignUp',
            'form_custom_value' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_signup';
    }

    public function titlesSections($builder) {
        //Administrative Titles
        $params = array(
            'disabled'=>false,
            'label'=>'Administrative',
            'fullClassName'=>'Oleg\UserdirectoryBundle\Entity\AdministrativeTitle',
            'formname'=>'administrativetitletype',
            'cycle'=>$this->params['cycle']
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

        return $builder;
    }

}
