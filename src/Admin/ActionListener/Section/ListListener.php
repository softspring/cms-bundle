<?php

namespace Softspring\CmsBundle\Admin\ActionListener\Section;

class ListListener extends AbstractSectionListener
{
    protected const ACTION_NAME = 'list';

    public static function getSubscribedEvents(): array
    {
        return [
            // SfsCmsEvents::ADMIN_SECTIONS_LIST_INITIALIZE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_LIST_FILTER_FORM_PREPARE => [],
            // SfsCmsEvents::ADMIN_SECTIONS_LIST_FILTER_FORM_INIT => [],
            // SfsCmsEvents::ADMIN_SECTIONS_LIST_FILTER => [],
            // SfsCmsEvents::ADMIN_SECTIONS_LIST_VIEW => [],
            // SfsCmsEvents::ADMIN_SECTIONS_LIST_EXCEPTION => [],
        ];
    }
}
