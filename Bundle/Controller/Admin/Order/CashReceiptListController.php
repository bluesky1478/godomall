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

use Component\Page\Page;
use Component\Payment\CashReceipt;
use Exception;

/**
 * 현금영수증 신청 리스트 페이지
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class CashReceiptListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'cashReceipt', 'list');

        // --- 모듈 호출
        $cashReceipt = new CashReceipt();
        $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');
        $order = \App::load('\\Component\\Order\\Order');

        // --- 현금영수증 데이터
        try {
            // --- PG 설정 불러오기
            $pgConf = gd_pgs();

            // --- 현금영수증 데이터
            $getData = $cashReceipt->getAdminCashReceiptList();
            $cashReceiptGetData = [];
            $newGetData = [];
            foreach($getData['data'] as $key => $val){
                foreach($val as $data){
                    if($data['taxPrice'] > 0 && $data['freePrice'] == 0){
                        $data['taxStatus'] = '과세';
                    }
                    if($data['taxPrice'] == 0 && $data['freePrice'] > 0){
                        $data['taxStatus'] = '면세';
                    }
                    if($data['taxPrice'] > 0 && $data['freePrice'] > 0){
                        $data['taxStatus'] = '복합과세';
                    }

                    // 발행금액 변경 체크
                    $changePriceFl = $cashReceipt->cashReceiptOrderGoodsDataChk($data['orderNo']);  // 클레임상태로 발급금액이 변경되었을 경우

                    if(empty($changePriceFl) === false){
                        $res = $cashReceipt->cashReceiptPriceChangeChk($data['orderNo']);   // 현금영수증 재발급이 있는 경우
                        //if ($res[0]['statusFl'] != 'y') {
                            if ($res[0]['priceChangeFl'] == 'y' && $res[0]['sno'] == $data['rSno']) {
                                $data['priceChangeFl'] = 'y';
                            } else {
                                $data['priceChangeFl'] = 'n';
                            }
                        //}
                    }else{
                        $data['priceChangeFl'] = 'n';
                    }
                    array_push($cashReceiptGetData, $data);
                    
                    // 현금영수증 발급/조회 같은 주문번호건 배경 색상 변경
                    $bgChk[$data['orderNo']] = $cashReceipt->cashReceiptListBgChk($data['orderNo']);

                }
            }

            foreach($cashReceiptGetData as $key => $val) {
                if (empty ($newGetData[$val['orderNo']]) === true) {
                    $newGetData[$val['orderNo']][] = $val;
                } else {
                    array_push($newGetData[$val['orderNo']], $val);
                }
            }

            $getData['data'] = $newGetData;

            // 주문 단계 설정
            $setStatus = $orderAdmin->getOrderStatusAdmin();

            // 결제 방법 설정
            $setSettleKind = $orderAdmin->getSettleKind();
        } catch (Exception $e) {
            // echo ($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->setData('getData',$getData);
        $this->setData('search', $getData['search']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $getData['page']);
        $this->setData('sort', $getData['sort']);
        //$this->setData($getData);
        $this->setData('pgConf', $pgConf);
        $this->setData('setStatus', $setStatus);
        $this->setData('setSettleKind', $setSettleKind);
        $this->setData('statusReceiptPossible', $orderAdmin->statusReceiptPossible);

        $this->setData('arrIssue', $cashReceipt::ISSUE_MODE);
        $this->setData('arrUseFl', $cashReceipt::APPLY_USE);
        $this->setData('arrCertFl', $cashReceipt::CERT_NUM_TYPE);
        $this->setData('arrStatus', $cashReceipt::CASH_RECEIPT_STATUS);
        $this->setData('arrOrdStatus', $order::ORDER_STATUS);

        $this->setData('res', $res);
        $this->setData('bgChk', $bgChk);
    }
}
