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
namespace Bundle\Controller\Admin\Scm;

use Exception;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

class ScmAdjustPsController extends \Controller\Admin\Controller
{
    /**
     * 공급사 정산 처리
     * [관리자 모드] 공급사 정산 처리
     *
     */
    public function index()
    {
        try {
            // 모드 별 처리
            $scmAdjust = \App::load(\Component\Scm\ScmAdjust::class);
            $postValue = Request::post()->toArray();
            $msg = __('정산 요청 되었습니다.');
            switch (Request::post()->get('mode')) {
                case 'insertScmAdjustOrder':
                    $result = $scmAdjust->setScmAdjustOrder($postValue['orderGoodsNo']);
                    if ($result > 0) {
                        $msg = sprintf(__('선택하신 %s건 중 이미 정산요청된 %s건을 제외하고 정산 요청 되었습니다.'), count($postValue['orderGoodsNo']), $result);
                    }
                    $this->layer($msg, null, 2000, null, 'top.location.href="scm_adjust_order.php";');
                    break;
                case 'insertScmAdjustDelivery':
                    $result = $scmAdjust->setScmAdjustDelivery($postValue['orderDeliveryNo']);
                    if ($result > 0) {
                        $msg = sprintf(__('선택하신 %s건 중 이미 정산요청된 %s건을 제외하고 정산 요청 되었습니다.'), count($postValue['orderDeliveryNo']), $result);
                    }
                    $this->layer($msg, null, 2000, null, 'top.location.href="scm_adjust_delivery.php";');
                    break;
                case 'insertScmAdjustAfterOrder':
                    $result = $scmAdjust->setScmAdjustAfterOrder($postValue['orderGoodsNo']);
                    if ($result > 0) {
                        $msg = sprintf(__('선택하신 %s건 중 이미 정산요청된 %s건을 제외하고 정산 요청 되었습니다.'), count($postValue['orderGoodsNo']), $result);
                    }
                    $this->layer($msg, null, 2000, null, 'top.location.href="scm_adjust_after_order.php";');
                    break;
                case 'insertScmAdjustAfterDelivery':
                    $result = $scmAdjust->setScmAdjustAfterDelivery($postValue['orderDeliveryNo']);
                    if ($result > 0) {
                        $msg = sprintf(__('선택하신 %s건 중 이미 정산요청된 %s건을 제외하고 정산 요청 되었습니다.'), count($postValue['orderDeliveryNo']), $result);
                    }
                    $this->layer($msg, null, 2000, null, 'top.location.href="scm_adjust_after_delivery.php";');
                    break;
                case 'insertScmAdjustManual':
                    $scmAdjust->setScmAdjustManual($postValue);
                    $this->layer(__('수기 정산 요청 되었습니다.'), null, null, null, 'top.location.href="scm_adjust_list.php";');
                    break;
                case 'modifyScmAdjustList':
                    $scmAdjust->setScmAdjustState($postValue['chk'], $postValue['scmAdjustState'], $postValue['scmAdjustMemo']);
                    $this->layer(__('정산상태가 변경되었습니다.'), null, null, null, 'top.location.href="scm_adjust_list.php";');
                    break;
                case 'insertScmAdjustScmTaxBill':
                    $godoBillConfig = gd_policy('order.taxInvoice');
                    if ($postValue['scmAdjustTaxBill'] == 'godo') {
                        if ($godoBillConfig['eTaxInvoiceFl'] != 'y') {
                            throw new Exception(__('전자세금계산서를 설정하셔야 합니다.'));
                        }
                    }
                    $scmAdjust->setScmAdjustScmTaxBill($postValue);
                    $this->layer(__('세금계산서 발행되었습니다.'), null, null, null, 'top.location.href="tax_bill_scm.php";');
                    break;
                case 'insertScmAdjustOrderTaxBill':
                    $godoBillConfig = gd_policy('order.taxInvoice');
                    if ($postValue['scmAdjustTaxBill'] == 'godo') {
                        if ($godoBillConfig['eTaxInvoiceFl'] != 'y') {
                            throw new Exception(__('전자세금계산서를 설정하셔야 합니다.'));
                        }
                    }
                    $scmAdjust->setScmAdjustOrderTaxBill($postValue);
                    $this->layer(__('세금계산서 발행되었습니다.'), null, null, null, 'top.location.href="tax_bill_order.php";');
                    break;
                default:
                    exit();
                    break;
            }
            exit;
        } catch (\Exception $e) {
            if ($e->getCode() == 500) {
                throw new LayerException($e->getMessage(), $e->getCode(), $e, null, 2000);
            } else {
                throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
            }
        }
    }
}
