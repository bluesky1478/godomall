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

use Component\Godo\MyGodoSmsServerApi;
use Request;
use Exception;

/**
 * 레이어 무통장 입금 은행 추가/수정 페이지
 * [관리자 모드] 레이어 무통장 입금 은행 추가/수정 페이지
 *
 * @package Bundle\Controller\Admin\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerSettleBankController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // 은행정보
            $order = \App::load('\\Component\\Order\\OrderAdmin');
            $getData = $order->getBankPolicyData(Request::request()->get('sno'));

            // 템플릿 변수 설정
            $this->setData('data', $getData['data']);
            $this->setData('checked', $getData['checked']);

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 인증후 SMS 템플릿 설정
            MyGodoSmsServerApi::setControllerData($this, '보안인증');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
