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

namespace Bundle\Controller\Admin\Mobile;

use Framework\Debug\Exception\AlertBackException;
use Component\Design\DesignBanner;
use Request;

/**
 * 움직이는 배너 미리보기
 * @author Bag yj <kookoo135@godo.co.kr>
 */
class SliderBannerPreviewController extends \Bundle\Controller\Admin\Design\SliderBannerPreviewController
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        parent::index();
        $this->getView()->setDefine('layoutContent', 'design/' . Request::getFileUri());
    }
}
