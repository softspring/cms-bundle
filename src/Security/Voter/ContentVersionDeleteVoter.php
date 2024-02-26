<?php

namespace Softspring\CmsBundle\Security\Voter;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Config\Exception\InvalidContentException;
use Softspring\CmsBundle\Manager\ContentManagerInterface;
use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ContentVersionDeleteVoter implements VoterInterface
{
    public function __construct(protected CmsConfig $cmsConfig, protected ContentManagerInterface $contentManager)
    {
    }

    /**
     * @throws InvalidContentException
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        // check version
        if (!is_object($subject) || !$subject instanceof ContentVersionInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }
        $version = $subject;

        $role = $attributes[0] ?? '';

        $type = $this->contentManager->getType($version->getContent());
        $contentConfig = $this->cmsConfig->getContent($type);

        if (empty($contentConfig['admin']['version_delete']['is_granted'])) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($role !== $contentConfig['admin']['version_delete']['is_granted']) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($version->isPublished()) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }
}
