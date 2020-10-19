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
namespace Bundle\Controller\Admin\Order;

use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\AlertOnlyException;
use Globals;
use App;
use Request;

class FrameEscrowDenyController extends \Controller\Admin\Controller
{
    /**
     * 에스크로 거절 확인 페이지
     * [관리자 모드] 에스크로 거절 확인 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     * @throws AlertOnlyException
     */
    public function index()
    {
        // --- 모듈 호출
        $pgCodeConfig = App::getConfig('payment.pg');

        // --- 에스크로 배송 등록 데이터
        try {
            ob_start();

            // --- PG 설정 불러오기
            $pgConf = gd_pgs();
            $pgConf['pgNm'] = Globals::get('gPg.' . $pgConf['pgName']);

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 주문 데이타
            $orderData = $order->getOrderData(Request::get()->get('orderNo'));

            // Allat 인 경우 지불방법 세팅
            if ($pgConf['pgName'] == 'allat') {
                $orderData['payType'] = $pgCodeConfig->getPgSettleCode()[$pgConf['pgName']][substr($orderData['settleKind'], 1, 1)];
            }

            if ($out = ob_get_clean()) {
                throw new Except('ECT_LOAD_FAIL', $out);
            }
        } catch (Except $e) {
            $e->actLog();
            throw new AlertOnlyException($e->ectMessage, null, null, 'parent.$.unblockUI();');
            // echo ($e->ectMessage);
            exit();
        }

        // --- 관리자 디자인 템플릿

        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());
        $this->getView()->setDefine('layoutPGContent', DIR_PG . $pgConf['pgName'] . '/escrow_deny_start.php');

        $this->setData('orderData', $orderData);
        $this->setData('pgConf', $pgConf);
        $this->setData('gMall', Globals::get('gMall'));
    }
}
