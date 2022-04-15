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
 * Created by JetBrains PhpStorm.
 * User: oli2002
 * Date: 10/4/13
 * Time: 12:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace App\UserdirectoryBundle\Util;



use App\UserdirectoryBundle\Comment\Model\CommentInterface;
use App\UserdirectoryBundle\Comment\Model\ThreadInterface;
use App\UserdirectoryBundle\Entity\FosComment;
use App\UserdirectoryBundle\Entity\FosThread;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class UserCommentUtil {

    protected $em;
    protected $container;
    protected $secToken;
    protected $secAuth;

    public function __construct( EntityManagerInterface $em, ContainerInterface $container=null ) {
        $this->em = $em;
        $this->container = $container;
        if( $container ) {
            $this->secToken = $container->get('security.token_storage');
            $this->secAuth = $container->get('security.authorization_checker');
        }
    }


    public function findThreadById( $id ) {
        $thread = $this->em->getRepository('AppUserdirectoryBundle:FosThread')->find($id);

        return $thread;
    }

    /**
     * Creates an empty comment thread instance.
     *
     * @param bool $id
     *
     * @return Thread
     */
    public function createThread($id = null)
    {
        //$class = $this->getClass();
        $thread = new FosThread();

        if (null !== $id) {
            $thread->setId($id);
        }

        //$event = new ThreadEvent($thread);
        //$this->dispatch($event, Events::THREAD_CREATE);

        return $thread;
    }

    /**
     * Persists a thread.
     *
     * @param ThreadInterface $thread
     */
    public function saveThread($thread)
    {
//        $event = new ThreadEvent($thread);
//        $this->dispatch($event, Events::THREAD_PRE_PERSIST);
//
//        $this->doSaveThread($thread);
//
//        $event = new ThreadEvent($thread);
//        $this->dispatch($event, Events::THREAD_POST_PERSIST);

        $this->em->persist($thread);
        //$this->em->flush();
    }


    public function findCommentTreeByThread( $thread, $depth = null )
    {
        //echo "threadId=".$thread->getId()."<br>";

        //return $this->em->getRepository('AppUserdirectoryBundle:FosComment')->find(14576);

        $repository = $this->em->getRepository('AppUserdirectoryBundle:FosComment');

        $dql =  $repository->createQueryBuilder("comment");
        $dql->select('comment');
        $dql->leftJoin("comment.thread", "thread");
        $dql->where("thread.id = :threadId");
        $query = $this->em->createQuery($dql);

        $query->setParameters(array('threadId'=>$thread->getId()));

        $comments = $query->getResult();

//        foreach($comments as $comment) {
//            echo $comment->getId().": comment=".$comment->getCommentShort()."<br>";
//        }

        return $comments;
    }
    /**
     * {@inheritdoc}
     */
    public function findCommentTreeByThread_ORIG(ThreadInterface $thread, $sorter = null, $depth = null)
    {
        $comments = $this->findCommentsByThread($thread, $depth);
        //$sorter = $this->sortingFactory->getSorter($sorter);

        //return $this->organiseComments($comments, $sorter);
        return $comments;
    }

    /**
     * {@inheritdoc}
     */
    public function findCommentsByThread(ThreadInterface $thread, $depth = null, $sorterAlias = null)
    {

        //echo "threadId=".$thread->getId()."<br>";

        $repository = $this->em->getRepository('AppUserdirectoryBundle:FosComment');

//        $dql =  $repository->createQueryBuilder("comment");
//        $dql->select('comment');
//        $dql->leftJoin("comment.thread", "thread");
//        $dql->where("thread.id = :threadId");
//        $query = $this->em->createQuery($dql);
//
//        $query->setParameters(array('threadId'=>$thread->getId()));
//
//        $comments = $query->getResult();
//
//        return $comments;

        $qb = $repository
            ->createQueryBuilder('c')
            ->join('c.thread', 't')
            ->where('t.id = :thread')
            ->orderBy('c.ancestors', 'ASC')
            ->setParameter('thread', $thread->getId());

        if (null !== $depth && $depth >= 0) {
            // Queries for an additional level so templates can determine
            // if the final 'depth' layer has children.

            $qb->andWhere('c.depth < :depth')
                ->setParameter('depth', $depth + 1);
        }

        $comments = $qb
            ->getQuery()
            ->execute();

        //if (null !== $sorterAlias) {
            //$sorter = $this->sortingFactory->getSorter($sorterAlias);
            //$comments = $sorter->sortFlat($comments);
        //}

        return $comments;
    }

    /**
     * Organises a flat array of comments into a Tree structure.
     *
     * For organising comment branches of a Tree, certain parents which
     * have not been fetched should be passed in as an array to $ignoreParents.
     *
     * @param CommentInterface[]      $comments      An array of comments to organise
     * //@param SortingInterface        $sorter        The sorter to use for sorting the tree
     * @param CommentInterface[]|null $ignoreParents An array of parents to ignore
     *
     * @return array A tree of comments
     */
    protected function organiseComments($comments, $ignoreParents = null)
    {
        $tree = new Tree();

        foreach ($comments as $comment) {
            $path = $tree;

            $ancestors = $comment->getAncestors();
            if (is_array($ignoreParents)) {
                $ancestors = array_diff($ancestors, $ignoreParents);
            }

            foreach ($ancestors as $ancestor) {
                $path = $path->traverse($ancestor);
            }

            $path->add($comment);
        }

        $tree = $tree->toArray();
        //$tree = $sorter->sort($tree);

        return $tree;
    }

    /**
     * {@inheritdoc}
     */
    public function createComment(ThreadInterface $thread, CommentInterface $parent = null)
    {
        $comment = new FosComment();
        $comment->setThread($thread);

        if (null !== $parent) {
            $comment->setParent($parent);
        }

        //$event = new CommentEvent($comment);
        //$this->dispatch($event, Events::COMMENT_CREATE);

        return $comment;
    }

}