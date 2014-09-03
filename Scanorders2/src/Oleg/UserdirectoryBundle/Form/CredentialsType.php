<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class CredentialsType extends AbstractType
{

//    protected $cicle;
//    protected $roleAdmin;
//    protected $user;
//    protected $roles;

    public function __construct()
    {
//        $this->cicle = $cicle;
//        $this->user = $user;
//        $this->roleAdmin = $roleAdmin;
//        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('employeeId', null, array(
            'label' => 'Employee Identification Number (EIN):',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('dob', 'date', array(
            'label' => 'Date of Birth:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('nationalProviderIdentifier', null, array(
            'label' => 'National Provider Identifier (NPI):',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('numberCLIA', null, array(
            'label' => 'Clinical Laboratory Improvement Amendments (CLIA) Number:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('cliaExpirationDate', 'date', array(
            'label' => 'CLIA Expiration Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('numberPFI', null, array(
            'label' => 'NY Permanent Facility Identifier (PFI) Number:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('numberCOQ', null, array(
            'label' => 'Certificate of Qualification (COQ):',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('coqExpirationDate', 'date', array(
            'label' => 'COQ Expiration Date:',
            'widget' => 'single_text',
            'required' => false,
            'format' => 'MM-dd-yyyy',
            'attr' => array('class' => 'datepicker form-control'),
        ));

        $builder->add('emergencyContactInfo', null, array(
            'label' => 'Emergency Contact Information:',
            'attr' => array('class'=>'textarea form-control')
        ));


        $builder->add('codeNYPH', 'collection', array(
            'type' => new CodeNYPHType(),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__codenyph__',
        ));

        $builder->add('stateLicense', 'collection', array(
            'type' => new StateLicenseType(),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__statelicense__',
        ));

        $builder->add('boardCertification', 'collection', array(
            'type' => new BoardCertificationType(),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__boardcertification__',
        ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\UserPreferences'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_userdirectorybundle_userpreferences';
    }

}
