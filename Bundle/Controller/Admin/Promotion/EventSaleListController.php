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

namespace Bundle\Controller\Admin\Promotion;

use Exception;

class EventSaleListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('promotion', 'eventSale', 'list');
        $this->addScript(['sms.js']);

        // --- 모듈 호출
        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 상품 아이콘 데이터
        try {
            $getData = $goods->getAdminListDisplayTheme('event');
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('data', $getData['data']);
        $this->setData('search', $getData['search']);
        $this->setData('sort', $getData['sort']);
        $this->setData('checked', $getData['checked']);
        $this->setData('selected', $getData['selected']);
        $this->setData('page', $page);
    }
}
