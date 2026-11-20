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

namespace App\WikiportalBundle\Controller;

use App\UserdirectoryBundle\Controller\OrderAbstractController;
use App\UserdirectoryBundle\Entity\Document;
use App\WikiportalBundle\Entity\WikiPage;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalWikiPageController extends OrderAbstractController
{

    #[Route(path: '/pages', name: 'wikiportal_pages', methods: ['GET'])]
    #[Template('AppWikiportalBundle/Default/pages.html.twig')]
    public function pagesAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_USER')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $pages = $em->getRepository(WikiPage::class)->findBy(array(), array('fullPath' => 'ASC'));

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'pages' => $pages
        );
    }

    #[Route(path: '/page/{path}', name: 'wikiportal_page_show', methods: ['GET'], requirements: ['path' => '.+'])]
    #[Template('AppWikiportalBundle/Default/page-show.html.twig')]
    public function showPageAction(Request $request, $path)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_USER')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(WikiPage::class)->findOneByFullPath(trim((string)$path, '/'));

        if (!$page) {
            throw $this->createNotFoundException('Wiki page not found: ' . $path);
        }

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'page' => $page,
            'rootPage' => $this->getHomePage()
        );
    }

    #[Route(path: '/page-new', name: 'wikiportal_page_new', methods: ['GET', 'POST'])]
    #[Template('AppWikiportalBundle/Default/page-edit.html.twig')]
    public function newPageAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_EDITOR')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();

        if ($request->isMethod('POST')) {
            $page = new WikiPage($this->getUser());
            $res = $this->updatePageFromRequest($page, $request);
            if ($res !== true) {
                $this->addFlash('warning', $res);
                return array(
                    'sitename' => $this->getParameter('wikiportal.sitename'),
                    'page' => $page,
                    'parentCandidates' => $this->getParentCandidates(null)
                );
            }

            $em->persist($page);
            $this->updateFullPathRecursive($page);
            $em->flush();
            $this->ensureSingleHomepage($page);

            $userSecUtil = $this->container->get('user_security_utility');
            $event = "New wiki page created: " . $page->getTitle() . " (id=" . $page->getId() . ")";
            $userSecUtil->createUserEditEvent($this->getParameter('wikiportal.sitename'), $event, $this->getUser(), $page, $request, 'Wiki Page Created');

            return $this->redirect($this->generateUrl('wikiportal_page_show', array('path' => $page->getFullPath())));
        }

        //pre-select parent from ?parent=id (e.g. "Add child page" link)
        $parentId = $request->query->get('parent');
        $presetParent = null;
        if ($parentId) {
            $presetParent = $em->getRepository(WikiPage::class)->find($parentId);
        }

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'page' => null,
            'presetParent' => $presetParent,
            'parentCandidates' => $this->getParentCandidates(null)
        );
    }

    #[Route(path: '/page-edit/{id}', name: 'wikiportal_page_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[Template('AppWikiportalBundle/Default/page-edit.html.twig')]
    public function editPageAction(Request $request, $id)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_EDITOR')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(WikiPage::class)->find($id);

        if (!$page) {
            throw $this->createNotFoundException('Wiki page not found: ' . $id);
        }

        if ($request->isMethod('POST')) {
            $oldBody = $page->getBody();

            $res = $this->updatePageFromRequest($page, $request);
            if ($res !== true) {
                $this->addFlash('warning', $res);
                return array(
                    'sitename' => $this->getParameter('wikiportal.sitename'),
                    'page' => $page,
                    'parentCandidates' => $this->getParentCandidates($page)
                );
            }

            //delete documents that were removed from the page body and are
            //not referenced by any other wiki page
            $removedDocIds = array_diff(
                $this->extractDocumentIds($oldBody),
                $this->extractDocumentIds($page->getBody())
            );
            $this->deleteUnreferencedDocuments($removedDocIds, $page->getId(), $request);

            $page->setUpdateuser($this->getUser());
            $this->updateFullPathRecursive($page);
            $em->flush();
            $this->ensureSingleHomepage($page);

            $userSecUtil = $this->container->get('user_security_utility');
            $event = "Wiki page updated: " . $page->getTitle() . " (id=" . $page->getId() . ")";
            $userSecUtil->createUserEditEvent($this->getParameter('wikiportal.sitename'), $event, $this->getUser(), $page, $request, 'Wiki Page Updated');

            return $this->redirect($this->generateUrl('wikiportal_page_show', array('path' => $page->getFullPath())));
        }

        return array(
            'sitename' => $this->getParameter('wikiportal.sitename'),
            'page' => $page,
            'parentCandidates' => $this->getParentCandidates($page)
        );
    }

    #[Route(path: '/page-delete/{id}', name: 'wikiportal_page_delete', methods: ['POST', 'DELETE'], requirements: ['id' => '\d+'])]
    public function deletePageAction(Request $request, $id)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_ADMIN')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(WikiPage::class)->find($id);

        if (!$page) {
            throw $this->createNotFoundException('Wiki page not found: ' . $id);
        }

        if ($page->getIsHomepage()) {
            $this->addFlash('warning', 'The home page cannot be deleted.');
            return $this->redirect($this->generateUrl('wikiportal_pages'));
        }

        //delete documents referenced only by this page (and its descendants,
        //which are removed by the same cascade)
        $docIds = $this->extractDocumentIds($page->getBody());
        $childIds = $this->getDescendantIds($page);
        $childIds[] = $page->getId();
        $this->deleteUnreferencedDocuments($docIds, $childIds, $request);

        $title = $page->getTitle();
        $em->remove($page); //children are removed by DB-level ON DELETE CASCADE on parent_id
        $em->flush();

        $userSecUtil = $this->container->get('user_security_utility');
        $event = "Wiki page deleted: " . $title . " (id=" . $id . ")";
        $userSecUtil->createUserEditEvent($this->getParameter('wikiportal.sitename'), $event, $this->getUser(), null, $request, 'Wiki Page Deleted');

        $this->addFlash('notice', "Wiki page '" . $title . "' and its sub-pages have been deleted.");
        return $this->redirect($this->generateUrl('wikiportal_pages'));
    }

    /**
     * Home page: the page flagged isHomepage, or the first parentless page
     */
    public function getHomePage()
    {
        $em = $this->getDoctrine()->getManager();
        $home = $em->getRepository(WikiPage::class)->findOneByIsHomepage(true);
        if (!$home) {
            $home = $em->getRepository(WikiPage::class)->findOneBy(array('parent' => null));
        }
        return $home;
    }

    /**
     * Populate page fields from the request. Returns true or an error string.
     */
    private function updatePageFromRequest($page, Request $request)
    {
        $title = trim((string)$request->request->get('title'));
        $slug = trim((string)$request->request->get('slug'));
        $body = $request->request->get('body');
        $sortOrder = $request->request->get('sortOrder');
        $isHomepage = (bool)$request->request->get('isHomepage');
        $parentId = $request->request->get('parent');

        if (!$title) {
            return 'Title is required.';
        }

        if (!$slug) {
            $slug = $this->slugify($title);
        }

        $em = $this->getDoctrine()->getManager();

        $parent = null;
        if ($parentId) {
            $parent = $em->getRepository(WikiPage::class)->find($parentId);
            if (!$parent) {
                return 'Selected parent page not found.';
            }
            if ($page->getId() && $parent->getId() == $page->getId()) {
                return 'A page cannot be its own parent.';
            }
            if (in_array($parent->getId(), $this->getDescendantIds($page))) {
                return 'A page cannot be moved under its own sub-page.';
            }
        }

        //enforce single homepage: homepage must be top-level
        if ($isHomepage && $parent) {
            return 'The home page cannot have a parent.';
        }

        //slug uniqueness among siblings
        $qb = $em->getRepository(WikiPage::class)->createQueryBuilder('p');
        $qb->where('p.slug = :slug')->setParameter('slug', $slug);
        if ($parent) {
            $qb->andWhere('p.parent = :parent')->setParameter('parent', $parent);
        } else {
            $qb->andWhere('p.parent IS NULL');
        }
        if ($page->getId()) {
            $qb->andWhere('p.id != :id')->setParameter('id', $page->getId());
        }
        if ($qb->getQuery()->getResult()) {
            return "A page with the slug '" . $slug . "' already exists under this parent.";
        }

        $page->setTitle($title);
        $page->setSlug($slug);
        $page->setBody($body);
        $page->setParent($parent);
        $page->setIsHomepage($isHomepage);
        $page->setSortOrder($sortOrder !== null && $sortOrder !== '' ? (int)$sortOrder : null);

        return true;
    }

    /**
     * Recompute fullPath for the page and all its descendants.
     * Must be called after parent/slug is set, before flush.
     */
    private function updateFullPathRecursive($page)
    {
        $parent = $page->getParent();
        $path = $parent ? $parent->getFullPath() . '/' . $page->getSlug() : $page->getSlug();
        $page->setFullPath($path);
        foreach ($page->getChildren() as $child) {
            $this->updateFullPathRecursive($child);
        }
    }

    /**
     * Only one page can be the home page
     */
    private function ensureSingleHomepage($page)
    {
        if (!$page->getIsHomepage()) {
            return;
        }
        $em = $this->getDoctrine()->getManager();
        $others = $em->getRepository(WikiPage::class)->findByIsHomepage(true);
        $changed = false;
        foreach ($others as $other) {
            if ($other->getId() != $page->getId()) {
                $other->setIsHomepage(false);
                $changed = true;
            }
        }
        if ($changed) {
            $em->flush();
        }
    }

    private function getDescendantIds($page)
    {
        $ids = array();
        if (!$page || !$page->getId()) {
            return $ids;
        }
        foreach ($page->getChildren() as $child) {
            $ids[] = $child->getId();
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    /**
     * Pages that may be selected as a parent: all pages except the edited
     * page itself and its descendants (cycle prevention)
     */
    private function getParentCandidates($excludePage)
    {
        $em = $this->getDoctrine()->getManager();
        $all = $em->getRepository(WikiPage::class)->findBy(array(), array('fullPath' => 'ASC'));
        if (!$excludePage) {
            return $all;
        }
        $excluded = $this->getDescendantIds($excludePage);
        $excluded[] = $excludePage->getId();
        return array_filter($all, function ($p) use ($excluded) {
            return !in_array($p->getId(), $excluded);
        });
    }

    /**
     * Document ids referenced by file-view/file-download links in page HTML
     */
    private function extractDocumentIds($html)
    {
        $ids = array();
        if ($html && preg_match_all('#/(file-view|file-download)/(\d+)#', $html, $matches)) {
            $ids = array_map('intval', $matches[2]);
        }
        return array_unique($ids);
    }

    /**
     * Remove Document records (and their files + thumbnails) that are no
     * longer referenced by any wiki page. $excludePageIds: page id(s) whose
     * body must not be counted as a reference (the page being edited/deleted).
     */
    private function deleteUnreferencedDocuments($documentIds, $excludePageIds, Request $request)
    {
        if (!$documentIds) {
            return;
        }
        $excludePageIds = (array)$excludePageIds;
        $em = $this->getDoctrine()->getManager();
        $userSecUtil = $this->container->get('user_security_utility');
        $sitename = $this->getParameter('wikiportal.sitename');

        foreach ($documentIds as $documentId) {
            //skip if still referenced by another wiki page
            $qb = $em->getRepository(WikiPage::class)->createQueryBuilder('p');
            $qb->select('COUNT(p.id)')
                ->where('p.body LIKE :viewref OR p.body LIKE :downloadref')
                ->setParameter('viewref', '%file-view/' . $documentId . '%')
                ->setParameter('downloadref', '%file-download/' . $documentId . '%');
            if ($excludePageIds) {
                $qb->andWhere('p.id NOT IN (:ids)')->setParameter('ids', $excludePageIds);
            }
            if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
                continue;
            }

            $document = $em->getRepository(Document::class)->find($documentId);
            if (!$document) {
                continue;
            }

            $documentPath = $document->getFullServerPath(null, false);

            $em->remove($document);

            if ($documentPath && is_file($documentPath)) {
                unlink($documentPath);
            }
            $smallPath = $document->getFullServerPath('small', false);
            if ($smallPath && is_file($smallPath)) {
                unlink($smallPath);
            }
            $mediumPath = $document->getFullServerPath('medium', false);
            if ($mediumPath && is_file($mediumPath)) {
                unlink($mediumPath);
            }

            $eventDescription = "Document (id=" . $documentId . ") has been deleted from the server by " . $this->getUser();
            $userSecUtil->createUserEditEvent($sitename, $eventDescription, $this->getUser(), null, $request, 'Document Deleted');
        }
    }

    private function slugify($text)
    {
        $slug = strtolower(trim((string)$text));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ? $slug : 'page';
    }
}
