<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Helper\TimeZoneUtil;

class UserPreferencesType extends AbstractType
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

        //timezone
        $tzUtil = new TimeZoneUtil();

        $builder->add( 'timezone', 'choice', array(
            'label' => 'Time Zone:',
            'choices' => $tzUtil->tz_list(),
            'required' => true,
            'preferred_choices' => array('America/New_York'),
            'attr' => array('class' => 'combobox combobox-width')
        ));

        $builder->add('tooltip', 'checkbox', array(
            'required' => false,
            'label' => 'Show tool tips for locked fields:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));


    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\UserPreferences'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_orderformbundle_userpreferences';
    }

}
