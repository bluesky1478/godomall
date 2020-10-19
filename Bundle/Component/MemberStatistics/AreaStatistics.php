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
use Framework\Utility\StringUtils;
use Logger;

/**
 * Class 회원 분석 클래스
 * @package Bundle\Component\MemberStatistics
 * @author  yjwee
 */
class AreaStatistics extends \Component\MemberStatistics\AbstractStatistics
{
    private $tableTotalHtml = [];
    private $tableHeaderHtml = [];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     * @deprecated 2016-07-11
     */
    public function scheduleStatistics()
    {
        $list = $this->listsByMember('memNo,address,entryDt');
        if (count($list) > 0) {
            $list = $this->statisticsData($list);
            $this->member->setTableFunctionName('tableMemberStatisticsDay');
            $this->member->setTableName(DB_MEMBER_STATISTICS_AREA);
            $this->member->save($list);
        } else {
            Logger::warning(__METHOD__ . ' join member zero');
        }
    }

    /**
     * @inheritdoc
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
            $area = StringUtils::getAreaName($item->getAddress());
            if (key_exists($entryYm, $result)) {
                $result[$entryYm][$entryD]['total']++;
                $result[$entryYm][$entryD][$area]++;
            } else {
                $result[$entryYm][$entryD]['total'] = 1;
                $result[$entryYm][$entryD][$area] = 1;
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
     * @inheritDoc
     */
    public function getStatisticsList(array $params, $offset = 0, $limit = 20)
    {
        $util = new JoinStatisticsUtil();
        $util->checkSearchDateTime($params);
        $util->initSearchDateTimeByPeriod($params);

        $this->member->setTableFunctionName('tableMemberStatisticsDay');
        $this->member->setTableName(DB_MEMBER_STATISTICS_AREA);
        $lists = $this->member->lists($params, $offset, $limit);

        $vo = new AreaVO();
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
        $arrCategory = $arrTotal = $arrSeoul = $arrBusan = $arrDaegu = $arrIncheon = $arrGwangju = $arrDaejeon = $arrUlsan = $arrSejong = $arrGyeonggi = $arrGangwon = $arrChungbuk = $arrChungnam = $arrJeonbuk = $arrJeonnam = $arrGyeongbuk = $arrGyeongnam = $arrJeju = $arrOther = [];

        /**
         * 모바일과 회원의 가입 차트 데이터 생성
         * @var AreaVO $vo
         */
        $arrData = $vo->getArrData();
        foreach ($arrData as $index => $item) {
            $arrCategory[] = DateTimeUtils::dateFormat('Y-m-d', $index);
            $arrTotal[] = StringUtils::strIsSet($item['total'], 0);
            $arrSeoul[] = StringUtils::strIsSet($item['seoul'], 0);
            $arrBusan[] = StringUtils::strIsSet($item['busan'], 0);
            $arrDaegu[] = StringUtils::strIsSet($item['daegu'], 0);
            $arrIncheon[] = StringUtils::strIsSet($item['incheon'], 0);
            $arrGwangju[] = StringUtils::strIsSet($item['gwangju'], 0);
            $arrDaejeon[] = StringUtils::strIsSet($item['daejeon'], 0);
            $arrUlsan[] = StringUtils::strIsSet($item['ulsan'], 0);
            $arrSejong[] = StringUtils::strIsSet($item['sejong'], 0);
            $arrGyeonggi[] = StringUtils::strIsSet($item['gyeonggi'], 0);
            $arrGangwon[] = StringUtils::strIsSet($item['gangwon'], 0);
            $arrChungbuk[] = StringUtils::strIsSet($item['chungbuk'], 0);
            $arrChungnam[] = StringUtils::strIsSet($item['chungnam'], 0);
            $arrJeonbuk[] = StringUtils::strIsSet($item['jeonbuk'], 0);
            $arrJeonnam[] = StringUtils::strIsSet($item['jeonnam'], 0);
            $arrGyeongbuk[] = StringUtils::strIsSet($item['gyeongbuk'], 0);
            $arrGyeongnam[] = StringUtils::strIsSet($item['gyeongnam'], 0);
            $arrJeju[] = StringUtils::strIsSet($item['jeju'], 0);
            $arrOther[] = StringUtils::strIsSet($item['other'], 0);
        }

        $result = [];
        $result['categories'] = $arrCategory;

        $this->tableHeaderHtml = $this->tableTotalHtml = [];
        $this->tableTotalHtml['head'][] = '<th class="bln point">'.__('전체 회원수').'</th>';
        $this->tableTotalHtml['body'][] = '<td class="bln point"><strong>' . number_format($vo->getTotal()) . '</strong></td>';
        $this->tableHeaderHtml[] = '<th class="">'.__('전체 회원수').'</th>';

        if ($vo->getSeoulTotal() > 0) {
            $result['series'][] = [
                'name' => '서울',
                'data' => $arrSeoul,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('서울')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getSeoulTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('서울')).'</th>';
        }
        if ($vo->getBusanTotal() > 0) {
            $result['series'][] = [
                'name' => '부산',
                'data' => $arrBusan,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('부산')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getBusanTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'),  __('부산')).'</th>';
        }
        if ($vo->getDaeguTotal() > 0) {
            $result['series'][] = [
                'name' => '대구',
                'data' => $arrDaegu,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('대구')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getDaeguTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('대구')).'</th>';
        }
        if ($vo->getIncheonTotal() > 0) {
            $result['series'][] = [
                'name' => '인천',
                'data' => $arrIncheon,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('인천')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getIncheonTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('인천')).'</th>';
        }
        if ($vo->getGwangjuTotal() > 0) {
            $result['series'][] = [
                'name' => '광주',
                'data' => $arrGwangju,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('광주')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getGwangjuTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('광주')).'</th>';
        }
        if ($vo->getDaejeonTotal() > 0) {
            $result['series'][] = [
                'name' => '대전',
                'data' => $arrDaejeon,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('대전')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getDaejeonTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('대전')).'</th>';
        }
        if ($vo->getUlsanTotal() > 0) {
            $result['series'][] = [
                'name' => '울산',
                'data' => $arrUlsan,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('울산')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getUlsanTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('울산')).'</th>';
        }
        if ($vo->getSejongTotal() > 0) {
            $result['series'][] = [
                'name' => '세종',
                'data' => $arrSejong,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('세종')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getSejongTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('세종')).'</th>';
        }
        if ($vo->getGyeonggiTotal() > 0) {
            $result['series'][] = [
                'name' => '경기',
                'data' => $arrGyeonggi,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('경기')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getGyeonggiTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('경기')).'</th>';
        }
        if ($vo->getGangwonTotal() > 0) {
            $result['series'][] = [
                'name' => '강원',
                'data' => $arrGangwon,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('강원')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getGangwonTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('강원')).'</th>';
        }
        if ($vo->getChungbukTotal() > 0) {
            $result['series'][] = [
                'name' => '충북',
                'data' => $arrChungbuk,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('충북')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getChungbukTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('충북')).'</th>';
        }
        if ($vo->getChungnamTotal() > 0) {
            $result['series'][] = [
                'name' => '충남',
                'data' => $arrChungnam,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('충남')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getChungnamTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('충남')).'</th>';
        }
        if ($vo->getJeonbukTotal() > 0) {
            $result['series'][] = [
                'name' => '전북',
                'data' => $arrJeonbuk,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('전북')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getJeonbukTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('전북')).'</th>';
        }
        if ($vo->getJeonnamTotal() > 0) {
            $result['series'][] = [
                'name' => '전남',
                'data' => $arrJeonnam,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('전남')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getJeonnamTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('전남')).'</th>';
        }
        if ($vo->getGyeongbukTotal() > 0) {
            $result['series'][] = [
                'name' => '경북',
                'data' => $arrGyeongbuk,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('경북')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getGyeongbukTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('경북')).'</th>';
        }
        if ($vo->getGyeongnamTotal() > 0) {
            $result['series'][] = [
                'name' => '경남',
                'data' => $arrGyeongnam,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('경남')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getGyeongnamTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('경남')).'</th>';
        }
        if ($vo->getJejuTotal() > 0) {
            $result['series'][] = [
                'name' => '제주',
                'data' => $arrJeju,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.sprintf(__('%s회원수'), __('제주')).'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getJejuTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.sprintf(__('%s회원수'), __('경남')).'</th>';
        }
        if ($vo->getOtherTotal() > 0) {
            $result['series'][] = [
                'name' => "지역 미확인 회원수",
                'data' => $arrOther,
            ];
            $this->tableTotalHtml['head'][] = '<th>'.__('지역 미확인 회원수').'</th>';
            $this->tableTotalHtml['body'][] = '<td class="font-num"><strong>' . number_format($vo->getOtherTotal()) . '</strong></td>';
            $this->tableHeaderHtml[] = '<th>'.__('지역 미확인 회원수').'</th>';
        }

        return $result;
    }

    /**
     * @inheritdoc
     *
     * @param AreaVO $vo
     */
    public function makeTable($vo)
    {
        $htmlList = [];

        $arrData = $vo->getArrData();
        if ($arrData > 0) {
            foreach ($arrData as $index => $item) {
                $htmlList[] = '<tr class="nowrap text-center">';
                $htmlList[] = '<td class="font-date bln"><span class="font-date">' . DateTimeUtils::dateFormat('Y-m-d', $index) . '</span></td>';
                $htmlList[] = '<td class="font-num">' . gd_isset($item['total'], 0) . '</td>';
                if ($vo->getSeoulTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['seoul'], 0) . '</td>';
                }
                if ($vo->getBusanTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['busan'], 0) . '</td>';
                }
                if ($vo->getDaeguTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['daegu'], 0) . '</td>';
                }
                if ($vo->getIncheonTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['incheon'], 0) . '</td>';
                }
                if ($vo->getGwangjuTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['gwangju'], 0) . '</td>';
                }
                if ($vo->getDaejeonTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['daejeon'], 0) . '</td>';
                }
                if ($vo->getUlsanTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['ulsan'], 0) . '</td>';
                }
                if ($vo->getSejongTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['sejong'], 0) . '</td>';
                }
                if ($vo->getGyeonggiTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['gyeonggi'], 0) . '</td>';
                }
                if ($vo->getGangwonTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['gangwon'], 0) . '</td>';
                }
                if ($vo->getChungbukTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['chungbuk'], 0) . '</td>';
                }
                if ($vo->getChungnamTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['chungnam'], 0) . '</td>';
                }
                if ($vo->getJeonbukTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['jeonbuk'], 0) . '</td>';
                }
                if ($vo->getJeonnamTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['jeonnam'], 0) . '</td>';
                }
                if ($vo->getGyeongbukTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['gyeongbuk'], 0) . '</td>';
                }
                if ($vo->getGyeongnamTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['gyeongnam'], 0) . '</td>';
                }
                if ($vo->getJejuTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['jeju'], 0) . '</td>';
                }
                if ($vo->getOtherTotal() > 0) {
                    $htmlList[] = '<td class="font-num">' . gd_isset($item['other'], 0) . '</td>';
                }
                $htmlList[] = '</tr>';
            }
        } else {
            return '<tr><td class="no-data" colspan="6">'.__('통계 정보가 없습니다.').'</td></tr>';
        }

        return join('', $htmlList);
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
