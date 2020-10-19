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

use Component\AbstractComponent;
use Component\Member\Member;
use Component\MemberStatistics\Member as MemberStatistics;
use DateTime;
use Request;

/**
 * Class AbstractStatistics
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
abstract class AbstractStatistics extends \Component\AbstractComponent
{
    /**
     * @var \Bundle\Component\MemberStatistics\Member $member
     */
    protected $member;

    private $targetDateTime;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->member = new MemberStatistics();
    }

    /**
     * 회원 통계에 필요한 데이터 조회 함수
     *
     * @param string $column 조회할 컬럼
     *
     * @return \Bundle\Component\Member\MemberVO[]
     */
    public function listsByMember($column = '*')
    {
        $arrWhere = [];
        $date = new DateTime();
        $this->targetDateTime = $date->modify('-1 day')->format('Y-m-d');
        if (Request::getRemoteAddress() === '127.0.0.1') {
            unset($arrWhere);
            $this->targetDateTime = '2016';
            //$this->targetDateTime = $date->modify('-8 day')->format('Y-m-d');
        }

        /**
         * App::load 에서 싱글톤으로 생성 시 마지막 클래스명을 키 값으로 설정하기때문에 MemberStatistics\Member 와 겹치는 이슈로 인해 직접 생성으로 변경
         *
         * @author yjwee
         * @date   2016-03-21
         */
        $member = new Member();

        $resultSet = $member->lists(
            [
                'key'     => 'entryDt',
                'keyword' => $this->targetDateTime,
            ], null, null, $column
        );

        unset($arrWhere, $date, $member);

        return $resultSet;
    }

    /**
     * 배치 실행 함수
     *
     * @return mixed
     */
    abstract public function scheduleStatistics();

    /**
     * 데이터 분석 함수
     *
     * @param array $list
     *
     * @return mixed
     */
    abstract public function statisticsData(array $list);

    /**
     * 데이터 리스트
     *
     * @param array $params
     * @param int   $offset
     * @param int   $limit
     *
     * @return object
     */
    abstract public function getStatisticsList(array $params, $offset = 0, $limit = 20);

    /**
     * 차트에 사용될 JSON 데이터를 생성
     *
     * @param object $vo
     *
     * @return array
     * @deprecated 2016-07-11
     */
    abstract public function getChartJsonData($vo);

    /**
     * 테이블 html 생성 후 반환하는 함수
     *
     * @param object $vo 테이블 데이터
     *
     * @return mixed 테이블 html
     */
    abstract public function makeTable($vo);

    /**
     * 통계 기간 검색 버튼 html 을 반환하는 함수
     *
     * @param array $searchDt
     * @param       $searchPeriod
     *
     * @return string
     * @deprecated 2016-07-11
     * @see        \Bundle\Component\MemberStatistics\JoinStatisticsUtil::makePeriodTable
     */
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
