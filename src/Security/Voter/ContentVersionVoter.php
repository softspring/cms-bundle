<?php

declare(strict_types=1);

namespace Softspring\CmsBundle\Security\Voter;

use Softspring\CmsBundle\Model\ContentVersionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ContentVersionVoter implements VoterInterface
{
    public function __construct(protected bool $contentRecompileEnabled)
    {
    }

    public function vote(TokenInterface $token, $subject, array $attributes, ?Vote $vote = null): int
    {
        if (!is_object($subject) || !$subject instanceof ContentVersionInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // if recompile is disabled, deny access
        if ('PERMISSION_SFS_CMS_ADMIN_CONTENT_VERSION_RECOMPILE' === $attributes[0] && !$this->contentRecompileEnabled) {
            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
