<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/14/14
 * Time: 1:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PrincipalType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        $this->params = $params;
        $this->entity = $entity; //current project entity
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'name', 'text', array(
            'label' => 'Principal Investigator (as entered by user):',
            'required' => true,
            'read_only' => true,
            'attr'=>array('class'=>'form-control form-control-modif'),
        ));

        $builder->add('principal', 'entity', array(
        'class' => 'OlegUserdirectoryBundle:User',
        'label'=>'Principal Investigator:',
        'required' => false,
        //'read_only' => true,    //not working => disable by twig
        //'multiple' => true,
        'attr' => array('class' => 'combobox combobox-width'),
        'query_builder' => function(EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->where('u.locked=:locked AND u.roles LIKE :role')
                ->setParameters( array(
                    'locked' => '0',
                    'role' => '%"' . 'ROLE_SCANORDER_PRINCIPAL_INVESTIGATOR' . '"%',
                ));
        },
    ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\UserdirectoryBundle\Entity\PIList'
        ));
    }

    public function getName()
    {
        return 'oleg_userdirectorybundle_principaltype';
    }
}
