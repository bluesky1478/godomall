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

namespace Bundle\Controller\Admin\Statistics;

use Exception;
use Framework\Utility\DateTimeUtils;
use Request;

/**
 * [관리자 모드] 주문분석 > 주문통계 페이지
 *
 * @package Bundle\Controller\Admin\Statistics
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderStatusDayController extends \Controller\Admin\Controller
{
    /**
     * @var string
     */
    protected $groupType = 'day';

    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 메뉴 설정
            $this->callMenu('statistics', 'order', 'status' . ucwords($this->groupType));

            // 모듈 호출
            $order = \App::load('\\Component\\Order\\OrderStatics');

            //--- 검색 설정
            $searchData = Request::get()->toArray();
            $searchData['periodFl'] = !empty($searchData['periodFl']) ? $searchData['periodFl'] : ($this->groupType == 'month' ? 90 : 7);

            // 탭버튼 누르는 경우 월별과 기타 선택에 대한 처리
            if (!empty(Request::server()->get('QUERY_STRING')) && basename(Request::getFileUri()) != basename(Request::getParserReferer()->path)) {
                if ($this->groupType == 'month') {
                    if (!in_array($searchData['periodFl'], [30, 90, 180, 360])) {
                        $searchData['periodFl'] = 90;
                    }
                } else {
                    if (!in_array($searchData['periodFl'], [1, 7, 15, 30, 90])) {
                        $searchData['periodFl'] = 7;
                    }
                }
                $searchData['treatDate'][0] = date('Y-m-d', strtotime('-' . $searchData['periodFl'] . ' day', strtotime($searchData['treatDate'][1] . ' 23:59:59')));
            }

            $getData = $order->getStatisticsOrderStatus($searchData, $this->groupType);
            $this->setData('payment', gd_isset($getData['payment']));
            $this->setData('search', gd_isset($getData['search']));
            $this->setData('checked', gd_isset($getData['checked']));
            $this->setData('statuses', gd_isset($order->getOrderStatusAdmin()));

            // 페이지 모드
            $page['groupType'] = $this->groupType;
            $page['maxLimit'] = 90;
            $page['dayOfWeek'] = [
                __('일요일'),
                __('월요일'),
                __('화요일'),
                __('수요일'),
                __('목요일'),
                __('금요일'),
                __('토요일'),
            ];
            switch ($this->groupType) {
                case 'day':
                    $page['title'] = '날짜';
                    $page['forLimit'] = $getData['search']['interval'];
                    break;

                case 'hour':
                    $page['title'] = '시간대';
                    $page['forLimit'] = 23;
                    break;

                case 'week':
                    $page['title'] = '요일';
                    $page['forLimit'] = 6;
                    break;

                case 'month':
                    $page['title'] = '월';
                    $page['forLimit'] = DateTimeUtils::intervalDateTime($getData['search']['treatDate'][0], $getData['search']['treatDate'][1])->m;
                    $page['maxLimit'] = 360;
                    break;

                case 'member':
                    $page['title'] = '회원구분';
                    $page['forLimit'] = $getData['search']['interval'];
                    $page['firstRow'] = '회원';
                    $page['secondRow'] = '비회원';
                    break;

                case 'tax':
                    $page['title'] = '과세구분';
                    $page['forLimit'] = $getData['search']['interval'];
                    $page['firstRow'] = '과세';
                    $page['secondRow'] = '면세';
                    break;

            }
            $this->setData('page', $page);

            // 연령별 템플릿
            $this->getView()->setPageName('statistics/order_status.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
