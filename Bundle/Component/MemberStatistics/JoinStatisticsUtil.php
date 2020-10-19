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
use Message;

/**
 * Class JoinStatisticsUtil
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class JoinStatisticsUtil
{
    public function toJsonByWeek($lists)
    {
        $arrCategory = $arrTotal = $arrPc = $arrMobile = [];
        if (count($lists) == 1) {
            $arrCategory[] = '0';
            $arrTotal[] = 0;
            $arrPc[] = 0;
            $arrMobile[] = 0;
        }
        foreach ($lists as $index => $item) {
            $weekName = DateTimeUtils::getWeekNameByNumber($index);
            $arrCategory[] = __($weekName);
            $arrTotal[] = gd_isset($item['totalCount'], 0);
            $arrPc[] = gd_isset($item['pcCount'], 0);
            $arrMobile[] = gd_isset($item['mobileCount'], 0);
        }

        return [
            'categories' => $arrCategory,
            'series'     => [
                [
                    'name' => __('전체 신규회원'),
                    'data' => $arrTotal,
                ],
                [
                    'name' => __('PC쇼핑몰 신규회원'),
                    'data' => $arrPc,
                ],
                [
                    'name' => __('모바일쇼핑몰 신규회원'),
                    'data' => $arrMobile,
                ],
            ],
        ];
    }

    public function toTableByWeek($lists)
    {
        $htmlList = [];

        if ($lists > 0) {
            foreach ($lists as $index => $item) {
                $weekName = DateTimeUtils::getWeekNameByNumber($index);
                $total = gd_isset($item['totalCount'], 0);
                $pc = gd_isset($item['pcCount'], 0);
                $mobile = gd_isset($item['mobileCount'], 0);
                $rates = $this->calculateRate($total, $pc, $mobile);

                $pcRate = round($rates[0], 0, PHP_ROUND_HALF_UP);
                $mobileRate = round($rates[1], 0, PHP_ROUND_HALF_DOWN);

                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td>' . __($weekName) . '</td>';
                $htmlList[] = '<td class="font-num">' . $total . '</td>';
                $htmlList[] = '<td class="font-num">' . $pc . '</td>';
                $htmlList[] = '<td class="font-num">' . $mobile . '</td>';
                $htmlList[] = '<td class="font-num"><div class="progress">';
                $htmlList[] = '<div class="progress-bar progress-bar-info" style="width:' . $pcRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $pcRate . '%</strong></div>';
                $htmlList[] = '<div class="progress-bar progress-bar-success" style="width:' . $mobileRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $mobileRate . '%</strong></div>';
                $htmlList[] = '</div></td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="6">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }

    public function toJsonByMonth($lists)
    {
        $arrCategory = $arrTotal = $arrPc = $arrMobile = [];
        if (count($lists) == 1) {
            $arrCategory[] = '0';
            $arrTotal[] = 0;
            $arrPc[] = 0;
            $arrMobile[] = 0;
        }
        foreach ($lists as $index => $item) {
            $arrCategory[] = gd_date_format('Y.m', $index . '01');
            $arrTotal[] = gd_isset($item['totalCount'], 0);
            $arrPc[] = gd_isset($item['pcCount'], 0);
            $arrMobile[] = gd_isset($item['mobileCount'], 0);
        }

        return [
            'categories' => $arrCategory,
            'series'     => [
                [
                    'name' => __('전체 신규회원'),
                    'data' => $arrTotal,
                ],
                [
                    'name' => __('PC쇼핑몰 신규회원'),
                    'data' => $arrPc,
                ],
                [
                    'name' => __('모바일쇼핑몰 신규회원'),
                    'data' => $arrMobile,
                ],
            ],
        ];
    }

    public function toTableByMonth($lists)
    {
        $htmlList = [];

        if ($lists > 0) {
            foreach ($lists as $index => $item) {
                $total = gd_isset($item['totalCount'], 0);
                $pc = gd_isset($item['pcCount'], 0);
                $mobile = gd_isset($item['mobileCount'], 0);
                $rates = $this->calculateRate($total, $pc, $mobile);

                $pcRate = round($rates[0], 0, PHP_ROUND_HALF_UP);
                $mobileRate = round($rates[1], 0, PHP_ROUND_HALF_DOWN);

                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td class="font-date bln"><span class="font-date">' . DateTimeUtils::dateFormat('Y년 m월', $index . '01') . '</span></td>';
                $htmlList[] = '<td class="font-num">' . $total . '</td>';
                $htmlList[] = '<td class="font-num">' . $pc . '</td>';
                $htmlList[] = '<td class="font-num">' . $mobile . '</td>';
                $htmlList[] = '<td class="font-num"><div class="progress">';
                $htmlList[] = '<div class="progress-bar progress-bar-info" style="width:' . $pcRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $pcRate . '%</strong></div>';
                $htmlList[] = '<div class="progress-bar progress-bar-success" style="width:' . $mobileRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $mobileRate . '%</strong></div>';
                $htmlList[] = '</div></td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="6">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }

    public function toJsonByDays($lists)
    {
        $arrCategory = $arrTotal = $arrPc = $arrMobile = [];
        if (count($lists) == 1) {
            $arrCategory[] = '0';
            $arrTotal[] = 0;
            $arrPc[] = 0;
            $arrMobile[] = 0;
        }
        foreach ($lists as $index => $item) {
            $arrCategory[] = gd_date_format('Y-m-d', $index);
            $arrTotal[] = gd_isset($item['totalCount'], 0);
            $arrPc[] = gd_isset($item['pcCount'], 0);
            $arrMobile[] = gd_isset($item['mobileCount'], 0);
        }

        return [
            'categories' => $arrCategory,
            'series'     => [
                [
                    'name' => __('전체 신규회원'),
                    'data' => $arrTotal,
                ],
                [
                    'name' => __('PC쇼핑몰 신규회원'),
                    'data' => $arrPc,
                ],
                [
                    'name' => __('모바일쇼핑몰 신규회원'),
                    'data' => $arrMobile,
                ],
            ],
        ];
    }

    public function toTableByDays($lists)
    {
        $htmlList = [];

        if ($lists > 0) {
            foreach ($lists as $index => $item) {
                $total = gd_isset($item['totalCount'], 0);
                $pc = gd_isset($item['pcCount'], 0);
                $mobile = gd_isset($item['mobileCount'], 0);
                $rates = $this->calculateRate($total, $pc, $mobile);

                $pcRate = round($rates[0], 0, PHP_ROUND_HALF_UP);
                $mobileRate = round($rates[1], 0, PHP_ROUND_HALF_DOWN);

                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td class="font-date bln"><span class="font-date">' . gd_date_format('Y-m-d', $index) . '</span></td>';
                $htmlList[] = '<td class="font-num">' . $total . '</td>';
                $htmlList[] = '<td class="font-num">' . $pc . '</td>';
                $htmlList[] = '<td class="font-num">' . $mobile . '</td>';
                $htmlList[] = '<td class="font-num"><div class="progress">';
                $htmlList[] = '<div class="progress-bar progress-bar-info" style="width:' . $pcRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $pcRate . '%</strong></div>';
                $htmlList[] = '<div class="progress-bar progress-bar-success" style="width:' . $mobileRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $mobileRate . '%</div>';
                $htmlList[] = '</div></td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="6">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }

    public function calculateRate($total, $pc, $mobile)
    {
        if ($total < 1) {
            $total = 1;
        }
        $pcRate = ($pc / $total) * 100;
        $mobileRate = ($mobile / $total) * 100;

        return [
            $pcRate,
            $mobileRate,
        ];
    }

    public function toJsonByHours($lists)
    {
        $arrCategory = $arrTotal = $arrPc = $arrMobile = [];
        for ($i = 0; $i < 24; $i++) {
            $arrCategory[] = $i . ':00';
            $arrTotal[] = gd_isset($lists[$i]['totalCount'], 0);
            $arrPc[] = gd_isset($lists[$i]['pcCount'], 0);
            $arrMobile[] = gd_isset($lists[$i]['mobileCount'], 0);
        }

        return [
            'categories' => $arrCategory,
            'series'     => [
                [
                    'name' => __('전체 신규회원'),
                    'data' => $arrTotal,
                ],
                [
                    'name' => __('PC쇼핑몰 신규회원'),
                    'data' => $arrPc,
                ],
                [
                    'name' => __('모바일쇼핑몰 신규회원'),
                    'data' => $arrMobile,
                ],
            ],
        ];
    }

    public function toTableByHours($lists)
    {
        $htmlList = [];

        if ($lists > 0) {
            foreach ($lists as $index => $item) {
                $total = gd_isset($item['totalCount'], 0);
                $pc = gd_isset($item['pcCount'], 0);
                $mobile = gd_isset($item['mobileCount'], 0);
                $rates = $this->calculateRate($total, $pc, $mobile);

                $pcRate = round($rates[0], 0, PHP_ROUND_HALF_UP);
                $mobileRate = round($rates[1], 0, PHP_ROUND_HALF_DOWN);

                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td class="font-date bln"><span class="font-date">' . $item['hour'] . ':00</span></td>';
                $htmlList[] = '<td class="font-num">' . $total . '</td>';
                $htmlList[] = '<td class="font-num">' . $pc . '</td>';
                $htmlList[] = '<td class="font-num">' . $mobile . '</td>';
                $htmlList[] = '<td class="font-num"><div class="progress">';
                $htmlList[] = '<div class="progress-bar progress-bar-info" style = "width:' . $pcRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $pcRate . '%</strong></div>';
                $htmlList[] = '<div class="progress-bar progress-bar-success" style = "width:' . $mobileRate . '%">';
                $htmlList[] = '<strong class="text-black">' . $mobileRate . '%</strong></div>';
                $htmlList[] = '</div></td>';
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan = "6">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
    }

    public function setEntryDateTime(array &$params)
    {
        /** @var DateTime[] $arrSearchDt */
        $arrSearchDt = $params['searchDt'];

        $params['entryDt'] = [
            $arrSearchDt[0]->format('Y-m-d') . ' 00:00:00',
            $arrSearchDt[1]->format('Y-m-d') . ' 23:59:59',
        ];
    }

    public function initSearchDateTimeByPeriod(array &$params, $days = 7)
    {
        if (gd_isset($params['searchDt'], '') === '') {
            $period = '-' . gd_isset($days, 7) . ' days';
            $params['searchDt'] = DateTimeUtils::getBetweenDateTime($period);
        } else {
            $params['searchDt'][0] = new DateTime($params['searchDt'][0], DateTimeUtils::getTimeZone());
            $params['searchDt'][1] = new DateTime($params['searchDt'][1], DateTimeUtils::getTimeZone());
        }
    }

    public function checkSearchDateTime(array $params)
    {
        $intervalDateTime = DateTimeUtils::intervalDateTime($params['searchDt'][0], $params['searchDt'][1]);
        if ($intervalDateTime->days > 90) {
            throw new Exception('검색기간은 최대 3개월입니다.');
        }
    }

    public function makePeriodTable(array $searchDt, $searchPeriod)
    {
        $period = [
            1  => __('전일'),
            7  => sprintf(__('%d일'), 7),
            15 => sprintf(__('%d일'), 15),
            30 => sprintf(__('%d개월'), 1),
            90 => sprintf(__('%d개월'), 3),
        ];

        $html = [];
        $html[] = '<table class="table table-cols">';
        $html[] = '<colgroup><col class="width-md"/><col/></colgroup>';
        $html[] = '<tbody><tr><th>'.__('기간검색').'</th><td><div class="form-inline">';
        $html[] = '<div class="input-group js-datepicker">';
        $html[] = '<input type="text" class="form-control width-xs" name="searchDt[]" value="' . $searchDt[0] . '"/>';
        $html[] = '<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>';
        $html[] = '</div>~';
        $html[] = '<div class="input-group js-datepicker">';
        $html[] = '<input type="text" class="form-control width-xs" name="searchDt[]" value="' . $searchDt[1] . '"/>';
        $html[] = '<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>';
        $html[] = '</div>';
        $html[] = '<div class="btn-group js-dateperiod-statistics" data-toggle="buttons" data-target-name="searchDt[]">';
        foreach ($period as $index => $item) {
            $checked = ($searchPeriod == $index) ? 'checked="checked"' : '';
            $active = ($searchPeriod == $index) ? 'active' : '';
            $html[] = '<label class="btn btn-white btn-sm ' . $active . '">';
            $html[] = '<input type="radio" name="searchPeriod" value="' . $index . '" ' . $checked . ' />';
            $html[] = $item;
            $html[] = '</label>';
        }
        $html[] = '</div>';
        $html[] = '</div></td></tr></tbody>';
        $html[] = '</table>';
        $html[] = '<div class="table-btn"><button type="submit" class="btn btn-lg btn-black">'.__('검색').'</button></div>';

        return join('', $html);
    }
}
