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

use Exception;

/**
 * 주문 쿠폰 복원 레이어 페이지
 * [관리자 모드] 주문 쿠폰 로그 레이어 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 * @see order/layer_order_coupon.php
 */
class LayerEventController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            // 리퀘스트
            $getValue = \Request::get()->toArray();

            // --- 모듈 호출
            $event = \App::load('\\Component\\Goods\\GoodsAdmin');
            $getData = $event->getAdminListDisplayTheme('event');

            // 페이지
            $page = \App::load('\\Component\\Page\\Page');
            $this->setData('page', $page);

            // 쿠폰유형 셀렉트 박스
            $statusText = [
                '' => '= ' . __('전체') . ' =',
                'product' => __('대기'),
                'order' => __('진행중'),
                'delivery' => __('종료'),
            ];
            $this->setData('statusText', $statusText);

            // 쿠폰 사용범위
            $displayDeviceText = [
                '' => '= ' . __('전체') . ' =',
                'all' => __('PC+모바일'),
                'pc' => __('PC'),
                'mobile' => __('모바일'),
            ];
            $this->setData('displayDeviceText', $displayDeviceText);

            // 템플릿 변수
            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'], 'search'));
            $this->setData('disabled', gd_isset($getValue['disabled'], ''));
            $this->setData('callFunc', gd_isset($getValue['callFunc'], ''));
            $this->setData('couponAdmin', $event);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('page', $page);

            // 레이어 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
