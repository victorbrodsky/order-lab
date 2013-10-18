<?php
/**
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/18/13
 * Time: 1:05 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Oleg\OrderformBundle\Helper;

use Oleg\OrderformBundle\Entity\Patient;

/**
 * Singleton class
 *
 */
final class NodeFactory {

    protected $em;

    /**
     * Call this method to get singleton
     *
     * @return NodeFactory
     */
    public static function Instance($em)
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new NodeFactory($em);
        }
        return $inst;
    }

    private function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Get next available MRN from DB
     */
    public function getMrn() {
        $em = $this->em;
        $mrn = $em->getRepository('OlegOrderformBundle:Patient')->getNextMrn();
        $patient = new Patient();
        $patient->setMrn($mrn);
        $em->persist($patient);

        return $mrn;
    }

}