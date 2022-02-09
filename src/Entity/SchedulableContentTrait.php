<?php

namespace Softspring\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait SchedulableContentTrait
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true})
     */
    protected $publishStartDate;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true})
     */
    protected $publishEndDate;

    public function getPublishStartDate(): ?\DateTime
    {
        return \DateTime::createFromFormat('U', $this->publishStartDate) ?: null;
    }

    public function setPublishStartDate(?\DateTime $publishStartDate): void
    {
        $this->publishStartDate = $publishStartDate instanceof \DateTime ? $publishStartDate->format('U') : null;
    }

    public function getPublishEndDate(): ?\DateTime
    {
        return \DateTime::createFromFormat('U', $this->publishEndDate) ?: null;
    }

    public function setPublishEndDate(?\DateTime $publishEndDate): void
    {
        $this->publishEndDate = $publishEndDate instanceof \DateTime ? $publishEndDate->format('U') : null;
    }
}
