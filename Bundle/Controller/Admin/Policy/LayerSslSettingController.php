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
namespace Bundle\Controller\Admin\Policy;

use Request;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * Class LayerSslSettingController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class LayerSslSettingController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 모듈 호출
        $sslAdmin = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
        // --- 페이지 데이터
        $getValue = Request::get()->toArray();
        try {
            $getData = $sslAdmin->getSslView($getValue);
            $checked['sslConfigUse'][$getData['sslConfigUse']] = 'checked';
            $checked['sslConfigApplyLimit'][$getData['sslConfigApplyLimit']] = 'checked';
            $checked['sslConfigImageUse'][$getData['sslConfigImageUse']] = 'checked';
            $checked['sslConfigImageType'][$getData['sslConfigImageType']] = 'checked';

            $this->setData('sslData', $getData);
            $this->setData('checked', $checked);
            $this->setData('sslRule', $sslAdmin->_sslRule);
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
