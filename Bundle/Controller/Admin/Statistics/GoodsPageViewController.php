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

namespace Bundle\Controller\Admin\Statistics;


use App;
use DateTime;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Request;

/**
 * Class GoodsPageViewController
 * @package Bundle\Controller\Admin\Statistics
 * @author  yjwee
 */
class GoodsPageViewController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        try {
            $this->callMenu('statistics', 'goods', 'pageView');

            $checked['searchCondition'][Request::get()->get('searchCondition', '')] = 'checked="checked"';
            $tabs = Request::get()->get('tabs', 'interest');
            $active['tabs'][$tabs] = 'active';

            /** @var \Bundle\Component\GoodsStatistics\PageViewService $statistics */
            $statistics = App::load('\\Component\\GoodsStatistics\\PageViewService');
            $requestGetParams = Request::get()->all();

            $searchDate = Request::get()->get('searchDt');

            $sDate = new DateTime();
            $eDate = new DateTime();
            if (!$searchDate[0]) {
                $searchDate[0] = $sDate->modify('-7 days')->format('Y-m-d');
            } else {
                $startDate = new DateTime($searchDate[0]);
                if ($sDate->format('Ymd') <= $startDate->format('Ymd')) {
                    $searchDate[0] = $sDate->modify('-1 days')->format('Y-m-d');
                } else {
                    $searchDate[0] = $startDate->format('Y-m-d');
                }
            }
            if (!$searchDate[1]) {
                $searchDate[1] = $eDate->modify('-1 days')->format('Y-m-d');
            } else {
                $endDate = new DateTime($searchDate[1]);
                if ($eDate->format('Ymd') <= $endDate->format('Ymd')) {
                    $searchDate[1] = $eDate->modify('-1 days')->format('Y-m-d');
                } else {
                    $searchDate[1] = $endDate->format('Y-m-d');
                }
            }

            $sDate = new DateTime($searchDate[0]);
            $eDate = new DateTime($searchDate[1]);
            $dateDiff = date_diff($sDate, $eDate);
            if ($dateDiff->days > 90) {
                $sDate = $eDate->modify('-7 day');
                $searchDate[0] = $sDate->format('Ymd');
                $searchPeriod = 7;
            }

            $requestGetParams['viewDate'][0] = $searchDate[0];
            $requestGetParams['viewDate'][1] = $searchDate[1];
            $requestGetParams['searchDt'][0] = $searchDate[0];
            $requestGetParams['searchDt'][1] = $searchDate[1];

            switch ($tabs) {
                case 'start':
                    $lists = $statistics->listsByStart($requestGetParams, null, null);
                    $lists = $statistics->getPageNameLists($lists);
                    $total = $statistics->getTotal('startCount', $requestGetParams);
                    break;
                case 'end':
                    $lists = $statistics->listsByEnd($requestGetParams, null, null);
                    $lists = $statistics->getPageNameLists($lists);
                    $total = $statistics->getTotal('endCount', $requestGetParams);
                    break;
                default:
                    $lists = $statistics->lists($requestGetParams, null, null);
                    $lists = $statistics->getPageNameLists($lists);
                    $total = $statistics->getTotal('pageViewCount', $requestGetParams);
                    break;
            }

            ArrayUtils::unsetDiff(
                $requestGetParams, [
                    'searchDt',
                    'keyword',
                ]
            );

            $this->setData('requestParams', $requestGetParams);
            $this->setData('checked', $checked);
            $this->setData('active', $active);
            $this->setData('lists', $lists);
            $this->setData('total', $total);
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
