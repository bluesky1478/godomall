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
use Component\Design\SkinDesign;
use Component\Database\DBTableField;
use Framework\Utility\UrlUtils;
use Globals;
use Request;
use UserFilePath;

/**
 * 멀티 팝업 등록 / 수정
 * @author Bag YJ <kookoo135@godo.co.kr>
 */
class MultiPopupRegisterController extends \Bundle\Controller\Admin\Design\MultiPopupRegisterController
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        $this->menuType = 'mobile';
        parent::index();
        $this->getView()->setDefine('layoutContent', 'design/' . \Request::getFileUri());
        $this->setData('adminList', UrlUtils::getAdminListUrl(null, true));
        $this->setData('skinType', $this->menuType);
    }
}
