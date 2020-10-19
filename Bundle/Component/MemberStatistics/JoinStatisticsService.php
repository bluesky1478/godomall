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

namespace Bundle\Component\MemberStatistics;


use DateTime;
use Exception;
use Framework\Utility\DateTimeUtils;

/**
 * Class JoinStatisticsService
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class JoinStatisticsService
{
    /** @var  JoinStatisticsDAO */
    private $statisticsDAO;
    /** @var  JoinStatisticsUtil */
    private $statisticsUtil;
    private $totalCount = 0;
    private $pcTotalCount = 0;
    private $mobileTotalCount = 0;

    /**
     * @inheritDoc
     */
    public function __construct(JoinStatisticsDAO $DAO = null, JoinStatisticsUtil $util = null)
    {
        $this->statisticsDAO = $DAO;
        if ($DAO === null) {
            $this->statisticsDAO = new JoinStatisticsDAO();
        }

        $this->statisticsUtil = $util;
        if ($util === null) {
            $this->statisticsUtil = new JoinStatisticsUtil();
        }
    }

    public function getStatisticsMonth(array $params)
    {
        // 검색기간이 3달을 넘어가면 오류 처리
        if (DateTimeUtils::intervalDateTime($params['searchDt'][0], $params['searchDt'][1])->days > 360) {
            throw new Exception(__('검색기간은 최대 12개월입니다.'));
        }
        $this->statisticsUtil->initSearchDateTimeByPeriod($params, 90);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->listsMonth($params);

        $days = [];
        foreach ($lists as $index => $list) {
            $weekNo = $list['entryDate'];
            $days[$weekNo] = $list;

            $this->totalCount += $days[$weekNo]['totalCount'];
            $this->pcTotalCount += $days[$weekNo]['pcCount'];
            $this->mobileTotalCount += $days[$weekNo]['mobileCount'];
        }

        return $days;
    }


    public function getDashboardByMonth(array $params)
    {
        // 검색기간이 3달을 넘어가면 오류 처리
        if (DateTimeUtils::intervalDateTime($params['searchDt'][0], $params['searchDt'][1])->days > 360) {
            throw new Exception(__('검색기간은 최대 12개월입니다.'));
        }
        $this->statisticsUtil->initSearchDateTimeByPeriod($params, 90);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->countByEntryDateTimeMonth($params);

        $vo = new JoinMonthVO();
        $vo->setSearchDt($params['searchDt']);
        foreach ($lists as $index => $item) {
            if ($vo->getMax() <= $item['count']) {
                $vo->setMax($item['count']);
                $vo->setMaxDt(new DateTime($item['entryDt']));
            }
            if ($vo->getMin() >= $item['count'] || $vo->getMin() === 0) {
                $vo->setMin($item['count']);
                $vo->setMinDt(new DateTime($item['entryDt']));
            }
        }

        return $vo;
    }

    public function getStatisticsWeek(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->listsWeek($params);

        $weeks = [];
        foreach ($lists as $index => $list) {
            $weekNo = $list['weekNo'];
            $weeks[$weekNo] = $list;

            $this->totalCount += $weeks[$weekNo]['totalCount'];
            $this->pcTotalCount += $weeks[$weekNo]['pcCount'];
            $this->mobileTotalCount += $weeks[$weekNo]['mobileCount'];
        }

        return $weeks;
    }


    public function getDashboardByWeek(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->countByEntryDateTimeWeek($params);

        $vo = new JoinWeekVO();
        $vo->setSearchDt($params['searchDt']);
        foreach ($lists as $index => $item) {
            if ($vo->getMax() <= $item['count']) {
                $vo->setMax($item['count']);
                $vo->setMaxDt(new DateTime($item['entryDt']));
            }
            if ($vo->getMin() >= $item['count'] || $vo->getMin() === 0) {
                $vo->setMin($item['count']);
                $vo->setMinDt(new DateTime($item['entryDt']));
            }
        }

        return $vo;
    }

    public function getStatisticsDays(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->listsDay($params);

        $days = [];
        foreach ($lists as $index => $list) {
            $entryDate = $list['entryDate'];
            $days[$entryDate] = $list;

            $this->totalCount += $days[$entryDate]['totalCount'];
            $this->pcTotalCount += $days[$entryDate]['pcCount'];
            $this->mobileTotalCount += $days[$entryDate]['mobileCount'];
        }

        return $days;
    }


    public function getDashboardByDays(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->countByEntryDateTimeDay($params);

        $vo = new JoinDayVO();
        $vo->setSearchDt($params['searchDt']);
        foreach ($lists as $index => $item) {
            if ($vo->getMax() <= $item['count']) {
                $vo->setMax($item['count']);
                $vo->setMaxDt(new DateTime($item['entryDt']));
            }
            if ($vo->getMin() >= $item['count'] || $vo->getMin() === 0) {
                $vo->setMin($item['count']);
                $vo->setMinDt(new DateTime($item['entryDt']));
            }
        }

        return $vo;
    }


    public function getStatisticsHours(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->listsHour($params);

        $listsByHour = [];
        foreach ($lists as $index => $list) {
            $hour = intval($list['hour']);
            $listsByHour[$hour] = $list;
        }

        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            if (key_exists($i, $listsByHour)) {
                $hours[$i] = $listsByHour[$i];
                $this->totalCount += $hours[$i]['totalCount'];
                $this->pcTotalCount += $hours[$i]['pcCount'];
                $this->mobileTotalCount += $hours[$i]['mobileCount'];
            } else {
                $hours[$i] = [
                    'hour'        => $i,
                    'totalCount'  => 0,
                    'pcCount'     => 0,
                    'mobileCount' => 0,
                ];
            }
        }

        return $hours;
    }

    public function getDashboardHours(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        $this->statisticsUtil->setEntryDateTime($params);

        $lists = $this->statisticsDAO->countByEntryDateTimeHour($params);

        $vo = new JoinHourVO();
        $vo->setSearchDt($params['searchDt']);
        foreach ($lists as $index => $item) {
            if ($vo->getMax() <= $item['count']) {
                $vo->setMax($item['count']);
                $vo->setMaxDt(new DateTime($item['entryDt']));
            }
            if ($vo->getMin() >= $item['count'] || $vo->getMin() === 0) {
                $vo->setMin($item['count']);
                $vo->setMinDt(new DateTime($item['entryDt']));
            }
        }

        return $vo;
    }

    /**
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return mixed
     */
    public function getPcTotalCount()
    {
        return $this->pcTotalCount;
    }

    /**
     * @return mixed
     */
    public function getMobileTotalCount()
    {
        return $this->mobileTotalCount;
    }
}
