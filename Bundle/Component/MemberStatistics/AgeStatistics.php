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
use Framework\Utility\DateTimeUtils;
use Logger;

/**
 * Class 회원 분석 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class AgeStatistics extends \Component\MemberStatistics\AbstractStatistics
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritDoc
     * @deprecated 2016-07-11
     */
    public function scheduleStatistics()
    {
        $list = $this->listsByMember('memNo,birthDt,entryDt');
        if (count($list) > 0) {
            $list = $this->statisticsData($list);
            $this->member->setTableFunctionName('tableMemberStatisticsDay');
            $this->member->setTableName(DB_MEMBER_STATISTICS_AGE);
            $this->member->save($list);
        } else {
            Logger::info(__METHOD__ . ' join member zero');
        }
    }

    /**
     * @inheritDoc
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
            /*
             * 생년월일을 기준으로 연령대를 구한 뒤 배열의 키로 이용한다.
             */
            if (gd_isset($item->getBirthDt(), '') === '') {
                $ageGroup = 'other';
            } else {
                $ageGroup = DateTimeUtils::getAgeGroupByDate($item->getBirthDt(true));
            }
            /*
             * 연령대별 카운트
             */
            if (key_exists($entryYm, $result)) {
                $result[$entryYm][$entryD]['total']++;
                $result[$entryYm][$entryD][$ageGroup]++;
            } else {
                $result[$entryYm][$entryD]['total'] = 1;
                $result[$entryYm][$entryD][$ageGroup] = 1;
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

        $this->member->setTableFunctionName('tableMemberStatisticsDay');
        $this->member->setTableName(DB_MEMBER_STATISTICS_AGE);
        $lists = $this->member->lists($params, $offset, $limit);

        $vo = new AgeVO();
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
        $arrCategory = $arrTotal = $arr10 = $arr20 = $arr30 = $arr40 = $arr50 = $arr60 = $arr70 = $arrOther = [];

        /**
         * 모바일과 회원의 가입 차트 데이터 생성
         * @var AgeVO $vo
         */
        $arrData = $vo->getArrData();
        foreach ($arrData as $index => $item) {
            $arrCategory[] = gd_date_format('Y-m-d', $index);
            $arrTotal[] = gd_isset($item['total'], 0);
            $arr10[] = gd_isset($item['10'], 0);
            $arr20[] = gd_isset($item['20'], 0);
            $arr30[] = gd_isset($item['30'], 0);
            $arr40[] = gd_isset($item['40'], 0);
            $arr50[] = gd_isset($item['50'], 0);
            $arr60[] = gd_isset($item['60'], 0);
            $arr70[] = gd_isset($item['70'], 0);
            $arrOther[] = gd_isset($item['other'], 0);
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

    /**
     * @inheritdoc
     */
    public function makeTable($vo)
    {
        $htmlList = [];

        $arrData = $vo->getArrData();
        if ($arrData > 0) {
            foreach ($arrData as $index => $item) {
                $total = gd_isset($item['total'], 0);
                $age10 = gd_isset($item['10'], 0);
                $age20 = gd_isset($item['20'], 0);
                $age30 = gd_isset($item['30'], 0);
                $age40 = gd_isset($item['40'], 0);
                $age50 = gd_isset($item['50'], 0);
                $age60 = gd_isset($item['60'], 0);
                $age70 = gd_isset($item['70'], 0);
                $other = gd_isset($item['other'], 0);

                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td class="font-date bln"><span class="font-date">' . gd_date_format('Y-m-d', $index) . '</span></td>';
                $htmlList[] = '<td class="font-num">' . $total . '</td>';
                $htmlList[] = '<td class="font-num">' . $age10 . '</td>';
                $htmlList[] = '<td class="font-num">' . $age20 . '</td>';
                $htmlList[] = '<td class="font-num">' . $age30 . '</td>';
                $htmlList[] = '<td class="font-num">' . $age40 . '</td>';
                $htmlList[] = '<td class="font-num">' . $age50 . '</td>';
                $htmlList[] = '<td class="font-num">' . $age60 . '</td>';
                $htmlList[] = '<td class="font-num">' . $age70 . '</td>';
                $htmlList[] = '<td class="font-num">' . $other . '</td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="6">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }
}
