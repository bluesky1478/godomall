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

use Component\Design\DesignBanner;
use Component\Category\CategoryAdmin;
use Component\Design\SkinBase;
use Request;

/**
 * 배너 그룹 리스트
 * @author Bag YJ <kookoo135@godo.co.kr>
 */
class BannerGroupListController extends \Bundle\Controller\Admin\Design\BannerGroupListController
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
        $this->setData('skinType', $this->menuType);
    }
}
