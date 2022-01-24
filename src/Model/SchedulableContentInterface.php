<?php

namespace Softspring\CmsBundle\Model;

interface SchedulableContentInterface
{
    /**
     * @return \DateTime|null
     */
    public function getPublishStartDate(): ?\DateTime;

    /**
     * @return \DateTime|null
     */
    public function setPublishStartDate(?\DateTime $publishStartDate): void;

    /**
     * @return \DateTime|null
     */
    public function getPublishEndDate(): ?\DateTime;

    /**
     * @return \DateTime|null
     */
    public function setPublishEndDate(?\DateTime $publishEndDate): void;
}