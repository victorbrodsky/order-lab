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
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('codeNYPH', null, array(
            'label' => 'NYPH Code:',
            'attr' => array('class'=>'form-control form-control-modif')
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
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
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
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

        $builder->add('stateLicense', null, array(
            'label' => 'License Number:',
            'attr' => array('class'=>'form-control form-control-modif')
        ));

        $builder->add('stateLicenseExpirationDate', 'date', array(
            'label' => 'License Expiration Date:',
            'attr' => array('class' => 'datepicker form-control patientdob-mask'),
        ));

//        $builder->add('stateLicense', null, array(
//            'label' => 'State License(s):',
//            'attr' => array('class'=>'form-control form-control-modif')
//        ));

        

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
