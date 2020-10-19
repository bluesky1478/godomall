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
 * @link      http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Policy;

use Globals;

/**
 * 상품의 과세/비과세 설정
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class GoodsTaxController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'basic', 'tax');

        // --- 상품 과세 / 비과세 설정 config 불러오기
        $data = gd_policy('goods.tax');
        gd_isset($data['taxPercent'], 10);

        if (empty($data['goodsTax'])) {
            $data['goodsTax'] = ["10", "0"];
        }

        if (empty($data['deliveryTax'])) {
            $data['deliveryTax'] = ["10", "0"];
        }

        // --- 기본값 설정
        if (empty($data['taxFreeFl'])) {
            $data['taxFreeFl'] = 't';
        }

        // --- 기본값 설정
        if (empty($data['deliveryTaxFreeFl'])) {
            $data['deliveryTaxFreeFl'] = 't';
        }
        if (empty($data['deliveryTaxPercent']) === falses) {
            $data['deliveryTaxPercent'] = 10;
        }

        if (empty($data['priceTaxFl'])) {
            // 과세시 상품 금액에 부가세를 포함할 것인지 아닌지를 결정 (현재 무조건 y)
            $data['priceTaxFl'] = 'y';
        }


        $checked = [];
        $checked['goodsTax'][$data['taxPercent']] = $checked['deliveryTax'][$data['deliveryTaxPercent']] = 'checked="checked"';

        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
        $this->setData('checked', $checked);
    }
}
