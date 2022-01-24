<?php

namespace Softspring\CmsBundle\Entity\Modules;

use Softspring\CmsBundle\Entity\AbstractModule;
use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Model\BlockInterface;

/**
 * @ORM\Table(name="cms_module_block")
 * @ORM\Entity()
 */
class BlockModule extends AbstractModule
{
    /**
     * @ORM\ManyToOne(targetEntity="Softspring\CmsBundle\Model\BlockInterface")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id")
     */
    protected ?BlockInterface $block;

    public function getBlock(): ?BlockInterface
    {
        return $this->block;
    }

    public function setBlock(?BlockInterface $block): void
    {
        $this->block = $block;
    }
}