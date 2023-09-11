<?php

namespace Softspring\CmsBundle\DataCollector;

use Symfony\Bridge\Twig\DataCollector\TwigDataCollector as BaseTwigDataCollector;

class TwigDataCollector extends BaseTwigDataCollector
{
    public function reset(): void
    {
        // do not remove data, to prevent overriding on _fragments or subrequests

        // $this->computed = null; the following code sets null on computed private variable
        $reflection = new \ReflectionClass(BaseTwigDataCollector::class);
        $computedPrivateProperty = $reflection->getProperty('computed');

        if ('array' == $computedPrivateProperty->getType()) {
            return; // skip for symfony >= 6.0
        }

        $computedPrivateProperty->setAccessible(true);
        $computedPrivateProperty->setValue($this, null);
    }
}
