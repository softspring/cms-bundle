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

    /**
     * @return \DateTime|null
     */
    public function getPublishStartDate(): ?\DateTime
    {
        return \DateTime::createFromFormat("U", $this->publishStartDate) ?: null;
    }

    /**
     * @param \DateTime|null $publishStartDate
     */
    public function setPublishStartDate(?\DateTime $publishStartDate): void
    {
        $this->publishStartDate = $publishStartDate instanceof \DateTime ? $publishStartDate->format('U') : null;
    }

    /**
     * @return \DateTime|null
     */
    public function getPublishEndDate(): ?\DateTime
    {
        return \DateTime::createFromFormat("U", $this->publishEndDate) ?: null;
    }

    /**
     * @param \DateTime|null $publishEndDate
     */
    public function setPublishEndDate(?\DateTime $publishEndDate): void
    {
        $this->publishEndDate = $publishEndDate instanceof \DateTime ? $publishEndDate->format('U') : null;
    }
}