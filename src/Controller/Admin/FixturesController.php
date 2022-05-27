<?php

namespace Softspring\CmsBundle\Controller\Admin;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Softspring\CmsBundle\Utils\FixturesDump;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FixturesController extends AbstractController
{
    public function saveFixtures(string $content, Request $request, string $version = null): Response
    {
        $config = $this->getContentConfig($request);
        $config = $config['admin'] + ['_id' => $config['_id']];

        $entity = $this->contentManager->getRepository($config['_id'])->findOneBy(['id' => $content]);

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        if ($version) {
            $version = $entity->getVersions()->filter(fn (ContentVersionInterface $versionI) => $versionI->getId() == $version)->first();
        }

//        if (!empty($config['is_granted'])) {
//            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
//        }

        FixturesDump::dumpContent($entity, $version, $config['_id']);

        return $this->redirectToRoute("sfs_cms_admin_content_{$config['_id']}_details", ['content' => $entity]);
    }
}
