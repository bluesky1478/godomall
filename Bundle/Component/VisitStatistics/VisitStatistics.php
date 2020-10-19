<?php

/**
 * 방문분석(Visit Statistics) Class
 *
 * @author    su
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
namespace Bundle\Component\VisitStatistics;

use Component\Mall\Mall;
use DateTime;
use Framework\StaticProxy\Proxy\Session;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use Request;

class VisitStatistics
{


    const ECT_INVALID_ARG = 'VisitStatistics.ECT_INVALID_ARG';
    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';  // __('%s인자가 잘못되었습니다.')

    protected $db;
    protected $visitPolicy;         // 방문통계 기본 설정 ( 방문횟수 유지 시간, 신규방문자 유지 시간, 방문통계 제외 url, 적용OS, 적용Browser )
    protected $visitStatisticsTime; // 방문통계 산출결과 처리날짜의 time
    public $noticeCommentFl; // 2월23일부터 디바이스 구분 통계 처리 문구 활성 여부

    /**
     * VisitStatistics constructor.
     *
     * @param null $date YYYY-mm-dd
     */
    public function __construct($date = null)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        // @todo mysql json type - bug - json object 순서가 mysql 저장시 자동으로 변경됨.
//        $this->_visitPolicy = gd_policy('visit.config');
        if (!$this->_visitPolicy['visitNumberTime']) {
            // 방문횟수 카운트 유지 시간(기본 1시간)
            $this->_visitPolicy['visitNumberTime'] = 3600;
        }
        if (!$this->_visitPolicy['visitNewCountTime']) {
            // 신규방문자 카운트 유지 시간(기본 30일)
            $this->_visitPolicy['visitNewCountTime'] = (30 * 24 * 3600);
        }
        if (!$this->_visitPolicy['exceptPage']) {
            $this->_visitPolicy['exceptPage'] = ['_ps.php', '_godoConn', 'popup'];
        }
        if (!$this->_visitPolicy['inflowAgent']) {
            $this->_visitPolicy['inflowAgent'] = [
                'naver.com'    => '네이버',
                'daum.net'     => '다음',
                'kakao.com'    => '카카오',
                'google.co.kr' => '구글',
                'google.com'   => '구글',
                'nate.com'     => '네이트',
                'bing.com'     => '빙',
            ];
        }
        if (!$this->_visitPolicy['osAgent']) {
            $this->_visitPolicy['osAgent'] = [
                'Windows NT 10'  => 'Win10',
                'Windows NT 6.3' => 'Win8.1',
                'Windows NT 6.2' => 'Win8',
                'Windows NT 6.1' => 'Win7',
                'Windows NT 6.0' => 'WinVista',
                'Windows NT 5.1' => 'WinXP',
                'iPhone'         => 'iPhone',
                'iPad'           => 'iPhone',
                'Android'        => 'Android',
                'Mac OS'         => 'Mac',
                'MacOS'          => 'Mac',
            ];
        }
        if (!$this->_visitPolicy['browserAgent']) {
            $this->_visitPolicy['browserAgent'] = [
                'MSIE 9'  => 'IE9',
                'MSIE 10' => 'IE10',
                'rv:11'   => 'IE11',
                'Edge'    => 'Edge',
                'OPR'     => 'Opera',
                'Chrome'  => 'Chrome',
                'CriOS'   => 'Chrome',
                'Safari'  => 'Safari',
            ];
        }
        if ($date) {
            $dateArr = explode('-', $date);
            $this->visitStatisticsTime = mktime(0, 0, 0, $dateArr[1], $dateArr[2]-1, $dateArr[0]);
        } else {
            $this->visitStatisticsTime = mktime(0, 0, 0, date('n'), date('j') - 1, date('Y'));
        }

        $globals = \App::getInstance('globals');
        $gLicense = $globals->get('gLicense');
        if ($gLicense['sdate'] < 20170223) {
            $noticeFl = true;
        }
        $this->noticeCommentFl = $noticeFl;
    }

    /**
     * getVisitColumn
     * 방문자 통계 필드
     *
     * @return array
     */
    public function getVisitColumn()
    {
        foreach ($this->_visitPolicy['inflowAgent'] as $val) {
            $inflowArr[$val] = 0;
        }
        $inflowArr['기타'] = 0;

        foreach ($this->_visitPolicy['osAgent'] as $val) {
            $osArr[$val] = 0;
        }
        $osArr['기타'] = 0;

        foreach ($this->_visitPolicy['browserAgent'] as $val) {
            $browserArr[$val] = 0;
        }
        $browserArr['기타'] = 0;
        $visitColumn = [
            'visitCount' => 0,
            'visitNumber' => 0,
            'pv' => 0,
            'visitNewCount' => 0,
            'visitNewPv' => 0,
            'visitReCount' => 0,
            'visitRePv' => 0,
            'visitInflow' => $inflowArr,
            'visitOs' => $osArr,
            'visitBrowser' => $browserArr,
        ];

        return $visitColumn;
    }

    /**
     * getVisitStatisticsPage
     * 방문통계 정보 페이지 출력
     *
     * @param      $searchDate
     * @param      $searchDevice
     * @param null $mallSno
     * @param      $groupFl
     * @param null $searchData
     * @param null $mode
     *
     * @return mixed
     * @throws \Exception
     */
    public function getVisitStatisticsPage($searchDate, $searchDevice, $mallSno = null, $groupFl = false, $searchData = null, $mode = null)
    {
        // --- 페이지 기본설정
        if ($mode == 'layer') {
            // --- 페이지 기본설정
            if (gd_isset($searchData['pagelink'])) {
                $searchData['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($searchData['pagelink'])));
            } else {
                $searchData['page'] = 1;
            }
            gd_isset($searchData['pageNum'], "20");
        } else {
            // --- 페이지 기본설정
            gd_isset($searchData['page'], 1);
            gd_isset($searchData['pageNum'], 20);
        }

        $searchData['page'] = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $searchData['page']->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $searchData['page']->setPage();
        $searchData['page']->setUrl(\Request::getQueryString());
        $groupSQL = 'vs.visitIP, vs.visitOS, vs.visitBrowser';
        $counting = ' c.cnt ';

        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $arrBind = [];
        if ($searchDevice != 'all') {
            $arrWhere[] = 'vs.visitDevice = ?';
            $this->db->bind_param_push($arrBind, 's', $searchDevice);
        }
        $arrWhere[] = 'vs.mallSno = ?';
        $this->db->bind_param_push($arrBind, 's', $mallSno);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        if($searchData['searchOS']) {
            $arrWhere[] = 'vs.visitOS = \'' . $searchData['searchOS'] . '\'';
        }
        if($searchData['searchBrowser']) {
            $arrWhere[] = 'vs.visitBrowser = \'' . $searchData['searchBrowser'] . '\'';
        }
        if($searchData['searchIP']) {
            $arrWhere[] = 'INET_NTOA(vs.visitIP) like \'%' . $searchData['searchIP'] . '%\'';
        }

        $arrWhere[] = 'vs.regDt BETWEEN ? AND ?';
        $this->db->bind_param_push($arrBind, 's', $sDate->format('Y-m-d 00:00:00'));
        $this->db->bind_param_push($arrBind, 's', $eDate->format('Y-m-d 23:59:59'));

        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strField = "vs.regDt, INET_NTOA(vs.visitIP) as visitIP, vs.visitOS, vs.visitBrowser, vs.visitReferer" . ($groupFl ? ', SUM(vs.visitPageView) visitPageView ' : ', vs.visitPageView');
        $this->db->strOrder = 'vs.visitIP ASC, vs.visitOS ASC, vs.visitBrowser ASC, vs.regDt DESC';
        if($groupFl) {
            $this->db->strGroup = $groupSQL;
            $counting = ' COUNT(c.cnt) ';
        }
        if($searchData['limit'] != 'unlimited') {
            $this->db->strLimit = $searchData['page']->recode['start'] . ',' . $searchData['pageNum'];
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_STATISTICS . ' as vs ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        /* 검색 count 쿼리 */
        $totalCountSQL =  'SELECT ' . $counting . ' AS cnt FROM (SELECT COUNT(vs.visitIP) AS cnt FROM ' . DB_VISIT_STATISTICS . ' as vs WHERE '.implode(' AND ', $arrWhere) . ($groupFl ? ' GROUP BY ' . $groupSQL : '') . ') AS c '; // . ' GROUP BY vs.visitIP ';
        $dataCount = $this->db->query_fetch($totalCountSQL, $arrBind, false);

        $searchData['page']->recode['total'] = $dataCount['cnt']; //검색 레코드 수
        $searchData['page']->recode['amount'] = $dataCount['cnt']; // 전체 레코드 수
        $searchData['page']->setPage();

        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }


    /**
     * getVisitStatisticsPageAllMall
     * 방문통계 - 전체 몰 정보 페이지 출력
     *
     * @param      $searchDate
     *
     * @return mixed
     * @throws \Exception
     */
    public function getVisitStatisticsPageAllMall($searchDate)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $getValue = Request::get()->toArray();

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $this->db->strWhere = 'vs.regDt BETWEEN ? AND ?';
        $this->db->bind_param_push($arrBind, 's', $searchDate[0]);
        $this->db->bind_param_push($arrBind, 's', $searchDate[1]);
        $this->db->strField = "vs.regDt, INET_NTOA(vs.visitIP) as visitIP, vs.visitOS, vs.visitBrowser, vs.visitPageView, vs.visitReferer";
        $this->db->strOrder = 'vs.regDt desc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_STATISTICS . ' as vs ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }

    /**
     * 방문통계 정보 출력
     *
     * @author su
     */
    public function getVisitStatistics($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $getValue = Request::get()->toArray();

        $sort['fieldName'] = gd_isset($getValue['sort']['name'], 'vs.regDt');
        $sort['sortMode'] = gd_isset($getValue['sort']['mode'], 'desc');

        $this->db->strWhere = ' (vs.regDt BETWEEN ? AND ?) AND (vs.mallSno = ?) ';
        $this->db->bind_param_push($arrBind, 's', $searchDate[0]);
        $this->db->bind_param_push($arrBind, 's', $searchDate[1]);
        $this->db->bind_param_push($arrBind, 'i', $mallSno);
        $this->db->strField = "vs.regDt, INET_NTOA(vs.visitIP) as visitIP, vs.visitOS, vs.visitBrowser, vs.visitPageView, vs.visitReferer";
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_STATISTICS . ' as vs ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;

        return $getData;
    }

    /**
     * 방문통계 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param array       $visit      session / ip / memNo / OS / browser / year / month / day - * hour 는 안됨(통계시간)
     * @param string      $visitField 출력할 필드명 (기본 null)
     * @param array       $arrBind    bind 처리 배열 (기본 null)
     * @param bool|string $dataArray  return 값을 배열처리 (기본값 false)
     *
     * @return array 방문통계 정보
     *
     * @author su
     */
    public function getVisitStatisticsInfo($visit = null, $visitField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (isset($visit['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            } else {
                $this->db->strWhere = ' vs.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            }
        }
        if (isset($visit['siteKey'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitSiteKey = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['siteKey']);
            } else {
                $this->db->strWhere = ' vs.visitSiteKey = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['siteKey']);
            }
        }
        if (isset($visit['IP'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitIP = INET_ATON(?) ';
                $this->db->bind_param_push($arrBind, 's', $visit['IP']);
            } else {
                $this->db->strWhere = ' vs.visitIP = INET_ATON(?) ';
                $this->db->bind_param_push($arrBind, 's', $visit['IP']);
            }
        }
        if (isset($visit['inetIP'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitIP = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['inetIP']);
            } else {
                $this->db->strWhere = ' vs.visitIP = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['inetIP']);
            }
        }
        if (isset($visit['memNo'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.memNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['memNo']);
            } else {
                $this->db->strWhere = ' vs.memNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['memNo']);
            }
        }
        if ($visit['notMemNo']) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.memNo = 0 ';
            } else {
                $this->db->strWhere = ' vs.memNo = 0 ';
            }
        }
        if (isset($visit['inflow'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitInflow = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['inflow']);
            } else {
                $this->db->strWhere = ' vs.visitInflow = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['inflow']);
            }
        }
        if (isset($visit['device'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['device']);
            } else {
                $this->db->strWhere = ' vs.visitDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['device']);
            }
        }
        if (isset($visit['OS'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitOS = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['OS']);
            } else {
                $this->db->strWhere = ' vs.visitOS = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['OS']);
            }
        }
        if ($visit['browser']) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitBrowser = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['browser']);
            } else {
                $this->db->strWhere = ' vs.visitBrowser = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['browser']);
            }
        }
        if ($visit['year']) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitYear = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['year']);
            } else {
                $this->db->strWhere = ' vs.visitYear = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['year']);
            }
        }
        if ($visit['month']) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitMonth = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['month']);
            } else {
                $this->db->strWhere = ' vs.visitMonth = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['month']);
            }
        }
        if ($visit['day']) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitDay = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['day']);
            } else {
                $this->db->strWhere = ' vs.visitDay = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['day']);
            }
        }
        if (isset($visit['hour']) && is_numeric($visit['hour'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vs.visitHour = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['hour']);
            } else {
                $this->db->strWhere = ' vs.visitHour = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['hour']);
            }
        }
        if ($visit['sort']) {
            $this->db->strOrder = $visit['sort'];
        }
        if ($visit['limit']) {
            $this->db->strLimit = $visit['limit'];
        }
        if ($visitField) {
            $this->db->strField = $visitField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_STATISTICS . ' as vs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return $getData[0];
        }

        return $getData;
    }

    /**
     * 방문통계-유입검색어 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param array       $visit      mallSno / device / referer / inflow / searchword
     * @param string      $visitField 출력할 필드명 (기본 null)
     * @param array       $arrBind    bind 처리 배열 (기본 null)
     * @param bool|string $dataArray  return 값을 배열처리 (기본값 false)
     *
     * @return array 방문통계 정보
     *
     * @author su
     */
    public function getVisitStatisticsSearchWordInfo($visit = null, $visitField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (isset($visit['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vssw.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            } else {
                $this->db->strWhere = ' vssw.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            }
        }
        if (isset($visit['device'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vssw.visitDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['device']);
            } else {
                $this->db->strWhere = ' vssw.visitDevice = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['device']);
            }
        }
        if (isset($visit['inflow'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vssw.visitInflow = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['inflow']);
            } else {
                $this->db->strWhere = ' vssw.visitInflow = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['inflow']);
            }
        }
        if (isset($visit['searchWord'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vssw.visitSearchWord = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['searchWord']);
            } else {
                $this->db->strWhere = ' vssw.visitSearchWord = ? ';
                $this->db->bind_param_push($arrBind, 's', $visit['searchWord']);
            }
        }
        if ($visit['sort']) {
            $this->db->strOrder = $visit['sort'];
        }
        if ($visit['limit']) {
            $this->db->strLimit = $visit['limit'];
        }
        if ($visitField) {
            $this->db->strField = $visitField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_SEARCH_WORD . ' as vs ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 방문통계 일별 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param Mixed       $visit         Ym / mallSno
     * @param string      $visitDayField 출력할 필드명 (기본 null)
     * @param array       $arrBind       bind 처리 배열 (기본 null)
     * @param bool|string $dataArray     return 값을 배열처리 (기본값 false)
     *
     * @return array 방문통계 정보
     *
     * @author su
     */
    public function getVisitDayStatisticsInfo($visit = null, $visitDayField = null, $arrBind = null, $dataArray = false,$isGenerator = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($visit['Ym'])) {
            if ($visit['Ym'][0] > 0 && $visit['Ym'][1] > 0) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND vd.visitYM BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ym'][0]);
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ym'][1]);
                } else {
                    $this->db->strWhere = ' vd.visitYM BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ym'][0]);
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ym'][1]);
                }
            }
        } else {
            if ($visit['Ym']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND vd.visitYM = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ym']);
                } else {
                    $this->db->strWhere = ' vd.visitYM = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ym']);
                }
            }
        }
        if (isset($visit['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vd.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            } else {
                $this->db->strWhere = ' vd.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            }
        }
        if ($visitDayField) {
            $this->db->strField = $visitDayField;
        }
        $query = $this->db->query_complete();

        if($isGenerator) {

            $strCountSQL = 'SELECT count(visitYM) as cnt FROM ' . DB_VISIT_DAY . ' as vd '.$query['where'];
            $totalNum = $this->db->query_fetch($strCountSQL, $arrBind,false)['cnt'];

            return $this->getVisitDayStatisticsInfoGenerator($totalNum,$query,$arrBind);

        } else {
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_DAY . ' as vd ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);

            if (count($getData) == 1 && $dataArray === false) {
                return $getData[0];
            }

            return $getData;
        }

    }

    /**
     * getVisitDayStatisticsInfoGenerator
     * getVisitDayStatisticsInfo 에서 generator 사용하여 생성시 사용
     *
     * @param string $totalNum 총 갯수
     * @param string $query 쿼리문
     * @param array $arrBind bind 처리 배열 (기본 null)
     *
     * @return generator object
     *
     * @author su
     */
    public function getVisitDayStatisticsInfoGenerator($totalNum,$query,$arrBind) {
        $pageLimit = "10000";

        if ($pageLimit >= $totalNum) $pageNum = 0;
        else $pageNum = ceil($totalNum / $pageLimit) - 1;

        $strField =   array_shift($query);
        for ($i = 0; $i <= $pageNum; $i++) {
            $strLimit = " LIMIT ".(($i * $pageLimit)) . "," . $pageLimit;
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_VISIT_DAY . ' as vd ' . implode(' ', $query).$strLimit;
            $tmpData =  $this->db->query_fetch_generator($strSQL, $arrBind);
            foreach($tmpData as $k => $v) {
                yield $v;
            }
            unset($tmpData);
        }
    }


    /**
     * 방문통계 시간별 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param Mixed       $visit          Ymd / mallSno
     * @param string      $visitHourField 출력할 필드명 (기본 null)
     * @param array       $arrBind        bind 처리 배열 (기본 null)
     * @param bool|string $dataArray      return 값을 배열처리 (기본값 false)
     *
     * @return array 방문통계 정보
     *
     * @author su
     */
    public function getVisitHourStatisticsInfo($visit = null, $visitHourField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($visit['Ymd'])) {
            if ($visit['Ymd'][0] > 0 && $visit['Ymd'][1] > 0) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND vh.visitYMD BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ymd'][0]);
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ymd'][1]);
                } else {
                    $this->db->strWhere = ' vh.visitYMD BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ymd'][0]);
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ymd'][1]);
                }
            }
        } else {
            if ($visit['Ymd']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND vh.visitYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ymd']);
                } else {
                    $this->db->strWhere = ' vh.visitYMD = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Ymd']);
                }
            }
        }
        if (isset($visit['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vh.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            } else {
                $this->db->strWhere = ' vh.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            }
        }
        if ($visitHourField) {
            $this->db->strField = $visitHourField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_HOUR . ' as vh ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 방문통계 월별 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param Mixed       $visit           Y / mallSno
     * @param string      $visitMonthField 출력할 필드명 (기본 null)
     * @param array       $arrBind         bind 처리 배열 (기본 null)
     * @param bool|string $dataArray       return 값을 배열처리 (기본값 false)
     *
     * @return array 방문통계 정보
     *
     * @author su
     */
    public function getVisitMonthStatisticsInfo($visit = null, $visitMonthField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (is_array($visit['Y'])) {
            if ($visit['Y'][0] > 0 && $visit['Y'][1] > 0) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND vm.visitY BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Y'][0]);
                    $this->db->bind_param_push($arrBind, 'i', $visit['Y'][1]);
                } else {
                    $this->db->strWhere = ' vm.visitY BETWEEN ? AND ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Y'][0]);
                    $this->db->bind_param_push($arrBind, 'i', $visit['Y'][1]);
                }
            }
        } else {
            if ($visit['Y']) {
                if ($this->db->strWhere) {
                    $this->db->strWhere = $this->db->strWhere . ' AND vm.visitY = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Y']);
                } else {
                    $this->db->strWhere = ' vm.visitY = ? ';
                    $this->db->bind_param_push($arrBind, 'i', $visit['Y']);
                }
            }
        }
        if (isset($visit['mallSno'])) {
            if ($this->db->strWhere) {
                $this->db->strWhere = $this->db->strWhere . ' AND vm.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            } else {
                $this->db->strWhere = ' vm.mallSno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
            }
        }
        if ($visitMonthField) {
            $this->db->strField = $visitMonthField;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_MONTH . ' as vm ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 방문통계 유입경로 정보
     *
     * @return string $visitInflow 방문통계 유입경로 정보
     *
     * @author su
     */
    public function getAgentInflow()
    {
        $visitInflow = '';
        $userReferer = strtolower(Request::getReferer());
        $userReferer = urldecode($userReferer);
        $parse = parse_url($userReferer);
        gd_isset($parse['host']);
        gd_isset($parse['path']);
        gd_isset($parse['query']);
        // @todo referer 체크 필요 http://도메인/order/cart.php 직접 입력시 referer (1.없다 2.http://도메인 3.http://도메인/order/cart.php) 어느 것이 맞는 것인지?
        // @todo referer 에 따라 direct link 처리 필요
        //        debug($parse['host']);
        //        debug($parse['path']);
        //        debug($parse['query']);

        if ($this->_visitPolicy['inflowAgent']) {
            foreach ($this->_visitPolicy['inflowAgent'] as $inflowKey => $inflowVal) {
                if (strpos($parse['host'], trim(strtolower($inflowKey))) !== false) {
                    $visitInflow = $inflowVal;
                    break;
                }
            }
        } else {
            $visitInflow = 'Inflow values SET User-Agent';
        }
        if ($visitInflow) {
            return $visitInflow;
        } else {
            return '기타';
        }
    }

    /**
     * 방문통계 유입검색어 정보
     *
     * @return string $visitSearchWord 방문통계 유입검색어 정보
     *
     * @author su
     */
    public function getSearchWord()
    {
        $visitSearchWord = '';

        $userReferer = strtolower(Request::getReferer());

//        @todo parse_url bing.com 에서 '크하하크' 한글깨짐 현상으로 문자열로 잘라 처리함
//        $parse = parse_url($userReferer);
//        $str = $parse['query'];
//        parse_str($str, $data);

        $url = explode('?', $userReferer);
        parse_str($url[1], $data);

        $searchWordKey = ['q', 'qr', 'query'];
        foreach ($data as $key => $val) {
            if (in_array($key, $searchWordKey)) {
                $visitSearchWord = trim(urldecode($val));

                // euc-kr인 경우 utf-8로 변경 및 인코딩 안되는 문자는 null 처리
                if (mb_detect_encoding($visitSearchWord, 'EUC-KR', true) == 'EUC-KR') {
                    $visitSearchWord = StringUtils::eucKrToUtf8($visitSearchWord);
                } else if (mb_detect_encoding($visitSearchWord, 'UTF-8', true) != 'UTF-8') {
                    $visitSearchWord = null;
                }

                break;
            }
        }

        return $visitSearchWord;
    }

    /**
     * 방문통계 OS 정보
     *
     * @return string $visitOS 방문통계 OS 정보
     *
     * @author su
     */
    public function getAgentOS()
    {
        $visitOS = '';
        $userAgent = strtolower(Request::getUserAgent());

        if ($this->_visitPolicy['osAgent']) {
            // 크롤러
            if ((preg_match("/(bot|http|slurp)/", $userAgent) || preg_match("/^microsoft/", $userAgent)) && strpos($userAgent, 'bsalsa.com') === false) {
                $visitOS = "Search Robot";
            } else {
                foreach ($this->_visitPolicy['osAgent'] as $osKey => $osVal) {
                    if (strpos($userAgent, trim(strtolower($osKey))) !== false) {
                        $visitOS = $osVal;
                        break;
                    }
                }
            }
        } else {
            $visitOS = 'OS values SET User-Agent';
        }
        if ($visitOS) {
            return $visitOS;
        } else {
            return __('기타');
        }
    }

    /**
     * 방문통계 Device 정보
     *
     * @return string $visitDevice 방문통계 Device 정보
     *
     * @author su
     */
    public function getDevice()
    {
        if (Request::isMobileDevice()) {
            $visitDevice = 'mobile';
        } else {
            $visitDevice = 'pc';
        }

        return $visitDevice;
    }

    /**
     * 방문통계 Browser 정보
     *
     * @return string $visitBrowser 방문통계 Browser 정보
     *
     * @author su
     */
    public function getAgentBrowser()
    {
        $visitBrowser = '';
        $userAgent = strtolower(Request::getUserAgent());

        if ($this->_visitPolicy['browserAgent']) {
            // 크롤러
            if ((preg_match("/(bot|http|slurp)/", $userAgent) || preg_match("/^microsoft/", $userAgent)) && strpos($userAgent, 'bsalsa.com') === false) {
                // 크롤러인 경우에는 도메인을 뽑아서 입력.
                preg_match("/[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})/", $userAgent, $match);
                $visitBrowser = $match[0];
            } else {
                foreach ($this->_visitPolicy['browserAgent'] as $browserKey => $browserVal) {
                    if (strpos($userAgent, trim(strtolower($browserKey))) !== false) {
                        $visitBrowser = $browserVal;
                        break;
                    }
                }
            }
        } else {
            $visitBrowser = 'Browser values SET User-Agent';
        }
        if ($visitBrowser) {
            return $visitBrowser;
        } else {
            return __('기타');
        }
    }

    /**
     * getVisitToday
     * 당일 방문통계
     *
     * @param $mallSno
     *
     * @return bool
     */
    public function getVisitToday($mallSno = null)
    {
        if ($mallSno != 'all') {
            $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);
            $visit['mallSno'] = $mallSno;
        }

        $returnData = [];
        $visit['year'] = date('Y');
        $visit['month'] = date('n');
        $visit['day'] = date('j');

        // 당일 총 방문자
        $getStatisticsDaily = $this->getVisitStatisticsTodayInfo($visit, null, null, true);
        $returnToday = [
            'Count' => $getStatisticsDaily[0]['visitCount'] + $getStatisticsDaily[1]['visitCount'],
            'VisitPv' => $getStatisticsDaily[0]['pv'] + $getStatisticsDaily[1]['pv'],
            'pc' => [
                'Count' => $getStatisticsDaily[0]['visitCount'],
                'VisitPv' => $getStatisticsDaily[0]['pv'],
            ],
            'mobile' => [
                'Count' => $getStatisticsDaily[1]['visitCount'],
                'VisitPv' => $getStatisticsDaily[1]['pv'],
            ],
        ];
        $returnData['total'] = $returnToday;

        // 당일 시간대 별 방문자
        $getStatisticsHour = $this->getVisitStatisticsDailyInfo($visit, null, null, true, $mallSno);
        foreach ($getStatisticsHour as $val) {
            $device = $val['visitDevice'];
            $hour = $val['visitHour'];
            $visitCount = $val['visitCount'];
            $pv = $val['pv'];

            $returnData['hour'][$hour][$device] = [
                'visitCount' => $visitCount,
                'pv' => $pv,
            ];
            $returnData['hour'][$hour]['visitCount'] += $visitCount;
            $returnData['hour'][$hour]['pv'] += $pv;
        }
        return $returnData;
    }

    /**
     * 방문통계 검색 일별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     * @param int   $mallSno
     *
     * @return array $getDataArr 방문통계 일별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitDay($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYm[0] = substr($searchDate[0], 0, 6);
        $visitYm[1] = substr($searchDate[1], 0, 6);

        $visit['Ym'] = $visitYm;
        $visit['mallSno'] = $mallSno;

        $getDataJson = $this->getVisitDayStatisticsInfo($visit, '*', '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['visitYM'] == $visitYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['visitYM'] == $visitYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $getDataArr[$dataVal['visitYM'] . sprintf("%02d", $key)] = json_decode($val, true);
                }
            }
        }

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $startDate = new DateTime($searchDate[0]);
        $endDate = new DateTime($searchDate[1]);
        $diffDay = $endDate->diff($startDate)->days;
        $returnVisitDay = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($searchDate[0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            $returnVisitDay[$searchDt->format('Ymd')] = $visitColumn;
            $returnVisitDay[$searchDt->format('Ymd')]['pc'] = $visitColumn;
            $returnVisitDay[$searchDt->format('Ymd')]['mobile'] = $visitColumn;
        }

        foreach ($getDataArr as $dateKey => $dateVal) {
            $returnVisitDay[$dateKey]['visitCount'] += $dateVal['visitCount'];
            $returnVisitDay[$dateKey]['visitNumber'] += $dateVal['visitNumber'];
            $returnVisitDay[$dateKey]['visitNewCount'] += $dateVal['visitNewCount'];
            $returnVisitDay[$dateKey]['visitReCount'] += $dateVal['visitReCount'];
            $returnVisitDay[$dateKey]['pv'] += $dateVal['pv'];
            $returnVisitDay[$dateKey]['visitNewPv'] += $dateVal['visitNewPv'];
            $returnVisitDay[$dateKey]['visitRePv'] += $dateVal['visitRePv'];
            foreach ($dateVal['visitInflow'] as $inflowKey => $inflowVal) {
                $returnVisitDay[$dateKey]['visitInflow'][$inflowKey] += $inflowVal;
            }
            foreach ($dateVal['visitOs'] as $osKey => $osVal) {
                $returnVisitDay[$dateKey]['visitOs'][$osKey] += $osVal;
            }
            foreach ($dateVal['visitBrowser'] as $browserKey => $browserVal) {
                $returnVisitDay[$dateKey]['visitBrowser'][$browserKey] += $browserVal;
            }
            $returnVisitDay[$dateKey]['pc']['visitCount'] += $dateVal['pc']['visitCount'];
            $returnVisitDay[$dateKey]['pc']['visitNumber'] += $dateVal['pc']['visitNumber'];
            $returnVisitDay[$dateKey]['pc']['visitNewCount'] += $dateVal['pc']['visitNewCount'];
            $returnVisitDay[$dateKey]['pc']['visitReCount'] += $dateVal['pc']['visitReCount'];
            $returnVisitDay[$dateKey]['pc']['pv'] += $dateVal['pc']['pv'];
            $returnVisitDay[$dateKey]['pc']['visitNewPv'] += $dateVal['pc']['visitNewPv'];
            $returnVisitDay[$dateKey]['pc']['visitRePv'] += $dateVal['pc']['visitRePv'];
            foreach ($dateVal['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                $returnVisitDay[$dateKey]['pc']['visitInflow'][$inflowKey] += $inflowVal;
            }
            foreach ($dateVal['pc']['visitOs'] as $osKey => $osVal) {
                $returnVisitDay[$dateKey]['pc']['visitOs'][$osKey] += $osVal;
            }
            foreach ($dateVal['pc']['visitBrowser'] as $browserKey => $browserVal) {
                $returnVisitDay[$dateKey]['pc']['visitBrowser'][$browserKey] += $browserVal;
            }
            $returnVisitDay[$dateKey]['mobile']['visitCount'] += $dateVal['mobile']['visitCount'];
            $returnVisitDay[$dateKey]['mobile']['visitNumber'] += $dateVal['mobile']['visitNumber'];
            $returnVisitDay[$dateKey]['mobile']['visitNewCount'] += $dateVal['mobile']['visitNewCount'];
            $returnVisitDay[$dateKey]['mobile']['visitReCount'] += $dateVal['mobile']['visitReCount'];
            $returnVisitDay[$dateKey]['mobile']['pv'] += $dateVal['mobile']['pv'];
            $returnVisitDay[$dateKey]['mobile']['visitNewPv'] += $dateVal['mobile']['visitNewPv'];
            $returnVisitDay[$dateKey]['mobile']['visitRePv'] += $dateVal['mobile']['visitRePv'];
            foreach ($dateVal['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                $returnVisitDay[$dateKey]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
            }
            foreach ($dateVal['mobile']['visitOs'] as $osKey => $osVal) {
                $returnVisitDay[$dateKey]['mobile']['visitOs'][$osKey] += $osVal;
            }
            foreach ($dateVal['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                $returnVisitDay[$dateKey]['mobile']['visitBrowser'][$browserKey] += $browserVal;
            }
        }

        return $returnVisitDay;
    }


    /**
     * 방문통계 - 전체 몰 검색 일별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     *
     * @return array $getDataArr 방문통계 일별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitDayAllMall($searchDate)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYm[0] = substr($searchDate[0], 0, 6);
        $visitYm[1] = substr($searchDate[1], 0, 6);

        $visit['Ym'] = $visitYm;

        $getDataJson = $this->getVisitDayStatisticsInfo($visit, '*', '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['visitYM'] == $visitYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['visitYM'] == $visitYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $getDataArr[$dataVal['visitYM'] . sprintf("%02d", $key)][$dataVal['mallSno']] = json_decode($val, true);
                }
            }
        }

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $startDate = new DateTime($searchDate[0]);
        $endDate = new DateTime($searchDate[1]);
        $diffDay = $endDate->diff($startDate)->days;
        $returnVisitDay = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($searchDate[0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            $returnVisitDay[$searchDt->format('Ymd')] = $visitColumn;
            $returnVisitDay[$searchDt->format('Ymd')]['pc'] = $visitColumn;
            $returnVisitDay[$searchDt->format('Ymd')]['mobile'] = $visitColumn;
        }

        foreach ($getDataArr as $dateKey => $dateVal) {
            foreach ($dateVal as $key => $val) {
                $returnVisitDay[$dateKey]['visitCount'] += $val['visitCount'];
                $returnVisitDay[$dateKey]['visitNumber'] += $val['visitNumber'];
                $returnVisitDay[$dateKey]['visitNewCount'] += $val['visitNewCount'];
                $returnVisitDay[$dateKey]['visitReCount'] += $val['visitReCount'];
                $returnVisitDay[$dateKey]['pv'] += $val['pv'];
                $returnVisitDay[$dateKey]['visitNewPv'] += $val['visitNewPv'];
                $returnVisitDay[$dateKey]['visitRePv'] += $val['visitRePv'];
                foreach ($val['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$dateKey]['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($val['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$dateKey]['visitOs'][$osKey] += $osVal;
                }
                foreach ($val['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$dateKey]['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitDay[$dateKey]['pc']['visitCount'] += $val['pc']['visitCount'];
                $returnVisitDay[$dateKey]['pc']['visitNumber'] += $val['pc']['visitNumber'];
                $returnVisitDay[$dateKey]['pc']['visitNewCount'] += $val['pc']['visitNewCount'];
                $returnVisitDay[$dateKey]['pc']['visitReCount'] += $val['pc']['visitReCount'];
                $returnVisitDay[$dateKey]['pc']['pv'] += $val['pc']['pv'];
                $returnVisitDay[$dateKey]['pc']['visitNewPv'] += $val['pc']['visitNewPv'];
                $returnVisitDay[$dateKey]['pc']['visitRePv'] += $val['pc']['visitRePv'];
                foreach ($val['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$dateKey]['pc']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($val['pc']['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$dateKey]['pc']['visitOs'][$osKey] += $osVal;
                }
                foreach ($val['pc']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$dateKey]['pc']['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitDay[$dateKey]['mobile']['visitCount'] += $val['mobile']['visitCount'];
                $returnVisitDay[$dateKey]['mobile']['visitNumber'] += $val['mobile']['visitNumber'];
                $returnVisitDay[$dateKey]['mobile']['visitNewCount'] += $val['mobile']['visitNewCount'];
                $returnVisitDay[$dateKey]['mobile']['visitReCount'] += $val['mobile']['visitReCount'];
                $returnVisitDay[$dateKey]['mobile']['pv'] += $val['mobile']['pv'];
                $returnVisitDay[$dateKey]['mobile']['visitNewPv'] += $val['mobile']['visitNewPv'];
                $returnVisitDay[$dateKey]['mobile']['visitRePv'] += $val['mobile']['visitRePv'];
                foreach ($val['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$dateKey]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($val['mobile']['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$dateKey]['mobile']['visitOs'][$osKey] += $osVal;
                }
                foreach ($val['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$dateKey]['mobile']['visitBrowser'][$browserKey] += $browserVal;
                }
            }
        }

        return $returnVisitDay;
    }

    /**
     * 방문통계 검색 시간별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     * @param int   $mallSno
     *
     * @return array $getDataArr 방문통계 일별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitHour($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYmd[0] = $searchDate[0];
        $visitYmd[1] = $searchDate[1];

        $visit['Ymd'] = $visitYmd;
        $visit['mallSno'] = $mallSno;

        $getDataJson = $this->getVisitHourStatisticsInfo($visit, '*', '', true);

        // 통계 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $returnVisitHour = [];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            $returnVisitHour[$hour] = $visitColumn;
            $returnVisitHour[$hour]['pc'] = $visitColumn;
            $returnVisitHour[$hour]['mobile'] = $visitColumn;
        }

        foreach ($getDataJson as $key => $val) {
            for ($i = 0; $i <= 23; $i++) {
                $data = json_decode($val[$i], true);
                $hour = sprintf("%02d", $i);
                $returnVisitHour[$hour]['visitCount'] += $data['visitCount'];
                $returnVisitHour[$hour]['visitNumber'] += $data['visitNumber'];
                $returnVisitHour[$hour]['visitNewCount'] += $data['visitNewCount'];
                $returnVisitHour[$hour]['visitReCount'] += $data['visitReCount'];
                $returnVisitHour[$hour]['pv'] += $data['pv'];
                $returnVisitHour[$hour]['newCountPv'] += $data['newCountPv'];
                $returnVisitHour[$hour]['reCountPv'] += $data['reCountPv'];
                foreach ($data['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitHour[$hour]['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($data['visitOs'] as $osKey => $osVal) {
                    $returnVisitHour[$hour]['visitOs'][$osKey] += $osVal;
                }
                foreach ($data['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitHour[$hour]['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitHour[$hour]['pc']['visitCount'] += $data['pc']['visitCount'];
                $returnVisitHour[$hour]['pc']['visitNumber'] += $data['pc']['visitNumber'];
                $returnVisitHour[$hour]['pc']['visitNewCount'] += $data['pc']['visitNewCount'];
                $returnVisitHour[$hour]['pc']['visitReCount'] += $data['pc']['visitReCount'];
                $returnVisitHour[$hour]['pc']['pv'] += $data['pc']['pv'];
                $returnVisitHour[$hour]['pc']['newCountPv'] += $data['pc']['newCountPv'];
                $returnVisitHour[$hour]['pc']['reCountPv'] += $data['pc']['reCountPv'];
                foreach ($data['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitHour[$hour]['pc']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($data['pc']['visitOs'] as $osKey => $osVal) {
                    $returnVisitHour[$hour]['pc']['visitOs'][$osKey] += $osVal;
                }
                foreach ($data['pc']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitHour[$hour]['pc']['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitHour[$hour]['mobile']['visitCount'] += $data['mobile']['visitCount'];
                $returnVisitHour[$hour]['mobile']['visitNumber'] += $data['mobile']['visitNumber'];
                $returnVisitHour[$hour]['mobile']['visitNewCount'] += $data['mobile']['visitNewCount'];
                $returnVisitHour[$hour]['mobile']['visitReCount'] += $data['mobile']['visitReCount'];
                $returnVisitHour[$hour]['mobile']['pv'] += $data['mobile']['pv'];
                $returnVisitHour[$hour]['mobile']['newCountPv'] += $data['mobile']['newCountPv'];
                $returnVisitHour[$hour]['mobile']['reCountPv'] += $data['mobile']['reCountPv'];
                foreach ($data['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitHour[$hour]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($data['mobile']['visitOs'] as $osKey => $osVal) {
                    $returnVisitHour[$hour]['mobile']['visitOs'][$osKey] += $osVal;
                }
                foreach ($data['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitHour[$hour]['mobile']['visitBrowser'][$browserKey] += $browserVal;
                }
            }
        }

        return $returnVisitHour;
    }

    /**
     * 방문통계 - 전체 몰 검색 시간별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     *
     * @return array $getDataArr 방문통계 일별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitHourAllMall($searchDate)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYmd[0] = $searchDate[0];
        $visitYmd[1] = $searchDate[1];

        $visit['Ymd'] = $visitYmd;

        $getDataJson = $this->getVisitHourStatisticsInfo($visit, '*', '', true);

        // 통계 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $returnVisitHour = [];
        for ($i = 0; $i <= 23; $i++) {
            $hour = sprintf("%02d", $i);
            $returnVisitHour[$hour] = $visitColumn;
            $returnVisitHour[$hour]['pc'] = $visitColumn;
            $returnVisitHour[$hour]['mobile'] = $visitColumn;
        }

        foreach ($getDataJson as $key => $val) {
            for ($i = 0; $i <= 23; $i++) {
                $data = json_decode($val[$i], true);
                $hour = sprintf("%02d", $i);
                $returnVisitHour[$hour]['visitCount'] += $data['visitCount'];
                $returnVisitHour[$hour]['visitNumber'] += $data['visitNumber'];
                $returnVisitHour[$hour]['visitNewCount'] += $data['visitNewCount'];
                $returnVisitHour[$hour]['visitReCount'] += $data['visitReCount'];
                $returnVisitHour[$hour]['pv'] += $data['pv'];
                $returnVisitHour[$hour]['newCountPv'] += $data['newCountPv'];
                $returnVisitHour[$hour]['reCountPv'] += $data['reCountPv'];
                foreach ($data['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitHour[$hour]['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($data['visitOs'] as $osKey => $osVal) {
                    $returnVisitHour[$hour]['visitOs'][$osKey] += $osVal;
                }
                foreach ($data['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitHour[$hour]['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitHour[$hour]['pc']['visitCount'] += $data['pc']['visitCount'];
                $returnVisitHour[$hour]['pc']['visitNumber'] += $data['pc']['visitNumber'];
                $returnVisitHour[$hour]['pc']['visitNewCount'] += $data['pc']['visitNewCount'];
                $returnVisitHour[$hour]['pc']['visitReCount'] += $data['pc']['visitReCount'];
                $returnVisitHour[$hour]['pc']['pv'] += $data['pc']['pv'];
                $returnVisitHour[$hour]['pc']['newCountPv'] += $data['pc']['newCountPv'];
                $returnVisitHour[$hour]['pc']['reCountPv'] += $data['pc']['reCountPv'];
                foreach ($data['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitHour[$hour]['pc']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($data['pc']['visitOs'] as $osKey => $osVal) {
                    $returnVisitHour[$hour]['pc']['visitOs'][$osKey] += $osVal;
                }
                foreach ($data['pc']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitHour[$hour]['pc']['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitHour[$hour]['mobile']['visitCount'] += $data['mobile']['visitCount'];
                $returnVisitHour[$hour]['mobile']['visitNumber'] += $data['mobile']['visitNumber'];
                $returnVisitHour[$hour]['mobile']['visitNewCount'] += $data['mobile']['visitNewCount'];
                $returnVisitHour[$hour]['mobile']['visitReCount'] += $data['mobile']['visitReCount'];
                $returnVisitHour[$hour]['mobile']['pv'] += $data['mobile']['pv'];
                $returnVisitHour[$hour]['mobile']['newCountPv'] += $data['mobile']['newCountPv'];
                $returnVisitHour[$hour]['mobile']['reCountPv'] += $data['mobile']['reCountPv'];
                foreach ($data['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitHour[$hour]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($data['mobile']['visitOs'] as $osKey => $osVal) {
                    $returnVisitHour[$hour]['mobile']['visitOs'][$osKey] += $osVal;
                }
                foreach ($data['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitHour[$hour]['mobile']['visitBrowser'][$browserKey] += $browserVal;
                }
            }
        }

        return $returnVisitHour;
    }

    /**
     * 방문통계 검색 요일별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     * @param int   $mallSno
     *
     * @return array $getDataArr 방문통계 요일별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitWeek($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYm[0] = substr($searchDate[0], 0, 6);
        $visitYm[1] = substr($searchDate[1], 0, 6);

        $visit['Ym'] = $visitYm;
        $visit['mallSno'] = $mallSno;

        $getDataJson = $this->getVisitDayStatisticsInfo($visit, '*', '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['visitYM'] == $visitYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['visitYM'] == $visitYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $getDataArr[$dataVal['visitYM'] . sprintf("%02d", $key)] = json_decode($val, true);
                }
            }
        }

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $returnVisitDay = [];
        for ($i = 0; $i <= 6; $i++) {
            $returnVisitDay[$i] = $visitColumn;
            $returnVisitDay[$i]['pc'] = $visitColumn;
            $returnVisitDay[$i]['mobile'] = $visitColumn;
        }

        foreach ($getDataArr as $dateKey => $dateVal) {
            $visitDt = new DateTime($dateKey);
            $week = $visitDt->format('w');
            $returnVisitDay[$week]['visitCount'] += $dateVal['visitCount'];
            $returnVisitDay[$week]['visitNumber'] += $dateVal['visitNumber'];
            $returnVisitDay[$week]['visitNewCount'] += $dateVal['visitNewCount'];
            $returnVisitDay[$week]['visitReCount'] += $dateVal['visitReCount'];
            $returnVisitDay[$week]['pv'] += $dateVal['pv'];
            $returnVisitDay[$week]['visitNewPv'] += $dateVal['visitNewPv'];
            $returnVisitDay[$week]['visitRePv'] += $dateVal['visitRePv'];
            foreach ($dateVal['visitInflow'] as $inflowKey => $inflowVal) {
                $returnVisitDay[$week]['visitInflow'][$inflowKey] += $inflowVal;
            }
            foreach ($dateVal['visitOs'] as $osKey => $osVal) {
                $returnVisitDay[$week]['visitOs'][$osKey] += $osVal;
            }
            foreach ($dateVal['visitBrowser'] as $browserKey => $browserVal) {
                $returnVisitDay[$week]['visitBrowser'][$browserKey] += $browserVal;
            }
            $returnVisitDay[$week]['pc']['visitCount'] += $dateVal['pc']['visitCount'];
            $returnVisitDay[$week]['pc']['visitNumber'] += $dateVal['pc']['visitNumber'];
            $returnVisitDay[$week]['pc']['visitNewCount'] += $dateVal['pc']['visitNewCount'];
            $returnVisitDay[$week]['pc']['visitReCount'] += $dateVal['pc']['visitReCount'];
            $returnVisitDay[$week]['pc']['pv'] += $dateVal['pc']['pv'];
            $returnVisitDay[$week]['pc']['visitNewPv'] += $dateVal['pc']['visitNewPv'];
            $returnVisitDay[$week]['pc']['visitRePv'] += $dateVal['pc']['visitRePv'];
            foreach ($dateVal['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                $returnVisitDay[$week]['pc']['visitInflow'][$inflowKey] += $inflowVal;
            }
            foreach ($dateVal['pc']['visitOs'] as $osKey => $osVal) {
                $returnVisitDay[$week]['pc']['visitOs'][$osKey] += $osVal;
            }
            foreach ($dateVal['pc']['visitBrowser'] as $browserKey => $browserVal) {
                $returnVisitDay[$week]['pc']['visitBrowser'][$browserKey] += $browserVal;
            }
            $returnVisitDay[$week]['mobile']['visitCount'] += $dateVal['mobile']['visitCount'];
            $returnVisitDay[$week]['mobile']['visitNumber'] += $dateVal['mobile']['visitNumber'];
            $returnVisitDay[$week]['mobile']['visitNewCount'] += $dateVal['mobile']['visitNewCount'];
            $returnVisitDay[$week]['mobile']['visitReCount'] += $dateVal['mobile']['visitReCount'];
            $returnVisitDay[$week]['mobile']['pv'] += $dateVal['mobile']['pv'];
            $returnVisitDay[$week]['mobile']['visitNewPv'] += $dateVal['mobile']['visitNewPv'];
            $returnVisitDay[$week]['mobile']['visitRePv'] += $dateVal['mobile']['visitRePv'];
            foreach ($dateVal['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                $returnVisitDay[$week]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
            }
            foreach ($dateVal['mobile']['visitOs'] as $osKey => $osVal) {
                $returnVisitDay[$week]['mobile']['visitOs'][$osKey] += $osVal;
            }
            foreach ($dateVal['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                $returnVisitDay[$week]['mobile']['visitBrowser'][$browserKey] += $browserVal;
            }
        }

        return $returnVisitDay;
    }

    /**
     * 방문통계 - 전체 몰 검색 요일별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     *
     * @return array $getDataArr 방문통계 요일별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitWeekAllMall($searchDate)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYm[0] = substr($searchDate[0], 0, 6);
        $visitYm[1] = substr($searchDate[1], 0, 6);

        $visit['Ym'] = $visitYm;

        $getDataJson = $this->getVisitDayStatisticsInfo($visit, '*', '', true);

        foreach ($getDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['visitYM'] == $visitYm[0]) {
                $sDay = substr($searchDate[0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($getDataJson[$key][$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['visitYM'] == $visitYm[1]) {
                $eDay = substr($searchDate[1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($getDataJson[$key][$i]);
                }
            }
        }

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $getDataArr[$dataVal['visitYM'] . sprintf("%02d", $key)][$dataVal['mallSno']] = json_decode($val, true);
                }
            }
        }

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $returnVisitDay = [];
        for ($i = 0; $i <= 6; $i++) {
            $returnVisitDay[$i] = $visitColumn;
            $returnVisitDay[$i]['pc'] = $visitColumn;
            $returnVisitDay[$i]['mobile'] = $visitColumn;
        }

        foreach ($getDataArr as $dateKey => $dateVal) {
            $visitDt = new DateTime($dateKey);
            $week = $visitDt->format('w');
            foreach ($dateVal as $key => $val) {
                $returnVisitDay[$week]['visitCount'] += $val['visitCount'];
                $returnVisitDay[$week]['visitNumber'] += $val['visitNumber'];
                $returnVisitDay[$week]['visitNewCount'] += $val['visitNewCount'];
                $returnVisitDay[$week]['visitReCount'] += $val['visitReCount'];
                $returnVisitDay[$week]['pv'] += $val['pv'];
                $returnVisitDay[$week]['visitNewPv'] += $val['visitNewPv'];
                $returnVisitDay[$week]['visitRePv'] += $val['visitRePv'];
                foreach ($val['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$week]['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($val['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$week]['visitOs'][$osKey] += $osVal;
                }
                foreach ($val['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$week]['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitDay[$week]['pc']['visitCount'] += $val['pc']['visitCount'];
                $returnVisitDay[$week]['pc']['visitNumber'] += $val['pc']['visitNumber'];
                $returnVisitDay[$week]['pc']['visitNewCount'] += $val['pc']['visitNewCount'];
                $returnVisitDay[$week]['pc']['visitReCount'] += $val['pc']['visitReCount'];
                $returnVisitDay[$week]['pc']['pv'] += $val['pc']['pv'];
                $returnVisitDay[$week]['pc']['visitNewPv'] += $val['pc']['visitNewPv'];
                $returnVisitDay[$week]['pc']['visitRePv'] += $val['pc']['visitRePv'];
                foreach ($val['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$week]['pc']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($val['pc']['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$week]['pc']['visitOs'][$osKey] += $osVal;
                }
                foreach ($val['pc']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$week]['pc']['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitDay[$week]['mobile']['visitCount'] += $val['mobile']['visitCount'];
                $returnVisitDay[$week]['mobile']['visitNumber'] += $val['mobile']['visitNumber'];
                $returnVisitDay[$week]['mobile']['visitNewCount'] += $val['mobile']['visitNewCount'];
                $returnVisitDay[$week]['mobile']['visitReCount'] += $val['mobile']['visitReCount'];
                $returnVisitDay[$week]['mobile']['pv'] += $val['mobile']['pv'];
                $returnVisitDay[$week]['mobile']['visitNewPv'] += $val['mobile']['visitNewPv'];
                $returnVisitDay[$week]['mobile']['visitRePv'] += $val['mobile']['visitRePv'];
                foreach ($val['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$week]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($val['mobile']['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$week]['mobile']['visitOs'][$osKey] += $osVal;
                }
                foreach ($val['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$week]['mobile']['visitBrowser'][$browserKey] += $browserVal;
                }
            }
        }

        return $returnVisitDay;
    }

    /**
     * 방문통계 검색 월별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     * @param int   $mallSno
     *
     * @return array $getDataArr 방문통계 월별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitMonth($searchDate, $mallSno = null)
    {
        // 상점별 고유번호 - 해외상점
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYM[0] = substr($searchDate[0], 0, 6);
        $visitYM[1] = substr($searchDate[1], 0, 6);

        $visit['Ym'] = $visitYM;
        $visit['mallSno'] = $mallSno;

        $getDataJson = $this->getVisitDayStatisticsInfo($visit, '*', '', true,true);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $getDataArr[$dataVal['visitYM']][$key] = json_decode($val, true);
                }
            }
        }

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $searchDt = new DateTime($searchDate[0]);
        $endDate = new DateTime($searchDate[1]);
        $returnVisitDay = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }
            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                $returnVisitDay[$searchDt->format('Ym')] = $visitColumn;
                $returnVisitDay[$searchDt->format('Ym')]['pc'] = $visitColumn;
                $returnVisitDay[$searchDt->format('Ym')]['mobile'] = $visitColumn;
            }
        }

        foreach ($getDataArr as $dateKey => $dateVal) {
            foreach ($dateVal as $dayKey => $dayVal) {
                $returnVisitDay[$dateKey]['visitCount'] += $dayVal['visitCount'];
                $returnVisitDay[$dateKey]['visitNumber'] += $dayVal['visitNumber'];
                $returnVisitDay[$dateKey]['visitNewCount'] += $dayVal['visitNewCount'];
                $returnVisitDay[$dateKey]['visitReCount'] += $dayVal['visitReCount'];
                $returnVisitDay[$dateKey]['pv'] += $dayVal['pv'];
                $returnVisitDay[$dateKey]['visitNewPv'] += $dayVal['visitNewPv'];
                $returnVisitDay[$dateKey]['visitRePv'] += $dayVal['visitRePv'];
                foreach ($dayVal['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$dateKey]['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($dayVal['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$dateKey]['visitOs'][$osKey] += $osVal;
                }
                foreach ($dayVal['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$dateKey]['visitBrowser'][$browserKey] += $browserVal;
                }

                $returnVisitDay[$dateKey]['pc']['visitCount'] += $dayVal['pc']['visitCount'];
                $returnVisitDay[$dateKey]['pc']['visitNumber'] += $dayVal['pc']['visitNumber'];
                $returnVisitDay[$dateKey]['pc']['visitNewCount'] += $dayVal['pc']['visitNewCount'];
                $returnVisitDay[$dateKey]['pc']['visitReCount'] += $dayVal['pc']['visitReCount'];
                $returnVisitDay[$dateKey]['pc']['pv'] += $dayVal['pc']['pv'];
                $returnVisitDay[$dateKey]['pc']['visitNewPv'] += $dayVal['pc']['visitNewPv'];
                $returnVisitDay[$dateKey]['pc']['visitRePv'] += $dayVal['pc']['visitRePv'];
                foreach ($dayVal['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$dateKey]['pc']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($dayVal['pc']['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$dateKey]['pc']['visitOs'][$osKey] += $osVal;
                }
                foreach ($dayVal['pc']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$dateKey]['pc']['visitBrowser'][$browserKey] += $browserVal;
                }
                $returnVisitDay[$dateKey]['mobile']['visitCount'] += $dayVal['mobile']['visitCount'];
                $returnVisitDay[$dateKey]['mobile']['visitNumber'] += $dayVal['mobile']['visitNumber'];
                $returnVisitDay[$dateKey]['mobile']['visitNewCount'] += $dayVal['mobile']['visitNewCount'];
                $returnVisitDay[$dateKey]['mobile']['visitReCount'] += $dayVal['mobile']['visitReCount'];
                $returnVisitDay[$dateKey]['mobile']['pv'] += $dayVal['mobile']['pv'];
                $returnVisitDay[$dateKey]['mobile']['visitNewPv'] += $dayVal['mobile']['visitNewPv'];
                $returnVisitDay[$dateKey]['mobile']['visitRePv'] += $dayVal['mobile']['visitRePv'];
                foreach ($dayVal['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                    $returnVisitDay[$dateKey]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
                }
                foreach ($dayVal['mobile']['visitOs'] as $osKey => $osVal) {
                    $returnVisitDay[$dateKey]['mobile']['visitOs'][$osKey] += $osVal;
                }
                foreach ($dayVal['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                    $returnVisitDay[$dateKey]['mobile']['visitBrowser'][$browserKey] += $browserVal;
                }
            }
        }

        return $returnVisitDay;
    }

    /**
     * 방문통계 - 전체 몰 검색 월별 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     *
     * @return array $getDataArr 방문통계 월별 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitMonthAllMall($searchDate)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 360) {
            throw new \Exception(__('검색 가능 일은 최대 12개월 입니다.'));
        }
        if ($searchDate[0] > $searchDate[1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }
        $visitYM[0] = substr($searchDate[0], 0, 6);
        $visitYM[1] = substr($searchDate[1], 0, 6);

        $visit['Ym'] = $visitYM;

        $getDataJson = $this->getVisitDayStatisticsInfo($visit, '*', '', true,true);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $getDataArr[$dataVal['visitYM']][$dataVal['mallSno']][$key] = json_decode($val, true);
                }
            }
        }

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $searchDt = new DateTime($searchDate[0]);
        $endDate = new DateTime($searchDate[1]);
        $returnVisitDay = [];
        for ($i = 0; $i <= 13; $i++) {
            if ($i > 0) {
                $lastDay = $searchDt->format('t');
                $searchDt = $searchDt->modify('+' . $lastDay . ' day');
            }
            if ($searchDt->format('Ym') <= $endDate->format('Ym')) {
                $returnVisitDay[$searchDt->format('Ym')] = $visitColumn;
                $returnVisitDay[$searchDt->format('Ym')]['pc'] = $visitColumn;
                $returnVisitDay[$searchDt->format('Ym')]['mobile'] = $visitColumn;
            }
        }

        foreach ($getDataArr as $dateKey => $dateVal) {
            foreach ($dateVal as $mallKey => $mallVal) {
                foreach ($mallVal as $key => $val) {
                    $returnVisitDay[$dateKey]['visitCount'] += $val['visitCount'];
                    $returnVisitDay[$dateKey]['visitNumber'] += $val['visitNumber'];
                    $returnVisitDay[$dateKey]['visitNewCount'] += $val['visitNewCount'];
                    $returnVisitDay[$dateKey]['visitReCount'] += $val['visitReCount'];
                    $returnVisitDay[$dateKey]['pv'] += $val['pv'];
                    $returnVisitDay[$dateKey]['visitNewPv'] += $val['visitNewPv'];
                    $returnVisitDay[$dateKey]['visitRePv'] += $val['visitRePv'];
                    foreach ($val['visitInflow'] as $inflowKey => $inflowVal) {
                        $returnVisitDay[$dateKey]['visitInflow'][$inflowKey] += $inflowVal;
                    }
                    foreach ($val['visitOs'] as $osKey => $osVal) {
                        $returnVisitDay[$dateKey]['visitOs'][$osKey] += $osVal;
                    }
                    foreach ($val['visitBrowser'] as $browserKey => $browserVal) {
                        $returnVisitDay[$dateKey]['visitBrowser'][$browserKey] += $browserVal;
                    }
                    $returnVisitDay[$dateKey]['pc']['visitCount'] += $val['pc']['visitCount'];
                    $returnVisitDay[$dateKey]['pc']['visitNumber'] += $val['pc']['visitNumber'];
                    $returnVisitDay[$dateKey]['pc']['visitNewCount'] += $val['pc']['visitNewCount'];
                    $returnVisitDay[$dateKey]['pc']['visitReCount'] += $val['pc']['visitReCount'];
                    $returnVisitDay[$dateKey]['pc']['pv'] += $val['pc']['pv'];
                    $returnVisitDay[$dateKey]['pc']['visitNewPv'] += $val['pc']['visitNewPv'];
                    $returnVisitDay[$dateKey]['pc']['visitRePv'] += $val['pc']['visitRePv'];
                    foreach ($val['pc']['visitInflow'] as $inflowKey => $inflowVal) {
                        $returnVisitDay[$dateKey]['pc']['visitInflow'][$inflowKey] += $inflowVal;
                    }
                    foreach ($val['pc']['visitOs'] as $osKey => $osVal) {
                        $returnVisitDay[$dateKey]['pc']['visitOs'][$osKey] += $osVal;
                    }
                    foreach ($val['pc']['visitBrowser'] as $browserKey => $browserVal) {
                        $returnVisitDay[$dateKey]['pc']['visitBrowser'][$browserKey] += $browserVal;
                    }
                    $returnVisitDay[$dateKey]['mobile']['visitCount'] += $val['mobile']['visitCount'];
                    $returnVisitDay[$dateKey]['mobile']['visitNumber'] += $val['mobile']['visitNumber'];
                    $returnVisitDay[$dateKey]['mobile']['visitNewCount'] += $val['mobile']['visitNewCount'];
                    $returnVisitDay[$dateKey]['mobile']['visitReCount'] += $val['mobile']['visitReCount'];
                    $returnVisitDay[$dateKey]['mobile']['pv'] += $val['mobile']['pv'];
                    $returnVisitDay[$dateKey]['mobile']['visitNewPv'] += $val['mobile']['visitNewPv'];
                    $returnVisitDay[$dateKey]['mobile']['visitRePv'] += $val['mobile']['visitRePv'];
                    foreach ($val['mobile']['visitInflow'] as $inflowKey => $inflowVal) {
                        $returnVisitDay[$dateKey]['mobile']['visitInflow'][$inflowKey] += $inflowVal;
                    }
                    foreach ($val['mobile']['visitOs'] as $osKey => $osVal) {
                        $returnVisitDay[$dateKey]['mobile']['visitOs'][$osKey] += $osVal;
                    }
                    foreach ($val['mobile']['visitBrowser'] as $browserKey => $browserVal) {
                        $returnVisitDay[$dateKey]['mobile']['visitBrowser'][$browserKey] += $browserVal;
                    }
                }
            }
        }

        return $returnVisitDay;
    }

    /**
     * 방문유입통계 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     * @param int   $mallSno
     *
     * @return array $getDataArr 방문유입통계 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitInflowDay($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);

        $getDataArr = $this->getVisitDay($searchDate, $mallSno);
        foreach ($getDataArr as $key => $val) {
            $visitData[$key]['visitInflow'] = $val['visitInflow'];
            $visitData[$key]['pc']['visitInflow'] = $val['pc']['visitInflow'];
            $visitData[$key]['mobile']['visitInflow'] = $val['mobile']['visitInflow'];
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 당일 통계 초기화
            $todayDataArr = [];
            foreach ($this->_visitPolicy['inflowAgent'] as $val) {
                $todayDataArr[$eDate->format('Ymd')]['visitInflow'][$val] = 0;
                $todayDataArr[$eDate->format('Ymd')]['pc']['visitInflow'][$val] = 0;
                $todayDataArr[$eDate->format('Ymd')]['mobile']['visitInflow'][$val] = 0;
            }
            $todayDataArr[$eDate->format('Ymd')]['visitInflow']['기타'] = 0;
            $todayDataArr[$eDate->format('Ymd')]['pc']['visitInflow']['기타'] = 0;
            $todayDataArr[$eDate->format('Ymd')]['mobile']['visitInflow']['기타'] = 0;

            $visit['mallSno'] = $mallSno;
            $visit['year'] = $eDate->format('Y');
            $visit['month'] = $eDate->format('n');
            $visit['day'] = $eDate->format('j');
            $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.visitDevice, vs.visitInflow', null, true);
            foreach ($getStatistics as $key => $val ) {
                $todayDataArr[$eDate->format('Ymd')]['visitInflow'][$val['visitInflow']]++;
                $todayDataArr[$eDate->format('Ymd')][$val['visitDevice']]['visitInflow'][$val['visitInflow']]++;
            }

            $getDataArr = $todayDataArr + $visitData;
        }
        ksort($getDataArr);

        return $getDataArr;
    }

    /**
     * 방문유입검색어통계 데이터
     *
     * @param array $searchData date [0]시작일,[1]종료일 / device / inflow / mallsno
     *
     * @return array $getDataArr 방문유입검색어통계 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitSearchWord($searchData)
    {
        $sDate = new DateTime($searchData['date'][0]);
        $eDate = new DateTime($searchData['date'][1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            throw new \Exception(__('검색 가능 일은 최대 90일 입니다.'));
        }
        if ($searchData['date'][0] > $searchData['date'][1]) {
            throw new \Exception(__('시작일이 종료일보다 클 수 없습니다.'));
        }

        $arrBind = [];
        if ($searchData['device'] != 'all') {
            $arrWhere[] = 'vsw.visitDevice = ?';
            $this->db->bind_param_push($arrBind, 's', $searchData['device']);
        }
        if ($searchData['inflow'] != 'all') {
            $arrWhere[] = 'vsw.visitInflow = ?';
            $this->db->bind_param_push($arrBind, 's', $searchData['inflow']);
        }
        if (!$searchData['mallSno']) {
            $searchData['mallSno'] = gd_isset($searchData['mallSno'], DEFAULT_MALL_NUMBER);
        }
        $arrWhere[] = 'vsw.mallSno = ?';
        $this->db->bind_param_push($arrBind, 'i', $searchData['mallSno']);

        $arrWhere[] = 'vsw.regDt BETWEEN ? AND ?';
        $this->db->bind_param_push($arrBind, 's', $sDate->format('Y-m-d 00:00:00'));
        $this->db->bind_param_push($arrBind, 's', $eDate->format('Y-m-d 23:59:59'));

        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strField = "vsw.regDt, vsw.visitSearchWord";
        $this->db->strOrder = 'vsw.regDt desc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_VISIT_SEARCH_WORD . ' as vsw ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        $visitData = [];
        foreach ($getData as $key => $val) {
            $visitData[$val['visitSearchWord']] += 1;
        }
        arsort($visitData);

        return $visitData;
    }

    /**
     * 방문운영체제통계 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     * @param int   $mallSno
     *
     * @return array $getDataArr 방문유입통계 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitOsDay($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);

        $getDataArr = $this->getVisitDay($searchDate, $mallSno);
        foreach ($getDataArr as $key => $val) {
            $visitData[$key]['visitOs'] = $val['visitOs'];
            $visitData[$key]['pc']['visitOs'] = $val['pc']['visitOs'];
            $visitData[$key]['mobile']['visitOs'] = $val['mobile']['visitOs'];
        }

        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 당일 통계 초기화
            $todayDataArr = [];
            foreach ($this->_visitPolicy['osAgent'] as $val) {
                $todayDataArr[$eDate->format('Ymd')]['visitOs'][$val] = 0;
                $todayDataArr[$eDate->format('Ymd')]['pc']['visitOs'][$val] = 0;
                $todayDataArr[$eDate->format('Ymd')]['mobile']['visitOs'][$val] = 0;
            }
            $todayDataArr[$eDate->format('Ymd')]['visitOs']['기타'] = 0;
            $todayDataArr[$eDate->format('Ymd')]['pc']['visitOs']['기타'] = 0;
            $todayDataArr[$eDate->format('Ymd')]['mobile']['visitOs']['기타'] = 0;

            $visit['mallSno'] = $mallSno;
            $visit['year'] = $eDate->format('Y');
            $visit['month'] = $eDate->format('n');
            $visit['day'] = $eDate->format('j');
            $visit['sort'] = 'vs.memNo asc, vs.visitIP asc, vs.regDt asc';
            $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.mallSno, vs.visitIP, vs.visitSiteKey, vs.memNo, vs.visitDevice, vs.visitOS, vs.visitBrowser, vs.regDt', null, true);

            $prev = [];
            foreach ($getStatistics as $key => $val ) {

                $checkNumberTime = 0;
                foreach ($prev as $checkKey => $checkVal) {
                    if ($checkKey != 'regDt') {
                        if ($val[$checkKey] == $checkVal) {
                            $checkNumberTime += 1;
                        } else {
                            $checkNumberTime -= 1;
                        }
                    }
                }

                if ($checkNumberTime == 10) {
                    if (strtotime($val['regDt']) > (strtotime($prev['regDt']) + $this->_visitPolicy['visitNumberTime'])) {
                        $todayDataArr[$eDate->format('Ymd')]['visitOs'][$val['visitOS']]++;
                        $todayDataArr[$eDate->format('Ymd')][$val['visitDevice']]['visitOs'][$val['visitOS']]++;
                    }
                } else {
                    $todayDataArr[$eDate->format('Ymd')]['visitOs'][$val['visitOS']]++;
                    $todayDataArr[$eDate->format('Ymd')][$val['visitDevice']]['visitOs'][$val['visitOS']]++;
                }

                $prev['mallSno'] = $val['mallSno'];
                $prev['visitIP'] = $val['visitIP'];
                $prev['visitSiteKey'] = $val['visitSiteKey'];
                $prev['memNo'] = $val['memNo'];
                $prev['visitDevice'] = $val['visitDevice'];
                $prev['visitOS'] = $val['visitOS'];
                $prev['visitBrowser'] = $val['visitBrowser'];
                $prev['visitYear'] = $val['visitYear'];
                $prev['visitMonth'] = $val['visitMonth'];
                $prev['visitDay'] = $val['visitDay'];
                $prev['regDt'] = $val['regDt'];
            }
            $getDataArr = $todayDataArr + $visitData;
        }
        ksort($getDataArr);

        return $getDataArr;
    }

    /**
     * 방문브라우저통계 데이터
     *
     * @param array $searchDate [0]시작일,[1]종료일
     * @param int   $mallSno
     *
     * @return array $getDataArr 방문유입통계 정보
     *
     * @throws \Exception
     * @author su
     */
    public function getVisitBrowserDay($searchDate, $mallSno = null)
    {
        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);

        $getDataArr = $this->getVisitDay($searchDate, $mallSno);
        foreach ($getDataArr as $key => $val) {
            $visitData[$key]['visitBrowser'] = $val['visitBrowser'];
            $visitData[$key]['pc']['visitBrowser'] = $val['pc']['visitBrowser'];
            $visitData[$key]['mobile']['visitBrowser'] = $val['mobile']['visitBrowser'];
        }
        $todayDate = new DateTime();
        // 오늘 검색에 따른 당일 통계
        if ($eDate->format('Ymd') >= $todayDate->format('Ymd')) {
            // 당일 통계 초기화
            $todayDataArr = [];
            foreach ($this->_visitPolicy['browserAgent'] as $val) {
                $todayDataArr[$eDate->format('Ymd')]['visitBrowser'][$val] = 0;
                $todayDataArr[$eDate->format('Ymd')]['pc']['visitBrowser'][$val] = 0;
                $todayDataArr[$eDate->format('Ymd')]['mobile']['visitBrowser'][$val] = 0;
            }
            $todayDataArr[$eDate->format('Ymd')]['visitBrowser']['기타'] = 0;
            $todayDataArr[$eDate->format('Ymd')]['pc']['visitBrowser']['기타'] = 0;
            $todayDataArr[$eDate->format('Ymd')]['mobile']['visitBrowser']['기타'] = 0;

            $visit['mallSno'] = $mallSno;
            $visit['year'] = $eDate->format('Y');
            $visit['month'] = $eDate->format('n');
            $visit['day'] = $eDate->format('j');
            $visit['sort'] = 'vs.memNo asc, vs.visitIP asc, vs.regDt asc';
            $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.mallSno, vs.visitIP, vs.visitSiteKey, vs.memNo, vs.visitDevice, vs.visitOS, vs.visitBrowser, vs.regDt', null, true);

            $prev = [];
            foreach ($getStatistics as $key => $val ) {
                $checkNumberTime = 0;
                foreach ($prev as $checkKey => $checkVal) {
                    if ($checkKey != 'regDt') {
                        if ($val[$checkKey] == $checkVal) {
                            $checkNumberTime += 1;
                        } else {
                            $checkNumberTime -= 1;
                        }
                    }
                }

                if ($checkNumberTime == 10) {
                    if (strtotime($val['regDt']) > (strtotime($prev['regDt']) + $this->_visitPolicy['visitNumberTime'])) {
                        $todayDataArr[$eDate->format('Ymd')]['visitBrowser'][$val['visitBrowser']]++;
                        $todayDataArr[$eDate->format('Ymd')][$val['visitDevice']]['visitBrowser'][$val['visitBrowser']]++;
                    }
                } else {
                    $todayDataArr[$eDate->format('Ymd')]['visitBrowser'][$val['visitBrowser']]++;
                    $todayDataArr[$eDate->format('Ymd')][$val['visitDevice']]['visitBrowser'][$val['visitBrowser']]++;
                }

                $prev['mallSno'] = $val['mallSno'];
                $prev['visitIP'] = $val['visitIP'];
                $prev['visitSiteKey'] = $val['visitSiteKey'];
                $prev['memNo'] = $val['memNo'];
                $prev['visitDevice'] = $val['visitDevice'];
                $prev['visitOS'] = $val['visitOS'];
                $prev['visitBrowser'] = $val['visitBrowser'];
                $prev['visitYear'] = $val['visitYear'];
                $prev['visitMonth'] = $val['visitMonth'];
                $prev['visitDay'] = $val['visitDay'];
                $prev['regDt'] = $val['regDt'];
            }

            $getDataArr = $todayDataArr + $visitData;
        }
        ksort($getDataArr);

        return $getDataArr;
    }

    /**
     * 방문자통계 - 메인 탭
     * getTodayMainTabOrder
     *
     * @param $searchData   orderYMD / mallSno
     *
     * @return array
     * @throws \Exception
     */
    public function getTodayMainTabVisit($searchData)
    {
        if ($searchData['mallSno'] == 'all') {
            unset($searchData['mallSno']);
        }

        $sDate = new DateTime($searchData['orderYMD'][0]);
        $eDate = new DateTime($searchData['orderYMD'][1]);
        unset($searchData['orderYMD']);

        $searchData['Ym'][0] = $sDate->format('Ym');
        $searchData['Ym'][1] = $eDate->format('Ym');
        $searchData['sort'] = 'vd.visitYM asc';
        $getField[] = 'vd.visitYM';
        for ($i=1; $i<=31; $i++) {
            $getField[] = 'vd.' . $i;
        }
        $field = implode(', ', $getField);
        $tmpDataJson = $this->getVisitDayStatisticsInfo($searchData, $field, null, true,true);

        $getDataJson = [];
        foreach ($tmpDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['visitYM'] == $searchData['Ym'][0]) {
                $sDay = $sDate->format('d');
                for ($i = 1; $i < $sDay; $i++) {
                    unset($val[$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['visitYM'] == $searchData['Ym'][1]) {
                $eDay = $eDate->format('d');
                for ($i = 31; $i > $eDay; $i--) {
                    unset($val[$i]);
                }
            }
            $getDataJson[$key] = $val;
        }
        unset($tmpDataJson);

        $returnVisitCount = 0;
        foreach ($getDataJson as $key => $val) {
            foreach ($val as $dayKey => $dayVal) {
                if (is_numeric($dayKey)) {
                    unset($dayStatistics);
                    $dayStatistics = json_decode($dayVal, true);
                    $returnVisitCount += $dayStatistics['visitCount'];
                }
            }
        }

        // 오늘 날짜 방문자
        $visit['year'] = date('Y');
        $visit['month'] = date('n');
        $visit['day'] = date('j');
        $visit['sort'] = 'vs.memNo asc, vs.visitIP asc, vs.modDt asc, vs.regDt asc';
        $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.memNo, vs.visitIP' , null, true,true);

        $prevMemberNo = 0;
        $prevVisitIP = 0;
        foreach ($getStatistics as $key => $val) {
            if ($val['memNo'] > 0) {
                if ($prevMemberNo != $val['memNo']) {
                    $returnVisitCount++;
                }
                $prevMemberNo = $val['memNo'];
            } else if ($val['visitIP'] > 0) {
                if ($prevVisitIP != $val['visitIP']) {
                    $returnVisitCount++;
                }
                $prevVisitIP = $val['visitIP'];
            }
        }

        return $returnVisitCount;
    }

    /**
     * 방문자통계 - 메인 테이블 / 차트
     * getTodayMainTableChartVisit
     *
     * @param $searchDate visitYMD
     *
     * @return array
     * @throws \Exception
     */
    public function getTodayMainTableChartVisit($searchDate)
    {
        $startDate = new DateTime($searchDate['visitYMD'][0]);
        $endDate = new DateTime($searchDate['visitYMD'][1]);
        $visitYm[0] = $startDate->format('Ym');
        $visitYm[1] = $endDate->format('Ym');
        $visit['Ym'] = $visitYm;

        $tmpDataJson = $this->getVisitDayStatisticsInfo($visit, '*', '', true,true);

        $getDataJson = [];
        foreach ($tmpDataJson as $key => $val) {
            // 시작일 앞자리 자르기
            if ($val['visitYM'] == $visitYm[0]) {
                $sDay = substr($searchDate['visitYMD'][0], 6, 2);
                for ($i = 1; $i < $sDay; $i++) {
                    unset($val[$i]);
                }
            }
            // 종료일 뒷자리 자르기
            if ($val['visitYM'] == $visitYm[1]) {
                $eDay = substr($searchDate['visitYMD'][1], 6, 2);
                for ($i = 31; $i > $eDay; $i--) {
                    unset($val[$i]);
                }
            }
            $getDataJson[$key] = $val;
        }
        unset($tmpDataJson);

        $getDataArr = [];
        foreach ($getDataJson as $dataKey => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (is_numeric($key) && $val) {
                    $getDataArr[$dataVal['visitYM'] . sprintf("%02d", $key)][$dataVal['mallSno']] = json_decode($val, true);
                }
            }
        }

        // 데이터 초기화
        $diffDay = $endDate->diff($startDate)->days;
        $returnVisitDay = [];
        for ($i = 0; $i <= $diffDay; $i++) {
            $searchDt = new DateTime($searchDate['visitYMD'][0]);
            $searchDt = $searchDt->modify('+' . $i . ' day');
            $returnVisitDay[$searchDt->format('Ymd')]['visitCount'] = 0;
            $returnVisitDay[$searchDt->format('Ymd')]['pv'] = 0;
            $returnVisitDay[$searchDt->format('Ymd')]['pc']['visitCount'] = 0;
            $returnVisitDay[$searchDt->format('Ymd')]['pc']['pv'] = 0;
            $returnVisitDay[$searchDt->format('Ymd')]['mobile']['visitCount'] = 0;
            $returnVisitDay[$searchDt->format('Ymd')]['mobile']['pv'] = 0;
        }

        foreach ($getDataArr as $dateKey => $dateVal) {
            foreach ($dateVal as $key => $val) {
                $returnVisitDay[$dateKey]['visitCount'] += $val['visitCount'];
                $returnVisitDay[$dateKey]['pv'] += $val['pv'];
                $returnVisitDay[$dateKey]['pc']['visitCount'] += $val['pc']['visitCount'];
                $returnVisitDay[$dateKey]['pc']['pv'] += $val['pc']['pv'];
                $returnVisitDay[$dateKey]['mobile']['visitCount'] += $val['mobile']['visitCount'];
                $returnVisitDay[$dateKey]['mobile']['pv'] += $val['mobile']['pv'];
            }
        }

        // 당일 방문자
        $visit['year'] = date('Y');
        $visit['month'] = date('n');
        $visit['day'] = date('j');
        $getStatistics = $this->getVisitStatisticsTodayInfo($visit, null, null, true);
        $returnToday[date('Ymd')] = [
            'visitCount' => gd_isset($getStatistics[0]['visitCount'], 0) + gd_isset($getStatistics[1]['visitCount'], 0),
            'pv' => gd_isset($getStatistics[0]['pv'], 0) + gd_isset($getStatistics[1]['pv'], 0),
            'pc' => [
                'visitCount' => gd_isset($getStatistics[0]['visitCount'], 0),
                'pv' => gd_isset($getStatistics[0]['pv'], 0),
            ],
            'mobile' => [
                'visitCount' => gd_isset($getStatistics[1]['visitCount'], 0),
                'pv' => gd_isset($getStatistics[1]['pv'], 0),
            ],
        ];
        $returnVisit = $returnToday + $returnVisitDay;
        ksort($returnVisit);

        return $returnVisit;
    }

    /**
     * 해외몰 별 사용했던 여부 저장
     * setVisitMallUsedCheck
     * @param $mallSno
     */
    public function setVisitMallUsedCheck($mallSno)
    {
        $visitUsedCheck = ComponentUtils::getPolicy('mall.used', $mallSno);
        if ($visitUsedCheck['visit'] === 'n') {
            $config['visit'] = 'y';
            ComponentUtils::setPolicy('mall.used', $config, true, $mallSno);
        }
    }

    /**
     * 방문 저장
     *
     * @param $mallSno
     *
     * @author su
     */
    public function saveVisit($mallSno = DEFAULT_MALL_NUMBER)
    {
        if (!$mallSno) {
            $mallSno = DEFAULT_MALL_NUMBER;
        }

        // 해외몰 별 사용했던 여부 저장
        $this->setVisitMallUsedCheck($mallSno);

        // 방문통계 저장 여부 (true-저장, false-저장안함)
        $checkVisit = false;
        // 방문통계 방문횟수 신규 저장 여부 (true-insert, false-update)
        $checkSaveVisit = false;

        // 방문통계 제외 페이지 체크
        if ($this->_visitPolicy['exceptPage']) {
            $pattern = '/' . implode('|', $this->_visitPolicy['exceptPage']) . '/';
            if (preg_match($pattern, Request::getPhpSelf()) == 0) {
                $checkVisit = true;
            }
        }

        if ($checkVisit) {
            $visit = [];
            $visit['mallSno'] = $mallSno;
            $visit['IP'] = Request::getRemoteAddress();
            $visit['siteKey'] = Session::get('siteKey');
            if (Session::get('member.memNo')) {
                $visit['memNo'] = Session::get('member.memNo');
            } else {
                $visit['memNo'] = 0;
            }
            $visit['referer'] = Request::getReferer();
            //            $visit['inflow'] = $this->getAgentInflow(); // 유입경로를 제외한 일별 정보를 가져오기 위한 주석 (유입경로는 방문횟수 증가는 관련없기에)
            $visit['device'] = $this->getDevice();
            $visit['OS'] = $this->getAgentOS();
            $visit['browser'] = $this->getAgentBrowser();
            $visit['year'] = date('Y');
            $visit['month'] = date('n');
            $visit['day'] = date('j');
            //            $visit['hour'] = date('G'); // 시간을 제외한 일별 정보를 가져오기 위한 주석 (시간은 방문횟수 증가는 관련없기에)
            $visit['pageView'] = 1;

            // 안드로이드 버젼에 따른 기본 브라우저가 특정 agent 가 없으므로 safari 로 나오면 chrome 로 처리
            if (strtolower($visit['OS']) == 'android' && strtolower($visit['browser']) == 'safari') {
                $visit['browser'] = 'Chrome';
            }
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_STATISTICS . " SET visitSiteKey=?, visitIP=INET_ATON(?), memNo=?, visitReferer=?, visitInflow=?, visitDevice=?, visitOS=?, visitBrowser=?, visitYear=?, visitMonth=?, visitDay=?, visitHour=?, visitPageView=?, regDt=now(), mallSno = ?";
            $this->db->bind_param_push($arrBind, 's', $visit['siteKey']);
            $this->db->bind_param_push($arrBind, 's', $visit['IP']);
            $this->db->bind_param_push($arrBind, 'i', $visit['memNo']);
            $this->db->bind_param_push($arrBind, 's', $visit['referer']);
            $this->db->bind_param_push($arrBind, 's', $this->getAgentInflow()); // $visit['inflow'] 은 일별 정보를 가져오기 위한 주석처리로 직접 입력
            $this->db->bind_param_push($arrBind, 's', $visit['device']);
            $this->db->bind_param_push($arrBind, 's', $visit['OS']);
            $this->db->bind_param_push($arrBind, 's', $visit['browser']);
            $this->db->bind_param_push($arrBind, 'i', $visit['year']);
            $this->db->bind_param_push($arrBind, 'i', $visit['month']);
            $this->db->bind_param_push($arrBind, 'i', $visit['day']);
            $this->db->bind_param_push($arrBind, 'i', date('G')); // $visit['hour'] 은 일별 정보를 가져오기 위한 주석처리로 직접 입력
            $this->db->bind_param_push($arrBind, 'i', $visit['pageView']);
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }

    /**
     * 방문 유입검색어 저장
     *
     * @param $mallSno
     *
     * @author su
     */
    public function saveVisitSearchWord($mallSno = DEFAULT_MALL_NUMBER)
    {
        if (!$mallSno) {
            $mallSno = DEFAULT_MALL_NUMBER;
        }

        if ($this->getSearchword()) {
            $inflow = $this->getAgentInflow();
            if ($inflow == '다음') {
                $userReferer = strtolower(Request::getReferer());
                $userReferer = urldecode($userReferer);
                $parse = parse_url($userReferer);
                gd_isset($parse['host']);
                gd_isset($parse['path']);
                gd_isset($parse['query']);
                if ($parse['path'] == '/nate') {
                    $inflow = '네이트';
                }
            }

            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_SEARCH_WORD . " SET visitYMD=?, mallSno=?, visitDevice=?, visitReferer=?, visitInflow=?, visitSearchWord=?, regDt=now()";
            $this->db->bind_param_push($arrBind, 'i', date('Ymd'));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_param_push($arrBind, 's', $this->getDevice());
            $this->db->bind_param_push($arrBind, 's', Request::getReferer());
            $this->db->bind_param_push($arrBind, 's', $inflow);
            $this->db->bind_param_push($arrBind, 's', $this->getSearchword());
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }
    }

    /**
     * setUniqueUserStatistics
     * 고유 방문자통계 정리(순방문자, 유일 IP AND 유일 memNo)
     * 비회원 IP + 회원 memNo = 총 고유 방문자수
     *
     * @param $mallSno
     *
     * @return bool
     */
    public function setUniqueUserStatistics($mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visit['mallSno'] = $mallSno;
        $visit['year'] = date('Y', $this->visitStatisticsTime);
        $visit['month'] = date('n', $this->visitStatisticsTime);
        $visit['day'] = date('j', $this->visitStatisticsTime);
        $visit['sort'] = 'vs.regDt asc';
        $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.visitIP, vs.memNo, vs.visitHour');
        if ($getStatistics) {
            $visitIPArr = array_column($getStatistics, 'visitIP');
            $visitIPArr = array_unique($visitIPArr);
            $memNoArr = array_column($getStatistics, 'memNo');
            $memNoArr = array_unique($memNoArr);
            foreach ($visitIPArr as $key => $val) {
                try {
                    $arrBind = [];
                    $strSQL = "INSERT INTO " . DB_VISIT_UNIQUE_USER . " SET visitIP=?, mallSno=?, visitYear=?, visitMonth=?, visitDay=?, visitHour=?, regDt=now()";
                    $this->db->bind_param_push($arrBind, 'i', $val);
                    $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
                    $this->db->bind_param_push($arrBind, 'i', $visit['year']);
                    $this->db->bind_param_push($arrBind, 'i', $visit['month']);
                    $this->db->bind_param_push($arrBind, 'i', $visit['day']);
                    $this->db->bind_param_push($arrBind, 'i', $getStatistics[$key]['visitHour']);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($arrBind);
                } catch (\Exception $e) {
                }
            }
            foreach ($memNoArr as $key => $val) {
                try {
                    $arrBind = [];
                    $strSQL = "INSERT INTO " . DB_VISIT_UNIQUE_USER . " SET memNo=?, mallSno=?, visitYear=?, visitMonth=?, visitDay=?, visitHour=?, regDt=now()";
                    $this->db->bind_param_push($arrBind, 'i', $val);
                    $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
                    $this->db->bind_param_push($arrBind, 'i', $visit['year']);
                    $this->db->bind_param_push($arrBind, 'i', $visit['month']);
                    $this->db->bind_param_push($arrBind, 'i', $visit['day']);
                    $this->db->bind_param_push($arrBind, 'i', $getStatistics[$key]['visitHour']);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($arrBind);
                } catch (\Exception $e) {
                }
            }
        }

        return true;
    }

    /**
     * 일별 방문통계 정리 - Deprecated
     *
     * Deprecated - 2017-02-23
     *
     * @param $mallSno
     *
     * @return bool
     */
    public function setDayStatistics($mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visit['mallSno'] = $mallSno;
        $visit['year'] = date('Y', $this->visitStatisticsTime);
        $visit['month'] = date('n', $this->visitStatisticsTime);
        $visit['day'] = date('j', $this->visitStatisticsTime);
        $visit['sort'] = 'vs.modDt, vs.regDt desc';
        $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.visitIP, vs.memNo, vs.regDt, vs.modDt');
        $visitIPArr = array_column($getStatistics, 'visitIP');
        $visitIPArr = array_unique($visitIPArr);
        $visitIPCount = count($visitIPArr);
        $memNoArr = array_column($getStatistics, 'memNo');
        $memNoArr = array_unique($memNoArr);
        $memNoCount = count($memNoArr);

        $getStatisticsData = $this->getVisitStatisticsInfo($visit, 'count(visitIP) as visitNumber, sum(vs.visitPageView) as pv');

        if ($visitIPCount + $memNoCount > 0) {
            $visitCount = $visitIPCount + $memNoCount; // 일 방문자수
        } else {
            $visitCount = 0;
        }
        $visitNewCount = 0; // 일 신규 방문자수
        $visitReCount = 0; // 일 재 방문자수
        if ($getStatisticsData['visitNumber'] > 0) {
            $visitNumber = $getStatisticsData['visitNumber']; // 일 방문횟수
        } else {
            $visitNumber = 0;
        }
        if ($getStatisticsData['pv'] > 0) {
            $pv = $getStatisticsData['pv']; // 일 페이지뷰
        } else {
            $pv = 0;
        }
        $newCountPv = 0; // 일 신규방문자 페이지뷰
        $reCountPv = 0; // 일 재방문자 페이지뷰

        foreach ($visitIPArr as $key => $val) {
            $visitSub['inetIP'] = $val;
            $visitSub['mallSno'] = $mallSno;
            $visit['sort'] = 'vs.modDt, vs.regDt desc';
            $visit['limit'] = '0,2';
            $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt, vs.visitPageView', '', true);
            $getDataCount = count($getData);
            if ($getDataCount > 1) {
                if (strtotime($getStatistics[$key]['modDt']) > 0) {
                    if (strtotime($getStatistics[$key]['modDt']) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $newCountPv += $getData[0]['visitPageView'];
                        $visitNewCount++;
                    } else {
                        $reCountPv += $getData[0]['visitPageView'];
                        $visitReCount++;
                    }
                } else if (strtotime($getStatistics[$key]['regDt']) > 0) {
                    if (strtotime($getStatistics[$key]['regDt']) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $newCountPv += $getData[0]['visitPageView'];
                        $visitNewCount++;
                    } else {
                        $reCountPv += $getData[0]['visitPageView'];
                        $visitReCount++;
                    }
                }
            } else {
                $newCountPv += $getData[0]['visitPageView'];
                $visitNewCount++;
            }
        }
        foreach ($memNoArr as $key => $val) {
            $visitSub['memNo'] = $val;
            $visitSub['mallSno'] = $mallSno;
            $visit['sort'] = 'vs.modDt, vs.regDt desc';
            $visit['limit'] = '0,2';
            $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt, vs.visitPageView', '', true);
            $getDataCount = count($getData);
            if ($getDataCount > 1) {
                if (strtotime($getStatistics[$key]['modDt']) > 0) {
                    if (strtotime($getStatistics[$key]['modDt']) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $newCountPv += $getData[0]['visitPageView'];
                        $visitNewCount++;
                    } else {
                        $reCountPv += $getData[0]['visitPageView'];
                        $visitReCount++;
                    }
                } else if (strtotime($getStatistics[$key]['regDt']) > 0) {
                    if (strtotime($getStatistics[$key]['regDt']) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $newCountPv += $getData[0]['visitPageView'];
                        $visitNewCount++;
                    } else {
                        $reCountPv += $getData[0]['visitPageView'];
                        $visitReCount++;
                    }
                }
            } else {
                $newCountPv += $getData[0]['visitPageView'];
                $visitNewCount++;
            }
        }
        $visitInflow = [];
        foreach ($this->_visitPolicy['inflowAgent'] as $key => $val) {
            $visit['inflow'] = $val;
            $getInflowData = $this->getVisitStatisticsInfo($visit, 'count(vs.visitIP) as visitInflow');
            $visitInflow[$val] = $getInflowData['visitInflow'];
            unset($visit['inflow']);
        }
        $visit['inflow'] = __('기타');
        $getInflowData = $this->getVisitStatisticsInfo($visit, 'count(vs.visitIP) as visitInflow');
        $visitInflow['기타'] = $getInflowData['visitInflow'];
        unset($visit['inflow']);

        $visitOs = [];
        foreach ($this->_visitPolicy['osAgent'] as $key => $val) {
            $visit['OS'] = $val;
            $getOsData = $this->getVisitStatisticsInfo($visit, 'count(vs.visitIP) as visitOs');
            $visitOs[$val] = $getOsData['visitOs'];
            unset($visit['OS']);
        }
        $visit['OS'] = '기타';
        $getOsData = $this->getVisitStatisticsInfo($visit, 'count(vs.visitIP) as visitOs');
        $visitOs['etc'] = $getOsData['visitOs'];
        unset($visit['OS']);

        $visitBrowser = [];
        foreach ($this->_visitPolicy['browserAgent'] as $key => $val) {
            $visit['browser'] = $val;
            $getBrowserData = $this->getVisitStatisticsInfo($visit, 'count(vs.visitIP) as visitBrowser');
            $visitBrowser[$val] = $getBrowserData['visitBrowser'];
            unset($visit['browser']);
        }
        $visit['browser'] = '기타';
        $getBrowserData = $this->getVisitStatisticsInfo($visit, 'count(vs.visitIP) as visitBrowser');
        $visitBrowser['etc'] = $getBrowserData['visitBrowser'];
        unset($visit['browser']);

        $visitDayArr = [
            'visitCount'    => $visitCount, // 일 방문자수
            'visitNumber'   => $visitNumber, // 일 방문횟수
            'visitNewCount' => $visitNewCount, // 일 신규방문자수
            'visitReCount'  => $visitReCount, // 일 재방문자수
            'pv'            => $pv, // 일 페이지뷰 수
            'newCountPv'    => $newCountPv, // 일 신규방문자 페이지뷰 수
            'reCountPv'     => $reCountPv, // 일 재방문자 페이지뷰 수
            'visitInflow'   => $visitInflow, // 일 유입경로 수
            'visitOs'       => $visitOs, // 일 OS 수
            'visitBrowser'  => $visitBrowser, // 일 Browser 수
        ];
        $visitDayJson = json_encode($visitDayArr, JSON_UNESCAPED_UNICODE);

        $visitDay['Ym'] = date('Ym', $this->visitStatisticsTime);
        $visitDay['mallSno'] = $mallSno;

        $getCheckDayStatistics = $this->getVisitDayStatisticsInfo($visitDay, 'vd.visitYM');

        if ($getCheckDayStatistics) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_VISIT_DAY . " SET `" . $visit['day'] . "`=?, modDt=now() WHERE `visitYM`=? AND `mallSno`=?";
            $this->db->bind_param_push($arrBind, 's', $visitDayJson);
            $this->db->bind_param_push($arrBind, 'i', date('Ym', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_DAY . " SET `visitYM`=?, `mallSno`=?, `" . $visit['day'] . "`=?, `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', date('Ym', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_param_push($arrBind, 's', $visitDayJson);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        return true;
    }

    /**
     * 시간별 방문통계 정리 - Deprecated
     *
     * Deprecated - 2017-02-23
     *
     * @param $mallSno
     *
     * @return bool
     */
    public function setHourStatistics($mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visit['mallSno'] = $mallSno;
        $visit['year'] = date('Y', $this->visitStatisticsTime);
        $visit['month'] = date('n', $this->visitStatisticsTime);
        $visit['day'] = date('j', $this->visitStatisticsTime);
        $visitHourJson = [];
        $updateColumn = [];
        for ($i = 0; $i < 24; $i++) {
            unset($visit['hour']);
            $visit['hour'] = $i;
            $visit['sort'] = 'vs.modDt, vs.regDt desc';
            $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.visitIP, vs.memNo, vs.regDt, vs.modDt');
            $visitIPArr = array_column($getStatistics, 'visitIP');
            $visitIPArr = array_unique($visitIPArr);
            $visitIPCount = count($visitIPArr);
            $memNoArr = array_column($getStatistics, 'memNo');
            $memNoArr = array_unique($memNoArr);
            $memNoCount = count($memNoArr);

            $getStatisticsData = $this->getVisitStatisticsInfo($visit, 'count(visitIP) as visitNumber, sum(vs.visitPageView) as pv');

            if ($visitIPCount + $memNoCount > 0) {
                $visitCount = $visitIPCount + $memNoCount; // 시간별 방문자수
            } else {
                $visitCount = 0;
            }
            $visitNewCount = 0; // 시간별 신규 방문자수
            $visitReCount = 0; // 시간별 재 방문자수
            if ($getStatisticsData['visitNumber'] > 0) {
                $visitNumber = $getStatisticsData['visitNumber']; // 시간별 방문횟수
            } else {
                $visitNumber = 0;
            }
            if ($getStatisticsData['pv'] > 0) {
                $pv = $getStatisticsData['pv']; // 시간별 페이지뷰
            } else {
                $pv = 0;
            }

            foreach ($visitIPArr as $key => $val) {
                unset($visitSub);
                $visitSub['inetIP'] = $val;
                $visitSub['mallSno'] = $mallSno;
                $visit['sort'] = 'vs.modDt, vs.regDt desc';
                $visit['limit'] = '0,2';
                $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt');
                $getDataCount = count($getData);
                if ($getDataCount > 1) {
                    if (strtotime($getStatistics[$key]['modDt']) > 0) {
                        if (strtotime($getStatistics[$key]['modDt']) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                            $visitNewCount++;
                        } else {
                            $visitReCount++;
                        }
                    } else if (strtotime($getStatistics[$key]['regDt']) > 0) {
                        if (strtotime($getStatistics[$key]['regDt']) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                            $visitNewCount++;
                        } else {
                            $visitReCount++;
                        }
                    }
                } else {
                    $visitNewCount++;
                }
            }
            foreach ($memNoArr as $key => $val) {
                unset($visitSub);
                $visitSub['memNo'] = $val;
                $visitSub['mallSno'] = $mallSno;
                $visit['sort'] = 'vs.modDt, vs.regDt desc';
                $visit['limit'] = '0,2';
                $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt');
                $getDataCount = count($getData);
                if ($getDataCount > 1) {
                    if (strtotime($getStatistics[$key]['modDt']) > 0) {
                        if (strtotime($getStatistics[$key]['modDt']) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                            $visitNewCount++;
                        } else {
                            $visitReCount++;
                        }
                    } else if (strtotime($getStatistics[$key]['regDt']) > 0) {
                        if (strtotime($getStatistics[$key]['regDt']) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                            $visitNewCount++;
                        } else {
                            $visitReCount++;
                        }
                    }
                } else {
                    $visitNewCount++;
                }
            }
            $visitHourArr = [
                'visitCount'    => $visitCount, // 일 방문자수
                'visitNumber'   => $visitNumber, // 일 방문횟수
                'visitNewCount' => $visitNewCount, // 일 신규방문자수
                'visitReCount'  => $visitReCount, // 일 재방문자수
                'pv'            => $pv, // 일 페이지뷰 수
            ];
            $visitHourJson[$i] = json_encode($visitHourArr, JSON_UNESCAPED_UNICODE);
            $updateColumn[$i] = '`' . $i . '`=?';
        }

        $visitHour['Ymd'] = date('Ymd', $this->visitStatisticsTime);
        $visitHour['mallSno'] = $mallSno;
        $getCheckHourStatistics = $this->getVisitHourStatisticsInfo($visitHour, 'vh.visitYMD');
        if ($getCheckHourStatistics) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_VISIT_HOUR . " SET " . implode(',', $updateColumn) . ", `modDt`=now() WHERE `visitYMD`=? AND `mallSno`=?";
            foreach ($visitHourJson as $val) {
                $this->db->bind_param_push($arrBind, 's', $val);
            }
            $this->db->bind_param_push($arrBind, 'i', date('Ymd', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_HOUR . " SET `visitYMD`=?, `mallSno`=?, " . implode(',', $updateColumn) . ", `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', date('Ymd', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            foreach ($visitHourJson as $val) {
                $this->db->bind_param_push($arrBind, 's', $val);
            }
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        return true;
    }

    /**
     * 월별 방문통계 정리 - Deprecated
     *
     * Deprecated - 2017-02-23
     *
     * @return bool
     */
    public function setMonthStatistics($mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visit['mallSno'] = $mallSno;
        $visit['year'] = date('Y', $this->visitStatisticsTime);
        $visit['month'] = date('n', $this->visitStatisticsTime);
        $visit['sort'] = 'vs.modDt, vs.regDt desc';
        $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.visitIP, vs.memNo, vs.regDt, vs.modDt');
        $visitIPArr = array_column($getStatistics, 'visitIP');
        $visitIPArr = array_unique($visitIPArr);
        $visitIPCount = count($visitIPArr);
        $memNoArr = array_column($getStatistics, 'memNo');
        $memNoArr = array_unique($memNoArr);
        $memNoCount = count($memNoArr);

        $getStatisticsData = $this->getVisitStatisticsInfo($visit, 'count(visitIP) as visitNumber, sum(vs.visitPageView) as pv');

        if ($visitIPCount + $memNoCount > 0) {
            $visitCount = $visitIPCount + $memNoCount; // 월 방문자수
        } else {
            $visitCount = 0;
        }
        $visitNewCount = 0; // 월 신규 방문자수
        $visitReCount = 0; // 월 재 방문자수
        if ($getStatisticsData['visitNumber'] > 0) {
            $visitNumber = $getStatisticsData['visitNumber']; // 월 방문횟수
        } else {
            $visitNumber = 0;
        }
        if ($getStatisticsData['pv'] > 0) {
            $pv = $getStatisticsData['pv']; // 월 페이지뷰
        } else {
            $pv = 0;
        }

        foreach ($visitIPArr as $key => $val) {
            unset($visitSub);
            $visitSub['inetIP'] = $val;
            $visitSub['mallSno'] = $mallSno;
            $visit['sort'] = 'vs.modDt, vs.regDt desc';
            $visit['limit'] = '0,2';
            $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt');
            $getDataCount = count($getData);
            if ($getDataCount > 1) {
                if (strtotime($getStatistics[$key]['modDt']) > 0) {
                    if (strtotime($getStatistics[$key]['modDt']) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $visitNewCount++;
                    } else {
                        $visitReCount++;
                    }
                } else if (strtotime($getStatistics[$key]['regDt']) > 0) {
                    if (strtotime($getStatistics[$key]['regDt']) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $visitNewCount++;
                    } else {
                        $visitReCount++;
                    }
                }
            } else {
                $visitNewCount++;
            }
        }
        foreach ($memNoArr as $key => $val) {
            unset($visitSub);
            $visitSub['memNo'] = $val;
            $visitSub['mallSno'] = $mallSno;
            $visit['sort'] = 'vs.modDt, vs.regDt desc';
            $visit['limit'] = '0,2';
            $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt');
            $getDataCount = count($getData);
            if ($getDataCount > 1) {
                if (strtotime($getStatistics[$key]['modDt']) > 0) {
                    if (strtotime($getStatistics[$key]['modDt']) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $visitNewCount++;
                    } else {
                        $visitReCount++;
                    }
                } else if (strtotime($getStatistics[$key]['regDt']) > 0) {
                    if (strtotime($getStatistics[$key]['regDt']) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                        $visitNewCount++;
                    } else {
                        $visitReCount++;
                    }
                }
            } else {
                $visitNewCount++;
            }
        }
        $visitMonthArr = [
            'visitCount'    => $visitCount, // 월 방문자수
            'visitNumber'   => $visitNumber, // 월 방문횟수
            'visitNewCount' => $visitNewCount, // 월 신규방문자수
            'visitReCount'  => $visitReCount, // 월 재방문자수
            'pv'            => $pv, // 월 페이지뷰 수
        ];
        $visitMonthJson = json_encode($visitMonthArr, JSON_UNESCAPED_UNICODE);

        $visitMonth['Y'] = date('Y', $this->visitStatisticsTime);
        $visitMonth['mallSno'] = $mallSno;
        $getCheckMonthStatistics = $this->getVisitMonthStatisticsInfo($visitMonth, 'vm.visitY');

        if ($getCheckMonthStatistics) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_VISIT_MONTH . " SET `" . $visit['month'] . "`=?, modDt=now() WHERE `visitY`=? AND `mallSno`=?";
            $this->db->bind_param_push($arrBind, 's', $visitMonthJson);
            $this->db->bind_param_push($arrBind, 'i', date('Y', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_MONTH . " SET `visitY`=?, `mallSno`=?, `" . $visit['month'] . "`=?, `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', date('Y', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_param_push($arrBind, 's', $visitMonthJson);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        return true;
    }

    /**
     * getVisitMemberCheckCount
     * 회원 회원고유번호 당 신규방문자 / 재방문자 처리
     *
     * @param $memberNo
     * @param $memberLastTime
     * @param null $mallSno
     * @return mixed
     */
    public function getVisitMemberCheckCount($memberNo, $memberLastTime, $mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visitSub['memNo'] = $memberNo;
        $visitSub['mallSno'] = $mallSno;
        $visitSub['sort'] = 'vs.modDt desc, vs.regDt desc';
        $visitSub['limit'] = '0, 2';
        $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt, vs.visitPageView', '', true);
        $getDataCount = count($getData);

        $return['new']['pv'] = 0;
        $return['new']['count'] = 0;
        $return['re']['pv'] = 0;
        $return['re']['count'] = 0;
        if ($getDataCount > 1) {
            if (strtotime($getData[1]['modDt']) > 0) {
                if (strtotime($memberLastTime) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                    $return['new']['pv'] = $getData[0]['visitPageView'];
                    $return['new']['count']++;
                } else {
                    $return['re']['pv'] = $getData[0]['visitPageView'];
                    $return['re']['count']++;
                }
            } else {
                if (strtotime($memberLastTime) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                    $return['new']['pv'] = $getData[0]['visitPageView'];
                    $return['new']['count']++;
                } else {
                    $return['re']['pv'] = $getData[0]['visitPageView'];
                    $return['re']['count']++;
                }
            }
        } else {
            $return['new']['pv'] = $getData[0]['visitPageView'];
            $return['new']['count']++;
        }

        return $return;
    }

    /**
     * getVisitIpCheckCount
     * 비회원 IP 당 신규방문자 / 재방문자 처리
     *
     * @param $ip
     * @param $ipLastTime
     * @param null $mallSno
     * @return mixed
     */
    public function getVisitIpCheckCount($ip, $ipLastTime, $mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visitSub['inetIP'] = $ip;
        $visitSub['notMemNo'] = true;
        $visitSub['mallSno'] = $mallSno;
        $visitSub['sort'] = 'vs.modDt desc, vs.regDt desc';
        $visitSub['limit'] = '0, 2';
        $getData = $this->getVisitStatisticsInfo($visitSub, 'vs.visitNo, vs.regDt, vs.modDt, vs.visitPageView', '', true);
        $getDataCount = count($getData);

        $return['new']['pv'] = 0;
        $return['new']['count'] = 0;
        $return['re']['pv'] = 0;
        $return['re']['count'] = 0;
        if ($getDataCount > 1) {
            if (strtotime($getData[1]['modDt']) > 0) {
                if (strtotime($ipLastTime) > (strtotime($getData[1]['modDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                    $return['new']['pv'] = $getData[0]['visitPageView'];
                    $return['new']['count']++;
                } else {
                    $return['re']['pv'] = $getData[0]['visitPageView'];
                    $return['re']['count']++;
                }
            } else {
                if (strtotime($ipLastTime) > (strtotime($getData[1]['regDt']) + $this->_visitPolicy['visitNewCountTime'])) {
                    $return['new']['pv'] = $getData[0]['visitPageView'];
                    $return['new']['count']++;
                } else {
                    $return['re']['pv'] = $getData[0]['visitPageView'];
                    $return['re']['count']++;
                }
            }
        } else {
            $return['new']['pv'] = $getData[0]['visitPageView'];
            $return['new']['count']++;
        }

        return $return;
    }

    /**
     * setVisitStatistics
     * 일별 / 시간별 방문통계 정리
     *
     * @param $mallSno
     *
     * @return bool
     */
    public function setVisitStatistics($mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visit['mallSno'] = $mallSno;
        $visit['year'] = date('Y', $this->visitStatisticsTime);
        $visit['month'] = date('n', $this->visitStatisticsTime);
        $visit['day'] = date('j', $this->visitStatisticsTime);
        $visit['sort'] = 'vs.memNo asc, vs.visitIP asc, vs.visitHour asc';
        $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.*' , null, true);

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();

        $visitStatistics['day'] = $visitColumn;
        $visitStatistics['day']['pc'] = $visitColumn;
        $visitStatistics['day']['mobile'] = $visitColumn;
        for ($i = 0; $i <= 23; $i++) {
            $visitStatistics['hour'][$i] = $visitColumn;
            $visitStatistics['hour'][$i]['pc'] = $visitColumn;
            $visitStatistics['hour'][$i]['mobile'] = $visitColumn;
        }

        $prev = [];
        $prevMemberNo = 0;
        $prevVisitIP = 0;
        // 방문자 데이터 가공
        foreach ($getStatistics as $key => $val) {
            if ($val['memNo'] > 0) {
                if (strtotime($val['modDt']) > 0) {
                    $lastTime = $val['modDt'];
                } else if (strtotime($val['regDt']) > 0) {
                    $lastTime = $val['regDt'];
                } else {
                    $lastTime = $val['regDt'];
                }
                if ($prevMemberNo != $val['memNo']) {
                    $memberCheckArr = $this->getVisitMemberCheckCount($val['memNo'], $lastTime, $mallSno);
                    // 일별 총
                    $visitStatistics['day']['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['day']['visitNewCount'] += $memberCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['day']['visitNewPv'] += $memberCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['day']['visitReCount'] += $memberCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['day']['visitRePv'] += $memberCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 일별 디바이스
                    $visitStatistics['day'][$val['visitDevice']]['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['day'][$val['visitDevice']]['visitNewCount'] += $memberCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['day'][$val['visitDevice']]['visitNewPv'] += $memberCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['day'][$val['visitDevice']]['visitReCount'] += $memberCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['day'][$val['visitDevice']]['visitRePv'] += $memberCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 시간별 총
                    $visitStatistics['hour'][$val['visitHour']]['visitCount']++; // 일 시간별 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['hour'][$val['visitHour']]['visitNewCount'] += $memberCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']]['visitNewPv'] += $memberCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['hour'][$val['visitHour']]['visitReCount'] += $memberCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']]['visitRePv'] += $memberCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 시간별 디바이스
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitCount']++; // 일 시간별 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitNewCount'] += $memberCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitNewPv'] += $memberCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitReCount'] += $memberCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitRePv'] += $memberCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                }
                $prevMemberNo = $val['memNo'];
            } else if ($val['visitIP'] > 0) {
                if (strtotime($val['modDt']) > 0) {
                    $lastTime = $val['modDt'];
                } else if (strtotime($val['regDt']) > 0) {
                    $lastTime = $val['regDt'];
                } else {
                    $lastTime = $val['regDt'];
                }
                if ($prevVisitIP != $val['visitIP']) {
                    $ipCheckArr = $this->getVisitIpCheckCount($val['visitIP'], $lastTime, $mallSno);
                    // 일별 총
                    $visitStatistics['day']['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['day']['visitNewCount'] += $ipCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['day']['visitNewPv'] += $ipCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['day']['visitReCount'] += $ipCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['day']['visitRePv'] += $ipCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 일별 디바이스
                    $visitStatistics['day'][$val['visitDevice']]['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['day'][$val['visitDevice']]['visitNewCount'] += $ipCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['day'][$val['visitDevice']]['visitNewPv'] += $ipCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['day'][$val['visitDevice']]['visitReCount'] += $ipCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['day'][$val['visitDevice']]['visitRePv'] += $ipCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 시간별 총
                    $visitStatistics['hour'][$val['visitHour']]['visitCount']++; // 일 시간별 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['hour'][$val['visitHour']]['visitNewCount'] += $ipCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']]['visitNewPv'] += $ipCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['hour'][$val['visitHour']]['visitReCount'] += $ipCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']]['visitRePv'] += $ipCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 시간별 디바이스
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitCount']++; // 일 시간별 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitNewCount'] += $ipCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitNewPv'] += $ipCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitReCount'] += $ipCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitRePv'] += $ipCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                }
                $prevVisitIP = $val['visitIP'];
            }

            $checkNumberTime = 0;
            foreach ($prev as $checkKey => $checkVal) {
                if ($checkKey != 'regDt') {
                    if ($val[$checkKey] == $checkVal) {
                        $checkNumberTime += 1;
                    } else {
                        $checkNumberTime -= 1;
                    }
                }
            }

            // 기존업체들의 경우, 절반으로 방문횟수가 줄어드는 부분에 대하여 상당한 클레임이 예상, 신규상점만 적용 (https://nhnent.dooray.com/project/posts/2755106752179536161)
            $visitHourDuplicationFl = gd_installed_date('2020-10-14') == false;

            if ($checkNumberTime == 10) {
                if (strtotime($val['regDt']) > (strtotime($prev['regDt']) + $this->_visitPolicy['visitNumberTime'])) {
                    $visitStatistics['day']['visitNumber']++; // 일 방문횟수 ( 60분 이후마다 방문자의 카운트 )
                    $visitStatistics['day'][$val['visitDevice']]['visitNumber']++; // 일 방문횟수 ( 60분 이후마다 방문자의 카운트 )
                    if($visitHourDuplicationFl) {
                        $visitStatistics['hour'][$val['visitHour']]['visitNumber']++; // 일 시간별 방문자수 ( 60분 이후마다 방문자의 카운트 )
                    }
                    $visitStatistics['hour'][$val['visitHour']]['visitNumber']++; // 일 시간별 방문자수 ( 60분 이후마다 방문자의 카운트 )
                    $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitNumber']++; // 일 시간별 방문자수 ( 60분 이후마다 방문자의 카운트 )
                }
            } else {
                $visitStatistics['day']['visitNumber']++; // 일 방문횟수 ( 60분 이후마다 방문자의 카운트 )
                $visitStatistics['day'][$val['visitDevice']]['visitNumber']++; // 일 방문횟수 ( 60분 이후마다 방문자의 카운트 )
                if($visitHourDuplicationFl) {
                    $visitStatistics['hour'][$val['visitHour']]['visitNumber']++; // 일 시간별 방문자수 ( 60분 이후마다 방문자의 카운트 )
                }
                $visitStatistics['hour'][$val['visitHour']]['visitNumber']++; // 일 시간별 방문자수 ( 60분 이후마다 방문자의 카운트 )
                $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitNumber']++; // 일 시간별 방문자수 ( 60분 이후마다 방문자의 카운트 )
            }

            // 일별 총
            $visitStatistics['day']['pv'] += $val['visitPageView'];
            $visitStatistics['day']['visitInflow'][$val['visitInflow']]++;
            $visitStatistics['day']['visitOs'][$val['visitOS']]++;
            $visitStatistics['day']['visitBrowser'][$val['visitBrowser']]++;
            // 일별 디바이스
            $visitStatistics['day'][$val['visitDevice']]['pv'] += $val['visitPageView'];
            $visitStatistics['day'][$val['visitDevice']]['visitInflow'][$val['visitInflow']]++;
            $visitStatistics['day'][$val['visitDevice']]['visitOs'][$val['visitOS']]++;
            $visitStatistics['day'][$val['visitDevice']]['visitBrowser'][$val['visitBrowser']]++;
            // 시간별 총
            $visitStatistics['hour'][$val['visitHour']]['pv'] += $val['visitPageView'];
            $visitStatistics['hour'][$val['visitHour']]['visitInflow'][$val['visitInflow']]++;
            $visitStatistics['hour'][$val['visitHour']]['visitOs'][$val['visitOS']]++;
            $visitStatistics['hour'][$val['visitHour']]['visitBrowser'][$val['visitBrowser']]++;
            // 시간별 디바이스
            $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['pv'] += $val['visitPageView'];
            $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitInflow'][$val['visitInflow']]++;
            $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitOs'][$val['visitOS']]++;
            $visitStatistics['hour'][$val['visitHour']][$val['visitDevice']]['visitBrowser'][$val['visitBrowser']]++;

            // 현재 처리된 정보 저장
            $prev['mallSno'] = $val['mallSno'];
            $prev['visitIP'] = $val['visitIP'];
            $prev['visitSiteKey'] = $val['visitSiteKey'];
            $prev['memNo'] = $val['memNo'];
            $prev['visitDevice'] = $val['visitDevice'];
            $prev['visitOS'] = $val['visitOS'];
            $prev['visitBrowser'] = $val['visitBrowser'];
            $prev['visitYear'] = $val['visitYear'];
            $prev['visitMonth'] = $val['visitMonth'];
            $prev['visitDay'] = $val['visitDay'];
            $prev['regDt'] = $val['regDt'];
        }

        // 방문 통계 일별 정리 저장
        $visitDayJson = json_encode($visitStatistics['day'], JSON_UNESCAPED_UNICODE);

        $visitDay['Ym'] = date('Ym', $this->visitStatisticsTime);
        $visitDay['mallSno'] = $mallSno;

        $getCheckDayStatistics = $this->getVisitDayStatisticsInfo($visitDay, 'vd.visitYM', null, true);
        if (count($getCheckDayStatistics) > 0) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_VISIT_DAY . " SET `" . $visit['day'] . "`=?, modDt=now() WHERE `visitYM`=? AND `mallSno`=?";
            $this->db->bind_param_push($arrBind, 's', $visitDayJson);
            $this->db->bind_param_push($arrBind, 'i', date('Ym', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_DAY . " SET `visitYM`=?, `mallSno`=?, `" . $visit['day'] . "`=?, `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', date('Ym', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_param_push($arrBind, 's', $visitDayJson);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        // 방문 통계 시간별 정리 저장
        for ($i = 0; $i <= 23; $i++) {
            $visitHourJson[$i] = json_encode($visitStatistics['hour'][$i], JSON_UNESCAPED_UNICODE);
            $updateColumn[$i] = '`' . $i . '`=?';
        }

        $visitHour['Ymd'] = date('Ymd', $this->visitStatisticsTime);
        $visitHour['mallSno'] = $mallSno;
        $getCheckHourStatistics = $this->getVisitHourStatisticsInfo($visitHour, 'vh.visitYMD', null, true);
        if (count($getCheckHourStatistics) > 0) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_VISIT_HOUR . " SET " . implode(',', $updateColumn) . ", `modDt`=now() WHERE `visitYMD`=? AND `mallSno`=?";
            foreach ($visitHourJson as $val) {
                $this->db->bind_param_push($arrBind, 's', $val);
            }
            $this->db->bind_param_push($arrBind, 'i', date('Ymd', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_HOUR . " SET `visitYMD`=?, `mallSno`=?, " . implode(',', $updateColumn) . ", `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', date('Ymd', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            foreach ($visitHourJson as $val) {
                $this->db->bind_param_push($arrBind, 's', $val);
            }
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        return true;
    }

    /**
     * setVisitMonthStatistics
     * 월별 방문통계 정리
     *
     * @param null $mallSno
     * @return bool
     */
    public function setVisitMonthStatistics($mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        $visit['mallSno'] = $mallSno;
        $visit['year'] = date('Y', $this->visitStatisticsTime);
        $visit['month'] = date('n', $this->visitStatisticsTime);
        $visit['sort'] = 'vs.memNo asc, vs.visitIP asc, vs.visitHour asc';
        $getStatistics = $this->getVisitStatisticsInfo($visit, 'vs.*' , null, true);

        // 데이터 초기화
        $visitColumn = $this->getVisitColumn();
        $visitStatistics['month'] = $visitColumn;
        $visitStatistics['month']['pc'] = $visitColumn;
        $visitStatistics['month']['mobile'] = $visitColumn;

        // 방문자 데이터 가공
        $prevMemberNo = 0;
        $prevVisitIP = 0;
        foreach ($getStatistics as $key => $val) {
            if ($val['memNo'] > 0) {
                if (strtotime($val['modDt']) > 0) {
                    $lastTime = $val['modDt'];
                } else if (strtotime($val['regDt']) > 0) {
                    $lastTime = $val['regDt'];
                } else {
                    $lastTime = $val['regDt'];
                }
                if ($prevMemberNo != $val['memNo']) {
                    $memberCheckArr = $this->getVisitMemberCheckCount($val['memNo'], $lastTime, $mallSno);
                    // 일별 총
                    $visitStatistics['month']['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['month']['visitNewCount'] += $memberCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['month']['visitNewPv'] += $memberCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['month']['visitReCount'] += $memberCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['month']['visitRePv'] += $memberCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 일별 디바이스
                    $visitStatistics['month'][$val['visitDevice']]['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['month'][$val['visitDevice']]['visitNewCount'] += $memberCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['month'][$val['visitDevice']]['visitNewPv'] += $memberCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['month'][$val['visitDevice']]['visitReCount'] += $memberCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['month'][$val['visitDevice']]['visitRePv'] += $memberCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                }
                $prevMemberNo = $val['memNo'];
            } else if ($val['visitIP'] > 0) {
                if (strtotime($val['modDt']) > 0) {
                    $lastTime = $val['modDt'];
                } else if (strtotime($val['regDt']) > 0) {
                    $lastTime = $val['regDt'];
                } else {
                    $lastTime = $val['regDt'];
                }
                if ($prevVisitIP != $val['visitIP']) {
                    $ipCheckArr = $this->getVisitIpCheckCount($val['visitIP'], $lastTime, $mallSno);
                    // 일별 총
                    $visitStatistics['month']['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['month']['visitNewCount'] += $ipCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['month']['visitNewPv'] += $ipCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['month']['visitReCount'] += $ipCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['month']['visitRePv'] += $ipCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                    // 일별 디바이스
                    $visitStatistics['month'][$val['visitDevice']]['visitCount']++; // 일 방문자수 ( IP 고유 방문자 - 비회원 )
                    $visitStatistics['month'][$val['visitDevice']]['visitNewCount'] += $ipCheckArr['new']['count']; // 일 신규방문자수 ( 30일 이후마다 방문자의 카운트 )
                    $visitStatistics['month'][$val['visitDevice']]['visitNewPv'] += $ipCheckArr['new']['pv']; // 일 신규방문자 페이지뷰 ( 30일 이후마다 방문자의 페이지뷰 )
                    $visitStatistics['month'][$val['visitDevice']]['visitReCount'] += $ipCheckArr['re']['count']; // 일 재방문자수 ( 30일 이전마다 방문자의 카운트 )
                    $visitStatistics['month'][$val['visitDevice']]['visitRePv'] += $ipCheckArr['re']['pv']; // 일 재방문자 페이지뷰 ( 30일 이전마다 방문자의 페이지뷰 )
                }
                $prevVisitIP = $val['visitIP'];
            }
            // 일별 총
            $visitStatistics['month']['visitNumber']++; // 일 방문횟수 ( 60분 이후마다 방문자의 카운트 )
            $visitStatistics['month']['pv'] += $val['visitPageView'];
            $visitStatistics['month']['visitInflow'][$val['visitInflow']]++;
            $visitStatistics['month']['visitOs'][$val['visitOS']]++;
            $visitStatistics['month']['visitBrowser'][$val['visitBrowser']]++;
            // 일별 디바이스
            $visitStatistics['month'][$val['visitDevice']]['visitNumber']++; // 일 방문횟수 ( 60분 이후마다 방문자의 카운트 )
            $visitStatistics['month'][$val['visitDevice']]['pv'] += $val['visitPageView'];
            $visitStatistics['month'][$val['visitDevice']]['visitInflow'][$val['visitInflow']]++;
            $visitStatistics['month'][$val['visitDevice']]['visitOs'][$val['visitOS']]++;
            $visitStatistics['month'][$val['visitDevice']]['visitBrowser'][$val['visitBrowser']]++;
        }

        $visitMonthJson = json_encode($visitStatistics['month'], JSON_UNESCAPED_UNICODE);

        $visitMonth['Y'] = date('Y', $this->visitStatisticsTime);
        $visitMonth['mallSno'] = $mallSno;
        $getCheckMonthStatistics = $this->getVisitMonthStatisticsInfo($visitMonth, 'vm.visitY');

        if ($getCheckMonthStatistics) {
            $arrBind = [];
            $strSQL = "UPDATE " . DB_VISIT_MONTH . " SET `" . $visit['month'] . "`=?, modDt=now() WHERE `visitY`=? AND `mallSno`=?";
            $this->db->bind_param_push($arrBind, 's', $visitMonthJson);
            $this->db->bind_param_push($arrBind, 'i', date('Y', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        } else {
            $arrBind = [];
            $strSQL = "INSERT INTO " . DB_VISIT_MONTH . " SET `visitY`=?, `mallSno`=?, `" . $visit['month'] . "`=?, `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', date('Y', $this->visitStatisticsTime));
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
            $this->db->bind_param_push($arrBind, 's', $visitMonthJson);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);
        }

        return true;
    }

    /**
     * checkPrevDayVisitStatistics
     * 기준일에서 1일 전 데이터 확인
     *
     * @return bool
     */
    public function checkPrevDayVisitStatistics()
    {
        // 기준몰 만 검색하여 체크
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);
        $visit['mallSno'] = $mallSno;
        // 처리할 방문통계 기준일에서 1일 전 날짜 구하기
        $prevDayTime = $this->visitStatisticsTime - (24 * 60 * 60);

        $visit['Ym'] = date('Ym', $prevDayTime);
        $visitField = date('j', $prevDayTime);
        $getPrevDayData = $this->getVisitDayStatisticsInfo($visit, '`' . $visitField . '`');
        $prevDayData = json_decode($getPrevDayData, true);
        if (is_array($prevDayData)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * setStatisticsSchedule
     *
     * @param null $mallSno
     * @return bool
     */
    public function setStatisticsSchedule($mallSno = null)
    {
        $mallSno = gd_isset($mallSno, DEFAULT_MALL_NUMBER);

        // 고유 방문통계
        $returnUnique = $this->setUniqueUserStatistics($mallSno);

        // 일별 / 시간별 방문통계
        $returnDayHour = $this->setVisitStatistics($mallSno);

        // 월별 방문통계 -- 메모리 부하로 인한 제거 - 일별 통계의 합으로 처리
//        $returnMonth = $this->setVisitMonthStatistics($mallSno);

        if ($returnUnique && $returnDayHour) {
            return true;
        } else {
            return false;
        }
    }

    public function getVisitIpSearchDate($searchDate)
    {
        $sDate = new DateTime();
        $eDate = new DateTime();
        if (!$searchDate[0]) {
            $searchDate[0] = $sDate->modify('-6 day')->format('Ymd');
        } else {
            $startDate = new DateTime($searchDate[0]);
            if ($sDate->format('Ymd') <= $startDate->format('Ymd')) {
                $searchDate[0] = $sDate->format('Ymd');
            } else {
                $searchDate[0] = $startDate->format('Ymd');
            }
        }
        if (!$searchDate[1]) {
            $searchDate[1] = $eDate->format('Ymd');
        } else {
            $endDate = new DateTime($searchDate[1]);
            if ($eDate->format('Ymd') <= $endDate->format('Ymd')) {
                $searchDate[1] = $eDate->format('Ymd');
            } else {
                $searchDate[1] = $endDate->format('Ymd');
            }
        }

        $sDate = new DateTime($searchDate[0]);
        $eDate = new DateTime($searchDate[1]);
        $dateDiff = date_diff($sDate, $eDate);
        if ($dateDiff->days > 90) {
            $sDate = $eDate->modify('-6 day');
            $searchDate[0] = $sDate->format('Ymd');
        }

        return $searchDate;
    }

    /**
     * getVisitStatisticsTodayInfo 당일 방문자 통계
     *
     * @param null $visit
     * @param null $visitField
     * @param null $arrBind
     * @param bool $dataArray
     * @return mixed
     */
    public function getVisitStatisticsTodayInfo($visit = null, $visitField = null, $arrBind = null, $dataArray = false, $mallSno = null)
    {
        /*
         * visitCount - 실제 방문자수 (중복제거, 타 시간대 동일방문자일 시 동일 방문자)
         * totalVisitCount - 총 방문자수 (중복포함)
         * pv - 페이지뷰
         * */
        $addWhere = '';
        if (empty($visit['mallSno']) === false) {
            $addWhere = ' AND vs.mallSno = ?';
        }
        $strSQL = "SELECT visitDevice, (CASE visitDevice WHEN 'pc' THEN pc WHEN 'mobile' THEN mobile ELSE NULL END) AS visitCount, SUM(vcnt) AS totalVisitCount, SUM(vpcnt) AS pv
                    FROM
                    (
                        SELECT
                        vs.visitDevice, COUNT(*) AS vcnt, SUM(vs.visitPageView) AS vpcnt
                        FROM
                        " . DB_VISIT_STATISTICS . " AS vs
                        WHERE
                        vs.visitYear = ? AND vs.visitMonth = ? AND vs.visitDay = ? " . $addWhere . "
                        GROUP BY vs.visitDevice
                    ) a,
                    (
                    SELECT
                        SUM(pc) AS pc, SUM(mobile) AS mobile
                        FROM (
                            SELECT
                            (CASE visitDevice WHEN 'pc' THEN 1 ELSE NULL END) AS 'pc', (CASE visitDevice WHEN 'mobile' THEN 1 ELSE NULL END) AS 'mobile'
                            FROM
                            " . DB_VISIT_STATISTICS . " AS vs
                            WHERE
                            vs.visitYear = ? AND vs.visitMonth = ? AND vs.visitDay = ? " . $addWhere . "
                            GROUP BY
                            vs.visitDevice, vs.memNo, vs.visitIP
                        ) b
                    ) b
                    GROUP BY visitDevice";
        $this->db->bind_param_push($arrBind, 'i', $visit['year']);
        $this->db->bind_param_push($arrBind, 'i', $visit['month']);
        $this->db->bind_param_push($arrBind, 'i', $visit['day']);
        if (empty($visit['mallSno']) === false) {
            $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
        }
        $this->db->bind_param_push($arrBind, 'i', $visit['year']);
        $this->db->bind_param_push($arrBind, 'i', $visit['month']);
        $this->db->bind_param_push($arrBind, 'i', $visit['day']);
        if (empty($visit['mallSno']) === false) {
            $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
        }
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return $getData[0];
        }
        return $getData;
    }

    /**
     * getVisitStatisticsDailyInfo 당일 시간별 방문자 통계
     *
     * @param null $visit
     * @param null $visitField
     * @param null $arrBind
     * @param bool $dataArray
     * @param null $mallSno
     * @return mixed
     */
    public function getVisitStatisticsDailyInfo($visit = null, $visitField = null, $arrBind = null, $dataArray = false)
    {
        $field = ['pc', 'mobile'];
        for ($hour = 0; $hour <= 23; $hour++) {
            foreach ($field as $val) {
                $fieldName = $val . '_' . $hour;
                $visitHour[$val][] = 'WHEN ' . $hour . ' THEN ' . $fieldName;
                $sum[$val][] = 'SUM(' . $fieldName . ') AS ' . $fieldName;
                $visitDevice[$val][] = "(CASE visitDevice WHEN '".$val."' THEN (CASE visitHour WHEN " . $hour . " THEN 1 ELSE NULL END) ELSE NULL END) AS '" . $fieldName . "'";
            }
        }

        /*
         * visitCount - 실제 방문자수 (중복제거, 타 시간대 동일방문자일 시 다른 방문자)
         * totalVisitCount - 총 방문자수 (중복포함)
         * pv - 페이지뷰
         * */

        $addWhere = '';
        if (empty($visit['mallSno']) === false) {
            $addWhere = ' AND vs.mallSno = ?';
        }
        $strSQL = "SELECT
                    visitDevice, visitHour,
                    (CASE visitDevice WHEN 'pc' THEN (CASE visitHour " . @implode(' ', $visitHour['pc']) . " ELSE NULL END)
                            WHEN 'mobile' THEN (CASE visitHour " . @implode(' ', $visitHour['mobile']) . " ELSE NULL END) ELSE NULL END) AS visitCount,
                    SUM(vcnt) AS totalVisitCount, SUM(vpcnt) AS pv
                    FROM
                    (
                        SELECT
                        vs.visitDevice, vs.visitHour, COUNT(*) AS vcnt, SUM(vs.visitPageView) AS vpcnt
                        FROM
                        " . DB_VISIT_STATISTICS . " AS vs
                        WHERE
                        vs.visitYear = ? AND vs.visitMonth = ? AND vs.visitDay = ? " . $addWhere . "
                        GROUP BY vs.visitDevice, vs.visitHour
                    ) a,
                    (
                    SELECT
                        " . @implode(', ', $sum['pc']) . ",
                        " . @implode(', ', $sum['mobile']) . "
                        FROM (
                        SELECT
                            " . @implode(', ', $visitDevice['pc']) . ",
                        " . @implode(', ', $visitDevice['mobile']) . "
                            FROM
                              " . DB_VISIT_STATISTICS . " AS vs
                            WHERE
                              vs.visitYear = ? AND vs.visitMonth = ? AND vs.visitDay = ? " . $addWhere . "
                            GROUP BY
                              vs.visitDevice, vs.memNo, vs.visitIP
                        ) b
                    ) b
                    GROUP BY visitDevice, visitHour";
        $this->db->bind_param_push($arrBind, 'i', $visit['year']);
        $this->db->bind_param_push($arrBind, 'i', $visit['month']);
        $this->db->bind_param_push($arrBind, 'i', $visit['day']);
        if (empty($visit['mallSno']) === false) {
            $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
        }
        $this->db->bind_param_push($arrBind, 'i', $visit['year']);
        $this->db->bind_param_push($arrBind, 'i', $visit['month']);
        $this->db->bind_param_push($arrBind, 'i', $visit['day']);
        if (empty($visit['mallSno']) === false) {
            $this->db->bind_param_push($arrBind, 'i', $visit['mallSno']);
        }
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return $getData[0];
        }
        return $getData;
    }
}
