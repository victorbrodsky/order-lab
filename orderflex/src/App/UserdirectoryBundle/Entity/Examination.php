<?php
/**
 * Copyright (c) 2017 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App\UserdirectoryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Table(name: 'user_examination')]
#[ORM\Entity]
class Examination
{

    /**
     * @var integer
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'date', nullable: true)]
    private $creationDate;

    #[ORM\ManyToOne(targetEntity: 'User')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: true)]
    private $createdBy;

    #[ORM\ManyToOne(targetEntity: 'Credentials', inversedBy: 'examinations')]
    #[ORM\JoinColumn(name: 'credentials_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $credentials;


    #[ORM\JoinTable(name: 'user_examination_score')]
    #[ORM\JoinColumn(name: 'examination_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'score_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: 'Document')]
    #[ORM\OrderBy(['createdate' => 'ASC'])]
    private $scores;

    //USMLE Step 1 Score,	USMLE Step 2 CK Score,	USMLE Step 2 CS Score (Pass/Fail),	USMLE Step 3 Score
    //USMLE Step 1 results are now reported a pass or fail only, as the exam transitioned to this format in January 2022.
    //Plus, 'No Score' can be provided without pass date (required by Clinical Chemistry Fellowship).
    #[ORM\Column(type: 'string', nullable: true)]
    private $USMLEStep1Score;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $USMLEStep1Percentile;

    #[ORM\Column(type: 'date', nullable: true)]
    private $USMLEStep1DatePassed;

    #[ORM\Column(type: 'string', nullable: true)]
    private $USMLEStep2CKScore;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $USMLEStep2CKPercentile;

    #[ORM\Column(type: 'date', nullable: true)]
    private $USMLEStep2CKDatePassed;

    /**
     * USMLE Step 2 CS Score (Pass/Fail)
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private $USMLEStep2CSScore;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $USMLEStep2CSPercentile;

    #[ORM\Column(type: 'date', nullable: true)]
    private $USMLEStep2CSDatePassed;

    #[ORM\Column(type: 'string', nullable: true)]
    private $USMLEStep3Score;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $USMLEStep3Percentile;

    #[ORM\Column(type: 'date', nullable: true)]
    private $USMLEStep3DatePassed;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $ECFMGCertificate;

    #[ORM\Column(type: 'string', nullable: true)]
    private $ECFMGCertificateNumber;

    #[ORM\Column(type: 'date', nullable: true)]
    private $ECFMGCertificateDate;

    //COMLEX-USA Level 1 Score,	COMLEX-USA Level 2 CE Score, COMLEX-USA Level 2 PE Score (Pass/Fail), COMLEX-USA Level 3 Score
    //COMLEX Level 1 result is pass/fail since May 10, 2022.
    #[ORM\Column(type: 'string', nullable: true)]
    private $COMLEXLevel1Score;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $COMLEXLevel1Percentile;

    #[ORM\Column(type: 'date', nullable: true)]
    private $COMLEXLevel1DatePassed;

    #[ORM\Column(type: 'date', nullable: true)]
    private $COMLEXLevel2DatePassed;

    #[ORM\Column(type: 'string', nullable: true)]
    private $COMLEXLevel2Score;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $COMLEXLevel2Percentile;

    //COMLEX-USA Level 2 PE Score (Pass/Fail)
    #[ORM\Column(type: 'date', nullable: true)]
    private $COMLEXLevel2PEDatePassed;

    #[ORM\Column(type: 'string', nullable: true)]
    private $COMLEXLevel2PEScore;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $COMLEXLevel2PEPercentile;

    #[ORM\Column(type: 'string', nullable: true)]
    private $COMLEXLevel3Score;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $COMLEXLevel3Percentile;

    #[ORM\Column(type: 'date', nullable: true)]
    private $COMLEXLevel3DatePassed;


    public function __construct( $user=null ) {
        if( $user ) {
            $this->setCreatedBy($user);
        }
        $this->setCreationDate( new \DateTime());

        $this->scores = new ArrayCollection();
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return mixed
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function addScore($item)
    {
        if( $item && !$this->scores->contains($item) ) {
            $this->scores->add($item);
            $item->createUseObject($this);
        }
        return $this;
    }
    public function removeScore($item)
    {
        $this->scores->removeElement($item);
        $item->clearUseObject();
    }
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * @param mixed $USMLEStep1DatePassed
     */
    public function setUSMLEStep1DatePassed($USMLEStep1DatePassed)
    {
        $this->USMLEStep1DatePassed = $USMLEStep1DatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep1DatePassed()
    {
        return $this->USMLEStep1DatePassed;
    }

    /**
     * @param mixed $USMLEStep1Score
     */
    public function setUSMLEStep1Score($USMLEStep1Score)
    {
        $this->USMLEStep1Score = $USMLEStep1Score;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep1Score( $onlyValid=false )
    {
        //USMLE Step 1 results are now reported a pass/fail only, as the exam transitioned to this format in January 2022.
        return $this->USMLEStep1Score;

//        if( $onlyValid ) {
//            //return $this->getUsmleIfValid($this->USMLEStep1Score);
//            if( $this->getUsmleIfValid($this->USMLEStep1Score) || $this->isPassFailfValidation($this->USMLEStep1Score) ) {
//                return $this->USMLEStep1Score;
//            } else {
//                return NULL;
//            }
//        }
//        return $this->USMLEStep1Score;
    }

    /**
     * @param mixed $USMLEStep2CKDatePassed
     */
    public function setUSMLEStep2CKDatePassed($USMLEStep2CKDatePassed)
    {
        $this->USMLEStep2CKDatePassed = $USMLEStep2CKDatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CKDatePassed()
    {
        return $this->USMLEStep2CKDatePassed;
    }

    /**
     * @param mixed $USMLEStep2CKScore
     */
    public function setUSMLEStep2CKScore($USMLEStep2CKScore)
    {
        $this->USMLEStep2CKScore = $USMLEStep2CKScore;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CKScore( $onlyValid=false )
    {
        if( $onlyValid ) {
            return $this->getUsmleIfValid($this->USMLEStep2CKScore);
        }
        return $this->USMLEStep2CKScore;
    }

    /**
     * @param mixed $USMLEStep2CSDatePassed
     */
    public function setUSMLEStep2CSDatePassed($USMLEStep2CSDatePassed)
    {
        $this->USMLEStep2CSDatePassed = $USMLEStep2CSDatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CSDatePassed()
    {
        return $this->USMLEStep2CSDatePassed;
    }

    /**
     * @param mixed $USMLEStep2CSScore
     */
    public function setUSMLEStep2CSScore($USMLEStep2CSScore)
    {
        $this->USMLEStep2CSScore = $USMLEStep2CSScore;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CSScore()
    {
        return $this->USMLEStep2CSScore;
    }

    /**
     * @param mixed $USMLEStep3DatePassed
     */
    public function setUSMLEStep3DatePassed($USMLEStep3DatePassed)
    {
        $this->USMLEStep3DatePassed = $USMLEStep3DatePassed;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep3DatePassed()
    {
        return $this->USMLEStep3DatePassed;
    }

    /**
     * @param mixed $USMLEStep3Score
     */
    public function setUSMLEStep3Score($USMLEStep3Score)
    {
        $this->USMLEStep3Score = $USMLEStep3Score;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep3Score( $onlyValid=false )
    {
        if( $onlyValid ) {
            return $this->getUsmleIfValid($this->USMLEStep3Score);
        }
        return $this->USMLEStep3Score;
    }

    /**
     * @param mixed $ECFMGCertificateDate
     */
    public function setECFMGCertificateDate($ECFMGCertificateDate)
    {
        $this->ECFMGCertificateDate = $ECFMGCertificateDate;
    }

    /**
     * @return mixed
     */
    public function getECFMGCertificateDate()
    {
        return $this->ECFMGCertificateDate;
    }

    /**
     * @param mixed $ECFMGCertificateNumber
     */
    public function setECFMGCertificateNumber($ECFMGCertificateNumber)
    {
        $this->ECFMGCertificateNumber = $ECFMGCertificateNumber;
    }

    /**
     * @return mixed
     */
    public function getECFMGCertificateNumber()
    {
        return $this->ECFMGCertificateNumber;
    }

    /**
     * @param mixed $ECFMGCertificate
     */
    public function setECFMGCertificate($ECFMGCertificate)
    {
        $this->ECFMGCertificate = $ECFMGCertificate;
    }

    /**
     * @return mixed
     */
    public function getECFMGCertificate()
    {
        return $this->ECFMGCertificate;
    }

    /**
     * @param mixed $COMLEXLevel1DatePassed
     */
    public function setCOMLEXLevel1DatePassed($COMLEXLevel1DatePassed)
    {
        $this->COMLEXLevel1DatePassed = $COMLEXLevel1DatePassed;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel1DatePassed()
    {
        return $this->COMLEXLevel1DatePassed;
    }

    /**
     * @param mixed $COMLEXLevel1Score
     */
    public function setCOMLEXLevel1Score($COMLEXLevel1Score)
    {
        $this->COMLEXLevel1Score = $COMLEXLevel1Score;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel1Score( $onlyValid=false )
    {
        //COMLEX Level 1 result is pass/fail since May 10, 2022.
        return $this->COMLEXLevel1Score;
        
//        if( $onlyValid ) {
//            return $this->getComlexIfValid($this->COMLEXLevel1Score);
//        }
//        return $this->COMLEXLevel1Score;
    }

    /**
     * @param mixed $COMLEXLevel2DatePassed
     */
    public function setCOMLEXLevel2DatePassed($COMLEXLevel2DatePassed)
    {
        $this->COMLEXLevel2DatePassed = $COMLEXLevel2DatePassed;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2DatePassed()
    {
        return $this->COMLEXLevel2DatePassed;
    }

    /**
     * @param mixed $COMLEXLevel2Score
     */
    public function setCOMLEXLevel2Score($COMLEXLevel2Score)
    {
        $this->COMLEXLevel2Score = $COMLEXLevel2Score;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2Score( $onlyValid=false )
    {
        if( $onlyValid ) {
            return $this->getComlexIfValid($this->COMLEXLevel2Score);
        }
        return $this->COMLEXLevel2Score;
    }

    /**
     * @param mixed $COMLEXLevel3DatePassed
     */
    public function setCOMLEXLevel3DatePassed($COMLEXLevel3DatePassed)
    {
        $this->COMLEXLevel3DatePassed = $COMLEXLevel3DatePassed;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel3DatePassed()
    {
        return $this->COMLEXLevel3DatePassed;
    }

    /**
     * @param mixed $COMLEXLevel3Score
     */
    public function setCOMLEXLevel3Score($COMLEXLevel3Score)
    {
        $this->COMLEXLevel3Score = $COMLEXLevel3Score;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel3Score( $onlyValid=false )
    {
        if( $onlyValid ) {
            return $this->getComlexIfValid($this->COMLEXLevel3Score);
        }
        return $this->COMLEXLevel3Score;
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep1Percentile()
    {
        return $this->USMLEStep1Percentile;
    }

    /**
     * @param mixed $USMLEStep1Percentile
     */
    public function setUSMLEStep1Percentile($USMLEStep1Percentile)
    {
        if( $this->isInteger($USMLEStep1Percentile) ) {
            $this->USMLEStep1Percentile = $USMLEStep1Percentile;
        }
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CKPercentile()
    {
        return $this->USMLEStep2CKPercentile;
    }

    /**
     * @param mixed $USMLEStep2CKPercentile
     */
    public function setUSMLEStep2CKPercentile($USMLEStep2CKPercentile)
    {
        if( $this->isInteger($USMLEStep2CKPercentile) ) {
            $this->USMLEStep2CKPercentile = $USMLEStep2CKPercentile;
        }
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep2CSPercentile()
    {
        return $this->USMLEStep2CSPercentile;
    }

    /**
     * @param mixed $USMLEStep2CSPercentile
     */
    public function setUSMLEStep2CSPercentile($USMLEStep2CSPercentile)
    {
        if( $this->isInteger($USMLEStep2CSPercentile) ) {
            $this->USMLEStep2CSPercentile = $USMLEStep2CSPercentile;
        }
    }

    /**
     * @return mixed
     */
    public function getUSMLEStep3Percentile()
    {
        return $this->USMLEStep3Percentile;
    }

    /**
     * @param mixed $USMLEStep3Percentile
     */
    public function setUSMLEStep3Percentile($USMLEStep3Percentile)
    {
        if( $this->isInteger($USMLEStep3Percentile) ) {
            $this->USMLEStep3Percentile = $USMLEStep3Percentile;
        }
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel1Percentile()
    {
        return $this->COMLEXLevel1Percentile;
    }

    /**
     * @param mixed $COMLEXLevel1Percentile
     */
    public function setCOMLEXLevel1Percentile($COMLEXLevel1Percentile)
    {
        if( $this->isInteger($COMLEXLevel1Percentile) ) {
            $this->COMLEXLevel1Percentile = $COMLEXLevel1Percentile;
        }
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2Percentile()
    {
        return $this->COMLEXLevel2Percentile;
    }

    /**
     * @param mixed $COMLEXLevel2Percentile
     */
    public function setCOMLEXLevel2Percentile($COMLEXLevel2Percentile)
    {
        if( $this->isInteger($COMLEXLevel2Percentile) ) {
            $this->COMLEXLevel2Percentile = $COMLEXLevel2Percentile;
        }
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2PEDatePassed()
    {
        return $this->COMLEXLevel2PEDatePassed;
    }

    /**
     * @param mixed $COMLEXLevel2PEDatePassed
     */
    public function setCOMLEXLevel2PEDatePassed($COMLEXLevel2PEDatePassed)
    {
        $this->COMLEXLevel2PEDatePassed = $COMLEXLevel2PEDatePassed;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2PEScore()
    {
        return $this->COMLEXLevel2PEScore;
    }

    /**
     * @param mixed $COMLEXLevel2PEScore
     */
    public function setCOMLEXLevel2PEScore($COMLEXLevel2PEScore)
    {
        $this->COMLEXLevel2PEScore = $COMLEXLevel2PEScore;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel2PEPercentile()
    {
        return $this->COMLEXLevel2PEPercentile;
    }

    /**
     * @param mixed $COMLEXLevel2PEPercentile
     */
    public function setCOMLEXLevel2PEPercentile($COMLEXLevel2PEPercentile)
    {
        $this->COMLEXLevel2PEPercentile = $COMLEXLevel2PEPercentile;
    }

    /**
     * @return mixed
     */
    public function getCOMLEXLevel3Percentile()
    {
        return $this->COMLEXLevel3Percentile;
    }

    /**
     * @param mixed $COMLEXLevel3Percentile
     */
    public function setCOMLEXLevel3Percentile($COMLEXLevel3Percentile)
    {
        if( $this->isInteger($COMLEXLevel3Percentile) ) {
            $this->COMLEXLevel3Percentile = $COMLEXLevel3Percentile;
        }
    }





    //interface methods
    public function addDocument($item)
    {
        $this->addScore($item);
        return $this;
    }
    public function removeDocument($item)
    {
        $this->removeScore($item);
    }
    public function getDocuments()
    {
        return $this->getScores();
    }


    public function isInteger( $variable ) {
        # Check if your variable is an integer
        if ( strval($variable) != strval(intval($variable)) ) {
            //echo "Your variable is not an integer";
            return false;
        } else {
            return true;
        }
    }

    public function getComlexIfValid( $score ) {
        if( $this->isComlexValid($score) ) {
            return $score;
        }
        return NULL;
    }
    public function isComlexValid( $score ) {
        //upper limit of 1000 and a lower limit of 8
        if( is_numeric($score) && $score !== NULL && $score >= 8 && $score <= 1000 )
        {
            return true;
        }
        return false;
    }

    public function getUsmleIfValid( $score ) {
        if( $this->isUsmleValid($score) ) {
            return $score;
        }
        return NULL;
    }
    public function isPassFailfValidation( $score ) {
        if( $score == 'pass' || $score == 'fail' ) {
            return $score;
        }
        return NULL;
    }
    public function isUsmleValid( $score ) {
        //https://en.wikipedia.org/wiki/USMLE_Step_1
        //Prior to January 2022, Step 1 scoring is a three-digit score, theoretically ranging from 1 to 300
        if( is_numeric($score) && $score !== NULL && $score >= 1 && $score <= 300 ) {
            return true;
        }
        return false;
    }


    public function __toString() {
        return "Examination";
    }

}