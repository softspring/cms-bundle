<?php

namespace Softspring\CmsBundle\Model;

use DateTime;

interface SchedulableContentInterface
{
    public function getPublishStartDate(): ?DateTime;

    public function setPublishStartDate(?DateTime $publishStartDate): void;

    public function getPublishEndDate(): ?DateTime;

    public function setPublishEndDate(?DateTime $publishEndDate): void;
}
