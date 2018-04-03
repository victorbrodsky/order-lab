<?php

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignUpType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('createdate')->add('updatedate')->add('userName')->add('email')->add('firstName')->add('lastName')->add('phone')->add('salt')->add('hashPassword')->add('registrationLinkID')->add('registrationStatus')->add('ip')->add('useragent')->add('width')->add('height')->add('user')->add('site')->add('updatedby')->add('institution')->add('administrativeTitle');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\SignUp'
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
