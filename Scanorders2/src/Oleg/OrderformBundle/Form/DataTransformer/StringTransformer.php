<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class StringTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $em;
    private $user;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $em=null, $user=null)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Transforms an object to a string.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($entity)
    {
        //echo "data transformer: ".$entity."<br>";
        if (null === $entity) {
            return "";
        }

        return $entity;  //Scan Region: entity is a string
    }

    /**
     * Transforms a string (number) to an object (i.e. stain).
     *
     * @param  string $number
     *
     * @return Stain|null
     *
     * @throws TransformationFailedException if object (stain) is not found.
     */
    public function reverseTransform($text)
    {
        //echo "data reverseTransform text=".$text."<br>";
        //exit();

        if (!$text) {
            return null;
        }

        return $text;
    }

}