<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Promotion;

use Component\Database\DBTableField;
use Framework\Utility\GodoUtils;
use Framework\Utility\HttpUtils;
use Framework\Utility\UrlUtils;
use Globals;
use App;

/**
 * Class ShortUrl
 *
 * @package Bundle\Component\Promotion
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class ShortUrl
{
    /**
     * @const SHORTURL_GODO_HOST 단축주소 서비스 host
     */
    const SHORTURL_GODO_HOST = 's.godo.kr';

    /**
     * @var \Framework\Database\DBTool null|object 데이터베이스 인스턴스(싱글턴)
     */
    protected $db;

    /**
     * @var array 쿼리 조건 바인딩
     */
    protected $arrBind = [];

    /**
     * @var array 리스트 검색 조건
     */
    protected $arrWhere = [];

    /**
     * @var array 체크박스 체크 조건
     */
    protected $checked = [];

    /**
     * @var array 검색
     */
    protected $search = [];

    /**
     * @var boolean
     */
    protected $isInstalled = false;

    /**
     * @var array 허용 프로토콜
     */
    private $_allowedProtocols = [];

    /**
     * ShortUrl 생성자.
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        // 허용프로토콜 선언
        $this->_allowedProtocols = [
            'http:',
            'https:',
            'mailto:',
        ];

        // 플러스샵 설치여부
        if (GodoUtils::isPlusShop(PLUSSHOP_CODE_SHORTURL)) {
            $this->isInstalled = true;
        }
    }

    /**
     * 플러스샵 설치 여부
     *
     * @return boolean
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getIsInstalled()
    {
        return $this->isInstalled;
    }

    /**
     * return the id for a given url (or -1 if the url doesn't exist)
     *
     * @param string $url
     *
     * @return integer
     * @throws \Framework\Debug\Exception\DatabaseException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getId($url)
    {
        $strSQL = 'SELECT id FROM ' . DB_SHORT_URL . ' WHERE longUrl = ? LIMIT 1';
        $arrBind = ['s', $url];
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        $getData = gd_htmlspecialchars_stripslashes($getData);
        unset($arrBind);

        if (empty($getData['id']) === false) {
            return $getData['id'];
        } else {
            return -1;
        }
    }

    /**
     * 단축주소 데이터 상세정보
     * id로 데이터 찾기
     *
     * @param string $id
     *
     * @return array|object|string
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getSno($id)
    {
        $strSQL = 'SELECT sno FROM ' . DB_SHORT_URL . ' WHERE id = ? LIMIT 1';
        $arrBind = ['s', $id];
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        $getData = gd_htmlspecialchars_stripslashes($getData);
        unset($arrBind);

        if (empty($getData['sno']) === false) {
            return $getData['sno'];
        } else {
            return -1;
        }
    }

    /**
     * return the url for a given id (or -1 if the id doesn't exist)
     *
     * @param string $id
     *
     * @return integer
     * @throws \Framework\Debug\Exception\DatabaseException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getUrl($id)
    {
        $arrField = DBTableField::setTableField('tableShortUrl', null, null);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_SHORT_URL . ' WHERE id = ? LIMIT 1';
        $arrBind = ['s', $id];
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        $getData = gd_htmlspecialchars_stripslashes($getData);
        unset($arrBind);

        if (empty($getData) === false) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 's', $id);
            $this->db->set_update_db(DB_SHORT_URL, "count = count + 1", 'id = ?', $arrBind);
            unset($arrBind);

            // id로 SNO 일련번호 가져오기
            $sno = $this->getSno($id);

            // 단축주소 통계 테이블내 카운트 기록
            if ($sno !== -1) {
                $arrBind = [];
                $this->db->bind_param_push($arrBind['bind'], 's', $sno);
                $this->db->bind_param_push($arrBind['bind'], 's', date('Y'));
                $this->db->bind_param_push($arrBind['bind'], 's', date('m'));
                $this->db->bind_param_push($arrBind['bind'], 's', date('d'));
                $affetedRow = $this->db->set_update_db(DB_SHORT_URL_STATISTICS, 'count = count + 1', 'sno<>\'\' AND shortUrlNo = ? AND year = ? AND month = ? AND day = ?', $arrBind['bind']);

                // 업데이트 데이터가 없으면 추가
                if ($affetedRow === 0) {
                    $data = [
                        'shortUrlNo' => $sno,
                        'year' => date('Y'),
                        'month' => date('m'),
                        'day' => date('d'),
                        'count' => 1,
                    ];
                    $compareField = array_keys($data);
                    $arrBind = $this->db->get_binding(DBTableField::tableShortUrlStatistics(), $data, 'insert', $compareField);
                    $this->db->set_insert_db(DB_SHORT_URL_STATISTICS, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                }

                unset($arrBind);
            }

            return $getData['longUrl'];
        } else {
            return -1;
        }
    }

    /**
     * 데이터베이스에 url 추가
     *
     * $msg 값에 따른 에러 메시지
     * -101: 유효하지 않은 URL
     * -103: 단축주소로 등록 불가
     * -104: 허용되지 않는 프로토콜
     * 이외: short URL 반환
     *
     * s.godo.kr에
     *
     * @param string $url
     * @param string $description
     *
     * @return mixed
     * @throws \Exception
     * @throws \Framework\Debug\Exception\DatabaseException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function addUrl($url, $description = null)
    {
        // 자동으로 http 스키마 장착
        $url = UrlUtils::addHttpScheme(urldecode($url));

        // URL 유효성 검사
        if (!preg_match('/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w]([-\d\w]{0,253}[\d\w])?\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.,\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.,\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.,\/\d\w]|%[a-fA-f\d]{2,2})*)?$/', urldecode($url))) {
            throw new \Exception(__('유효하지 않은 URL이 입력되었습니다.'));
        }

        // 단축주소 URL인 경우 등록불가 선언 (s.godo.kr)
        if (stripos($url, self::SHORTURL_GODO_HOST) !== false) {
            throw new \Exception(__('단축주소로 등록이 불가합니다.'));
        }

        $protocolOk = false;
        if (count($this->_allowedProtocols)) {
            foreach ($this->_allowedProtocols as $ap) {
                if (strtolower(substr($url, 0, strlen($ap))) == strtolower($ap)) {
                    $protocolOk = true;
                    break;
                }
            }
        } else {
            $protocolOk = true;
        }

        // 데이터베이스에 추가
        if ($protocolOk) {
            $id = $this->getId($url);

            // 기본 도메인 추출
            $usableDomain = DOMAIN_USEABLE_LIST;
            $controllerNameResolver = App::getInstance('ControllerNameResolver');
            $defaultDomain = $usableDomain['api'] . '.' . $controllerNameResolver->getDefaultDomain();

            // 자체 단축주소 생성
            if ($id == -1) {
                $id = $this->_getNextId($this->_getLastId());

                // 0을 숫자형으로 인식못하고 boolean으로 인식해 id가 최초 null 값으로 생성된다.
                if (empty($id) === true) {
                    $id = '0';
                }

                // 단축주소 s.godo.kr 접속 후 주소 만들기
                $params = [
                    'longUrl' => \Request::getDomainUrl($defaultDomain) . '/surl/?id=' . $id,
                    'shopSno' => Globals::get('gLicense.godosno'),
                ];
                $result = json_decode(HttpUtils::remotePost('http://s.godo.kr/api/v1/transurl', $params), true);

                if (empty($result) === true) {
                    throw new \Exception(__('단축주소 서버에 장애가 있으니 잠시 후 다시 시도해주세요.'));
                }

                if ($result['godosurl']['result']['code'] == '000') {
                    // 변수 축약
                    $data = $result['godosurl']['data'];

                    // 내부변수로 변경
                    $data['id'] = $id;
                    $data['longUrl'] = $url;

                    // 설명 추가
                    if (empty($description) !== true) {
                        $data['description'] = $description;
                    }

                    // DB insert
                    $data['managerNo'] = \Session::get('manager.sno');
                    $compareField = array_keys($data);
                    $arrBind = $this->db->get_binding(DBTableField::tableShortUrl(), $data, 'insert', $compareField);
                    $insertResult = $this->db->set_insert_db(DB_SHORT_URL, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);

                    if ($insertResult !== false) {
                        return true;
                    }
                }
            } else {
                throw new \Exception(__('이미 등록되어 있는 단축 URL 입니다.'));
            }
        } else {
            throw new \Exception(__('허용되지 않는 프로토콜입니다.'));
        }
    }

    /**
     * deleteUrl
     *
     * @param integer $sno
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function deleteUrl($sno)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $sno);
        $this->db->set_delete_db(DB_SHORT_URL, 'sno = ?', $arrBind);
        unset($arrBind);
    }

    // return the most recent id (or -1 if no ids exist)
    private function _getLastId()
    {
        $strSQL = 'SELECT id FROM ' . DB_SHORT_URL . ' ORDER BY regDt DESC LIMIT 1';
        $getData = $this->db->query_fetch($strSQL, null, false);
        unset($arrBind);

        if (empty($getData) === false) {
            return $getData['id'];
        } else {
            return -1;
        }
    }

    /**
     * return the next id
     *
     * @param $lastId
     *
     * @return int|mixed|string
     * @throws \Framework\Debug\Exception\DatabaseException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    private function _getNextId($lastId)
    {
        // 라스트 ID가 존재하지 않으면 -1을 반환하고 0부터 시작한다.
        if ($lastId == -1) {
            $nextId = 0;
        } else {
            // 문자를 찾을 때까지 반복
            for ($x = 1; $x <= strlen($lastId); $x++) {
                $pos = strlen($lastId) - $x;

                if ($lastId[$pos] != 'z') {
                    $nextId = $this->_incrementId($lastId, $pos);
                    break; //문자를 찾으면 루프에서 빠져나온다.
                }
            }

            // if every character was already at its max value (z),
            // append another character to the string
            if (!isset($nextId)) {
                $nextId = $this->_appendId($lastId);
            }
        }

        // check to see if the $nextId we made already exists, and if it does,
        // loop the function until we find one that doesn't
        //
        // (this is basically a failsafe to get around the potential dangers of
        //  my kludgey use of a timestamp to pick the most recent id)
        $arrField = DBTableField::setTableField('tableShortUrl', ['id'], null);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ', regDt FROM ' . DB_SHORT_URL . ' WHERE id = ? LIMIT 1';
        $arrBind = ['s', $nextId];
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if (empty($getData) === false) {
            $nextId = $this->_getNextId($nextId);
        }

        return $nextId;
    }

    /**
     * make every character in the string 0, and then add an additional 0 to that
     *
     * @param $id
     *
     * @return string
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    private function _appendId($id)
    {
        for ($x = 0; $x < strlen($id); $x++) {
            $id[$x] = 0;
        }

        $id .= 0;

        return $id;
    }

    /**
     * increment a character to the next alphanumeric value and return the modified id
     *
     * @param $id
     * @param $pos
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    private function _incrementId($id, $pos)
    {
        $char = $id[$pos];

        // add 1 to numeric values
        if (is_numeric($char)) {
            if ($char < 9) {
                $newChar = $char + 1;
            } else // if we're at 9, it's time to move to the alphabet
            {
                $newChar = 'a';
            }
        } else // move it up the alphabet
        {
            $newChar = chr(ord($char) + 1);
        }

        $id[$pos] = $newChar;

        // set all characters after the one we're modifying to 0
        if ($pos != (strlen($id) - 1)) {
            for ($x = ($pos + 1); $x < strlen($id); $x++) {
                $id[$x] = 0;
            }
        }

        return $id;
    }

    /**
     * _setSearch
     *
     * @param array   $searchData
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    private function _setSearch($searchData)
    {
        // 통합 검색
        $this->search['combineSearch'] = [
            'all' => '=통합검색=',
            's.longUrl' => '원본 URL',
            's.shortUrl' => '단축 URL',
            's.description' => 'URL 설명',
        ];

        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        }

        // --- 정렬
        $this->search['sortList'] = [
            's.regDt desc' => '등록일↓',
            's.regDt asc' => '등록일↑',
        ];

        // --- 검색 설정
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], 6);
        $this->search['key'] = gd_isset($searchData['key'], 's.longUrl');
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['sort'] = gd_isset($searchData['sort'], 's.regDt desc');
        $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], $searchData['searchPeriod'] == -1 ? $searchData['searchDate'][0] : date('Y-m-d', strtotime('-' . $this->search['searchPeriod'] . ' day')));
        $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], $searchData['searchPeriod'] == -1 ? $searchData['searchDate'][1] : date('Y-m-d'));

        // --- 검색 설정
        $this->checked['periodFl'][$searchData['searchPeriod']] = 'active';

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array_keys($this->search['combineSearch']);
                array_shift($tmpWhere);
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            }
        }

        // 처리일자 검색
        if (isset($this->search['searchPeriod']) && $this->search['searchPeriod'] != -1 && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 's.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * getShortUrlList
     *
     * @param array   $searchData
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     * @return mixed
     */
    public function getList($searchData)
    {
        // --- 검색 설정
        $this->_setSearch($searchData);

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 정렬 설정
        $orderSort = gd_isset($searchData['sort'], $this->search['sortList']);

        // 추출 필드
        $arrIncludeS = [
            'id',
            'shortUrl',
            'longUrl',
            'description',
            'count',
        ];
        $arrIncludeM = [
            'managerId',
            'managerNm',
        ];

        $tmpField[] = DBTableField::setTableField('tableShortUrl', $arrIncludeS, null, 's');
        $tmpField[] = DBTableField::setTableField('tableManager', $arrIncludeM, null, 'm');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' AS m ON s.managerNo = m.sno ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 's.sno, s.regDt, ' . implode(', ', $arrField);
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $this->search['sort'];
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SHORT_URL . ' s ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        unset($query['left'], $query['order'], $query['limit']);
        $page->recode['total'] = $this->db->query_count($query, DB_SHORT_URL . ' s', $this->arrBind);
        $page->recode['amount'] = $this->db->getCount(DB_SHORT_URL);
        //        $page->recode['amount'] = $this->db->table_status(DB_ORDER, 'Rows'); // 전체 레코드 수
        $page->setPage();

        // 데이터 설정
        $getData['data'] = $data;

        // 검색값 설정
        if (empty($this->search) === false) {
            $getData['search'] = gd_htmlspecialchars($this->search);
        }

        // 체크값 설정
        if (empty($this->checked) === false) {
            $getData['checked'] = $this->checked;
        }

        return $getData;
    }

    /**
     * 단축주소 데이터 상세정보
     * sno로 데이터 찾기
     *
     * @param array   $searchData
     *
     * @return array|object|string
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getView($searchData)
    {
        // 단축URL 정보
        $arrField = DBTableField::setTableField('tableShortUrl', null, null);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ', regDt FROM ' . DB_SHORT_URL . ' WHERE sno = ? LIMIT 1';
        $arrBind = ['s', $searchData['sno']];
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        unset($arrBind, $data);

        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        }

        // --- 정렬
        $this->search['sortList'] = [
            's.regDt desc' => '등록일↓',
            's.regDt asc' => '등록일↑',
        ];

        // --- 검색 설정
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], 6);
        $this->search['queryType'] = gd_isset($searchData['queryType'], 'day');
        $this->search['sort'] = gd_isset($searchData['sort'], 's.regDt desc');
        $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], $searchData['searchPeriod'] == -1 ? $searchData['searchDate'][0] : date('Y-m-d', strtotime('-' . $this->search['searchPeriod'] . ' day')));
        $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], $searchData['searchPeriod'] == -1 ? $searchData['searchDate'][1] : date('Y-m-d'));

        // --- 검색 설정
        $this->checked['queryType'][$this->search['queryType']] = 'checked="checked"';

        // 해당 단축주소 통계 조건
        $this->arrWhere[] = 's.shortUrlNo = ?';
        $this->db->bind_param_push($this->arrBind, 's', $searchData['sno']);

        // 처리일자 검색
        if (isset($this->search['searchPeriod']) && $this->search['searchPeriod'] != -1 && $this->search['searchDate'][0] && $this->search['searchDate'][1]) {
            $this->arrWhere[] = 's.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }

        if (empty($this->arrBind) === true) {
            $this->arrBind = null;
        }

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 추출할 필드 정의
        $arrField = DBTableField::setTableField('tableShortUrlStatistics', ['year' , 'month', 'day', 'count'], null);

        if ($this->search['queryType'] === 'month') {
            $this->db->strGroup = 's.year, s.month';
            $strField = ', SUM(s.count) AS count';
        } else {
            $strField = '';
        }

        // 현 페이지 결과
        $this->db->strField = implode(', ', $arrField) . $strField;
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $this->search['sort'];
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SHORT_URL_STATISTICS . ' s ' . implode(' ', $query);
        $getData['statistics'] = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        unset($query['left'], $query['order'], $query['limit']);
        $total = $this->db->query_fetch('SELECT SUM(s.count) AS total FROM ' . DB_SHORT_URL_STATISTICS . ' s ' . implode('', $query), $this->arrBind, false);
        $page->recode['total'] = gd_isset($total['total'], 0);
        unset($query['where']);
        $amount = $this->db->fetch('SELECT SUM(s.count) as total FROM ' . DB_SHORT_URL_STATISTICS . ' s WHERE shortUrlNo=' . $searchData['sno']);
        $page->recode['amount'] = gd_isset($amount['total'], 0);
        $page->setPage();

        // 검색값 설정
        if (empty($this->search) === false) {
            $getData['search'] = gd_htmlspecialchars($this->search);
        }

        // 체크값 설정
        if (empty($this->checked) === false) {
            $getData['checked'] = $this->checked;
        }

        //
        if ($this->search['queryType'] !== 'month') {

        }

        return $getData;
    }
}
