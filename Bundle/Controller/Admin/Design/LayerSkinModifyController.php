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

namespace Bundle\Controller\Admin\Design;

use Component\Design\SkinBase;
use Framework\Debug\Exception\LayerException;
use Message;
use Globals;
use Request;

/**
 * 디자인 스킨 정보
 * @author <kookoo135@godo.co.kr>
 */
class LayerSkinModifyController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        //--- 페이지 데이터
        if (Request::get()->has('skinCode') === true) {
            $skinCode = Request::get()->get('skinCode');
        } else {
            throw new LayerException(__('로딩이 실패했습니다.'));
        }
        if (Request::get()->has('skinType') === true) {
            $skinType = Request::get()->get('skinType');
        } else {
            throw new LayerException(__('로딩이 실패했습니다.'));
        }
        $skinBase = new SkinBase($skinType);
        $skinInfo = $skinBase->getSkinInfo($skinCode);

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', 'design/' . Request::getFileUri());

        $this->setData('skinType', $skinType);
        $this->setData('skinInfo', $skinInfo);
    }
}
