<?php

namespace PictureChat\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class PictureChatUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
