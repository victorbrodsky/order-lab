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

    /**
     * One-time seed: creates/updates the Home wiki page from a server-local
     * HTTrack mirror of the old Confluence site.
     *
     * Usage: /wiki-portal/seed-homepage?dir=<path-to-mirror-root>
     *   expects <dir>/display/pathlabmed/Home.html
     *   and     <dir>/download/attachments/8164/<files>
     *
     * The page body is extracted from #main-content, attachment links are
     * imported as Documents and rewritten to local file-download URLs.
     * Idempotent: re-running reuses already imported documents.
     */
    #[Route(path: '/seed-homepage', name: 'wikiportal_seed_homepage', methods: ['GET', 'POST'])]
    public function seedHomepageAction(Request $request)
    {
        if (false === $this->isGranted('ROLE_WIKIPORTAL_ADMIN')) {
            return $this->redirect($this->generateUrl('wikiportal-nopermission'));
        }

        $mirrorRoot = $request->get('dir');
        if (!$mirrorRoot) {
            return new Response(
                'Usage: ' . $this->generateUrl('wikiportal_seed_homepage') . '?dir=<path-to-httrack-mirror-root>' .
                ' expecting <dir>/display/pathlabmed/Home.html and <dir>/download/attachments/8164/'
            );
        }
        $mirrorRoot = rtrim(str_replace('\\', '/', $mirrorRoot), '/');
        $htmlFile = $mirrorRoot . '/display/pathlabmed/Home.html';
        $attachDir = $mirrorRoot . '/download/attachments/8164';

        if (!is_file($htmlFile)) {
            return new Response('Confluence page not found: ' . $htmlFile, 500);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $projectRoot = $this->getParameter('kernel.project_dir');

        $uploadPath = $this->getParameter('wikiportal.uploadpath');
        //exit('$uploadPath='.$uploadPath); //wikiportal
        //documents store uploadDirectory with the leading 'private/' (see UploadListener)
        $destDirRel = 'private/Uploaded/' . $uploadPath;
        $destDirAbs = $projectRoot . '/' . $destDirRel;
        if (!is_dir($destDirAbs)) {
            mkdir($destDirAbs, 0777, true);
        }

        //extract the page body from #main-content
        $body = $this->extractMainContent(file_get_contents($htmlFile));
        if ($body === null) {
            return new Response('Could not locate #main-content in ' . $htmlFile, 500);
        }

        $imported = 0;
        $reused = 0;
        $missing = array();
        $self = $this;

        //rewrite attachment links (absolute nexus URLs or ../../ relative
        //paths) to local file-download URLs
        $body = preg_replace_callback(
            '#(href|src)="[^"]*attachments/8164/([^"?]+)(?:\?[^"]*)?"#',
            function ($matches) use ($em, $user, $attachDir, $destDirRel, $destDirAbs, &$imported, &$reused, &$missing, $self) {
                $filename = urldecode($matches[2]);
                $srcFile = $attachDir . '/' . $filename;
                //exit('$srcFile='.$srcFile); //C:/MyWebSites/Nexus 2/nexus.weill.cornell.edu/download/attachments/8164/LOGO_PATH LAB MED_2LINE_RGB7fb1.png
                if (!is_file($srcFile)) {
                    $missing[] = $filename;
                    return $matches[0]; //keep the original link
                }

                $document = $self->importAttachment($em, $user, $srcFile, $filename, $destDirRel, $destDirAbs);
                if ($document['reused']) {
                    $reused++;
                } else {
                    $imported++;
                }
                return $matches[1] . '="' . $self->generateUrl('wikiportal_file_download', array('id' => $document['document']->getId())) . '"';
            },
            $body
        );

        //create or update the home page
        $home = $em->getRepository(WikiPage::class)->findOneByIsHomepage(true);
        if (!$home) {
            $home = $em->getRepository(WikiPage::class)->findOneBySlug('home');
        }
        $isNew = false;
        if (!$home) {
            $home = new WikiPage($user);
            $home->setTitle('Home');
            $home->setSlug('home');
            $isNew = true;
        }
        $home->setIsHomepage(true);
        $home->setParent(null);
        $home->setBody($body);
        $home->setUpdateuser($user);

        $em->persist($home);
        $this->updateFullPathRecursive($home);
        $em->flush();
        $this->ensureSingleHomepage($home);

        $userSecUtil = $this->container->get('user_security_utility');
        $event = "Wiki home page seeded (imported=$imported, reused=$reused)";
        $userSecUtil->createUserEditEvent($this->getParameter('wikiportal.sitename'), $event, $user, $home, $request, 'Wiki Page Seeded');

        $msg = ($isNew ? 'Created' : 'Updated') . " home page (id=" . $home->getId() . "): "
            . "$imported attachments imported, $reused reused"
            . ($missing ? ", MISSING: " . implode(', ', $missing) : '');
        $this->addFlash('notice', $msg);

        return $this->redirect($this->generateUrl('wikiportal_home'));
    }

    /**
     * Extract the inner HTML of the #main-content element from a Confluence
     * (HTTrack) page. Returns null when not found.
     */
    private function extractMainContent($html)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $node = $xpath->query('//*[@id="main-content"]')->item(0);
        if (!$node) {
            return null;
        }

        $inner = '';
        foreach ($node->childNodes as $child) {
            $inner .= $dom->saveHTML($child);
        }
        return $inner;
    }

    /**
     * Copy an attachment file into the site upload directory and create a
     * Document record (same fields as UploadListener). Reuses an existing
     * Document with the same originalname+uploadDirectory.
     */
    public function importAttachment($em, $user, $srcFile, $filename, $destDirRel, $destDirAbs)
    {
        $existing = $em->getRepository(Document::class)->findOneBy(array(
            'uploadDirectory' => $destDirRel,
            'originalname' => $filename
        ));
        if ($existing) {
            return array('document' => $existing, 'reused' => true);
        }

        //oneup uniqid namer convention: 11-hex hash + original extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $uniquename = substr(md5(uniqid()), 0, 11) . ($ext ? '.' . $ext : '');
        copy($srcFile, $destDirAbs . '/' . $uniquename);

        $document = new Document($user);
        $document->setCleanOriginalname($filename);
        $document->setUniquename($uniquename);
        $document->setUploadDirectory($destDirRel);
        $document->setSize(filesize($srcFile));

        $userServiceUtil = $this->container->get('user_service_utility');
        $userServiceUtil->generateTwoThumbnails($document);

        $em->persist($document);
        $em->flush(); //flush to get document id for the link

        return array('document' => $document, 'reused' => false);
    }

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
