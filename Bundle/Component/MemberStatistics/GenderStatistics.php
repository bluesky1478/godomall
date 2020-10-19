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

use Logger;

/**
 * Class 회원 분석 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class GenderStatistics extends \Component\MemberStatistics\AbstractStatistics
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->member->setTableFunctionName('tableMemberStatisticsDay');
        $this->member->setTableName(DB_MEMBER_STATISTICS_GENDER);
    }

    /**
     * @inheritDoc
     * @deprecated 2016-07-11
     */
    public function scheduleStatistics()
    {
        $list = $this->listsByMember('memNo,sexFl,entryDt');
        if (count($list) > 0) {
            $list = $this->statisticsData($list);
            $this->member->save($list);
        } else {
            Logger::info(__METHOD__ . ' join member zero');
        }
    }

    /**
     * statisticsData
     *
     * @param array $list
     *
     * @return array
     */
    public function statisticsData(array $list)
    {
        $result = [];
        /**
         * @var \Bundle\Component\Member\MemberVO $item
         */
        foreach ($list as $index => $item) {
            $entryDt = $item->getEntryDt(true);
            $entryYm = $entryDt->format('Ym');
            $entryD = $entryDt->format('j');
            if (key_exists($entryYm, $result)) {
                $result[$entryYm][$entryD]['total']++;
                if ($item->getSexFl() === 'w') {
                    $result[$entryYm][$entryD]['female']++;
                } else if ($item->getSexFl() === 'm') {
                    $result[$entryYm][$entryD]['male']++;
                } else {
                    $result[$entryYm][$entryD]['other']++;
                }
            } else {
                $result[$entryYm][$entryD]['total'] = 1;
                if ($item->getSexFl() === 'w') {
                    $result[$entryYm][$entryD]['female'] = 1;
                } else if ($item->getSexFl() === 'm') {
                    $result[$entryYm][$entryD]['male'] = 1;
                } else {
                    $result[$entryYm][$entryD]['other'] = 1;
                }
            }
        }

        /*
         * Daily Array Data to Json
         */
        foreach ($result as $joinYm => &$arrDay) {
            foreach ($arrDay as $day => &$arrData) {
                $arrData = json_encode($arrData);
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getStatisticsList(array $params, $offset = 0, $limit = 20)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($params);
        $util->initSearchDateTimeByPeriod($params);

        $lists = $this->member->lists($params, $offset, $limit);

        $vo = new GenderVO();
        foreach ($lists as $index => $item) {
            $vo->setEntryDt($item['joinYM']);
            $vo->setSearchDt($params['searchDt']);
            unset($item['joinYM'], $item['regDt'], $item['modDt']);
            $vo->setArrData($item);
        }

        return $vo;
    }

    /**
     * @inheritdoc
     */
    public function getChartJsonData($vo)
    {
        $arrCategory = $arrTotal = $arrMale = $arrFemale = $arrOther = [];

        /**
         * 모바일과 회원의 가입 차트 데이터 생성
         * @var GenderVO $vo
         */
        $arrData = $vo->getArrData();
        foreach ($arrData as $index => $item) {
            $arrCategory[] = gd_date_format('Y-m-d', $index);
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
     * @inheritdoc
     */
    public function makeTable($vo)
    {
        $htmlList = [];

        $vo->calculateRate();
        $arrData = $vo->getArrData();
        if ($arrData > 0) {
            foreach ($arrData as $index => $item) {
                $total = gd_isset($item['total'], 0);
                $male = gd_isset($item['male'], 0);
                $female = gd_isset($item['female'], 0);
                $other = gd_isset($item['other'], 0);
                $maleRate = number_format($item['maleRate']);
                $femaleRate = number_format($item['femaleRate']);
                $otherRate = number_format($item['otherRate']);

                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td class="font-date bln"><span class="font-date">' . gd_date_format('Y-m-d', $index) . '</span></td>';
                $htmlList[] = '<td class="font-num">' . $total . '</td>';
                $htmlList[] = '<td class="font-num">' . $male . '</td>';
                $htmlList[] = '<td class="font-num">' . $female . '</td>';
                $htmlList[] = '<td class="font-num">' . $other . '</td>';
                $htmlList[] = '<td class="font-num"><div class="progress">';
                $htmlList[] = '<div class="progress-bar progress-bar-info" style="width:' . $maleRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $maleRate . '%</strong></div>';
                $htmlList[] = '<div class="progress-bar progress-bar-success" style="width:' . $femaleRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $femaleRate . '%</strong></div>';
                $htmlList[] = '<div class="progress-bar progress-bar-error" style="width:' . $otherRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $otherRate . '%</strong></div>';
                $htmlList[] = '</div></td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="6">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }
}
