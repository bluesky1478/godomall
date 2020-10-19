<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Globals;

class ImageViewerController extends \Controller\Admin\Controller
{
    /**
     * 이미지뷰어
     * @author    oneorzero, sunny
     * @version   1.0
     * @since     1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData(
            'headerScript',
            [
                // drag event
                PATH_ADMIN_GD_SHARE . 'script/jquery/jquery.event.drag.js'
                // BASE
                ,
                PATH_ADMIN_GD_SHARE . 'script/jquery/jquery.imageViewer.js',
            ]
        );


    }
}
