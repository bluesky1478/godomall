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

namespace Bundle\Component\Member;

use App;
use Component\Database\DBTableField;
use Component\Mail\MailUtil;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Util\MemberUtil;
use Component\Mileage\Mileage;
use Component\Mileage\MileageDAO;
use Component\Sms\Code;
use Component\Sms\SmsAuto;
use Component\Sms\SmsAutoCode;
use Component\Validator\Validator;
use Exception;
use Framework\Security\Digester;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\GodoUtils;
use Globals;
use Logger;
use Request;
use Session;

/**
 * Class 관리자에서 사용하는 회원 관리
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class MemberAdmin extends \Component\Member\Member
{
    protected $arrBind;
    protected $arrWhere;
    protected $fieldTypes;
    /** @var  \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
    private $mailMimeAuto;
    /** @var  MemberDAO $memberDAO */
    private $memberDAO;
    /** @var  MileageDAO $mileageDAO */
    private $mileageDAO;
    /** @var  SmsAuto $smsAuto */
    private $smsAuto;
    private $beforeMembersByGroupBatch = [];
    private $afterMembersByGroupBatch = [];

    /**
     * MemberAdmin constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct();
        $this->fieldTypes = array_merge(DBTableField::getFieldTypes('tableMember'), DBTableField::getFieldTypes('tableMemberMileage'));
        $this->mailMimeAuto = is_object($config['mailMimeAuto']) ? $config['mailMimeAuto'] : App::load('\\Component\\Mail\\MailMimeAuto');
        $this->memberDAO = is_object($config['memberDao']) ? $config['memberDao'] : new MemberDAO();
        $this->mileageDAO = is_object($config['mileageDAO']) ? $config['mileageDAO'] : new MileageDAO();
        $this->smsAuto = is_object($config['smsAuto']) ? $config['smsAuto'] : new SmsAuto();
    }

    /**
     * 회원 목록
     *
     * @author sunny
     *
     * @param $arrData
     *
     * @return array data
     * @deprecated
     */
    public function getMemberPageList($arrData)
    {
        $getData = $arrBind = $search = $arrWhere = $checked = $selected = [];

        $tmp = $this->searchMemberWhere($arrData);
        $arrBind = $tmp['arrBind'];
        $search = $tmp['search'];
        $arrWhere = $tmp['arrWhere'];
        $checked = MemberUtil::checkedByMemberListSearch($arrData);
        $selected = MemberUtil::selectedByMemberListSearch($arrData);
        $combineSearch = Member::COMBINE_SEARCH;
        $search['detailSearch'] = $arrData['detailSearch'];

        // 검색제한(회원 일괄 관리)
        if (gd_isset($arrData['indicate']) === false) {
            $arrWhere[] = '0';
        }
        // 검색제한(회원등급평가(수동))
        if (gd_isset($arrData['groupValidDt']) === true) {
            $arrWhere[] = 'groupValidDt < now()';
        }

        // 메일이나 SMS 보내기에 따른 검색 설정
        if (isset($arrData['sendMode']) === true) {
            if ($arrData['sendMode'] === 'mail') {
                $arrWhere[] = '(email != \'\' AND email IS NOT NULL)';
            }
            if ($arrData['sendMode'] === 'sms') {
                $arrWhere[] = '(cellPhone != \'\' AND cellPhone IS NOT NULL)';
            }
        }

        // --- 정렬 설정
        $sort = gd_isset($arrData['sort'], 'entryDt desc');
        $selected['sort'][$sort] = 'selected="selected"';

        // --- 페이지 설정
        $nowPage = gd_isset($arrData['page']);
        $pageNum = gd_isset($arrData['pageNum']);
        if ($pageNum == '') {
            $pageNum = '10';
        }
        $selected['pageNum'][$pageNum] = 'selected="selected"';
        $page = \App::load('Component\\Page\\Page', $nowPage, 0, 0, $pageNum);

        $start = $page->recode['start'];
        $limit = $page->page['list'];

        // --- 목록
        $data = $this->getMemberList($arrWhere, $sort, $arrBind, $start, $limit);

        // --- 페이지 리셋
        unset($arrBind);

        $page->recode['total'] = $this->foundRowsByMemberList(); // 검색 레코드 수
        $page->recode['amount'] = $this->db->getCount(DB_MEMBER, 'memNo', 'WHERE sleepFl=\'n\'');   // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // --- 각 데이터 배열화
        $getData['data'] = StringUtils::htmlSpecialCharsStripSlashes(gd_isset($data));
        $getData['search'] = StringUtils::htmlSpecialChars($search);
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;
        $getData['combineSearch'] = $combineSearch;
        $getData['groups'] = gd_member_groups(); // 회원등급

        return $getData;
    }

    /**
     * 회원리스트 검색조건문
     *
     * @param array $search 검색항목
     *
     * @return array 검색조건문
     */
    public function searchMemberWhere($search)
    {
        $arrBind = $arrWhere = [];

        $combineSearch = Member::COMBINE_SEARCH;

        // 키워드 검색
        $this->searchKeyword($search, $combineSearch, $arrBind, $arrWhere);
        // 상점고유번호
        $this->db->bindParameter('mallSno', $search, $arrBind, $arrWhere, $this->tableFunctionName, 'm');
        // 회원구분
        $this->db->bindParameter('memberFl', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 가입경로
        $this->db->bindParameter('entryPath', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 승인
        $this->db->bindParameter('appFl', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 등급
        $this->db->bindParameter('groupSno', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 성별
        $this->db->bindParameter('sexFl', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 메일수신동의
        $this->db->bindParameter('maillingFl', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // SMS수신동의
        $this->db->bindParameter('smsFl', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 양력/음력
        $this->db->bindParameter('calendarFl', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 결혼여부
        $this->db->bindParameter('marriFl', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 주문건수
        $this->db->bindParameterByRange('saleCnt', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 구매액
        $this->db->bindParameterByRange('saleAmt', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 마일리지
        $this->db->bindParameterByRange('mileage', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 예치금
        $this->db->bindParameterByRange('deposit', $search, $arrBind, $arrWhere, $this->tableFunctionName);
        // 방문횟수
        $this->db->bindParameterByRange('loginCnt', $search, $arrBind, $arrWhere, $this->tableFunctionName);

        // 회원가입일
        $this->db->bindParameterByDateTimeRange('entryDt', $search, $arrBind, $arrWhere, $this->tableFunctionName);

        // 최종로그인
        $this->db->bindParameterByDateTimeRange('lastLoginDt', $search, $arrBind, $arrWhere, $this->tableFunctionName);

        // 생일
        $this->db->bindParameterByDateTimeRange('birthDt', $search, $arrBind, $arrWhere, $this->tableFunctionName);

        // 결혼기념일
        $this->db->bindParameterByDateTimeRange('marriDate', $search, $arrBind, $arrWhere, $this->tableFunctionName);

        // 만14세 미만회원만 보기가 체크된 경우 연령층 검색은 전체로 설정된다.
        if (gd_isset($search['under14'], 'n') === 'y') {
            $under14Date = DateTimeUtils::getDateByUnderAge(14);
            $arrWhere[] = 'birthDt > ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['birthDt'], $under14Date);
        } else {
            // 연령층
            $search['age'] = gd_isset($search['age']);
            if ($search['age'] > 0) {
                $ageTerms = DateTimeUtils::getDateByAge($search['age']);
                $arrWhere[] = 'birthDt BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['birthDt'], $ageTerms[1]);
                $this->db->bind_param_push($arrBind, $this->fieldTypes['birthDt'], $ageTerms[0]);
            }
        }

        // 장기 미로그인
        $novisit = intval($search['novisit']);
        if ($novisit >= 0 && is_numeric($search['novisit'])) {
            $arrWhere[] = 'IF(lastLoginDt IS NULL, DATE_FORMAT(entryDt,\'%Y%m%d\') <= ?, DATE_FORMAT(lastLoginDt,\'%Y%m%d\') <= ?)';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $novisit . ' day')));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $novisit . ' day')));
        }

        //휴면전환예정회원 검색.
        if ($search['dormantMemberExpected'] === 'y') {

            $expirationDay = $search['expirationDay'];// 휴면 전환 예정 7일, 30일, 60일
            $expirationFl = $search['expirationFl']; // 개인정보 유효기간 전체,1년,3년,5년 선택값

            //개인정보유효기간 전체 선택 시
            if (!$expirationFl) {
                $arrWhere[] = 'm.expirationFl != \'999\' AND CASE m.expirationFl WHEN \'1\' THEN IF(lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?) WHEN \'3\' THEN IF(lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?) WHEN \'5\' THEN IF(lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?) END';
                $dormantMemberTerms = [
                    365 - $expirationDay,
                    365,
                    1095 - $expirationDay,
                    1095,
                    1825 - $expirationDay,
                    1825,
                ];
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[2] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[3] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[2] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[3] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[4] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[5] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[4] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[5] . ' day')));
            } else {
                $arrWhere[] = 'IF(lastLoginDt = \'0000-00-00 00:00:00\' OR m.lastLoginDt IS NULL, DATE_FORMAT(m.entryDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.entryDt,\'%Y%m%d\') >= ?, DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') <= ? AND DATE_FORMAT(m.lastLoginDt,\'%Y%m%d\') >= ?)';
                if ($expirationFl == 1) {
                    $dormantMemberTerms = [
                        365 - $expirationDay,
                        365,
                    ];
                } else if ($expirationFl == 3) {
                    $dormantMemberTerms = [
                        1095 - $expirationDay,
                        1095,
                    ];
                } else if ($expirationFl == 5) {
                    $dormantMemberTerms = [
                        1825 - $expirationDay,
                        1825,
                    ];
                }
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[0] . ' day')));
                $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $dormantMemberTerms[1] . ' day')));

            }

        }

        // 휴면회원 여부
        $arrWhere[] = 'sleepFl != \'y\'';

        return [
            'search'        => $search,
            'arrBind'       => $arrBind,
            'arrWhere'      => $arrWhere,
            'combineSearch' => $combineSearch,
        ];
    }

    /**
     * 통합검색 검색어
     *
     * @param $requestParams        array 통합검색
     * @param $combineSearch        array 통합검색 리스트
     * @param $arrBind              array
     * @param $arrWhere             array
     *
     * @return array
     */
    public function searchKeyword(&$requestParams, $combineSearch, &$arrBind, &$arrWhere)
    {
        $requestParams['key'] = gd_isset($requestParams['key']);
        $requestParams['keyword'] = gd_isset($requestParams['keyword']);
        if ($requestParams['key'] && $requestParams['keyword']) {
            if ($requestParams['key'] == 'all' || $requestParams['key'] == '') {
                $arrWhereAll = [];
                foreach ($combineSearch as $mKey => $mVal) {
                    if ($mKey == 'all' || $mKey == '') {
                        continue;
                    }
                    $arrWhereAll[] = '(' . $mKey . ' LIKE concat(\'%\',?,\'%\'))';

                    // 전화번호인 경우 -(하이픈)이 없어도 검색되도록 처리
                    if (stripos($mKey, 'Phone') !== false) {
                        $tmpKeyword = StringUtils::numberToPhone($requestParams['keyword'], true);
                        $this->db->bind_param_push($arrBind, $this->fieldTypes[$mKey], $tmpKeyword);
                    } else {
                        $this->db->bind_param_push($arrBind, $this->fieldTypes[$mKey], $requestParams['keyword']);
                    }
                }
                $arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                $arrWhere[] = $requestParams['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($arrBind, $this->fieldTypes[$requestParams['key']], $requestParams['keyword']);
            }
        }
    }

    /**
     * 회원 테이블 리스트 조회
     *
     * @param array|null $arrWhere        조회
     * @param null       $sort            정렬
     * @param array|null $arrBind         데이터
     * @param null       $start           시작
     * @param null       $limit           조회 수
     * @param array|null $arrIncludeField 조회할 필드
     * @param array|null $arrExcludeField 조회에서 제외할 필드
     *
     * @return array
     */
    public function getMemberList(array $arrWhere = null, $sort = null, array $arrBind = null, $start = null, $limit = null, array $arrIncludeField = null, array $arrExcludeField = null)
    {
        $this->arrBind = $arrBind;
        $this->arrWhere = $arrWhere;
        if (is_null($arrIncludeField) === true) {
            $arrIncludeField = explode(',', 'memNo,memId,groupSno,memNm,nickNm,appFl,memberFl,smsFl,mileage,deposit,maillingFl,saleAmt,entryDt,lastLoginDt,loginCnt,sexFl,email,cellPhone,expirationFl');
        }
        $this->db->strField = implode(', ', DBTableField::setTableField('tableMember', $arrIncludeField, $arrExcludeField, 'm'));
        $this->db->strField .= ', mg.groupNm, IF(ms.connectFl=\'y\', ms.snsTypeFl, \'\') AS snsTypeFl';
        $this->db->strField .= ', IF(mh.updateColumn IS NULL, IF(m.entryDt < \'2014-11-29\', \'2014-11-28 00:00:00\', m.entryDt), MAX(mh.regDt)) AS smsAgreementDt';
        $this->db->strField .= ', IF(mh2.updateColumn IS NULL, IF(m.entryDt < \'2014-11-29\', \'2014-11-28 00:00:00\', m.entryDt), MAX(mh2.regDt)) AS mailAgreementDt';
        if (is_null($arrWhere) === false) {
            $this->db->strWhere = implode(' AND ', $arrWhere);
        }
        if (is_null($sort) === false) {
            $this->db->strOrder = $sort;
        }
        if (is_null($start) === false && is_null($limit) === false) {
            $this->db->strLimit = '?,?';
            $this->db->bind_param_push($arrBind, 'i', $start);
            $this->db->bind_param_push($arrBind, 'i', $limit);
        }
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER_SNS . ' AS ms ON m.memNo = ms.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_GROUP . ' AS mg ON m.groupSno = mg.sno';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_HISTORY . ' AS mh ON (m.memNo=mh.memNo AND mh.updateColumn=\'SMS수신동의\')';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_HISTORY . ' AS mh2 ON (m.memNo=mh2.memNo AND mh2.updateColumn=\'메일수신동의\')';
        $this->db->strGroup = 'm.memNo';
        $arrQuery = $this->db->query_complete(true, true);
        $strSQL = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $arrQuery);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        unset($arrWhere, $arrBind, $arrExcludeField, $arrIncludeField, $arrQuery);

        return $data;
    }

    /**
     * foundRowsByMemberList
     *
     * @return int
     * @throws \Framework\Debug\Exception\DatabaseException
     * @deprecated
     */
    public function foundRowsByMemberList()
    {
        $query = $this->db->getQueryCompleteBackup(
            [
                'field' => 'COUNT(*) AS cnt',
                'limit' => null,
            ]
        );
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);
        $cnt = $this->db->query_fetch($strSQL, $this->arrBind, false)['cnt'];
        StringUtils::strIsSet($cnt, 0);

        return $cnt;
    }

    /**
     * MemberBatchMileageListController::index
     * MemberCrmMileageController::index
     * 회원 마일리지 리스트
     *
     * @param $arrData
     *
     * @return array
     */
    public function getMemberMileagePageList($arrData)
    {
        $getData = $arrBind = $search = $arrWhere = $checked = $selected = [];

        // --- 검색 설정
        $tmp = $this->searchMemberMileageWhere($arrData, Mileage::COMBINE_SEARCH);
        $arrBind = $tmp['arrBind'];
        $search = $tmp['search'];
        $arrWhere = $tmp['arrWhere'];
        $checked = $tmp['checked'];
        $selected = $tmp['selected'];
        $combineSearch = Mileage::COMBINE_SEARCH;
        $search['detailSearch'] = $arrData['detailSearch'];
        //        \Logger::debug(__METHOD__, $arrBind);
        // 검색제한(회원 일괄 관리)
        if (gd_isset($arrData['indicate']) === false) {
            $arrWhere[] = '0';
        }
        // 검색제한(회원등급평가(수동))
        if (gd_isset($arrData['groupValidDt']) === true) {
            $arrWhere[] = 'groupValidDt < now()';
        }

        if (gd_isset($arrData['handleMode'])) {
            $arrWhere[] = 'handleMode = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['handleMode'], $search['handleMode']);
        }

        if (gd_isset($arrData['handleCd'])) {
            $arrWhere[] = 'handleCd = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['handleCd'], $search['handleCd']);
        }

        if (gd_isset($arrData['handleNo'])) {
            $arrWhere[] = 'handleNo = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['handleNo'], $search['handleNo']);
        }

        if (gd_isset($arrData['memNo'])) {
            $arrWhere[] = 'mm.memNo = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memNo'], $search['memNo']);
        }

        if (gd_isset($arrData['deleteFl'])) {
            $arrWhere[] = 'deleteFl = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['deleteFl'], $search['deleteFl']);
        }


        // --- 정렬 설정
        $sort = gd_isset($arrData['sort'], 'mm.sno desc');
        $selected['sort'][$sort] = 'selected="selected"';

        // --- 페이지 설정
        $nowPage = gd_isset($arrData['page'], 1);
        $pageNum = gd_isset($arrData['pageNum'], 10);
        $selected['pageNum'][$pageNum] = 'selected="selected"';
        $page = App::load('Component\\Page\\Page', $nowPage, 0, 0, $pageNum);

        $start = $page->recode['start'];
        $limit = $page->page['list'];

        $data = $this->mileageDAO->selectMemberBatchMileageList($arrData, $start, $limit);
        if (count($data) > 0) {
            Manager::displayListData($data);
        }

        $cnt = $this->mileageDAO->countMemberBatchMileageList();
        unset($arrBind);

        $page->recode['total'] = $cnt; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(Request::getQueryString());

        // --- 각 데이터 배열화
        $getData['data'] = StringUtils::htmlSpecialCharsStripSlashes(gd_isset($data));
        $getData['search'] = StringUtils::htmlSpecialChars($search);
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;
        $getData['combineSearch'] = $combineSearch;
        $getData['groups'] = gd_member_groups(); // 회원등급

        return $getData;
    }

    /**
     * 마일리지 지급/차감 검색조건 쿼리 바인딩 및 프로퍼티 설정
     *
     * @param $search
     * @param $combineSearch
     *
     * @return array
     */
    public function searchMemberMileageWhere($search, $combineSearch)
    {
        $checked = $selected = $arrBind = $arrWhere = [];

        $selected['groupSno'][$search['groupSno']] = $selected['reasonCd'][$search['reasonCd']] = 'selected="selected"';
        $this->searchKeyword($search, $combineSearch, $arrBind, $arrWhere);
        $this->db->bindParameter('reasonCd', $search, $arrBind, $arrWhere, 'tableMemberMileage');
        $this->db->bindParameter('groupSno', $search, $arrBind, $arrWhere, 'tableMember');
        $this->db->bindParameterByRange('mileage', $search, $arrBind, $arrWhere, 'tableMemberMileage', 'mm');

        // 지급/차감 구분
        if ($search['mode'] == 'add') {
            $arrWhere[] = 'mm.mileage >= 0';
        } else if ($search['mode'] == 'remove') {
            $arrWhere[] = 'mm.mileage <= 0';
        }

        // 사유 기타일 경우 사유 내용 확인
        $search['contents'] = gd_isset($search['contents']);
        if ($search['reasonCd'] == Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ETC && $search['contents'] != '') {
            $arrWhere[] = 'mm.contents' . ' LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['contents'], $search['contents']);
        }

        // 지급/차감일
        $search['regDt'][0] = gd_isset($search['regDt'][0]);
        $search['regDt'][1] = gd_isset($search['regDt'][1]);
        $this->db->bindParameterByDateTimeRange('regDt', $search, $arrBind, $arrWhere, 'tableMemberMileage', 'mm');
        $checked['mode'][$search['mode']] = 'checked="checked"';
        $checked['regDtPeriod'][$search['regDtPeriod']] = 'checked="checked"';

        Logger::debug(__METHOD__, $search);

        return [
            'search'   => $search,
            'checked'  => $checked,
            'selected' => $selected,
            'arrBind'  => $arrBind,
            'arrWhere' => $arrWhere,
        ];
    }

    /**
     * 관리자 회원정보 수정
     *
     * @param $arrData
     * @param $from
     *
     * @return bool
     * @throws Exception
     */
    public function modifyMemberData($arrData, $from = null)
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $db = \App::getInstance('DB');
        $memberDAO = \App::load('Component\\Member\\MemberDAO');
        $historyFilter = array_keys($arrData);

        StringUtils::strIsSet($arrData['maillingFl'], 'n');
        StringUtils::strIsSet($arrData['smsFl'], 'n');

        $arrData = $this->validateMemberByAdminModification($arrData);
        //@formatter:off
        $arrBind = $db->get_binding(DBTableField::tableMember(), $arrData, 'update', array_keys($arrData), ['memNo', 'memId', 'memPw',]);
        //@formatter:on

        $before = $memberDAO->selectMemberByOne($arrData['memNo']);
        if ($before['memNo'] != $session->get(\Component\Member\Member::SESSION_MODIFY_MEMBER_INFO . '.memNo')) {
            throw new \Exception('이미 수정중인 회원이 있습니다. 잠시후 다시 시도해주세요.', 902);
        }

        if (isset($arrData['memPw'])) {
            $arrData['changePasswordFl'] = 'y';
            $arrBind['param'][] = 'memPw=?';
            $memPw = $arrData['memPw'];
            if (strlen($memPw) < 17) {
                if(GodoUtils::sha256Fl()) {
                    $memPw = Digester::digest($memPw);
                } else {
                    $memPw = \App::getInstance('password')->hash($memPw);
                }
            }
            $db->bind_param_push($arrBind['bind'], $this->fieldTypes['memPw'], $memPw);
        }
        $db->bind_param_push($arrBind['bind'], 'i', $arrData['memNo']);
        $result = $db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind'], false);

        if ($result == 1) {
            if ($before === null) {
                throw new \Exception(__('수정 전 회원정보가 없습니다.'));
            }
            $after = $memberDAO->selectMemberByOne($arrData['memNo']);
            if ($after === null) {
                throw new \Exception(__('회원 정보를 찾을수 없습니다.'));
            }
            \Component\Member\Util\MemberUtil::combineMemberData($before);
            \Component\Member\Util\MemberUtil::combineMemberData($after);

            $historyService = \App::load('Component\\Member\\History');
            $historyService->setMemNo($arrData['memNo']);
            $historyService->setProcessor('admin');
            $historyService->setProcessorIp($request->getRemoteAddress());
            $historyService->initBeforeAndAfter();
            $historyService->addFilter($historyFilter);
            $historyService->writeHistory();

            //패스워드 변경에 따른 변경 안내 자동메일
            if (isset($arrData['memPw'])) {
                $mailData = [
                    'memNm'    => $after['memNm'],
                    'memId'    => $after['memId'],
                    'changeDt' => DateTimeUtils::dateFormat('Y-m-d', 'now'),
                    'email'    => $after['email'],
                ];
                $mailMimeAuto = \App::load('Component\\Mail\\MailMimeAuto');
                $mailMimeAuto->init(\Component\Mail\MailMimeAuto::CHANGE_PASSWORD, $mailData)->autoSend();
            }
            // 메일,sms 수신동의설정에 변경이 발생한 경우
            if ($before['maillingFl'] !== $after['maillingFl'] || $before['smsFl'] !== $after['smsFl']) {
                $mailData = [
                    'email'      => $after['email'],
                    'memNm'      => $after['memNm'],
                    'smsFl'      => $after['smsFl'],
                    'maillingFl' => $after['maillingFl'],
                    'modDt'      => DateTimeUtils::dateFormat('Y-m-d', 'now'),
                ];
                $mailMimeAuto = \App::load('Component\\Mail\\MailMimeAuto');
                $mailMimeAuto->init(\Component\Mail\MailMimeAuto::AGREEMENT, $mailData)->autoSend();
            }

            if ($before['groupSno'] !== $after['groupSno'] || $from == 'excel') {
                // 관리자가 회원등급을 변경한 경우
                if ($from != 'excel') {
                    $aBasicInfo = gd_policy('basic.info');
                    $groupInfo = \Component\Member\Group\Util::getGroupName('sno=' . $after['groupSno']);
                    $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                    $smsAuto->setSmsType(SmsAutoCode::MEMBER);
                    $smsAuto->setSmsAutoCodeType(\Component\Sms\Code::GROUP_CHANGE);
                    $smsAuto->setReceiver($after['cellPhone']);
                    $smsAuto->setReplaceArguments(
                        [
                            'rc_groupNm' => $groupInfo[$after['groupSno']],
                            'name'       => $after['memNm'],
                            'memNm'      => $after['memNm'],
                            'memId'      => $after['memId'],
                            'mileage'    => $after['mileage'],
                            'deposit'    => $after['deposit'],
                            'groupNm'    => $groupInfo[$after['groupSno']],
                            'rc_mallNm'  => Globals::get('gMall.mallNm'),
                            'shopUrl'    => $aBasicInfo['mallDomain'],
                        ]
                    );
                    $smsAuto->autoSend();

                    \Component\Mail\MailUtil::sendMemberGroupChangeMail($after);
                }

                $this->applyExcelCoupon($from, $before['groupSno'], $after['groupSno'], $arrData);
            }
            if ($before['appFl'] == 'n' && $after['appFl'] == 'y') {
                $this->notifyApprovalJoin($after);
                $this->benefitJoin($after, true);
            }

            // 추천인 등록시 혜택 지급
            if (empty($before['recommId']) && $before['recommFl'] != 'y' && empty($after['recommId']) == false) {
                $benefit = \App::load('Component\\Member\\Benefit');
                $benefit->benefitMoidfyRecommender($after);
                unset($benefit);
            }
            // 회원정보 수정 이벤트
            $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
            if ($modifyEvent->checkAdminModifyFl($before['mallSno'], 'modify') && $after['appFl'] != 'n') {
                $modifyEvent->applyMemberModifyEvent($arrData, $before);
            }
        } else {
            $session->del(\Component\Member\Member::SESSION_MODIFY_MEMBER_INFO);
            throw new Exception(__('회원정보 수정이 실패하였습니다.'));
        }

        return $result;
    }

    /**
     * validateMemberByAdminModification
     *
     * @param $params
     *
     * @return mixed
     */
    protected function validateMemberByAdminModification($params)
    {
        return $this->_validateMemberByAdminModification($params);
    }

    /**
     * 관리자 회원정보 수정 검증
     *
     * @param array $params 회원정보
     *
     * @return mixed
     * @throws Exception
     */
    private function _validateMemberByAdminModification($params)
    {
        // 데이터 조합
        MemberUtil::combineMemberData($params);
        // 회원정보
        $member = $this->memberDAO->selectMemberByOne($params['memNo']);
        $joinItemPolicy = ComponentUtils::getPolicy('member.joinitem', $member['mallSno']);
        $require = MemberUtil::getRequireField(null, false, $member['mallSno']);
        $length = MemberUtil::getMinMax($member['mallSno']);
        StringUtils::strIsSet($joinItemPolicy['busiNo']['charlen'], 10); // 사업자번호 길이
        if ($params['groupSno'] != $member['groupSno']) {
            $params['groupChange'] = true;
        }
        /*
        * 회원 등급 검증 추가
        */
        if (empty($params['groupSno']) === false || $params['groupChange']) {
            $params['groupModDt'] = date('Y-m-d H:i:s');
            if (empty($group['calcKeep']) === false) {
                $params['groupValidDt'] = date('Y-m-d', strtotime('+' . $group['calcKeep'] . ' month'));
            } else {
                $params['groupValidDt'] = '0000-00-00';
            }
        }

        $v = new Validator();
        $v->init();
        $v->add('memId', 'userid', true, '{' . __('아이디') . '}', true, false); // 아이디
        if ($member['mallSno'] > DEFAULT_MALL_NUMBER) {
            // 회원정보 검증 시 해외몰 회원 여부에 따라 처리하는 \Component\Member\MemberValidation::addValidateMember 함수에서 로직때문에 생성한 세션
            \App::getInstance('session')->set(SESSION_GLOBAL_MALL, ['isAdminModify' => true]);
        }
        \Component\Member\MemberValidation::addValidateMember($v, $require);
        $v->add('memNo', 'number', true);
        if (!$this->isExcelUpload) {
            if (gd_isset($params['memPw'], '') !== '') {
                if ($joinItemPolicy['passwordCombineFl'] == 'default') {
                    $v->add('memPw', 'simplePassword', $require['memPw'], '{' . __('비밀번호') . '}'); // 비밀번호
                } else {
                    $v->add('memPw', 'password', $require['memPw'], '{' . __('비밀번호') . '}'); // 비밀번호
                }
                $v->add('memPw', 'minlen', $require['memPw'], '{' . __('비밀번호') . '}', $length['memPw']['minlen'], 6); // 비밀번호 최소길이
                $v->add('memPw', 'maxlen', $require['memPw'], '{' . __('비밀번호') . '}', $length['memPw']['maxlen'], 20); // 비밀번호 최대길이
            }
        }
        $v->add('appFl', 'yn', false, '{' . __('가입승인') . '}'); // 가입승인

        if ($params['appFl'] == 'y' && $member['appFl'] == 'n') {
            $params['approvalDt'] = date('Y-m-d H:i:s');
            $v->add('approvalDt', ''); // 가입승인 일
        }
        $v->add('entryBenefitOfferFl', 'yn', false, '{' . __('가입혜택지급') . '}'); // 가입혜택지급
        if (isset($params['memberFl']) === true && $params['memberFl'] == 'business') {
            \Component\Member\MemberValidation::addValidateMemberBusiness($v, $require);
        }

        if (isset($params['marriFl']) === true && $params['marriFl'] == 'y') {
            $v->add('marriDate', '', $require['marriDate'], '{' . __('결혼기념일') . '}'); // 결혼기념일
        } elseif (isset($params['marriFl']) === true && $params['marriFl'] == 'n') {
            $v->add('marriDate', '', false, '{' . __('결혼기념일') . '}'); // 결혼기념일
            $params['marriDate'] = '';
        }
        $v->add('adminMemo', '', false, '{' . __('관리자메모') . '}'); // 관리자 메모
        \Component\Member\MemberValidation::addValidateMemberExtra($v, $require);
        if ($joinItemPolicy['pronounceName']['use'] == 'y') {
            $v->add('pronounceName', '', $joinItemPolicy['pronounceName']['require'], '{' . __('이름(발음)') . '}');
        }
        if ($v->act($params, true) === false) {
            throw new Exception(implode("\n", $v->errors), 500);
        }

        // 닉네임 중복여부 체크
        if ($require['nickNm'] || !empty($params['nickNm'])) {
            if (MemberUtil::overlapNickNm($params['memId'], $params['nickNm'])) {
                Logger::error(__METHOD__ . ' / ' . sprintf('%s는 이미 사용중인 닉네임입니다', $params['nickNm']));
                throw new Exception(sprintf(__('%s는 이미 사용중인 닉네임입니다'), $params['nickNm']));
            }
        }

        // 이메일 중복여부 체크
        if ($require['email'] || !empty($params['email'])) {
            if (MemberUtil::overlapEmail($params['memId'], $params['email'])) {
                Logger::error(__METHOD__ . ' / ' . sprintf('%s는 이미 사용중인 이메일입니다', $params['email']));
                throw new Exception(sprintf(__('%s는 이미 사용중인 이메일입니다'), $params['email']));
            }
        }

        // 추천아이디 실존인물인지 체크
        if ($require['recommId'] || !empty($params['recommId'])) {
            if (MemberUtil::checkRecommendId($params['recommId'], $params['memId']) === false) {
                throw new Exception(sprintf(__('등록된 회원 아이디가 아닙니다. 추천하실 아이디를 다시 확인해주세요.'), $params['recommId']));
            }
        }

        // 사업자번호 중복여부 체크
        if ($params['memberFl'] == 'business' && !empty($params['busiNo'])) {
            if (strlen(gd_remove_special_char($params['busiNo'])) != $joinItemPolicy['busiNo']['charlen']) {
                throw new Exception(sprintf(__('사업자번호는 %s자로 입력해야 합니다.'), $joinItemPolicy['busiNo']['charlen']));
            }
            if ($params['busiNo'] != $member['busiNo'] && $joinItemPolicy['busiNo']['overlapBusiNoFl'] == 'y' && MemberUtil::overlapBusiNo($params['memId'], $params['busiNo'])) {
                throw new Exception(sprintf(__('이미 등록된 사업자번호입니다. 중복되는 회원이 있는지 확인해주세요.')));
            }
        }

        return $params;
    }

    function applyExcelCoupon($from, $before, $after, $arrData)
    {
        $memberGroupService = new MemberGroup();
        $groupInfoDetail = $memberGroupService->getGroupList();
        foreach ($groupInfoDetail['data'] as $key => $value) {
            if ($value['sno'] == $after) {
                $groupInfoDetail = $groupInfoDetail['data'][$key];
                break;
            }
        }

        $policy = ComponentUtils::getPolicy('member.group');
        $applyCoupon = false;
        //회원 등급 엑셀 수정 시

        if ($from == 'excel') {
            //등급 변경시에만 발급이라면 별도 루틴을 따를 것
            if ($policy['couponConditionExcel'] == 'y') {
                //발급 설정이 되어 있는가?(Y)
                if ($policy['couponConditionExcelChange'] == 'y') {
                    //등급 변경 시에만 발급인가?(Y)
                    if ($before == 0) {
                        //회원 추가 인가?
                        $applyCoupon = false;
                    } else {
                        if ($before !== $after) {
                            //회원 등급이 변경 되었는가?
                            if (!empty($groupInfoDetail['groupCoupon'])) {
                                //업데이트된 회원 등급에 쿠폰 혜택이 있는가?(Y)
                                $applyCoupon = true;
                            } else {
                                $applyCoupon = false;
                            }
                        } else {
                            $applyCoupon = false;
                        }
                    }
                } else {
                    //등급 변경 시에만 발급인가?(N)
                    if (!empty($groupInfoDetail['groupCoupon'])) {
                        //속해있는 회원등급에 쿠폰 혜택이 있는가?(Y)
                        $applyCoupon = true;
                    } else {
                        //속해있는 회원등급에 쿠폰 혜택이 있는가?(N)
                        $applyCoupon = false;
                    }
                }
            } else {
                //발급 설정이 되어 있는가?(N)
                $applyCoupon = false;
            }
        } else {
            //회원등급을 직접 수정 시
            if ($policy['couponConditionManual'] == 'y') {
                //발급 설정이 되어 있는가?(Y)
                if (!empty($groupInfoDetail['groupCoupon'])) {
                    //업데이트된 회원 등급에 쿠폰 혜택이 있는가?(Y)
                    $applyCoupon = true;
                } else {
                    //업데이트된 회원 등급에 쿠폰 혜택이 있는가?(N)
                    $applyCoupon = false;
                }
            } else {
                $applyCoupon = false;
            }
        }

        //쿠폰 지급 하는 코드
        if ($applyCoupon === true) {
            $applyCouponList = explode(INT_DIVISION, $groupInfoDetail['groupCoupon']);
            foreach ($applyCouponList as $value) {
                $coupon = new \Component\Coupon\CouponAdmin;
                \Request::post()->set('couponNo', $value);
                \Request::post()->set('couponSaveAdminId', '회원등급 쿠폰 혜택');
                \Request::post()->set('managerNo', Session::get('manager.sno'));
                \Request::post()->set('memberCouponStartDate', $coupon->getMemberCouponStartDate($value));
                \Request::post()->set('memberCouponEndDate', $coupon->getMemberCouponEndDate($value));
                \Request::post()->set('memberCouponState', 'y');

                $memberArr[] = $arrData['memNo'];

                $coupon->saveMemberCouponSms($memberArr);
                unset($memberArr);
            }
        }
    }

    /**
     * 검색된 선택회원 등급변경
     *
     * @param $groupSno
     * @param $memberNo
     *
     * @return array
     * @throws Exception
     */
    public function applyGroupGradeByMemberNo($groupSno, $memberNo)
    {
        Logger::info(__METHOD__);
        if (is_array($memberNo) === false) {
            throw new Exception(__('회원번호가 없습니다.'));
        }

        $cfgGroup = gd_policy('member.group');
        $arrWhere[] = "find_in_set(memNo,?)";

        $this->db->bind_param_push($arrBind, 's', implode(',', $memberNo));
        $result = $this->getResultByApplyGroupGrade($groupSno, $arrWhere, $arrBind, $cfgGroup);

        return $result;
    }

    /**
     * getResultByApplyGroupGrade
     *
     * @param $groupSno
     * @param $arrWhere
     * @param $arrBind
     * @param $cfgGroup
     *
     * @return array
     */
    protected function getResultByApplyGroupGrade($groupSno, $arrWhere, $arrBind, $cfgGroup)
    {
        return $this->_getResultByApplyGroupGrade($groupSno, $arrWhere, $arrBind, $cfgGroup);
    }

    /**
     * 회원등급 변경 후 결과를 반환하는 함수
     *
     * @param integer $groupSno 변경될 등급 번호
     * @param         $arrWhere
     * @param         $arrBind
     * @param         $cfgGroup
     *
     * @return array
     * @throws Exception
     */
    private function _getResultByApplyGroupGrade($groupSno, $arrWhere, $arrBind, $cfgGroup)
    {
        if (!Validator::required($groupSno)) {
            throw new Exception(__('변경을 위해 선택된 회원등급이 없습니다.'));
        }

        $where = (count($arrWhere) ? ' WHERE ' . implode(' and ', $arrWhere) : '');
        $strSQL = 'SELECT * FROM ' . DB_MEMBER . $where;
        $members = $this->db->query_fetch($strSQL, (empty($arrBind) === false ? $arrBind : null));
        $this->beforeMembersByGroupBatch = $members;
        $result = [
            'total'    => count($members),
            'groupSno' => $groupSno,
            'success'  => 0,
            'fail'     => 0,
            'pass'     => 0,
        ];

        if (isset($members) && is_array($members)) {
            foreach ($members as $val) {
                if ($val['groupSno'] == $groupSno) {
                    $result['pass']++;
                    \Logger::info(__METHOD__ . ' >> group change pass memberNo[' . $val['groupSno'] . '], before group[' . $val['groupSno'] . '], after group[' . $groupSno . ']');
                    continue;
                }

                unset($arrBind);
                $arrBind = [];
                if ($groupSno != $val['groupSno']) {
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['groupSno'], $groupSno);
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['groupModDt'], date('Y-m-d'));
                    if (empty($cfgGroup['calcKeep']) === false) {
                        $this->db->bind_param_push($arrBind, $this->fieldTypes['groupValidDt'], date('Y-m-d', strtotime('+' . $cfgGroup['calcKeep'] . ' month')));
                    } else {
                        $this->db->bind_param_push($arrBind, $this->fieldTypes['groupValidDt'], '0000-00-00');
                    }
                    $this->db->bind_param_push($arrBind, 'i', $val['memNo']);
                    $updateResult = $this->db->set_update_db(DB_MEMBER, 'groupSno=?, groupModDt=?, groupValidDt=?', 'memNo = ?', $arrBind, false);

                    if ($updateResult == 1) {
                        $receiver = $val;
                        $receiver['groupSno'] = $groupSno;
                        $this->afterMembersByGroupBatch[] = $receiver;
                        $result['success']++;

                        //쿠폰 발급 하기
                        $couponPolicy = ComponentUtils::getPolicy('member.group');
                        if ($couponPolicy['couponConditionManual'] == 'y') {
                            //회원등급을직접수정시발급인가?(Y)
                            $group = \App::load('Component\Member\Group\GroupDAO');
                            $groupConfig = $group->selectGroup($groupSno)['groupCoupon'];

                            if (!empty($groupConfig)) {
                                //업데이트된 회원 등급에 쿠폰 혜택이 있는가?(Y)
                                $applyCoupon = true;
                            } else {
                                $applyCoupon = false;
                            }
                        }

                        //쿠폰 지급 하는 코드
                        if ($applyCoupon === true) {
                            $applyCouponList = explode(INT_DIVISION, $groupConfig);
                            foreach ($applyCouponList as $couponValue) {
                                $coupon = new \Component\Coupon\CouponAdmin;
                                \Request::post()->set('couponNo', $couponValue);
                                \Request::post()->set('couponSaveAdminId', '회원등급 쿠폰 혜택');
                                \Request::post()->set('managerNo', Session::get('manager.sno'));
                                \Request::post()->set('memberCouponStartDate', $coupon->getMemberCouponStartDate($couponValue));
                                \Request::post()->set('memberCouponEndDate', $coupon->getMemberCouponEndDate($couponValue));
                                \Request::post()->set('memberCouponState', 'y');

                                $memberArr[] = $val['memNo'];

                                $coupon->saveMemberCouponSms($memberArr);
                                unset($memberArr);
                            }
                        }
                    } else {
                        $result['fail']++;
                        \Logger::info(__METHOD__ . ' >> update fail memberNo[' . $val['groupSno'] . '], before group[' . $val['groupSno'] . '], after group[' . $groupSno . ']');
                    }
                }
            }

            return $result;
        }

        return $result;
    }

    /**
     * 검색된 전체회원 등급변경
     *
     * @param $groupSno
     * @param $searchJson
     *
     * @return array
     * @throws Exception
     */
    public function allApplyGroupGradeByMemberNo($groupSno, $searchJson)
    {
        Logger::info(__METHOD__);
        if (Validator::required($searchJson) === false) {
            throw new Exception(__('검색조건을 찾을 수 없습니다.'));
        }
        $cfgGroup = ComponentUtils::getPolicy('member.group');
        $tmp = $this->searchMemberWhere(ArrayUtils::objectToArray(json_decode($searchJson)));
        $arrBind = $tmp['arrBind'];
        $arrWhere = $tmp['arrWhere'];
        $result = $this->getResultByApplyGroupGrade($groupSno, $arrWhere, $arrBind, $cfgGroup);

        return $result;
    }

    /**
     * 등급변경 알림 메일 발송
     *
     * @param array $members
     */
    public function sendGroupChangeEmail(array $members)
    {
        foreach ($members as $index => $member) {
            MailUtil::sendMemberGroupChangeMail($member);
        }
    }

    /**
     * 일괄 처리 등급변경 알림 SMS 발송
     *
     * @param array $members
     * @param $passwordCheckFl
     *
     * @throws Exception
     */
    public function sendGroupChangeSms(array $members, $passwordCheckFl = true)
    {
        foreach ($members as $index => $member) {
            $aBasicInfo = gd_policy('basic.info');
            $groupInfo = GroupUtil::getGroupName('sno=' . $member['groupSno']);
            $passwordCheckFl = $passwordCheckFl == 'n' ? false : true;
            $this->smsAuto->setPasswordCheckFl($passwordCheckFl);
            $this->smsAuto->setSmsType(SmsAutoCode::MEMBER);
            $this->smsAuto->setSmsAutoCodeType(Code::GROUP_CHANGE);
            $this->smsAuto->setReceiver($member);
            $this->smsAuto->setReplaceArguments(
                [
                    'rc_groupNm' => $groupInfo[$member['groupSno']],
                    'groupNm'    => $groupInfo[$member['groupSno']],
                    'name'       => $member['memNm'],
                    'memNm'      => $member['memNm'],
                    'memId'      => $member['memId'],
                    'mileage'    => $member['mileage'],
                    'deposit'    => $member['deposit'],
                    'rc_mallNm'  => Globals::get('gMall.mallNm'),
                    'shopUrl'    => $aBasicInfo['mallDomain'],
                ]
            );
            $this->smsAuto->autoSend();
        }
    }

    /**
     * 회원등급변경 이력 기록 함수
     *
     * @param array $beforeMembers 변경전 회원정보
     * @param array $members       변경후 회원정보
     */
    public function writeGroupChangeHistory(array $beforeMembers, array $members)
    {
        $historyFilter = [
            'groupSno',
            'groupModDt',
        ];
        $manager = Session::get(Manager::SESSION_MANAGER_LOGIN);
        foreach ($members as $index => $member) {
            Session::set(Member::SESSION_MODIFY_MEMBER_INFO, $beforeMembers[$index]);
            $historyService = new History();
            $historyService->setMemNo($member['memNo']);
            $historyService->setProcessor($manager['managerId']);
            $historyService->setManagerNo($manager['sno']);
            $historyService->setProcessorIp(Request::getRemoteAddress());
            $historyService->initBeforeAndAfter();
            $historyService->addFilter($historyFilter);
            $historyService->writeHistory();
        }
    }

    /**
     * 검색회원 선택 회원가입 승인 처리
     *
     * @param $memberNo
     *
     * @return array
     * @throws Exception
     */
    public function approvalJoinByMemberNo($memberNo)
    {
        $arrBind = $search = $arrWhere = [];

        if (is_array($memberNo) === false) {
            throw new Exception(__('회원번호가 없습니다.'));
        }

        $arrWhere[] = "find_in_set(memNo,?)";
        $this->db->bind_param_push($arrBind, 's', implode(',', $memberNo));

        $result = $this->getResultByApprovalJoin($arrWhere, $arrBind);

        return $result;
    }

    /**
     * getResultByApprovalJoin
     *
     * @param $arrWhere
     * @param $arrBind
     *
     * @return array
     */
    protected function getResultByApprovalJoin($arrWhere, $arrBind)
    {
        return $this->_getResultByApprovalJoin($arrWhere, $arrBind);
    }

    /**
     * 회원 승인 처리 후 결과를 반환하는 함수
     *
     * @param $arrWhere
     * @param $arrBind
     *
     * @return array
     */
    private function _getResultByApprovalJoin($arrWhere, $arrBind)
    {
        $count = count($arrWhere);
        $where = $count > 0 ? ' WHERE ' . implode(' and ', $arrWhere) : '';
        $strSQL = 'SELECT * FROM ' . DB_MEMBER . $where;
        $data = $this->db->query_fetch($strSQL, (count($arrWhere) > 0 ? $arrBind : null));

        $result = [
            'total'   => count($data),
            'appFl'   => 'y',
            'success' => 0,
            'fail'    => 0,
            'pass'    => 0,
        ];

        if (isset($data) && is_array($data)) {
            foreach ($data as $val) {
                if ($val['appFl'] === 'y') {
                    $result['pass']++;
                    continue;
                }
                unset($arrBind);
                $arrBind = [];
                $arrBind['param'][] = 'appFl = ?';
                $arrBind['param'][] = 'approvalDt = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', 'y');
                $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
                $this->db->bind_param_push($arrBind['bind'], 'i', $val['memNo']);
                $this->db->bind_param_push($arrBind['bind'], 's', 'n');
                $updateResult = $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ? AND appFl = ?', $arrBind['bind'], false);
                if ($updateResult == 1) {
                    $result['success']++;
                    $val['appFl'] = 'y';
                    $this->notifyApprovalJoin($val);
                    $this->benefitJoin($val, true);
                } else {
                    $result['fail']++;
                }
            }
        }

        return $result;
    }

    /**
     * 검색회원 전체 회원가입 승인 처리
     *
     * @param $searchJson
     *
     * @return array
     * @throws Exception
     */
    public function allApprovalJoinByMemberNo($searchJson)
    {
        Logger::debug(__METHOD__);

        if (Validator::required($searchJson) === false) {
            throw new Exception(__('검색조건을 찾을 수 없습니다.'));
        }

        $tmp = $this->searchMemberWhere(ArrayUtils::objectToArray(json_decode($searchJson)));
        $arrBind = $tmp['arrBind'];
        $arrWhere = $tmp['arrWhere'];

        $result = $this->getResultByApprovalJoin($arrWhere, $arrBind);

        return $result;
    }

    /**
     * 검색회원 선택 회원가입 미승인 처리
     *
     * @param $memberNo
     *
     * @return array
     * @throws Exception
     */
    public function disapprovalJoinByMemberNo($memberNo)
    {
        Logger::debug(__METHOD__);
        $arrBind = $search = $arrWhere = [];

        if (is_array($memberNo) === false) {
            throw new Exception(__('회원번호가 없습니다.'));
        }

        $arrWhere[] = "find_in_set(memNo,?)";
        $this->db->bind_param_push($arrBind, 's', implode(',', $memberNo));

        $result = $this->getResultByDisapprovalJoin($arrWhere, $arrBind);

        return $result;
    }

    /**
     * getResultByDisapprovalJoin
     *
     * @param $arrWhere
     * @param $arrBind
     *
     * @return array
     */
    protected function getResultByDisapprovalJoin($arrWhere, $arrBind)
    {
        return $this->_getResultByDisapprovalJoin($arrWhere, $arrBind);
    }

    /**
     * 회원 승인 처리 후 결과를 반환하는 함수
     *
     * @param $arrWhere
     * @param $arrBind
     *
     * @return array
     */
    private function _getResultByDisapprovalJoin($arrWhere, $arrBind)
    {
        $where = (count($arrWhere) ? ' WHERE ' . implode(' and ', $arrWhere) : '');
        $strSQL = 'SELECT memNo, appFl FROM ' . DB_MEMBER . $where;
        $data = $this->db->query_fetch($strSQL, (empty($arrBind) === false ? $arrBind : null));

        $result = [
            'total'   => count($data),
            'appFl'   => 'n',
            'success' => 0,
            'fail'    => 0,
            'pass'    => 0,
        ];

        if (isset($data) && is_array($data)) {
            foreach ($data as $val) {
                if ($val['appFl'] == 'n') {
                    $result['pass']++;
                    continue;
                }
                unset($arrBind);
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 'i', $val['memNo']);
                $updateResult = $this->db->set_update_db(DB_MEMBER, 'appFl="n"', 'memNo = ?', $arrBind, false);
                if ($updateResult == 1) {
                    $result['success']++;
                } else {
                    $result['fail']++;
                }
            }

            return $result;
        }

        return $result;
    }

    /**
     * 검색회원 전체 회원가입 미승인 처리
     *
     * @param $searchJson
     *
     * @return array
     * @throws Exception
     */
    public function allDisapprovalJoinByMemberNo($searchJson)
    {
        Logger::debug(__METHOD__);

        if (Validator::required($searchJson) === false) {
            throw new Exception(__('검색조건을 찾을 수 없습니다.'));
        }

        $tmp = $this->searchMemberWhere(ArrayUtils::objectToArray(json_decode($searchJson)));
        $arrBind = $tmp['arrBind'];
        $arrWhere = $tmp['arrWhere'];

        $result = $this->getResultByDisapprovalJoin($arrWhere, $arrBind);

        return $result;
    }

    /**
     * 회원 추천받은 아이디 내역
     *
     * @author     sunny
     * @return array data
     * @deprecated 미사용 함수 사용하지 마시기 바랍니다. 제거 될 함수입니다.
     */
    public function getRecommIdList($memNo)
    {

        // --- 회원아이디
        $arrBind = [];
        $strSQL = 'SELECT memId FROM ' . DB_MEMBER . ' WHERE memNo=?';
        $this->db->bind_param_push($arrBind, 's', $memNo);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        $memId = $data['memId'];

        // --- 검색
        $getData = $arrBind = $arrWhere = [];
        $arrWhere[] = "recommId=?";
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memId'], $memId);

        // --- 페이지 설정
        $nowPage = gd_isset($_GET['page']);
        $pageNum = '10';
        $page = App::load('\\Component\\Page\\Page', $nowPage, 0, 0, $pageNum);
        $funcLists = function ($arrBind, $arrWhere) use ($page) {
            $start = $page->recode['start'];
            $limit = $page->page['list'];

            // --- 목록
            $this->db->strField = "memNo,memId,groupSno,memNm,nickNm,appFl,mileage,entryDt";
            $this->db->strWhere = implode(" and ", $arrWhere);
            $this->db->strOrder = 'entryDt desc';
            $this->db->strLimit = "?,?";
            $this->db->bind_param_push($arrBind, 'i', $start);
            $this->db->bind_param_push($arrBind, 'i', $limit);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT /* 추천인 조회 */' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
            $resultSet = $this->db->query_fetch($strSQL, $arrBind);

            return $resultSet;
        };
        $data = $funcLists($arrBind, $arrWhere);
        $funcFoundRows = function ($arrBind, $arrWhere) {
            $db = \App::getInstance('DB');
            $db->strField = 'COUNT(*) AS cnt';
            $db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT /* 추천인 조회 */' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
            $cnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
            StringUtils::strIsSet($cnt, 0);

            return $cnt;
        };

        // --- 페이지 리셋
        $page->recode['total'] = $funcFoundRows($arrBind, $arrWhere); // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // --- 각 데이터 배열화
        $getData['data'] = StringUtils::htmlSpecialCharsStripSlashes(gd_isset($data));
        $getData['groups'] = gd_member_groups(); // 회원등급

        return $getData;
    }

    /**
     * 메일 수신여부 카운트 함수
     *
     * @param $groupSno
     *
     * @return array
     */
    public function mailingAgreeCount($groupSno)
    {
        $member = \App::load('\\Component\\Member\\Member');

        if (empty($groupSno)) {
            $requestParams = [
                'maillingFl' => 'n',
            ];
            $reject = $member->lists($requestParams, null, null, 'maillingFl');
            $rejectCount = count($reject);

            return [
                'all'    => intval($member->getCount(DB_MEMBER, 1, 'WHERE sleepFl!=\'y\'')),
                'reject' => $rejectCount,
            ];
        } else {
            $requestParams = [
                'maillingFl' => 'n',
                'groupSno'   => $groupSno,
            ];
            $reject = $member->lists($requestParams, null, null, 'maillingFl');
            $rejectCount = count($reject);

            return [
                'all'    => intval($member->getCount(DB_MEMBER, 1, 'WHERE sleepFl!=\'y\' AND groupSno=\'' . $groupSno . '\'')),
                'reject' => $rejectCount,
            ];
        }
    }

    /**
     * @return array
     */
    public function getBeforeMembersByGroupBatch()
    {
        return $this->beforeMembersByGroupBatch;
    }

    /**
     * @return array
     */
    public function getAfterMembersByGroupBatch()
    {
        return $this->afterMembersByGroupBatch;
    }

    public function approvalJoin(array $members)
    {
        $result = [
            'total'   => count($members),
            'appFl'   => 'y',
            'success' => 0,
            'fail'    => 0,
            'pass'    => 0,
        ];

        if (isset($data) && is_array($data)) {
            foreach ($data as $val) {
                if ($val['appFl'] === 'y') {
                    $result['pass']++;
                    continue;
                }
                unset($arrBind);
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 'i', $val['memNo']);
                $updateResult = $this->db->set_update_db(DB_MEMBER, 'appFl="y"', 'memNo = ? AND appFl=\'n\'', $arrBind, false);
                if ($updateResult == 1) {
                    $result['success']++;
                    $val['appFl'] = 'y';
                    $this->notifyApprovalJoin($val);
                    $this->benefitJoin($val, true);
                } else {
                    $result['fail']++;
                }
            }
        }

        return $result;
    }
}
