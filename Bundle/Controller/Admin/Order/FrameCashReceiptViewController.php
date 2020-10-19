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
 * 현금영수증 요청 / 발급 정보 확인
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class FrameCashReceiptViewController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // --- 모듈 호출
        $cashReceipt = new CashReceipt();
        $order = \App::load('\\Component\\Order\\Order');

        // --- 현금영수증 데이터
        try {
            // --- PG 설정 불러오기
            $pgConf = gd_pgs();
            if (empty($pgConf['pgName'])) {
                $pgConf['pgNm'] = '';
            } else {
                $pgConf['pgNm'] = Globals::get('gPg.' . $pgConf['pgName']);
            }
            // --- 현금영수증 데이터
            // 관리자, 고객용 따로 하기 위함.
            //$getData = $cashReceipt->getCashReceiptInfo(Request::get()->get('orderNo'), Request::get()->get('sno'));
            $getData = $cashReceipt->getAdminCashReceiptInfo(Request::get()->get('orderNo'), Request::get()->get('sno'));

            // 주문 단계 설정
            $tmpStatus = gd_policy('order.status');
            foreach ($tmpStatus as $key => $val) {
                if ($key != 'autoCancel') {
                    foreach ($val as $oKey => $oVal) {
                        if (strlen($oKey) == 2) {
                            $orderStatus[$oKey] = $oVal['admin'];
                        }
                    }
                }
            }

            $certNo = Encryptor::decrypt($getData['certNo']);
            if ($getData['certFl'] == 'l') {
                $certNo = substr($certNo, 0, 6) . '-*******';
            } else {
                $certNo = $certNo;
            }

            // 현금영수증 발급 가능 여부
            $cashReceiptApplyFl = false;
            $cashReceiptApplyMsg = '';
            if ($getData['statusFl'] === 'r' || $getData['statusFl'] === 'f') {
                if (empty($pgConf['pgName']) === false && $pgConf['cashReceiptFl'] == 'y') {
                    if ($getData['issueMode'] === 'a') {
                        $cashReceiptApplyFl = true;
                        $cashReceiptApplyMsg = '발급 가능';
                    } elseif ($getData['issueMode'] === 'u') {
                        if (in_array(substr($getData['orderStatus'], 0, 1), $order->statusReceiptApprovalPossible)) {
                            // 기간 체크 - 결제 완료일로 부터 5일을 초과하여 발급시 미발급으로 간주되어 과태료가 부가 되므로 5일로 체크를 함
                            $checkDate = date('Ymd', strtotime('-5 day'));
                            $paymentDate = gd_date_format('Ymd', $getData['paymentDt']);
                            if ($paymentDate < $checkDate) {
                                $cashReceiptApplyMsg = __('발급 불가 - 기간 만료');
                            } else {
                                $cashReceiptApplyFl = true;
                                $cashReceiptApplyMsg = __('발급 가능');
                            }
                        } else {
                            $cashReceiptApplyMsg = __('발급 불가 - 주문 단계');
                        }
                    } elseif ($getData['issueMode'] === 'p') {
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

                // 발급후 취소 버튼 노출
                if ($getData['statusFl'] === 'y') {
                    if (empty($pgConf['pgName']) === false && $pgConf['cashReceiptFl'] == 'y') {
                        if ($getData['issueMode'] === 'a') {
                            $cashReceiptApplyFl = true;
                        } elseif ($getData['issueMode'] === 'u') {
                            $cashReceiptApplyFl = true;
                        }
                    }
                }
            }

            if (in_array($getData['statusFl'], ['c', 'd']) === true) {
                $cashReceiptApplyFl = false;
            }

            // --- 관리자 디자인 템플릿
            //$this->getView()->setDefine('layout', 'layout_blank.php');
            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutCashReceiptForm', Request::getDirectoryUri() . '/frame_cash_receipt_form.php');

            $this->setData('data', gd_htmlspecialchars($getData));
            $this->setData('pgConf', $pgConf);
            $this->setData('gPg', Globals::get('gPg'));
            $this->setData('orderStatus', $orderStatus);
            $this->setData('gMall', Globals::get('gMall'));
            $this->setData('certNo', $certNo);
            $this->setData('cashReceiptApplyFl', $cashReceiptApplyFl);
            $this->setData('cashReceiptApplyMsg', $cashReceiptApplyMsg);

            $this->setData('arrIssue', $cashReceipt::ISSUE_MODE);
            $this->setData('arrUseFl', $cashReceipt::APPLY_USE);
            $this->setData('arrCertFl', $cashReceipt::CERT_NUM_TYPE);
            $this->setData('arrStatus', $cashReceipt::CASH_RECEIPT_STATUS);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
