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

use Component\Member\Manager;
use Framework\Debug\Exception\Except;
use Exception;
use Globals;
use Request;

/**
 * 배송비조건 관리 리스트
 *
 * @package Bundle\Controller\Admin\Provider\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryConfigController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'delivery', 'config');

        // --- 모듈 호출
        $delivery = \App::load('\\Component\\Delivery\\Delivery');

        // --- 배송 정책 설정 데이터
        try {
            $mode['fix'] = [
                'all'    => '=' . __('통합검색') . '=',
                'free'   => $delivery->getFixFlText('free'),
                'price'  => $delivery->getFixFlText('price'),
                'count'  => $delivery->getFixFlText('count'),
                'weight' => $delivery->getFixFlText('weight'),
                'fixed'  => $delivery->getFixFlText('fixed'),
            ];
            $mode['price'] = ['order' => __('할인된 상품판매가의 합'), 'goods' => __('할인안된 상품판매가의 합')];
            $mode['print'] = ['above' => __('이상'), 'below' => __('이하')];

            $getData = $delivery->getBasicDeliveryList();
            $page = \App::load('Component\\Page\\Page');

            $this->setData('mode', $mode);
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($page->page['list']));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('total', count($getData['data']));
            $this->setData('deliveryMethodList', $delivery->deliveryMethodList); //배송 방식

        } catch (Exception $e) {
            throw $e;
        }
    }
}
