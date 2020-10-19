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

class TaxBillScmController extends \Controller\Admin\Controller
{
    /**
     */
    public function index()
    {
        $this->callMenu('scm', 'taxBill', 'scmTaxBill');

        $taxConf = gd_policy('order.taxInvoice');
        // 모듈호출
        $scmAdjust = new ScmAdjust();

        // --- 리스트 데이터
        try {
            $getData = $scmAdjust->getTaxBillScmList(Request::get()->all());
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('checked', $getData['checked']);
        $this->setData('taxConf', $taxConf);
    }
}
