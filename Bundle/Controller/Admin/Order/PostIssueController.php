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
class PostIssueController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('order', 'epostParcel', 'issue');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                ]
            );

            $godopost= gd_policy('order.godopost');
            if(empty($godopost['compdivcd']) === true) {
                throw new AlertBackException(__("우체국택배 서비스 신청 후 이용 가능합니다."));
            }

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            gd_isset($data['searchPeriod'], 6);

            // --- 리스트 설정
            $getValue = Request::get()->toArray();

            $getValue['statusMode'] = "p,g";

            $getData = $order->getOrderGodoPostListForAdmin($getValue, $data['searchPeriod']);

            unset($getData['search']['combineSearch']['pu.purchaseNm']); //우체국 택배에서는 매입처 검색 제외
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('deliveryNoneCount', $getData['deliveryNoneCount']);
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            $statusSearchableRange = array_merge($order->getOrderStatusList('p'),$order->getOrderStatusList('g'));

            // --- 템플릿 정의
            $this->getView()->setDefine('layoutOrderSearchForm', Request::getDirectoryUri() . '/layout_order_search_form.php');// 검색폼
            $this->getView()->setDefine('layoutOrderList', Request::getDirectoryUri() . '/layout_order_goods_list.php');// 리스트폼

            // --- 템플릿 변수 설정
            $this->setData('statusStandardNm', $order->statusStandardNm);
            $this->setData('statusStandardCode', $order->statusStandardCode);
            $this->setData('statusListCombine', $order->statusListCombine);
            $this->setData('statusListExclude', $order->statusListExclude);
            $this->setData('type', $order->getOrderType());
            $this->setData('channel', $order->getOrderChannel());
            $this->setData('settle', $order->getSettleKind());
            $this->setData('formList', $order->getDownloadFormList());
            $this->setData('statusExcludeCd', $order->statusExcludeCd);
            $this->setData('statusSearchableRange', $statusSearchableRange);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/post_issue.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
