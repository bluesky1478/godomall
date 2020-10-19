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

use App;
use Framework\Utility\StringUtils;
use Framework\Debug\Exception\AlertBackException;
use Globals;
use Request;
use Message;

/**
 * 현금영수증 발급 요청 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class FrameCashReceiptRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 현금영수증 데이터
        try {
            if (!is_numeric(Request::request()->get('orderNo'))) {
                $this->js(__('잘못된 주문번호입니다.'));
            }
            // --- PG 설정 불러오기
            $pgConf = gd_pgs();
            if (empty($pgConf['pgName'])) {
                $pgConf['pgNm'] = '';
            } else {
                $pgConf['pgNm'] = Globals::get('gPg.' . $pgConf['pgName']);
            }

            // --- 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 주문 데이터
            $orderData = $order->getOrderViewForReceipt(StringUtils::xssClean(Request::request()->get('orderNo')));

            // --- 회원 데이터
            if (empty($orderData['memNo']) == false) {
                $member = \App::load('\\Component\\Member\\Member');
                $memberData = $member->getMemberInfo($orderData['memNo']);
                $this->setData('memberData', $memberData);
            }
        } catch (\Exception $e) {
            $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
            $this->layer(__('오류가 발생 하였습니다.'). $item);
        }

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_blank.php');

        // 공급사와 템플릿 공유
        $this->getView()->setPageName('order/frame_cash_receipt_register.php');

        $this->setData('orderData', $orderData);
        $this->setData('pgConf', $pgConf);
        $this->setData('emailDomain', $emailDomain);
    }
}
