<?php

namespace Softspring\CmsBundle\Model;

interface SchedulableContentInterface
{
    public function getPublishStartDate(): ?\DateTime;

    /**
     * @return \DateTime|null
     */
    public function setPublishStartDate(?\DateTime $publishStartDate): void;

    public function getPublishEndDate(): ?\DateTime;

    /**
     * @return \DateTime|null
     */
    public function setPublishEndDate(?\DateTime $publishEndDate): void;
}
