<?php

namespace Softspring\CmsBundle\Render;

class RenderErrorException extends \Exception
{
    protected RenderErrorList $renderErrorList;

    public function __construct(RenderErrorList $renderErrorList)
    {
        $this->renderErrorList = $renderErrorList;
        parent::__construct();
    }

    public function getRenderErrorList(): RenderErrorList
    {
        return $this->renderErrorList;
    }
}
