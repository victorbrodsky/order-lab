<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\FellAppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class FellAppUserType extends UserType
{

    public function __construct( $params )
    {

        parent::__construct($params);

        if( $this->sc->isGranted('ROLE_FELLAPP_ADMIN') || $this->sc->isGranted('ROLE_PLATFORM_DEPUTY_ADMIN') ) {
            $this->roleAdmin = true;
        } else {
            $this->roleAdmin = false;
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        //Name and Preferred Contact Info
        $this->addUserInfos($builder);

        $this->userTrainings($builder);

        $this->userLocations($builder);

        $this->addCredentials($builder);

    }



    public function userLocations($builder) {
        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
        $builder->add('locations', 'collection', array(
            'type' => new FellAppLocationType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__locations__',
        ));

        return $builder;
    }


    public function userTrainings($builder) {
        $params = array('read_only'=>$this->readonly,'admin'=>$this->roleAdmin,'currentUser'=>$this->currentUser,'cycle'=>$this->cycle,'em'=>$this->em,'subjectUser'=>$this->subjectUser);
        $builder->add('trainings', 'collection', array(
            'type' => new FellAppTrainingType($params),
            'label' => false,
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'prototype' => true,
            'prototype_name' => '__trainings__',
        ));

        return $builder;
    }

}
