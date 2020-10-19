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

class FramePgPartCancelController extends \Controller\Admin\Controller
{
    /**
     * PG 취소 페이지
     * [관리자 모드] PG 취소 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @param array $get
     * @param array $post
     * @param array $files
     * @throws Except
     * @throws AlertOnlyException
     * @copyright ⓒ 2016, NHN godo: Corp.
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

            // 지금까지의 환불 금액
            $orderData['totalCancelPrice'] = $order->getOrderRefundPrice(Request::get()->get('orderNo'));
            gd_isset($orderData['totalCancelPrice'], 0);

            // Allat 인 경우 지불방법 세팅
            if ($orderData['pgName'] == 'allat') {
                $orderData['payType'] = $pgCodeConfig->getPgSettleCode()[$orderData['pgName']][substr($orderData['settleKind'], 1, 1)];
            }

            // 올더게이트 인경우 카드사 코드 처리
            if ($orderData['pgName'] == 'allthegate') {
                if (in_array($pgCodeConfig->getPgCards()[$orderData['pgName']][$orderData['pgCardCd']], array('0100', '0200')) === true) {
                    $orderData['SubTy'] = 'isp';
                    $orderData['pgAppDt'] = substr($orderData['pgAppDt'], 0, 8); // 8자리 YYYYMMDD
                } else {
                    $orderData['SubTy'] = 'visa3d';
                    $orderData['pgAppDt'] = $orderData['pgAppDt']; // 14자리 YYYYMMDDHHMMSS
                }
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
        $this->getView()->setDefine('layoutPGContent', DIR_PG . $pgConf['pgName'] . '/cancel_part_start.php');

        $this->setData('orderData', $orderData);
        $this->setData('handleSno', Request::get()->get('handleSno'));
        $this->setData('pgConf', $pgConf);
        $this->setData('gMall', Globals::get('gMall'));
    }
}
