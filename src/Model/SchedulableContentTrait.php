<?php

namespace Softspring\CmsBundle\Model;

trait SchedulableContentTrait
{
    protected ?int $publishStartDate = null;

    protected ?int $publishEndDate = null;

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
