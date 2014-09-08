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


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oleg\OrderformBundle\Form\PerSiteSettingsType;

use Oleg\UserdirectoryBundle\Form\UserType;

class ScanUserType extends UserType
{

    public function addHookFields($builder) {

        $builder->add('perSiteSettings', 'collection', array(
            'type' => new PerSiteSettingsType($this->user,$this->roleAdmin),
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'required' => false,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__persitesettings__',
        ));

    }

}
