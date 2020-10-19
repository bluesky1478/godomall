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

use Framework\Utility\StringUtils;

/**
 * Class MemberStatisticsService
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class MemberStatisticsService
{
    /** @var  MemberStatisticsDAO */
    private $statisticsDAO;
    /** @var  JoinStatisticsUtil */
    private $statisticsUtil;
    private $tableTotalHtml = [];
    private $tableHeaderHtml = [];

    /**
     * @inheritDoc
     */
    public function __construct(MemberStatisticsDAO $DAO = null, JoinStatisticsUtil $util = null)
    {
        $this->statisticsDAO = $DAO;
        if ($DAO === null) {
            $this->statisticsDAO = new MemberStatisticsDAO();
        }

        $this->statisticsUtil = $util;
        if ($util === null) {
            $this->statisticsUtil = new JoinStatisticsUtil();
        }
    }

    public function scheduleStatistics($date = null)
    {
        $genders = $this->statisticsDAO->listsGender($date);
        $ages = $this->statisticsDAO->listsAge($date);
        $areas = $this->statisticsDAO->listsArea($date);

        $statistics = $genders;
        foreach ($ages as $index => $age) {
            $statistics[$age['ageBand']] = intval($age['ageCount']);
        }
        foreach ($areas as $index => $area) {
            $statistics[$area['area']] = intval($area['areaCount']);
        }
        if ($date) {
            $statistics['statisticsDt'] = $date;
        }
        $sno = $this->statisticsDAO->insertStatistics($statistics);

        return $sno;
    }

    public function getAreaStatistics(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        /** @var \DateTime[] $arrSearchDt */
        $arrSearchDt = $params['searchDt'];
        $params['statisticsDt'] = [
            $arrSearchDt[0]->format('Y-m-d'),
            $arrSearchDt[1]->format('Y-m-d'),
        ];
        $lists = $this->statisticsDAO->listsByStatisticsArea($params);

        return $lists;
    }

    public function toJsonArea($lists)
    {
        $arrCategory = $arrTotal = $arrSeoul = $arrBusan = $arrDaegu = $arrIncheon = $arrGwangju = $arrDaejeon = $arrUlsan = $arrSejong = $arrGyeonggi = $arrGangwon = $arrChungbuk = $arrChungnam = $arrJeonbuk = $arrJeonnam = $arrGyeongbuk = $arrGyeongnam = $arrJeju = $arrOther = [];

        $areas = [];
        if (count($lists) == 1) {
            $arrCategory[] = '0';
            $keys = array_keys($lists[0]);
            foreach ($keys as $key) {
                $areas[$key][] = 0;
            }
        }
        foreach ($lists as $list) {
            foreach ($list as $index => $item) {
                if ($index == 'statisticsDt') {
                    $arrCategory[] = $item;
                }
                $areas[$index][] = gd_isset($item, 0);
            }
        }

        $result = [];
        $result['categories'] = $arrCategory;

        $last = end($lists);

        $this->tableHeaderHtml = $this->tableTotalHtml = [];
        $this->tableTotalHtml['head'][] = '<th class="bln point">'.__('전체 회원수').'</th>';
        $this->tableTotalHtml['body'][] = '<td class="bln point"><strong>' . number_format($last['total']) . '</strong></td>';
        $this->tableHeaderHtml[] = '<th class="">'.__('전체 회원수').'</th>';
        unset($last['statisticsDt'], $last['total']);

        foreach ($last as $index => $item) {
            if ($item > 0) {
                $areaName = StringUtils::getAreaName($index, true);
                $result['series'][] = [
                    'name' => $areaName,
                    'data' => $areas[$index],
                ];
                $this->tableTotalHtml['head'][] = '<th>' . $areaName . __('회원수').'</th>';
                $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($item) . '</strong></td>';
                $this->tableHeaderHtml[] = '<th>' . $areaName .__('회원수').'</th>';
            }
        }

        return $result;
    }

    public function getAgeStatistics(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        /** @var \DateTime[] $arrSearchDt */
        $arrSearchDt = $params['searchDt'];
        $params['statisticsDt'] = [
            $arrSearchDt[0]->format('Y-m-d'),
            $arrSearchDt[1]->format('Y-m-d'),
        ];
        $lists = $this->statisticsDAO->listsByStatisticsAge($params);

        return $lists;
    }

    public function toJsonAge($lists)
    {
        $arrCategory = $arrTotal = $arr10 = $arr20 = $arr30 = $arr40 = $arr50 = $arr60 = $arr70 = $arrOther = [];
        if (count($lists) == 1) {
            $arrCategory[] = '0';
            $arrTotal[] = $arr10[] = $arr20[] = $arr30[] = $arr40[] = $arr50[] = $arr60[] = $arr70[] = $arrOther[] = 0;
        }
        foreach ($lists as $index => $item) {
            $arrCategory[] = $item['statisticsDt'];
            $arrTotal[] = gd_isset($item['total'], 0);
            $arr10[] = gd_isset($item['age10'], 0);
            $arr20[] = gd_isset($item['age20'], 0);
            $arr30[] = gd_isset($item['age30'], 0);
            $arr40[] = gd_isset($item['age40'], 0);
            $arr50[] = gd_isset($item['age50'], 0);
            $arr60[] = gd_isset($item['age60'], 0);
            $arr70[] = gd_isset($item['age70'], 0);
            $arrOther[] = gd_isset($item['ageOther'], 0);
        }

        return [
            'categories' => $arrCategory,
            'series'     => [
                [
                    'name' =>__('전체 회원수'),
                    'data' => $arrTotal,
                ],
                [
                    'name' =>sprintf(__('%d대'), 10),
                    'data' => $arr10,
                ],
                [
                    'name' => sprintf(__('%d대'), 20),
                    'data' => $arr20,
                ],
                [
                    'name' => sprintf(__('%d대'), 30),
                    'data' => $arr30,
                ],
                [
                    'name' => sprintf(__('%d대'), 40),
                    'data' => $arr40,
                ],
                [
                    'name' => sprintf(__('%d대'), 50),
                    'data' => $arr50,
                ],
                [
                    'name' => sprintf(__('%d대'), 60),
                    'data' => $arr60,
                ],
                [
                    'name' => sprintf(__('%d대'), 70),
                    'data' => $arr70,
                ],
                [
                    'name' => __("성별 미확인 회원수"),
                    'data' => $arrOther,
                ],
            ],
        ];
    }

    public function getGenderStatistics(array $params)
    {
        $this->statisticsUtil->checkSearchDateTime($params);
        $this->statisticsUtil->initSearchDateTimeByPeriod($params);
        /** @var \DateTime[] $arrSearchDt */
        $arrSearchDt = $params['searchDt'];
        $params['statisticsDt'] = [
            $arrSearchDt[0]->format('Y-m-d'),
            $arrSearchDt[1]->format('Y-m-d'),
        ];
        $lists = $this->statisticsDAO->listsByStatisticsGender($params);

        return $lists;
    }

    public function toJsonGender($lists)
    {
        $arrCategory = $arrTotal = $arrMale = $arrFemale = $arrOther = [];
        if (count($lists) == 1) {
            $arrCategory[] = '0';
            $arrTotal[] = $arrMale[] = $arrFemale[] = $arrOther[] = 0;
        }
        foreach ($lists as $index => $item) {
            $arrCategory[] = gd_date_format('Y-m-d', $item['statisticsDt']);
            $arrTotal[] = gd_isset($item['total'], 0);
            $arrMale[] = gd_isset($item['male'], 0);
            $arrFemale[] = gd_isset($item['female'], 0);
            $arrOther[] = gd_isset($item['other'], 0);
        }

        return [
            'categories' => $arrCategory,
            'series'     => [
                [
                    'name' => __('전체 회원수'),
                    'data' => $arrTotal,
                ],
                [
                    'name' => __('남자 회원수'),
                    'data' => $arrMale,
                ],
                [
                    'name' => __('여자 회원수'),
                    'data' => $arrFemale,
                ],
                [
                    'name' => __("성별 미확인 회원수"),
                    'data' => $arrOther,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getTableTotalHtml()
    {
        return $this->tableTotalHtml;
    }

    /**
     * @return array
     */
    public function getTableHeaderHtml()
    {
        return $this->tableHeaderHtml;
    }
}
