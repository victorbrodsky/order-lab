<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/13/13
 * Time: 5:02 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form\CustomType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Oleg\OrderformBundle\Form\DataTransformer\StainTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;


class StainSelectorType extends AbstractType {

    /**
     * @var ObjectManager
     * @var SecurityContext
     */
    private $om;
    private $sc;

     /**
     * @param ObjectManager $om
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, SecurityContext $sc)
    {
        $this->om = $om;
        $this->sc = $sc;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $username = $this->sc->getToken()->getUser();

        $transformer = new StainTransformer($this->om, $username);
        $builder->addModelTransformer($transformer);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selected stain does not exist',
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'stain_selector';
    }


}