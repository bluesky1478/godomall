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

use Exception;
use Globals;
use Request;

/**
 * 상품준비중 리스트
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderInvoiceController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'order', 'invoice');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                ]
            );

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            gd_isset($data['searchPeriod'], 6);

            // 요청 파라미터
            $getValue = Request::get()->all();

            // --- 리스트 설정
            $getData = $order->getOrderInvoiceList($getValue, $data['searchPeriod']);
            $this->setData('search', $getData['search']);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('checked', $getData['checked']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', $page);
            $this->setData('pageNum', Request::get()->get('pageNum', $getData['search']['pageNum']));

            // --- 템플릿 변수 설정
            $this->setData('currentStatusCode', $this->_currentStatusCode);
            $this->setData('statusStandardCode', $order->statusStandardCode);
            $this->setData('statusStandardNm', $order->statusStandardNm);
            $this->setData('statusListCombine', $order->statusListCombine);
            $this->setData('statusListExclude', $order->statusListExclude);
            $this->setData('statusExcludeCd', $order->statusExcludeCd);
            $this->setData('statusSearchableRange', $order->getOrderStatusList($this->_currentStatusCode));
            $this->setData('status', $order->getOrderStatusAdmin());
            $this->setData('type', $order->getOrderType());
            $this->setData('channel', $order->getOrderChannel());
            $this->setData('settle', $order->getSettleKind());
            $this->setData('formList', $order->getDownloadFormList());

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/order_invoice.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
