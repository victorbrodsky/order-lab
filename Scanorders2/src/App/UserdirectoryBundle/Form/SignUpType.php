<?php

namespace App\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignUpType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$builder->add('createdate')->add('updatedate')->add('userName')->add('email')->add('firstName')->add('lastName')->add('phone')->add('salt')->add('hashPassword')->add('registrationLinkID')->add('registrationStatus')->add('ip')->add('useragent')->add('width')->add('height')->add('user')->add('site')->add('updatedby')->add('institution')->add('administrativeTitle');

        $builder->add( 'userName', TextType::class, array(
            'label'=>'User Name:',
            //'required'=> true,
            'attr' => array('class'=>'form-control'),
        ));

        //password RepeatedType::class
        $builder->add( 'hashPassword', PasswordType::class, array(
            //'mapped' => false,
            'label'=>'Password:',
            'attr' => array('class' => 'form-control')
        ));

        $builder->add( 'email', EmailType::class, array(
            'label'=>'Email:',
            //'required'=> true, //does not work here
            'attr' => array('class'=>'form-control'), //form-control-modif email-mask
        ));

        //used to display recaptcha error
        $builder->add( 'recaptcha', HiddenType::class, array(
            'mapped' => false,
            'error_bubbling' => false,
            'label' => false,
            'attr' => array('class'=>'form-control g-recaptcha1'),
        ));

        $builder->add('submit', SubmitType::class, array(
            'label' => 'Sign Up',
            'attr' => array('class' => 'btn btn-primary') //'onclick'=>'transresValidateHandsonTable();'
        ));

    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'App\UserdirectoryBundle\Entity\SignUp'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oleg_userdirectorybundle_signup';
    }


}
