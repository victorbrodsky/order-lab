<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 4/14/14
 * Time: 1:09 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class DirectorType extends AbstractType
{

    protected $entity;
    protected $params;

    public function __construct( $params=null, $entity=null )
    {
        $this->params = $params;
        $this->entity = $entity; //current course entity
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add( 'name', 'text', array(
            'label' => 'Course Director (as entered by user):',
            'required' => true,
            'read_only' => true,
            'attr'=>array('class'=>'form-control form-control-modif'),
        ));

        $builder->add('director', 'entity', array(
        'class' => 'OlegOrderformBundle:User',
        'label'=>'Course Director:',
        'required' => false,
        //'read_only' => true,    //not working => disable by twig
        //'multiple' => true,
        'attr' => array('class' => 'combobox combobox-width'),
        'query_builder' => function(EntityRepository $er) {
            return $er->createQueryBuilder('u')
                ->where('u.locked=:locked AND u.roles LIKE :role')
                ->setParameters( array(
                    'locked' => '0',
                    'role' => '%"' . 'ROLE_COURSE_DIRECTOR' . '"%',
                ));
        },
    ));

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oleg\OrderformBundle\Entity\DirectorList'
        ));
    }

    public function getName()
    {
        return 'oleg_orderformbundle_directortype';
    }
}
