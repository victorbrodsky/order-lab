<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 9/12/13
 * Time: 3:47 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\UserdirectoryBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class StringToBooleanTransformer implements DataTransformerInterface
{
    private $em;
    private $user;

    public function __construct(ObjectManager $em=null, $user=null)
    {
        $this->em = $em;
        $this->user = $user;
    }

    /**
     * Transforms a string to boolean.
     */
    public function transform($string)
    {
        if( strpos($string,'Yes') !== false || strpos($string,'yes') !== false ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Transforms boolean to a string.
     */
    public function reverseTransform($boolean)
    {
        if( $boolean == true ) {
            return 'Yes';
        } else {
            return 'No';
        }
    }

}