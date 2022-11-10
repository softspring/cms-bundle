<?php

namespace Softspring\CmsBundle\Validator;

use Symfony\Component\Validator\Constraints\File;

class ContentZipFile extends File
{
    public string $invalidNameFormat = 'The name is not valid. Make sure it\'s a content dump';
    public string $canNotOpenFile = 'Can not open zip file. Make sure it\'s a content dump';
    public string $doesNotContainContentFile = 'Does not contain content file. Make sure it\'s a content dump';
}
