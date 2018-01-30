<?php

namespace Themsaid\MailPreview;

use Illuminate\Routing\Controller as BaseController;

class MailPreviewController extends BaseController
{
    /**
     * @return string
     */
    public function preview()
    {
        if ($previewPath = request()->input('path')) {
            $content = file_get_contents(config('mailpreview.path').'/'.$previewPath.'.html');
        } else {
            $lastPreviewName = last(glob(config('mailpreview.path').'/*.html'));
            $content = file_get_contents($lastPreviewName);
        }

        return $content;
    }
}