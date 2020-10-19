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
namespace Bundle\Controller\Admin\Policy;

use Component\Mall\Mall;
use Component\Order\OrderAdmin;
use Exception;
use Framework\Debug\Exception\LayerException;

class OrderAddFieldListController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'order', 'orderAddFieldList');

        $mall = new Mall();
        $mallList = $mall->getListByUseMall();
        $mallSno = gd_isset(\Request::get()->get('mallSno'), 1);

        // --- 설정값 정보
        $orderAddFieldConfig = gd_policy('order.addField', $mallSno);

        // --- 기본값 설정
        gd_isset($orderAddFieldConfig['orderAddFieldUseFl'],'n');
        $checked['orderAddFieldUseFl'][$orderAddFieldConfig['orderAddFieldUseFl']] = 'checked="checked"';

        // --- 모듈 호출
        $orderAdmin = new OrderAdmin();

        // --- 주문 추가 필드 데이터
        try {
            $getData = $orderAdmin->getOrderAddFieldList($mallSno);
            $dataCount = $orderAdmin->getOrderAddFieldCount();
            $convertData = $orderAdmin->convertOrderAddField($getData['data'], true);
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        $this->setData('checked', $checked);
        $this->setData('data', $getData['data']);
        $this->setData('convertData', $convertData);
        $this->setData('dataCount', $dataCount);
        $this->setData('type', $getData['type']);
        $this->setData('display', $getData['display']);
        $this->setData('required', $getData['required']);
        $this->setData('mallCnt', count($mallList));
        $this->setData('mallList', $mallList);
        $this->setData('mallSno', $mallSno);
    }
}
