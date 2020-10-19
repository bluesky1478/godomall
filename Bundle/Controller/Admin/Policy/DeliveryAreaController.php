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

use Framework\Debug\Exception\Except;
use Globals;
use Request;
use Exception;

/**
 * 지역별 추가배송비 관리
 * [관리자 모드] 배송 정책 설정 관리 페이지
 *
 * @package Bundle\Controller\Admin\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryAreaController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('policy', 'delivery', 'area');

            // --- 모듈 호출
            $delivery = \App::load('\\Component\\Delivery\\Delivery');

            $getData = $delivery->getAreaGroupDeliveryList();
            $page = \App::load('Component\\Page\\Page');

            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($page->page['list']));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('delivery', $delivery);
            $this->setData('total', count($getData['data']));

        } catch (Exception $e) {
            throw $e;
        }
    }
}
