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


use Component\Database\DBTableField;
use Component\Member\Util\MemberUtil;
use Exception;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * Class History
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class History extends \Component\AbstractComponent
{
    protected $mallDao;
    private $before;
    private $after;
    private $memNo;
    private $processor;
    private $managerNo;
    private $processorIp;
    private $filters = [];
    private $otherValue;
    private $columnName = [
        'memNo'                   => '회원번호',
        //__('회원번호')
        'memId'                   => '아이디',
        //__('아이디')
        'groupSno'                => '회원등급sno',
        //__('회원등급sno')
        'groupModDt'              => '등급수정일',
        //__('등급수정일')
        'groupValidDt'            => '등급유효일',
        //__('등급유효일')
        'memNm'                   => '이름',
        //__('이름')
        'nickNm'                  => '닉네임',
        //__('닉네임')
        'memPw'                   => '비밀번호',
        //__('비밀번호')
        'appFl'                   => '가입승인',
        //__('가입승인')
        'memberFl'                => '회원구분',
        //__('회원구분')
        'entryBenefitOfferDt'     => '가입혜택지급일',
        //__('가입혜택지급일')
        'sexFl'                   => '성별',
        //__('성별')
        'birthDt'                 => '생년월일',
        //__('생년월일')
        'calendarFl'              => '양력,음력',
        //__('양력,음력')
        'email'                   => '이메일',
        //__('이메일')
        'zipcode'                 => '구 우편번호',
        //__('구 우편번호')
        'zonecode'                => '우편번호',
        //__('우편번호')
        'address'                 => '주소',
        //__('주소')
        'addressSub'              => '상세주소',
        //__('상세주소')
        'phone'                   => '전화번호',
        //__('전화번호')
        'cellPhone'               => '휴대폰',
        //__('휴대폰')
        'phoneCountryCode'        => '전화번호 국가코드',
        //__('전화번호')
        'cellPhoneCountryCode'    => '휴대폰 국가코드',
        //__('휴대폰')
        'fax'                     => '팩스번호',
        //__('팩스번호')
        'company'                 => '회사명',
        //__('회사명')
        'service'                 => '업태',
        //__('업태')
        'item'                    => '종목',
        //__('종목')
        'busiNo'                  => '사업자번호',
        //__('사업자번호')
        'ceo'                     => '대표자',
        //__('대표자')
        'comZipcode'              => '사업장 구 우편번호',
        //__('사업장 구 우편번호')
        'comZonecode'             => '사업장 우편번호',
        //__('사업장 우편번호')
        'comAddress'              => '사업장주소',
        //__('사업장주소')
        'comAddressSub'           => '사업장상세주소',
        //__('사업장상세주소')
        'mileage'                 => '마일리지',
        //__('마일리지')
        'deposit'                 => '예치금',
        //__('예치금')
        'maillingFl'              => '메일수신동의',
        //__('메일수신동의')
        'smsFl'                   => 'SMS수신동의',
        //__('SMS수신동의')
        'marriFl'                 => '결혼여부',
        //__('결혼여부')
        'marriDate'               => '결혼기념일',
        //__('결혼기념일')
        'job'                     => '직업',
        //__('직업')
        'interest'                => '관심분야',
        //__('관심분야')
        'reEntryFl'               => '재가입여부',
        //__('재가입여부')
        'entryDt'                 => '회원가입일',
        //__('회원가입일')
        'entryPath'               => '가입경로',
        //__('가입경로')
        'lastLoginDt'             => '최종로그인',
        //__('최종로그인')
        'lastLoginIp'             => '최종로그인IP',
        //__('최종로그인IP')
        'lastSaleDt'              => '최종구매일',
        //__('최종구매일')
        'loginCnt'                => '로그인횟수',
        //__(로그인횟수)
        'saleCnt'                 => '구매횟수',
        //__('구매횟수')
        'saleAmt'                 => '총구매금액',
        //__('총구매금액')
        'memo'                    => '남기는말',
        //__('남기는말')
        'recommId'                => '추천인ID',
        //__('추천인ID')
        'ex1'                     => '추가1',
        //__('추가1')
        'ex2'                     => '추가2',
        //__('추가2')
        'ex3'                     => '추가3',
        //__('추가3')
        'ex4'                     => '추가4',
        //__('추가4')
        'ex5'                     => '추가5',
        //__('추가5')
        'ex6'                     => '추가6',
        //__(추가6)
        'privateApprovalFl'       => '개인정보 수집 및 이용 필수',
        //__('개인정보 수집 및 이용 필수')
        'privateApprovalOptionFl' => '개인정보 수집 및 이용 선택',
        //__('개인정보 수집 및 이용 선택')
        'privateOfferFl'          => '개인정보동의 제3자 제공',
        //__('개인정보동의 제3자 제공')
        'privateConsignFl'        => '개인정보동의 취급업무 위탁',
        //__('개인정보동의 취급업무 위탁')
        'foreigner'               => '내외국인구분',
        //__('내외국인구분')
        'dupeinfo'                => '중복가입확인정보',
        //__('중복가입확인정보')
        'pakey'                   => '가상번호',
        //__('가상번호')
        'rncheck'                 => '본인확인방법',
        //__('본인확인방법')
        'adminMemo'               => '관리자 메모',
        //__('관리자 메모')
        'sleepFl'                 => '휴면회원 여부',
        //__('휴면회원 여부')
        'sleepMailFl'             => '휴면전환안내메일발송여부',
        //__('휴면전환안내메일발송여부')
        'expirationFl'            => '개인정보유효기간',
        //__('개인정보유효기간')
        'regDt'                   => '등록일',
        //__('등록일')
        'modDt'                   => '수정일',
        //__('수정일')
    ];
    private $exclude = [
        'modDt',
        'groupModDt',
    ];

    public function __construct(array $config = [])
    {
        $this->mallDao = is_object($config['mallDao']) ? $config['mallDao'] : \Component\Mall\MallDAO::getInstance();
        parent::__construct($config['db']);
    }

    /**
     * 수정 전/후 데이터 불러오기
     *
     * @return $this
     */
    public function initBeforeAndAfter()
    {
        $session = \App::getInstance('session');
        $before = $session->get(Member::SESSION_MODIFY_MEMBER_INFO, null);
        $after = $this->db->getData(DB_MEMBER, $this->memNo, 'memNo');

        MemberUtil::combineMemberData($before);
        MemberUtil::combineMemberData($after);

        $this->before = $before;
        $this->after = $after;

        return $this;
    }

    /**
     * 회원정보 수정이력 저장
     *
     */
    public function writeHistory()
    {
        $this->validateInsert();

        $different = [];
        $this->getDifferent($different);
        if (key_exists('smsFl', $different)) {
            $this->filters[] = 'smsFl';
        }
        if (key_exists('maillingFl', $different)) {
            $this->filters[] = 'maillingFl';
        }

        if (key_exists('privateApprovalOptionFl', $different)) {
            $this->filters[] = 'privateApprovalOptionFl';
        }
        if (key_exists('privateOfferFl', $different)) {
            $this->filters[] = 'privateOfferFl';
        }
        if (key_exists('privateConsignFl', $different)) {
            $this->filters[] = 'privateConsignFl';
        }

        $hasFilter = count($this->filters) > 0;
        foreach ($different as $key => $value) {
            if ($hasFilter && !in_array($key, $this->filters)) {
                continue;
            }
            $this->insertHistory($key, $value);
        }
    }

    /**
     * 수정이력을 저장할 대상항목 추가
     *
     * @param $column
     */
    public function addFilter($column)
    {
        if (is_array($column)) {
            $this->filters = $column;
        } else {
            $this->filters[] = $column;
        }
    }

    /**
     * addExclude
     *
     * @param $column
     */
    public function addExclude($column)
    {
        if (is_array($column)) {
            $this->exclude = array_merge($this->exclude, $column);
        } else {
            $this->exclude[] = $column;
        }
    }

    /**
     * 수정이력 저장 전/후 데이터 검증
     *
     * @throws Exception
     */
    public function validateInsert()
    {
        if (is_null($this->before)) {
            throw new Exception(__('회원 정보를 찾을 수 없습니다.'));
        }
        if (is_null($this->after)) {
            throw new Exception(__('수정하신 회원 정보를 찾을 수 없습니다.'));
        }
    }

    /**
     * 수정이력 데이터 중 서로 다른 데이터 체크 및 저장할 데이터 생성
     *
     * @param $different
     */
    public function getDifferent(&$different)
    {
        foreach ($this->after as $key => $value) {
            if (in_array($key, $this->exclude)) {
                continue;
            }

            $beforeValue = $this->before[$key];
            if ($value != $beforeValue) {
                if ('memPw' == $key) {
                    $value = $beforeValue = __('비밀번호 변경');
                }
                if ('groupSno' == $key) {
                    $displayArray = [];
                    $dao = \App::load('Component\\Member\\Group\\GroupDAO');
                    $resultSet = $dao->selectGroupName();
                    foreach ($resultSet as $index => $item) {
                        $displayArray[$item['sno']] = $item['groupNm'];
                    }
                    $beforeValue = $displayArray[$beforeValue];
                    $value = $displayArray[$value];
                    if (key_exists('groupModDt', $this->after) && ($this->before['groupModDt'] != $this->after['groupModDt'])) {
                        $different['groupModDt'] = [
                            $this->before['groupModDt'],
                            $this->after['groupModDt'],
                        ];
                    }
                }
                if ('interest' == $key) {
                    // 같은내용인데 변경내용으로 저장되는 경우가 있음 ex) 01001001 > |01001001|
                    $displayArray = gd_code('01001');
                    $before = $after = [];
                    $beforeValueArray = array_filter(explode('|', $beforeValue));
                    $valueArray = array_filter(explode('|', $value));
                    foreach ($beforeValueArray as $value2) {
                        $before[] = $displayArray[$value2];
                    }
                    foreach ($valueArray as $value3) {
                        $after[] = $displayArray[$value3];
                    }

                    $beforeValue = @implode($before, ',');
                    $value = @implode($after, ',');
                    unset($before);
                    unset($after);
                }
                if ('job' == $key) {
                    $displayArray = gd_code('01002');
                    $beforeValue = $displayArray[$beforeValue];
                    $value = $displayArray[$value];
                }
                if ('memberFl' == $key) {
                    $displayArray = [
                        'personal' => __('개인회원'),
                        'business' => __('사업자회원'),
                    ];
                    $beforeValue = $displayArray[$beforeValue];
                    $value = $displayArray[$value];
                }
                if ('marriFl' == $key) {
                    $displayArray = [
                        'n' => __('미혼'),
                        'y' => __('기혼'),
                    ];
                    $beforeValue = $displayArray[$beforeValue];
                    $value = $displayArray[$value];
                }
                unset($displayArray);
                $different[$key] = [
                    $beforeValue,
                    $value,
                ];
            }
        }
    }

    /**
     * 수정이력 대상항목 DB 저장
     *
     * @param        $key
     * @param  array $value
     *
     * @return int
     */
    public function insertHistory($key, $value)
    {
        $session = \App::getInstance('session');
        $arrData['memNo'] = $this->after['memNo'];
        $arrData['processor'] = $this->processor;
        $arrData['managerNo'] = $session->get('manager.sno');
        $arrData['processorIp'] = $this->processorIp;
        $arrData['updateColumn'] = $this->columnName[$key];
        if (is_array($value['otherValue']) && count($value['otherValue']) > 0) {
            $arrData['otherValue'] = $value['otherValue'];
        } else if (!empty($this->otherValue)) {
            $arrData['otherValue'] = $this->otherValue;
        }
        $arrData['beforeValue'] = $value[0];
        $arrData['afterValue'] = $value[1];
        $arrBind = $this->db->get_binding(DBTableField::tableMemberHistory(), $arrData, 'insert');
        $this->db->set_insert_db(DB_MEMBER_HISTORY, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrData);

        return $this->db->insert_id();
    }

    /**
     * 수정이력 추가할 회원번호
     *
     * @param $memNo
     */
    public function setMemNo($memNo)
    {
        $this->memNo = $memNo;
    }

    /**
     * 수정이력 처리자
     *
     * @param $processor
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    /**
     * 수정이력 처리자 아이피
     *
     * @param $processorIp
     */
    public function setProcessorIp($processorIp)
    {
        $this->processorIp = $processorIp;
    }

    /**
     * 수정이력 처리자가 관리자인 경우
     *
     * @param $managerNo
     */
    public function setManagerNo($managerNo)
    {
        $this->managerNo = $managerNo;
    }


    /**
     * @param mixed $after
     */
    public function setAfter($after)
    {
        $this->after = $after;
    }

    /**
     * otherValue값(주문서 작성 시 주소 변경 적용) 저장
     *
     * @throws Exception
     */
    public function setOtherValue($otherValue)
    {
        $this->otherValue = $otherValue;
    }

    /**
     * 회원 정보 수정 이력
     *
     * @param $memNo
     * @param $nowPage
     * @param $pageNum
     *
     * @return array
     */
    public function getMemberHistory($memNo, $nowPage, $pageNum)
    {
        // --- 페이지 설정
        $selected['pageNum'][$pageNum] = 'selected="selected"';
        $page = \App::load('Component\\Page\\Page', $nowPage, 0, 0, $pageNum);

        $funcLists = function () use ($page, $memNo) {
            $this->db->strField = "h.* , m.isDelete";
            $this->db->strWhere = "memNo = ?";
            $this->db->bind_param_push($arrBind, 'i', $memNo);
            $this->db->strOrder = 'regDt desc';
            $this->db->strLimit = '?,?';
            $this->db->bind_param_push($arrBind, 'i', $page->recode['start']);
            $this->db->bind_param_push($arrBind, 'i', $page->page['list']);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HISTORY . ' as h LEFT OUTER JOIN ' . DB_MANAGER . ' as m ON h.managerNo = m.sno ' . implode(' ', $query);

            return $this->db->query_fetch($strSQL, $arrBind);
        };
        $resultSet = $funcLists();
        $funcFoundRows = function () use ($memNo) {
            $db = \App::getInstance('DB');
            $db->strField = 'COUNT(*) AS cnt';
            $db->strWhere = 'memNo=?';
            $db->bind_param_push($arrBind, 'i', $memNo);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HISTORY . ' as h LEFT OUTER JOIN ' . DB_MANAGER . ' as m ON h.managerNo = m.sno ' . implode(' ', $query);
            $resultSet = $this->db->query_fetch($strSQL, $arrBind, false);
            StringUtils::strIsSet($resultSet['cnt'], 0);

            return $resultSet['cnt'];
        };
        $cnt = $funcFoundRows();
        Manager::displayListData($resultSet);
        // --- 페이지 리셋
        $page->recode['total'] = $cnt; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        if ($resultSet !== null) {
            $countries = $this->mallDao->selectCountries();
            $countryNames = [];
            $memberLifeEventValue = []; // 평생회원 이벤트 참여
            foreach ($countries as $index => $country) {
                if ($country['callPrefix'] > 0) {
                    $countryNames[$country['code']] = $country['countryNameKor'] . '(+' . $country['callPrefix'] . ')';
                }
            }
            foreach ($resultSet as $index => $item) {
                if ($item['updateColumn'] == '개인정보유효기간' && $item['beforeValue'] != '999' && $item['afterValue'] == '999' && $item['processor'] == 'member') {
                    $memberLifeEventValue[$index] = '(평생회원 이벤트 참여로 변경)';
                }

                $item['idx'] = $page->idx--;
                if ($item['updateColumn'] == '휴대폰 국가코드' || $item['updateColumn'] == '전화번호 국가코드') {
                    $item['beforeValue'] = $countryNames[$item['beforeValue']];
                    $item['afterValue'] = $countryNames[$item['afterValue']];
                }
                StringUtils::strIsSet($item['otherValue'], '{}');
                $otherValue = json_decode($item['otherValue'], true);
                StringUtils::strIsSet($otherValue['smsFl']['sms080']['regDt'], '');
                if (!empty($otherValue['smsFl']['sms080']['regDt']) && $item['updateColumn'] == 'SMS수신동의') {
                    $item['displayOtherValue'] = sprintf(' (080 수신거부 : %s)', $otherValue['smsFl']['sms080']['regDt']);
                }
                if (!empty($otherValue['reflectApplyMemberInfo']['orderNo']) && empty($otherValue['simpleJoin'])) {
                    $item['otherValue'] = sprintf('(주문서 작성 시 변경 : 주문번호 - ' . $otherValue['reflectApplyMemberInfo']['orderNo'] . ')');
                }
                if (!empty($otherValue['reflectApplyMemberInfo']['orderNo']) && !empty($otherValue['simpleJoin'])) {
                    $item['otherValue'] = sprintf('(비회원 주문 간단 가입 시 등록 : 주문번호 - ' . $otherValue['reflectApplyMemberInfo']['orderNo'] . ')');
                }
                $resultSet[$index] = $item;
            }

            if (count($memberLifeEventValue) > 0) {
                $resultSet[gd_array_last_key($memberLifeEventValue)]['memberLifeEventValue'] = gd_array_last($memberLifeEventValue);
                unset($memberLifeEventValue);
            }
        }

        return [
            'data' => gd_htmlspecialchars_stripslashes($resultSet),
            'page' => $page,
        ];
    }

    /**
     * 회원의 마지막 수신동의 일자 조회
     *
     * @param $memberNo
     *
     * @return array|object
     */
    public function selectLastReceiveAgreementByMember($memberNo)
    {
        // __('SMS수신동의')
        // __('메일수신동의')
        $this->db->strField = 'mh.memNo, mh.updateColumn, mh.afterValue, MAX(mh.regDt) AS lastUpdateDt';
        $this->db->strWhere = 'mh.memNo=? AND mh.updateColumn IN (\'SMS수신동의\', \'메일수신동의\')';
        $this->db->strGroup = 'mh.updateColumn';
        $this->db->bind_param_push($bind, 'i', $memberNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HISTORY . ' AS mh' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $bind);

        return $result;
    }

    /**
     * 회원의 마지막 수신동의 일자 조회
     *
     * @param $memberNo
     *
     * @return array
     */
    public function getLastReceiveAgreementByMember($memberNo)
    {
        // __('SMS수신동의')
        // __('메일수신동의')
        $history = $this->selectLastReceiveAgreementByMember($memberNo);
        $result = [];
        foreach ($history as $item) {
            if ($item['updateColumn'] == 'SMS수신동의') {
                $result['lastReceiveAgreementDt']['sms'] = DateTimeUtils::dateFormat('Y-m-d', $item['lastUpdateDt']);
            } elseif ($item['updateColumn'] == '메일수신동의') {
                $result['lastReceiveAgreementDt']['mail'] = DateTimeUtils::dateFormat('Y-m-d', $item['lastUpdateDt']);
            }
        }

        return $result;
    }
}
