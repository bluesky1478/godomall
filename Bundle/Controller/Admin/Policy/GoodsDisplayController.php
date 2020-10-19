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

use Request;
use Session;

/**
 * 상품 정보 노출 설정 페이지
 * @author atomyang
 */
class GoodsDisplayController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'goods', 'display');

        // --- 상품  설정 config 불러오기
        $data['config'] = gd_policy('goods.display');

        //기본값 세팅
        gd_isset($data['config']['priceFl'],'y');
        gd_isset($data['config']['imageLazyFl'],'y');
        gd_isset($data['config']['goodsModDtTypeUp'], 'y');
        gd_isset($data['config']['goodsModDtTypeList'], 'y');
        gd_isset($data['config']['goodsModDtTypeAll'], 'y');
        gd_isset($data['config']['goodsModDtTypeExcel'], 'y');
        gd_isset($data['config']['goodsModDtTypeScm'], 'y');
        gd_isset($data['config']['goodsModDtFl'], 'n');
        gd_isset($data['config']['optionPriceFl'], 'y');

        $data['option_1903'] = gd_policy('goods.option_1903');
        if(Session::get('manager.isSuper') == 'n') $data['option_1903'] = 'y';

        //체크박스
        $data['checked']['imageLazyFl'][$data['config']['imageLazyFl']] =
        $data['checked']['priceFl'][$data['config']['priceFl']] =
        $data['checked']['goodsModDtTypeUp'][$data['config']['goodsModDtTypeUp']] =
        $data['checked']['goodsModDtTypeList'][$data['config']['goodsModDtTypeList']] =
        $data['checked']['goodsModDtTypeAll'][$data['config']['goodsModDtTypeAll']] =
        $data['checked']['goodsModDtTypeExcel'][$data['config']['goodsModDtTypeExcel']] =
        $data['checked']['goodsModDtTypeScm'][$data['config']['goodsModDtTypeScm']] =
        $data['checked']['goodsModDtFl'][$data['config']['goodsModDtFl']] =
        $data['checked']['optionPriceFl'][$data['config']['optionPriceFl']] = "checked='checked'";


        // --- 관리자 디자인 템플릿
        $this->setData('data', $data);
    }
}