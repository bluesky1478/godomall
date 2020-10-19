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
use Component\Category\CategoryAdmin;
use Globals;
use Request;

/**
 * 팝업 리스트
 * @author Bag YJ <kookoo135@godo.co.kr>
 */
class LayerBannerGroupController extends \Bundle\Controller\Admin\Design\LayerBannerGroupController
{
    /**
     * index
     *
     */
    public function index()
    {
        $this->menuType = 'mobile';
        parent::index();
        $this->getView()->setDefine('layoutContent', 'design/' . Request::getFileUri());
    }
}
