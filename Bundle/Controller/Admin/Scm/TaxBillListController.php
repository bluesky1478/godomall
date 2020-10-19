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

class TaxBillListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('scm', 'taxBill', 'taxBillList');

        // 모듈호출
        $scmAdjust = new ScmAdjust();

        // --- 상품 리스트 데이터
        $getValue = Request::get()->toArray();
        try {
            $getData = $scmAdjust->getTaxBillList($getValue);
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('scm/tax_bill_list.php');
    }
}
