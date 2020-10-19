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

namespace Bundle\Controller\Admin\Share;

use Framework\Debug\Exception\Except;
use Globals;
use Request;
use Exception;

class LayerDeliveryController extends \Controller\Admin\Controller
{
    /**
     * 배송비 상품 등록 페이지
     *
     * [관리자 모드] 레이어 상품 등록 페이지
     * 설명 : 상품 정보가 필요한 페이지에서 선택할 상품의 리스트
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {

        try {
            // --- 모듈 호출
            $delivery = \App::load('\\Component\\Delivery\\Delivery');

            if(Request::get()->has('scmFl') =='y')   Request::get()->set('scmFl', '1');
            else if(Request::get()->has('scmFl') =='n')  Request::get()->set('scmFl','0');

            // --- 배송 정책 설정 데이터
            if (!Request::get()->has('scmNo')) {
                Request::get()->set('scmNo', DEFAULT_CODE_SCMNO);
            }

            $getValue = Request::get()->toArray();

            $searchData['fix'] = [
                'all'    => __('전체'),
                'free'   => __('배송비무료'),
                'price'  => __('금액별배송'),
                'count'  => __('수량별배송'),
                'weight' => __('무게별배송'),
                'fixed'  => __('고정배송비'),
            ];
            $searchData['price'] = ['order' => __('할인된 상품판매가의 합'), 'goods' => __('할인안된 상품판매가의 합')];
            $searchData['print'] = ['above' => __('이상'), 'below' => __('이하')];

            $getData = $delivery->getBasicDeliveryList('layer');

            $page = \App::load('Component\\Page\\Page');

            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('searchData', $searchData);
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('total', count($getData['data']));


            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'],'search'));
            $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
            $this->setData('scmFl', gd_isset($getValue['scmFl']));
            $this->setData('scmNo', gd_isset($getValue['scmNo']));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_delivery.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
