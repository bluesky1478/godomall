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
namespace Bundle\Controller\Admin\Order;

use Framework\Debug\Exception\AlertBackException;
use Exception;
use Globals;
use Request;

/**
 *
 *
 * @package Bundle\Controller\Admin\Order
 * @author  jung young eun <atomyang@godo.co.kr>
 */
class PostReserveController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'epostParcel', 'reserve');

            $godopost= gd_policy('order.godopost');
            if(empty($godopost['compdivcd']) === true) {
                throw new AlertBackException(__("우체국택배 서비스 신청 후 이용 가능합니다."));
            }

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 리스트 설정
            Request::get()->set('dateSearchFl','n');
            $getValue = Request::get()->toArray();

            $getValue['invoiceReserveFl'] = "n";
            $getValue['view'] = "orderGoods";
            $getData = $order->getOrderGodoPostListForAdmin($getValue, '365',true);

            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('deliveryNoneCount', $getData['deliveryNoneCount']);
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            $this->setData('statusListCombine', $order->statusListCombine);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/post_reserve.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
