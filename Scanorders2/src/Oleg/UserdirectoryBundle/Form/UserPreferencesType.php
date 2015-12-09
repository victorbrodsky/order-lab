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

    protected $params;
//    protected $cycle;
//    protected $roleAdmin;
//    protected $user;
    protected $roles;

    public function __construct($params)
    {
        $this->params = $params;
//        $this->cycle = $cycle;
//        $this->user = $user;
//        $this->roleAdmin = $roleAdmin;
//        $this->roles = $roles;

        if( array_key_exists('roles', $params) ) {
            $this->roles = $params['roles'];
        } else {
            $this->roles = null;
        }
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



        $builder->add('excludeFromSearch', 'checkbox', array(
            'required' => false,
            'label' => 'Exclude from Employee Directory search results:',
            'attr' => array('class'=>'form-control form-control-modif', 'style'=>'margin:0')
        ));

        $builder->add('hide', 'checkbox', array(
            'required' => false,
            'label' => 'Hide this profile:',
            'attr' => array('class'=>'form-control form-control-modif user-preferences-hide', 'style'=>'margin:0')
        ));

        $builder->add( 'showToInstitutions', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:Institution',
            //'property' => 'name',
            'property' => 'getTreeName',
            'label'=>'Only show this profile to members of the following institution(s):',
            'required'=> false,
            'multiple' => true,
            //'empty_value' => false,
            'attr' => array('class' => 'combobox combobox-width user-preferences-showToInstitutions'),
            'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('list')
                        ->where("(list.type = :typedef OR list.type = :typeadd) AND list.level = :level")
                        ->orderBy("list.orderinlist","ASC")
                        ->setParameters( array(
                            'typedef' => 'default',
                            'typeadd' => 'user-added',
                            'level' => 0
                        ));
                },
        ));

        $builder->add('showToRoles', 'choice', array(
            'choices' => $this->roles,
            'label' => 'Only show this profile to users with the following roles:',
            'attr' => array('class' => 'combobox combobox-width user-preferences-showToRoles'),
            'multiple' => true,
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
