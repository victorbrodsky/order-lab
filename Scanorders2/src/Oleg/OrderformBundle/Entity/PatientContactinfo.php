<?php

namespace Oleg\OrderformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Oleg\OrderformBundle\Entity\PatientArrayFieldAbstract;


/**
 * @ORM\Entity
 * @ORM\Table(name="scan_patientContactinfo")
 */
class PatientContactinfo extends PatientArrayFieldAbstract
{

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="contactinfo")
     * @ORM\JoinColumn(name="patient_id", referencedColumnName="id", nullable=true)
     */
    protected $patient;

    //Location Timestamp:

    //Location Role: [Select2 list of location roles - same idea as abstracting from users via roles, but this abstracts from locations; add to List Manager; values: "Pick Up", "Accessioning", "Storage", "Filing Room", "Off Site Slide Storage", "Patient Contact Information"]
    //Location: [a set of links to the Locations table, filter by type "Patient Contact Information"]
    //Location Source System: [select2 listing systems]
    //Location Submitter: [select2 user list]
    //Location Submission Timestamp: [timestamp]

    //As I understand, the location is our existing object and the patient should be linked to a such location.
    //Our existing Location object has all fields except source field. So, I'll create a new object
    //PatientContactinfo with 2 fields: "Source System" (already exists in base class) and a link to our existing Location.

    /**
     * @ORM\ManyToOne(targetEntity="Oleg\UserdirectoryBundle\Entity\Location", cascade={"persist"})
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true)
     */
    protected $field;




}