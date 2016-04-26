<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oleg\VacReqBundle\Form;

use Doctrine\ORM\EntityRepository;
use Oleg\UserdirectoryBundle\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;



class VacReqUserComboboxType extends UserType
{

    protected $params;

    public function __construct( $params )
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('users', 'entity', array(
            'class' => 'OlegUserdirectoryBundle:User',
            'label' => $this->params['btnName'].":",    //"New Submitters:",
            'required' => false,
            'multiple' => true,
            //'property' => 'name',
            'attr' => array('class' => 'combobox vacreq-emailusers'),
            //'read_only' => ($this->params['review'] ? true : false),
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('user')
                    ->leftJoin("user.infos","infos")
                    ->leftJoin("user.employmentStatus", "employmentStatus")
                    ->leftJoin("employmentStatus.employmentType", "employmentType")
                    ->andWhere("user.keytype IS NOT NULL AND user.primaryPublicUserId != 'system'")
                    ->andWhere("employmentType.name != 'Pathology Fellowship Applicant' OR employmentType.id IS NULL")
                    ->orderBy("infos.lastName","ASC");
            },
        ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            //'data_class' => 'Oleg\UserdirectoryBundle\Entity\User',
            'csrf_protection' => false,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'oleg_vacreqbundle_user_participants';
    }

}
