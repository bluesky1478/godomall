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
use Globals;
use Request;
use UserFilePath;
use Component\Scm\ScmAdjust;

/**
 * Class TaxBillPrintController
 * @package Bundle\Controller\Admin\Order
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class TaxBillPrintController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        // 모듈호출
        $scmAdjust = new ScmAdjust();

        // --- 리스트 데이터
        $postValue = Request::post()->toArray();
        try {
            $getData = $scmAdjust->getTaxBillInfo($postValue['scmAdjustTaxBillNo']);

            if (!$getData['scmAdjustTaxBillDt']) {
                $getData['scmAdjustTaxBillDt'] = $getData['regDt'];
            }
            // 세금계산서 정보
            $taxInvoice = gd_policy('order.taxInvoice');
            if (empty($taxInvoice['taxStampIamge']) === false) {
                $sealPath = UserFilePath::data('etc', $taxInvoice['taxStampIamge'])->www();
            } else {
                $sealPath = '';
            }

            // 출력 종류
            if ($postValue['taxBillPrintMode'] == 'blue') {
                $taxInfo['classids'] = ['cssblue'];                //-- 공급받는자용
            } else if ($postValue['taxBillPrintMode'] == 'red') {
                $taxInfo['classids'] = ['cssred'];                //-- 공급자용
            } else {
                $taxInfo['classids'] = [
                    'cssblue',
                    'cssred',
                ];    //-- 공급받는자용, 공급자용
            }
            $taxInfo['headuser'] = [
                'cssblue' => __('공급받는자보관용'),
                'cssred'  => __('공급자보관용'),
            ];
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->getView()->setPageName('scm/tax_bill_print.php');

        $this->setData('gMall', gd_htmlspecialchars((Globals::get('gMall'))));
        $this->setData('sealPath', gd_isset($sealPath));
        $this->setData('taxInfo', gd_isset($taxInfo));
        $this->setData('data', $getData);
    }
}
