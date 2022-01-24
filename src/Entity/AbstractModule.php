<?php

namespace Softspring\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Model\PageInterface;
use Softspring\DoctrineTemplates\Entity\Traits\UniqId;

/**
 * @ORM\Table(name="cms_module_abstract")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 */
abstract class AbstractModule
{
    use UniqId;

    /**
     * @ORM\ManyToOne(targetEntity="Softspring\CmsBundle\Model\PageInterface", inversedBy="modules")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    protected ?PageInterface $page;

    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    public function setPage(?PageInterface $page): void
    {
        $this->page = $page;
    }
}