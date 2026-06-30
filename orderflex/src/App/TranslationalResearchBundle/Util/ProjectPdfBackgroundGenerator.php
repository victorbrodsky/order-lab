<?php

namespace App\TranslationalResearchBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProjectPdfBackgroundGenerator
{
    private $container;
    private $httpClient;
    private $router;

    public function __construct(
        ContainerInterface $container,
        HttpClientInterface $httpClient,
        UrlGeneratorInterface $router
    )
    {
        $this->container = $container;
        $this->httpClient = $httpClient;
        $this->router = $router;
    }

    //Called by controller evrytime when project is created/updated
    public function queueProjectPdfGeneration(Request $request, int $projectId, ?int $userId = null): void
    {
        if( $projectId <= 0 ) {
            return;
        }

        $logger = $this->container->get('logger');
        $logger->notice('[ProjectPdfFlow] queueProjectPdfGeneration begin; projectId='.(int)$projectId.'; userId='.(int)$userId);
        try {
            $sessionId = null;
            if( $request->hasSession() ) {
                $session = $request->getSession();
                if( $session ) {
                    $session->save();
                    session_write_close();
                    $sessionId = $session->getId();
                }
            }

            $logger->notice('[ProjectPdfFlow] queueProjectPdfGeneration session prepared; projectId='.(int)$projectId.'; hasSessionId='.( $sessionId ? 'yes' : 'no' ));

            $executeUrl = $this->router->generate(
                'translationalresearch_project_pdf_generate_execute',
                array('id' => $projectId),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $logger->notice('[ProjectPdfFlow] queueProjectPdfGeneration launching detached HTTP call; projectId='.(int)$projectId.'; url='.$executeUrl);
            $userServiceUtil = $this->container->get('user_service_utility');
            if( $userServiceUtil->isWinOs() ) {
                $this->runDetachedHttpCallV2((int)$projectId, $sessionId);
            } else {
                //$this->runDetachedHttpCall($executeUrl, $sessionId);
                $this->runDetachedHttpCallV2((int)$projectId, $sessionId);
            }
            $logger->notice('[ProjectPdfFlow] queueProjectPdfGeneration detached launch command dispatched; projectId='.(int)$projectId);
        } catch( \Throwable $e ) {
            $logger->error('[ProjectPdfFlow] queueProjectPdfGeneration failed: '.$e->getMessage());
            $this->setProjectPdfGenerationStatus($projectId, array(
                'status' => 'failed',
                'message' => 'Project PDF generation failed to start',
                'updatedAt' => time(),
                'projectId' => $projectId,
            ));
        }
    }

    private function runDetachedHttpCall(string $url, ?string $sessionId = null): void
    {
        $logger = $this->container->get('logger');
        $userServiceUtil = $this->container->get('user_service_utility');

        $contextOptions = array(
            'http' => array(
                'method' => 'GET',
                'timeout' => 1800,
            )
        );

        if( $sessionId ) {
            $contextOptions['http']['header'] = "Cookie: PHPSESSID=".$sessionId."\r\n";
        }

        $phpCode = '$context = stream_context_create(' . var_export($contextOptions, true) . ');' .
            '@file_get_contents(' . var_export($url, true) . ', false, $context);';

        $phpBinary = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';
        $command = escapeshellarg($phpBinary) . ' -r ' . escapeshellarg($phpCode);

        //$logger->notice('[ProjectPdfFlow] runDetachedHttpCall prepared command; url='.$url.'; platform='.(DIRECTORY_SEPARATOR === '\\' ? 'windows' : 'unix'));
//        if( DIRECTORY_SEPARATOR === '\\' ) {
//            $logger->notice("windows: command=$command");
//            pclose(popen('start /B "" ' . $command, 'r'));
//        } else {
//            $logger->notice("not windows: command=$command");
//            exec($command . ' > /dev/null 2>&1 &');
//        }

        if( $userServiceUtil->isWinOs() ) {
            $logger->notice("windows: command=$command");
            $oExec = pclose(popen('start /B "" ' . $command, 'r'));
        } else {
            $logger->notice("not windows: command=$command");
            $oExec = exec($command . ' > /dev/null 2>&1 &');
        }

        $logger->notice('[ProjectPdfFlow] runDetachedHttpCall prepared command; url='.$url.'; platform='.($userServiceUtil->isWinOs() ? 'windows' : 'unix'));

        //$oExec = $userServiceUtil->execInBackground($command);
        $logger->notice('[ProjectPdfFlow] runDetachedHttpCall execInBackground returned; value='.(string)$oExec.'; url='.$url);

        $logger->notice('[ProjectPdfFlow] runDetachedHttpCall dispatched; url='.$url);
    }

    private function runDetachedHttpCallV2(int $projectId, ?string $sessionId = null): void
    {
        $logger = $this->container->get('logger');

        $url = $this->router->generate(
            'translationalresearch_project_pdf_generate_execute',
            array('id' => $projectId),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $options = array(
            'timeout' => 5,
            'max_duration' => 5,
        );

        if( $sessionId ) {
            $options['headers'] = array(
                'Cookie' => 'PHPSESSID='.$sessionId,
            );
        }

        $logger->notice('[ProjectPdfFlow] runDetachedHttpCallV2 dispatching http_client request; projectId='.(int)$projectId.'; url='.$url.'; hasSessionId='.( $sessionId ? 'yes' : 'no' ));

        try {
            $this->httpClient->request('GET', $url, $options);
            $logger->notice('[ProjectPdfFlow] runDetachedHttpCallV2 request dispatched; projectId='.(int)$projectId.'; url='.$url);
        } catch( \Throwable $e ) {
            $logger->error('[ProjectPdfFlow] runDetachedHttpCallV2 failed: '.$e->getMessage().'; projectId='.(int)$projectId.'; url='.$url);
            throw $e;
        }

    }

    private function getProjectPdfStatusFilePath(int $projectId): string
    {
        $statusDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'transres_project_pdf_status';
        if( !is_dir($statusDir) ) {
            mkdir($statusDir, 0700, true);
        }

        return $statusDir . DIRECTORY_SEPARATOR . 'project_' . $projectId . '.json';
    }

    private function setProjectPdfGenerationStatus(int $projectId, array $statusInfo): void
    {
        $statusInfo['projectId'] = $projectId;
        $statusInfo['updatedAt'] = $statusInfo['updatedAt'] ?? time();
        $statusFilePath = $this->getProjectPdfStatusFilePath($projectId);
        file_put_contents($statusFilePath, json_encode($statusInfo));
    }
}
