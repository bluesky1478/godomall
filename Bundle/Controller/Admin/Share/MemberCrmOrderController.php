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

use App;
use Component\Validator\Validator;
use Component\Board\ArticleListAdmin;
use Exception;
use Framework\StaticProxy\Proxy\Session;
use Framework\Utility\ArrayUtils;
use Request;


/**
 * Class 관리자-CRM 요약내역
 *
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 * @see     \Core\Base\Interceptor\AdminLayout
 */
class MemberCrmOrderController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        try {
            $memberData = $this->getData('memberData');
            $memberData['memberFl'] = $memberData['memberFl'] == 'business' ? __('사업자회원') : __('개인회원');
            $this->setData('memberData', gd_set_default_value($memberData, '-'));

            // 관리자 정보
            $this->setData('managerData', Session::get('manager'));

            // --- 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderAdmin');

            // --- 주문 리스트 설정 config 불러오기
            $data = gd_policy('order.defaultSearch');
            gd_isset($data['searchPeriod'], 6);

            // CRM 회원 주문 검색어 설정
            $memberNo = Request::get()->get('memNo', Request::post()->get('memNo'));
            $getValue['memNo'] = $memberNo;

            // --- 리스트 설정
            $getValue = Request::get()->toArray();
            $getValue['view'] = 'orderGoods';
            $getData = $order->getOrderListForAdmin($getValue, $data['searchPeriod']);
            $this->setData('data', gd_isset($getData['data']));

            unset($getData['search']['combineSearch']['m.memId']);
            unset($getData['search']['combineSearch']['m.nickNm']);
            unset($getData['search']['combineSearch']['__disable3']);
            $this->setData('search', $getData['search']);
            $this->setData('checked', $getData['checked']);

            // 페이지 설정
            $page = \App::load('Component\\Page\\Page');
            $this->setData('total', count($getData['data']));
            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($pageNum));

            // --- 주문 일괄처리 셀렉트박스
            foreach ($order->getOrderStatusAdmin() as $key => $val) {
                if (in_array($key, $order->statusListExclude) === false && in_array(substr($key, 0, 1), $order->statusExcludeCd) === false && substr($key, 0, 1) != 'o') {
                    $selectBoxOrderStatus[$key] = $val;
                }
            }
            $this->setData('selectBoxOrderStatus', $selectBoxOrderStatus);

            // --- 템플릿 정의
            $this->getView()->setDefine('layoutOrderList', Request::getDirectoryUri() . '/../order/layout_order_goods_list.php');// 리스트폼

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
            $this->setData('statusSearchableRange', $order->getOrderStatusAdmin());

        } catch (Exception $e) {
            throw $e;
        }
    }
}
