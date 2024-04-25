<?php

namespace Softspring\CmsBundle\Model;

use DateTime;

trait SchedulableContentTrait
{
    protected ?int $publishStartDate = null;

    protected ?int $publishEndDate = null;

    public function getPublishStartDate(): ?DateTime
    {
        return $this->publishStartDate ? DateTime::createFromFormat('U', "{$this->publishStartDate}") : null;
    }

    public function setPublishStartDate(?DateTime $publishStartDate): void
    {
        $this->publishStartDate = $publishStartDate instanceof DateTime ? (int) $publishStartDate->format('U') : null;
    }

    public function getPublishEndDate(): ?DateTime
    {
        return $this->publishEndDate ? DateTime::createFromFormat('U', "{$this->publishEndDate}") : null;
    }

    public function setPublishEndDate(?DateTime $publishEndDate): void
    {
        $this->publishEndDate = $publishEndDate instanceof DateTime ? (int) $publishEndDate->format('U') : null;
    }
}
