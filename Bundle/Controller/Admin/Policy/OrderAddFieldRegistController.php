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
use Component\Database\DBTableField;
use Exception;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\UrlUtils;
use Request;

class OrderAddFieldRegistController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 모듈 호출
        $orderAdmin = new OrderAdmin();

        // --- 주문 추가 필드 데이터
        try {
            $mallSno = gd_isset(\Request::get()->get('mallSno'), $getData['mallSno']) ?? 1;
            $getData = [];
            // 주문 추가 필드 고유 번호
            $orderAddFieldNo = Request::get()->get('orderAddFieldNo');
            // $orderAddFieldNo 가 없으면 디비 디폴트 값 설정
            if ($orderAddFieldNo > 0) {
                $getData = $orderAdmin->getOrderAddField($orderAddFieldNo, $mallSno);
                $getConvertData = $orderAdmin->convertOrderAddField($getData);
                $mode = 'modifyOrderAddField';
                // --- 메뉴 설정
                $this->callMenu('policy', 'order', 'orderAddFieldModify');
            } else {
                DBTableField::setDefaultData('tableOrderAddField', $getData);
                $getData['orderAddFieldApplyType'] = 'all';
                $getData['orderAddFieldExceptCategoryType'] = '';
                $getData['orderAddFieldExceptBrandType'] = '';
                $getData['orderAddFieldExceptGoodsType'] = '';
                $mode = 'insertOrderAddField';
                // --- 메뉴 설정
                $this->callMenu('policy', 'order', 'orderAddFieldInsert');
            }

            $checked['orderAddFieldDisplay'][$getData['orderAddFieldDisplay']] =
            $checked['orderAddFieldRequired'][$getData['orderAddFieldRequired']] =
            $checked['orderAddFieldType'][$getData['orderAddFieldType']] =
            $checked['orderAddFieldOption'][$getData['orderAddFieldType']]['encryptor'][$getData['orderAddFieldOption'][$getData['orderAddFieldType']]['encryptor']] =
            $checked['orderAddFieldOption'][$getData['orderAddFieldType']]['password'][$getData['orderAddFieldOption'][$getData['orderAddFieldType']]['password']] =
            $checked['orderAddFieldProcess'][$getData['orderAddFieldProcess']] =
            $checked['orderAddFieldApplyType'][$getData['orderAddFieldApplyType']] =
            $checked['orderAddFieldExceptCategoryType'][$getData['orderAddFieldExceptCategoryType']] =
            $checked['orderAddFieldExceptBrandType'][$getData['orderAddFieldExceptBrandType']] =
            $checked['orderAddFieldExceptGoodsType'][$getData['orderAddFieldExceptGoodsType']] = 'checked="checked"';

            $mall = new Mall();
            $mallList = $mall->getListByUseMall();
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $this->setData('mode', gd_isset($mode));
        $this->setData('getData', gd_isset($getData));
        $this->setData('checked', gd_isset($checked));
        $this->setData('mallSno', $mallSno);
        $this->setData('mallList', $mallList);
        $this->setData('mallCnt', count($mallList));
        $this->setData('adminList', UrlUtils::getAdminListUrl());
    }
}
