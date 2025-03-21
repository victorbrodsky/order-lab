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

/**
 * Created by PhpStorm.
 * User: ch3
 * Date: 9/6/2017
 * Time: 4:43 PM
 */

namespace App\TranslationalResearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'transres_committeeReview')]
#[ORM\Entity]
class CommitteeReview extends ReviewBase
{

    #[ORM\ManyToOne(targetEntity: 'Project', inversedBy: 'committeeReviews')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private $project;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $primaryReview;

//    /**
//     * @ORM\ManyToMany(targetEntity="App\UserdirectoryBundle\Entity\FosComment")
//     * @ORM\JoinTable(name="transres_committeereview_comment",
//     *      joinColumns={@ORM\JoinColumn(name="review_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="comment_id", referencedColumnName="id")}
//     * )
//     **/
//    protected $comments;


    public function __construct($reviewer=null) {
        parent::__construct($reviewer);

        $this->setPrimaryReview(false);
    }


    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * @return mixed
     */
    public function getPrimaryReview()
    {
        return $this->primaryReview;
    }

    /**
     * @param mixed $primaryReview
     */
    public function setPrimaryReview($primaryReview)
    {
        $this->primaryReview = $primaryReview;
    }

//    public function getComments()
//    {
//        return $this->comments;
//    }
//    public function addComment($item)
//    {
//        if( $item && !$this->comments->contains($item) ) {
//            $this->comments->add($item);
//        }
//        return $this;
//    }
//    public function removeComment($item)
//    {
//        $this->comments->removeElement($item);
//    }


    public function getStateStr() {
        return "committee_review";
    }


}