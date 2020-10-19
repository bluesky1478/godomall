<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Policy;

/**
 * 거래명세서/주문내역서 출력 설정
 * @author <bumyul2000@godo.co.kr>
 */
class OrderPrintConfigController extends \Controller\Admin\Controller
{

    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'order', 'orderPrint');

        // --- 장바구니 설정 정보
        try {
            $data = gd_policy('order.print');

            //거래명세서 출력 설정
            gd_isset($data['orderPrintSameDisplay'], 'y');
            gd_isset($data['orderPrintBusinessInfo'], 'n');
            gd_isset($data['orderPrintBusinessInfoType'], 'companyWithOrder');
            gd_isset($data['orderPrintBottomInfo'], 'n');
            gd_isset($data['orderPrintBottomInfoType'], '');
            gd_isset($data['orderPrintBottomInfoText'], '');
            gd_isset($data['orderPrintQuantityDisplay'], 'n');
            gd_isset($data['orderPrintAmountDelivery'], 'y');
            gd_isset($data['orderPrintAmountDiscount'], 'y');
            gd_isset($data['orderPrintAmountMileage'], 'n');
            gd_isset($data['orderPrintAmountDeposit'], 'n');
            gd_isset($data['orderPrintGoodsNo'], 'n');
            gd_isset($data['orderPrintGoodsCd'], 'n');

            //주문내역서 출력 설정
            gd_isset($data['orderPrintOdSameDisplay'], 'y');
            gd_isset($data['orderPrintOdGoodsCode'], 'n');
            gd_isset($data['orderPrintOdSelfGoodsCode'], 'n');
            gd_isset($data['orderPrintOdScmDisplay'], 'y');
            gd_isset($data['orderPrintOdImageDisplay'], 'y');
            gd_isset($data['orderPrintOdSettleInfoDisplay'], 'y');
            gd_isset($data['orderPrintOdAdminMemoDisplay'], 'n');
            gd_isset($data['orderPrintOdBottomInfo'], 'n');
            gd_isset($data['orderPrintOdBottomInfoText'], '');

            //주문내역서 (고객용) 출력 설정
            gd_isset($data['orderPrintOdCsGoodsCode'], 'n');
            gd_isset($data['orderPrintOdCsSelfGoodsCode'], 'n');
            gd_isset($data['orderPrintOdCsImageDisplay'], 'y');
            gd_isset($data['orderPrintOdCsSettleInfoDisplay'], 'y');
            gd_isset($data['orderPrintOdCsAdminMemoDisplay'], 'n');
            gd_isset($data['orderPrintOdCsBottomInfo'], 'n');
            gd_isset($data['orderPrintOdCsBottomInfoText'], '');

            $checked = [];
            $checked['orderPrintBusinessInfoType'][$data['orderPrintBusinessInfoType']] = $checked['orderPrintSameDisplay'][$data['orderPrintSameDisplay']] = $checked['orderPrintBusinessInfo'][$data['orderPrintBusinessInfo']] = $checked['orderPrintBottomInfo'][$data['orderPrintBottomInfo']] = $checked['orderPrintBottomInfoType'][$data['orderPrintBottomInfoType']] = $checked['orderPrintQuantityDisplay'][$data['orderPrintQuantityDisplay']] = $checked['orderPrintAmountDelivery'][$data['orderPrintAmountDelivery']] = $checked['orderPrintAmountDiscount'][$data['orderPrintAmountDiscount']] = $checked['orderPrintAmountMileage'][$data['orderPrintAmountMileage']] = $checked['orderPrintAmountDeposit'][$data['orderPrintAmountDeposit']] = $checked['orderPrintOdSameDisplay'][$data['orderPrintOdSameDisplay']] = $checked['orderPrintOdGoodsCode'][$data['orderPrintOdGoodsCode']] = $checked['orderPrintOdSelfGoodsCode'][$data['orderPrintOdSelfGoodsCode']] = $checked['orderPrintOdScmDisplay'][$data['orderPrintOdScmDisplay']] = $checked['orderPrintOdImageDisplay'][$data['orderPrintOdImageDisplay']] = $checked['orderPrintOdSettleInfoDisplay'][$data['orderPrintOdSettleInfoDisplay']] = $checked['orderPrintOdAdminMemoDisplay'][$data['orderPrintOdAdminMemoDisplay']] = $checked['orderPrintOdBottomInfo'][$data['orderPrintOdBottomInfo']] = $checked['orderPrintOdCsSelfGoodsCode'][$data['orderPrintOdCsSelfGoodsCode']] = $checked['orderPrintOdCsImageDisplay'][$data['orderPrintOdCsImageDisplay']] = $checked['orderPrintOdCsSettleInfoDisplay'][$data['orderPrintOdCsSettleInfoDisplay']] = $checked['orderPrintOdCsAdminMemoDisplay'][$data['orderPrintOdCsAdminMemoDisplay']] = $checked['orderPrintOdCsBottomInfo'][$data['orderPrintOdCsBottomInfo']] = $checked['orderPrintOdCsGoodsCode'][$data['orderPrintOdCsGoodsCode']] = $checked['orderPrintGoodsNo'][$data['orderPrintGoodsNo']] = $checked['orderPrintGoodsCd'][$data['orderPrintGoodsCd']] = 'checked="checked"';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
    }
}
