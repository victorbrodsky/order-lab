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
use Doctrine\ORM\EntityRepository;

use Oleg\UserdirectoryBundle\Util\TimeZoneUtil;

class UserPreferencesType extends AbstractType
{

//    protected $cycle;
//    protected $roleAdmin;
//    protected $user;
//    protected $roles;

    public function __construct()
    {
//        $this->cycle = $cycle;
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
            //'label' => $translator->translate('timezone',$formtype,'Time Zone:'),
            'choices' => $tzUtil->tz_list(),
            'required' => true,
            'preferred_choices' => array('America/New_York'),
            'attr' => array('class' => 'combobox combobox-width')
        ));

//        $builder->add('tooltip', 'checkbox', array(
//            'required' => false,
//            'label' => 'Show tool tips for locked fields:',
//            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
//        ));


        $builder->add( 'languages', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:LanguageList',
            'label'=> "Language(s):",
            'required'=> false,
            'multiple' => true,
            'property' => 'fulltitle',
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
        ));


        $builder->add( 'locale', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:LocaleList',
            'label'=> "Locale:",
            'required'=> false,
            'multiple' => false,
            'property' => 'fulltitle',
            'attr' => array('class'=>'combobox combobox-width'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("list.type = :typedef OR list.type = :typeadd")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                        ));
                },
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
