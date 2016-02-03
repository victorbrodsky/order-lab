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

use Doctrine\ORM\EntityRepository;
use Oleg\OrderformBundle\Form\PerSiteSettingsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class AccessRequestUserType extends AbstractType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('roles', 'choice', array(
            'choices' => $this->params['roles'],
            'label' => ucfirst($this->params['sitename']) . ' Role(s):',
            'attr' => array('class' => 'combobox combobox-width'),
            'multiple' => true,
        ));


//        if(0) {
//            $builder->add( 'permittedInstitutionalPHIScope', 'entity', array(
//                'class' => 'OlegUserdirectoryBundle:Institution',
//                'mapped' => false,
//                //'property' => 'name',
//                'property' => 'getTreeName',
//                'label'=>'Institutional PHI Scope:',
//                'required' => false,
//                'multiple' => true,
//                'attr' => array('class'=>'combobox combobox-width'),
//                'query_builder' => function(EntityRepository $er) {
//                        return $er->createQueryBuilder('list')
//                            ->leftJoin("list.types","institutiontype")
//                            //->where("(list.type = :typedef OR list.type = :typeadd) AND institutiontype.name = :medicalInstitution")
//                            ->where("list.type = :typedef OR list.type = :typeadd")
//                            ->orderBy("list.orderinlist","ASC")
//                            ->setParameters( array(
//                                'typedef' => 'default',
//                                'typeadd' => 'user-added',
//                                //'medicalInstitution' => 'Medical'
//                            ));
//                    },
//            ));
//        }

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_userdirectorybundle_user';
    }



}
