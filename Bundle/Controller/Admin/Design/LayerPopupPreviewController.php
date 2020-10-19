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

use Framework\Debug\Exception\AlertBackException;
use Component\Design\SkinDesign;
use Component\Design\DesignPopup;
use Component\Database\DBTableField;
use Globals;
use Request;

/**
 * 팝업 미리보기
 * @author Jung Young Eun <atomyang@godo.co.kr>
 */
class LayerPopupPreviewController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        //--- DesignPopup 정의
        $designPopup = new DesignPopup();

        // _GET 데이터
        $getValue = Request::get()->toArray();

        $getData = $designPopup->getPopupDetailData($getValue['sno']);

        $this->setData('data', $getData);
        $this->getView()->setDefine('layout', 'layout_layer.php');

    }
}
