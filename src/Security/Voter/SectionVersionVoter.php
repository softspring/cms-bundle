<?php

namespace Softspring\CmsBundle\Security\Voter;

use Softspring\CmsBundle\Model\SectionVersionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SectionVersionVoter implements VoterInterface
{
    public function __construct(protected bool $recompileEnabled)
    {
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!is_object($subject) || !$subject instanceof SectionVersionInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // if recompile is disabled, deny access
        if ('PERMISSION_SFS_CMS_ADMIN_SECTION_RECOMPILE_VERSION' === $attributes[0] && !$this->recompileEnabled) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
