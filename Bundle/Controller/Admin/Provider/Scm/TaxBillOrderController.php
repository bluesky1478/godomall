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

use Component\Scm\ScmAdjust;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

class TaxBillOrderController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('scm', 'taxBill', 'orderTaxBill');

        $taxConf = gd_policy('order.taxInvoice');
        // 모듈호출
        $scmAdjust = new ScmAdjust();

        // --- 리스트 데이터
        $getValue = Request::get()->toArray();
        if ($getValue['scmFl'] == 'all') {
            if ($getValue['scmNo'] == 1) {
                $getValue['scmFl'] = 0;
            } else {
                $getValue['scmFl'] = 1;
            }
        }
        if (!$getValue['treatDate'][0] && !$getValue['treatDate'][1]) {
            $treadDate = __('전체');
        } else {
            $treadDate = $getValue['treatDate'][0] . ' ~ ' . $getValue['treatDate'][1];
        }
        try {
            $getData = $scmAdjust->getTaxBillOrderList($getValue);
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            $convertGetData = $scmAdjust->convertScmAdjustArrData($getData['data']);
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('scm', $getData['scm']);
        $this->setData('conventData', $convertGetData);
        $this->setData('getValue', $getValue);
        $this->setData('treadDate', $treadDate);
        $this->setData('sort', $getData['sort']);
        $this->setData('page', $page);
        $this->setData('taxConf', $taxConf);
    }
}
