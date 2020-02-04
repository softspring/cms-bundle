<?php

namespace Softspring\CmsBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Softspring\CmsBundle\Model\SiteInterface;

trait MultiSiteTrait
{
    /**
     * @var Collection|SiteInterface[]
     * @ORM\ManyToMany(targetEntity="Softspring\CmsBundle\Model\SiteInterface")
     */
    protected $sites;

    /**
     * @return Collection|SiteInterface[]
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    /**
     * @param SiteInterface $site
     */
    public function addSite(SiteInterface $site): void
    {
        if (!$this->sites->contains($site)) {
            $this->sites->add($site);
        }
    }

    /**
     * @param SiteInterface $site
     */
    public function removeSite(SiteInterface $site): void
    {
        if ($this->sites->contains($site)) {
            $this->sites->removeElement($site);
        }
    }
}