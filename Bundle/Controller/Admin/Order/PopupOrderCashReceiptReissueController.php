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

use Component\Payment\CashReceipt;
use Globals;
use Request;
use Encryptor;
use Exception;

/**
 * 현금영수증 재발행
 * @author sueun <cseun555@godo.co.kr>
 */
class PopupOrderCashReceiptReissueController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // --- 모듈 호출
        $cashReceipt = new CashReceipt();
        $order = \App::load('\\Component\\Order\\Order');
        $orderAdmin = \App::load(\Component\Order\OrderAdmin::class);

        // 공급사인 경우 입금대기, 주문취소건 제외
        $excludeStatus = null;
        if ($this->getData('isProvider')) {
            $excludeStatus = ['o', 'c'];
        }

        // --- PG 설정 불러오기
        $pgConf = gd_pgs();
        if (empty($pgConf['pgName'])) {
            $pgConf['pgNm'] = '';
        } else {
            $pgConf['pgNm'] = Globals::get('gPg.' . $pgConf['pgName']);
        }

        // --- 현금영수증 데이터
        try {
            // --- 주문 데이터
            $data = $orderAdmin->getOrderView(Request::get()->get('orderNo'), null, null, null, $excludeStatus, null);

            // --- 현금영수증 데이터
            $getData = $cashReceipt->getAdminCashReceiptInfo(Request::get()->get('orderNo'), Request::get()->get('sno'));

            // 취소된 주문이 있다면 결제금액 수정
            if(empty($data['dashBoardPrice']['dueSettlePrice']) === false){ // 취소인 경우
                $getData['settlePrice'] = $data['dashBoardPrice']['dueSettlePrice'];
            }else if(empty($data['dashBoardPrice']['refundPrice']) === false){  // 환불 완료인 경우
                $getData['settlePrice'] = $data['dashBoardPrice']['settlePrice'];
            }/*else if(empty($data['dashBoardPrice']['dueRefundPrice']) === false){  // 환불 접수인 경우
                $getData['settlePrice'] = $data['dashBoardPrice']['settlePrice'] - $data['dashBoardPrice']['dueRefundPrice'] ;
            }*/

            // 과세/면세/복합과세 표시를 위함
            $goodsTaxInfo = [];
            foreach ($data['goods'] as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        $ordStatus = substr($val['orderStatus'], 0, 1);
                        if ($ordStatus == 'o' || $ordStatus == 'p' || $ordStatus == 'g' || $ordStatus == 'd' || $ordStatus == 's'){
                            $goodsTaxInfo[$val['goodsTaxInfo'][0]] = true;
                        }
                    }
                }
            }
            if(count($goodsTaxInfo) > 1){
                $getData['taxInfo'] = 'mix';
            }else{
                $taxInfo = $goodsTaxInfo;
                if($taxInfo['t'] === true){
                    $getData['taxInfo'] = 't';
                }else if($taxInfo['f'] === true){
                    $getData['taxInfo'] = 'f';
                }
            }
            $checked = array();
            $checked['taxInfo'][$getData['taxInfo']] = 'checked="checked"';

            // --- 과세/면세설정값
            //$tax = $cashReceipt->gettaxInfoData(Request::get()->get('orderNo'));
            $tax = gd_policy('goods.tax');

            // --- 인증 종류(사업자, 핸드폰번호)
            $certNo = Encryptor::decrypt($getData['certNo']);

            // 현금영수증 발급 가능 여부
            /*$cashReceiptApplyFl = false;
            $cashReceiptApplyMsg = '';
            foreach($getData as $key => $val) {
                if ($val['statusFl'] === 'r' || $val['statusFl'] === 'f') {
                    if (empty($pgConf['pgName']) === false && $pgConf['cashReceiptFl'] == 'y') {
                        if ($getData['issueMode'] === 'a') {
                            $cashReceiptApplyFl = true;
                            $cashReceiptApplyMsg = '발급 가능';
                        } elseif ($val['issueMode'] === 'u') {
                            if (in_array(substr($val['orderStatus'], 0, 1), $order->statusReceiptApprovalPossible)) {
                                // 기간 체크 - 결제 완료일로 부터 5일을 초과하여 발급시 미발급으로 간주되어 과태료가 부가 되므로 5일로 체크를 함
                                $checkDate = date('Ymd', strtotime('-5 day'));
                                $paymentDate = gd_date_format('Ymd', $val['paymentDt']);
                                if ($paymentDate < $checkDate) {
                                    $cashReceiptApplyMsg = __('발급 불가 - 기간 만료');
                                } else {
                                    $cashReceiptApplyFl = true;
                                    $cashReceiptApplyMsg = __('발급 가능');
                                }
                            } else {
                                $cashReceiptApplyMsg = __('발급 불가 - 주문 단계');
                            }
                        } elseif ($val['issueMode'] === 'p') {
                            $cashReceiptApplyMsg = __('PG사 자동 발급됨');
                        }
                    } else {
                        if (empty($pgConf['pgName'])) {
                            $cashReceiptApplyMsg = __('PG 미신청');
                        } else {
                            $cashReceiptApplyMsg = __('현금영수증 미신청');
                        }
                    }
                } else {
                    $arrStatus = $cashReceipt::CASH_RECEIPT_STATUS;
                    $cashReceiptApplyMsg = $arrStatus[$getData['statusFl']];
                }
            }*/

            // --- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_blank.php');

            $this->setData('ordData', gd_htmlspecialchars($data));
            $this->setData('data', gd_htmlspecialchars($getData));
            $this->setData('checked', $checked);

            $this->setData('pgConf', $pgConf);
            $this->setData('gPg', Globals::get('gPg'));
            $this->setData('tax', $tax);

            $this->setData('certNo', $certNo);
            /*$this->setData('cashReceiptApplyFl', $cashReceiptApplyFl);
            $this->setData('cashReceiptApplyMsg', $cashReceiptApplyMsg);*/

            $this->setData('arrIssue', $cashReceipt::ISSUE_MODE);
            $this->setData('arrUseFl', $cashReceipt::APPLY_USE);
            $this->setData('arrCertFl', $cashReceipt::CERT_NUM_TYPE);
            $this->setData('arrStatus', $cashReceipt::CASH_RECEIPT_STATUS);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
