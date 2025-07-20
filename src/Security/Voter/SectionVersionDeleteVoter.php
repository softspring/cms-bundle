<?php

namespace Softspring\CmsBundle\Security\Voter;

use Softspring\CmsBundle\Config\CmsConfig;
use Softspring\CmsBundle\Manager\SectionManagerInterface;
use Softspring\CmsBundle\Model\SectionVersionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SectionVersionDeleteVoter implements VoterInterface
{
    public function __construct(protected CmsConfig $cmsConfig, protected SectionManagerInterface $sectionManager)
    {
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        // check version
        if (!is_object($subject) || !$subject instanceof SectionVersionInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }
        $version = $subject;

        $role = $attributes[0] ?? '';

        if ('PERMISSION_SFS_CMS_ADMIN_SECTION_VERSION_DELETE' !== $role) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // checks is not keep, not published nor last version
        if (!$version->deleteOnCleanup()) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_GRANTED;
    }
}
