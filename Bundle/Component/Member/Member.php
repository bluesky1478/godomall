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
use Bundle\Component\Godo\GodoKakaoServerApi;
use Component\Database\DBTableField;
use Component\Facebook\Facebook;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\GoodsStatistics\GoodsStatistics;
use Component\Mail\MailMimeAuto;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Util\MemberUtil;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Component\Sms\SmsAutoObserver;
use Component\Validator\Validator;
use Encryptor;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Object\SimpleStorage;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\ImageUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Framework\Security\Digester;
use Framework\Utility\GodoUtils;
use Globals;
use Logger;
use Request;
use Session;
use UserFilePath;
use Component\Member\MemberDAO;
use Component\Sms\SmsAuto;

/**
 * Class 회원 관리
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class Member extends \Component\AbstractComponent
{
    /** 로그인 세션 키 */
    const SESSION_MEMBER_LOGIN = 'member';
    /** 드림시큐리티 휴대폰 인증 관련 정보 세션 키 */
    const SESSION_DREAM_SECURITY = 'SESSION_DREAM_SECURITY';
    /** 아이핀 인증 관련 정보 세션 키 */
    const SESSION_IPIN = 'SESSION_IPIN';
    /** 본인인증정보 세션 키 */
    const SESSION_USER_CERTIFICATION = 'SESSION_USER_CERTIFICATION';
    /** 본인인증정보 세션 키 */
    const SESSION_USER_MAIL_CERTIFICATION = 'SESSION_USER_MAIL_CERTIFICATION';

    /** 회원정보 수정 세션 키 */
    const SESSION_MODIFY_MEMBER_INFO = 'SESSION_MODIFY_MEMBER_INFO';
    /** 회원가입 세션 키 */
    const SESSION_JOIN_INFO = 'SESSION_JOIN_INFO';
    /** 신규회원 세션 키 */
    const SESSION_NEW_MEMBER = 'SESSION_NEW_MEMBER';
    /** 연령 인증 확인 세션 키 */
    const SESSION_CHECK_AGE_AUTH = 'SESSION_CHECK_AGE_AUTH';
    /** 프론트 자동로그인 쿠키 키 */
    const COOKIE_AUTO_LOGIN = 'COOKIE_FRONT_AUTO_LOGIN';
    /** 마이앱 SNS 로그인 메시지 세션 키 */
    const SESSION_MYAPP_SNS_LOGIN_MESSAGE = 'SESSION_MYAPP_SNS_LOGIN_MESSAGE';
    /** 마이앱 SNS 로그인 세션 키 */
    const SESSION_MYAPP_SNS_LOGIN = 'SESSION_MYAPP_SNS_LOGIN';
    /** 마이앱 SNS 자동 로그인 세션 키 */
    const SESSION_MYAPP_SNS_AUTO_LOGIN = 'SESSION_MYAPP_SNS_AUTO_LOGIN';
    /** 회원 통합검색 항목 */
    const COMBINE_SEARCH = [
        'memId'     => '아이디',
        //__('아이디')
        'memNm'     => '이름',
        //__('이름')
        'nickNm'    => '닉네임',
        //__('닉네임')
        'email'     => '이메일',
        //__('이메일')
        'cellPhone' => '휴대폰번호',
        //__('휴대폰번호')
        'phone'     => '전화번호',
        //__('전화번호')
        'company'   => '회사명',
        //__('회사명')
        'busiNo'    => '사업자등록번호',
        //__('사업자등록번호')
        'ceo'       => '대표자명',
        //__('대표자명')
        'recommId'  => '추천인아이디',
        //__('추천인')
        'fax'       => '팩스번호',
        //__('fax')
    ];
    /** 모바일앱 회원 통합검색 항목 */
    const MOBILEAPP_COMBINE_SEARCH = [
        'all'       => '통합검색',
        'memId'     => '아이디',
        'memNm'     => '이름',
        'email'     => '이메일',
        'phone'     => '전화번호',
        'cellPhone' => '휴대폰',
    ];
    /** @var array 회원 가입항목 */
    public $memberJoinItemSet = [];
    /** @var bool 회원 가입항목 로딩 여부 */
    protected $isMemberJoinItemSet = false;
    protected $isExcelUpload = false;
    protected $fieldTypes;
    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
    private $mailMimeAuto;
    /** @var  \Bundle\Component\Member\MemberDAO */
    private $memberDAO;
    /** @var  \Bundle\Component\Sms\SmsAuto */
    private $smsAuto;

    /**
     * Member constructor.
     *
     * @param array $config 클래스 생성자에 필요한 각종 설정을 받는 인자. 객체 주입이 주 목적이다.
     */
    public function __construct($config = [])
    {
        parent::__construct();
        $this->tableFunctionName = 'tableMember';
        $this->fieldTypes = DBTableField::getFieldTypes($this->tableFunctionName);
        if (isset($config['memberDao']) && \is_object($config['memberDao'])) {
            $this->memberDAO = $config['memberDao'];
        } else {
            $this->memberDAO = \App::load(MemberDAO::class);
        }

        if (isset($config['smsAuto']) && \is_object($config['smsAuto'])) {
            $this->smsAuto = $config['smsAuto'];
        } else {
            $this->smsAuto = \App::load(SmsAuto::class);
        }
    }

    /**
     * 관리자 회원검색 리스트 셀렉트 박스 목록
     *
     * @static
     * @return array
     */
    public static function getCombineSearchSelectBox()
    {
        $result = [];
        $i = 0;
        foreach (self::COMBINE_SEARCH as $key => $val) {
            $result[$key] = $val;
            if ($key == 'phone' || $key == 'ceo') {
                $result['__disable' . $i] = '==========';
            }
            $i++;
        }

        return $result;
    }

    /**
     * 회원 정보
     *
     * @author artherot
     *
     * @param integer $memNo 회원번호
     *
     * @return array 회원 정보
     */
    public function getMemberInfo($memNo = null)
    {
        $session = \App::getInstance('session');
        if ($memNo === null && !$session->get(self::SESSION_MEMBER_LOGIN)['memNo']) {
            return [];
        }

        // --- 회원 정보
        //@formatter:off
        $arrInclude['member'] = [ 'memNm', 'groupSno', 'memberFl', 'mileage', 'deposit', 'email', 'phoneCountryCode', 'phone', 'cellPhoneCountryCode', 'cellPhone', 'fax', 'zonecode', 'zipcode', 'address', 'addressSub', 'company', 'ceo', 'busiNo', 'service', 'item', 'comZonecode', 'comZipcode', 'comAddress', 'comAddressSub', 'saleAmt', 'mallSno', 'adultFl', 'adultConfirmDt', 'sexFl', 'birthDt',];
        $arrExclude['memberGroup'] = [ 'apprFigureOrderPriceFl', 'apprFigureOrderRepeatFl', 'apprFigureReviewRepeatFl', 'apprFigureOrderPriceMore', 'apprFigureOrderPriceBelow', 'apprFigureOrderRepeat', 'apprFigureReviewRepeat', 'apprPointMore', 'apprPointBelow', 'apprFigureOrderPriceMoreMobile', 'apprFigureOrderPriceBelowMobile', 'apprFigureOrderRepeatMobile', 'apprFigureReviewRepeatMobile', 'apprPointMoreMobile', 'apprPointBelowMobile', 'regId',];
        //@formatter:on
        $arrField['member'] = DBTableField::setTableField('tableMember', $arrInclude['member'], null, 'm');
        $arrField['memberGroup'] = DBTableField::setTableField('tableMemberGroup', null, $arrExclude['memberGroup'], 'mg');

        $this->db->strField = implode(', ', $arrField['member']) . ', ' . implode(', ', $arrField['memberGroup']);
        $this->db->strJoin = 'INNER JOIN ' . DB_MEMBER_GROUP . ' mg ON m.groupSno = mg.sno ';

        if ($memNo === null) {
            $this->db->strWhere = 'm.appFl = \'y\' AND m.sleepFl = \'n\' AND m.memNo = ? AND m.memId = ? AND m.memPw = ?';

            $arrBind = [];
            $memPw = Encryptor::decrypt(Session::get('member.memPw'));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memNo'], Session::get('member.memNo'));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memId'], Session::get('member.memId'));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memPw'], $memPw);
        } else {
            $this->db->strWhere = 'm.appFl = \'y\' AND m.sleepFl = \'n\' AND m.memNo = ?';
            $arrBind = [];
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memNo'], $memNo);

        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' m ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        // 데이타가 없으면 리턴
        if (empty($data) === true) {
            return false;
        }

        if ($data['mallSno'] > DEFAULT_MALL_NUMBER) {
            $data['dcLine'] = 0;
            $data['dcPercent'] = 0;
            $data['overlapDcLine'] = 0;
            $data['overlapDcPercent'] = 0;
            $data['mileageLine'] = 0;
            $data['mileagePercent'] = 0;
            $data['settleGb'] = 'all';
        }
        // 데이터 변형
        //@formatter:off
        $data = gd_array_json_decode(
            gd_htmlspecialchars_stripslashes($data),
            [ 'fixedRateOption', 'dcExOption', 'dcExScm', 'dcExCategory', 'dcExBrand', 'dcExGoods', 'overlapDcOption', 'overlapDcScm', 'overlapDcCategory', 'overlapDcBrand', 'overlapDcGoods', 'dcBrandInfo',]
        );
        //@formatter:on

        // 등급 아이콘 처리
        if ($data['groupMarkGb'] == 'icon' && empty($data['groupIcon']) === false) {
            $data['groupIcon'] = ImageUtils::imagePrint(UserFilePath::icon('group_icon', $data['groupIcon'])->www(), 'image', $data['groupNm'], 'middle');
        }

        // 등급 이미지 처리
        if ($data['groupMarkGb'] == 'icon' && empty($data['groupImage']) === false) {
            $data['groupImage'] = ImageUtils::imagePrint(UserFilePath::icon('group_image', $data['groupImage'])->www(), 'image', $data['groupNm'], 'middle');
        }

        $data['groupNmWithLabel'] = $data['groupNm'] . SkinUtils::displayGroupLabel();

        return $data;
    }

    /**
     * 회원 아이디
     *
     * @author     artherot
     *
     * @param integer $memNo 회원번호
     *
     * @return array 회원 정보
     * @deprecated use getMember
     */
    public function getMemberId($memNo)
    {
        // --- 회원 정보
        $arrInclude[0] = [
            'memId',
            'nickNm',
        ];
        $arrInclude[1] = ['groupNm'];
        $arrField[0] = DBTableField::setTableField('tableMember', $arrInclude[0], null, 'm');
        $arrField[1] = DBTableField::setTableField('tableMemberGroup', $arrInclude[1], null, 'mg');
        $this->db->strField = implode(', ', $arrField[0]) . ', ' . implode(', ', $arrField[1]);
        $this->db->strJoin = 'INNER JOIN ' . DB_MEMBER_GROUP . ' mg ON m.groupSno = mg.sno ';
        $this->db->strWhere = 'm.memNo = ?';
        $arrBind = [];
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memNo'], $memNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' m ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        return $data;
    }

    /**
     * getRecommendCount
     * 추천받은 횟수 반환
     *
     * @param string $recommendId 추천아이디
     *
     * @return int
     */
    public function getRecommendCount($recommendId)
    {
        $arrBind = [];
        $strSQL = 'SELECT count(memNo) as cnt FROM ' . DB_MEMBER . ' WHERE recommId=?';
        $this->db->bind_param_push($arrBind, 's', $recommendId);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        return $data['cnt'];
    }

    /**
     * 회원 삭제 함수
     *
     * @param integer $memNo 회원번호
     *
     * @throws Exception
     */
    public function delete($memNo)
    {
        if (Validator::number($memNo, null, null, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), __('회원번호')), 500);
        }
        $arrBind = [
            'i',
            $memNo,
        ];
        $this->db->set_delete_db(DB_MEMBER, 'memNo = ?', $arrBind);
        $this->db->set_delete_db(DB_MEMBER_MILEAGE, 'memNo = ?', $arrBind);
    }

    /**
     * 휴면회원 체크 함수
     *
     * @param $sleepFl
     * @param $memId
     * @param $memPw
     *
     * @throws AlertRedirectException
     */
    public function checkSleepMember($sleepFl, $memId, $memPw)
    {
        // --- 휴면회원 체크 및 해제
        if ($sleepFl === 'y') {
            Session::del(Member::SESSION_DREAM_SECURITY);
            Session::del(Member::SESSION_IPIN);
            Session::set(
                MemberSleep::SESSION_WAKE_INFO, [
                    'memId' => $memId,
                    'memPw' => $memPw,
                ]
            );
            \Logger::channel('userLogin')->warning('휴면회원 해제 필요', [$this->getRequestData()]);
            throw new AlertRedirectException(__('휴면회원 해제가 필요합니다.'), 401, null, '../member/wake.php', 'parent');
        }
    }

    /**
     * 로그인 처리 함수
     *
     * @param $memId
     * @param $memPw
     *
     * @throws AlertRedirectException
     * @throws Exception
     */
    public function login($memId, $memPw)
    {
        $logger = \App::getInstance('logger');
        $logger->channel('userLogin')->info('로그인 시도', [$this->getRequestData()]);

        // 동일 IP 접근제한 로그 (로그인 시도)
        $loginIp = Request::getRemoteAddress();
        $this->setLogIpUserLoginTry($loginIp);

        $session = App::getInstance('session');
        if ($session->has(Member::SESSION_MEMBER_LOGIN && Request::isMyapp() != true)) {
            throw new AlertRedirectException(__('회원으로 로그인 된 상태입니다.'), 300, null, '/');
        }

        if (Validator::userid($memId, true, false) === false) {
            throw new Exception(sprintf(__('입력된 %s은(는) 잘못된 형식입니다.'), __('아이디')), 500);
        }

        if (Validator::required($memPw) === false) {
            throw new Exception(sprintf(__('입력된 %s은(는) 잘못된 형식입니다.'), __('정보')), 500);
        }

        // 동일 IP 로그인 연속 시도에 의한 접속제한 처리
        $logIpLoginTryInfo = $this->selectLogIpUserLoginTry($loginIp, 'Y');
        if ($logIpLoginTryInfo['limitFlag'] === 'Y') {
            if ($this->isCheckLoginTimeout($logIpLoginTryInfo['onLimitDt'])) {
                $logger->channel('userLogin')->warning('로그인이 제한된 회원입니다.', [$this->getRequestData(['userLoginTryLimit' => $logIpLoginTryInfo,]),]);
                throw new Exception('존재하지 않거나 잘못된 정보로 잦은 로그인 시도하였습니다. 정보보호를 위해 15분간 접속이 차단됩니다.', 500);
            }
        }

        $memberWithGroup = $this->getMemberByLogin($memId);
        if ($memberWithGroup['loginLimit']['limitFlag'] == 'y') {
            if ($this->isGreaterThanLimitLoginTime($memberWithGroup['loginLimit']['onLimitDt'])) {
                $logger->channel('userLogin')->warning('로그인이 제한된 회원입니다.', [$this->getRequestData(['loginLimit' => $memberWithGroup['loginLimit'],]),]);
                throw new \Exception('로그인이 제한되었습니다. 10분 후에 시도해 주세요.', 500);
            } else {
                $initLimitLoginLog = $this->initLimitLoginLog($memberWithGroup);
                $memberWithGroup = $initLimitLoginLog['member'];
                unset($initLimitLoginLog);
            }
        }
        if ($session->has(SESSION_GLOBAL_MALL)) {
            $mallBySession = $session->get(SESSION_GLOBAL_MALL);
            if ($memberWithGroup['mallSno'] != $mallBySession['sno']) {
                $logger->channel('userLogin')->warning(
                    '가입된 상점이 다릅니다.', [
                        $this->getRequestData(
                            [
                                'memberMallSno'  => $memberWithGroup['mallSno'],
                                'sessionMallSno' => $mallBySession['sno'],
                            ]
                        ),
                    ]
                );
                throw new Exception(__('회원정보를 찾을 수 없습니다.'), 500);
            }
        } elseif ($memberWithGroup['mallSno'] != DEFAULT_MALL_NUMBER) {
            $logger->channel('userLogin')->warning(
                '가입된 상점이 다릅니다.', [
                    $this->getRequestData(
                        [
                            'memberMallSno' => $memberWithGroup['mallSno'],
                        ]
                    ),
                ]
            );
            throw new Exception(__('회원정보를 찾을 수 없습니다.'), 500);
        }
        if (!is_array($memberWithGroup) && $memberWithGroup['memId'] != $memId) {
            $logger->channel('userLogin')->warning(
                '로그인 아이디가 다릅니다.', [
                    $this->getRequestData(
                        [
                            'memberMallId' => $memberWithGroup['memId'],
                        ]
                    ),
                ]
            );
            throw new Exception(__('회원정보를 찾을 수 없습니다.'), 500);
        }

        $memberWithGroup = $this->credentialMemberPassword($memPw, $memberWithGroup);
        $hackOutParams = [
            'memNo' => $memberWithGroup['memNo'],
            'memId' => $memberWithGroup['memId'],
        ];
        $this->_validateHackOuMember($hackOutParams);

        $this->checkSleepMember($memberWithGroup['sleepFl'], $memId, $memPw);

        $this->_validateApplyFlag(gd_isset($memberWithGroup['appFl'], 'n'));
        $this->_checkAdultFlagAndUpdate($memberWithGroup);

        $encryptMember = MemberUtil::encryptMember($memberWithGroup);

        $this->saveLoginLog($encryptMember['memNo']);
        $this->refreshMemberByLogin($encryptMember['memNo'], $encryptMember['loginCnt']);
        $this->refreshBasket($encryptMember['memNo']);
        if ($encryptMember['loginLimit']['loginFailCount'] > 0) {
            $initLimitLoginLog = $this->initLimitLoginLog($encryptMember);
            $encryptMember = $initLimitLoginLog['member'];
            unset($initLimitLoginLog);
        }
        // 모듈 설정
        $cart = \App::load('Component\\Cart\\Cart');
        $cart->setMergeCart($encryptMember['memNo']);
        $this->setSessionByLogin($encryptMember);
    }

    /**
     * 회원 로그인 정보 갱신
     *
     * @param $memberNo
     * @param $loginCount
     */
    public function refreshMemberByLogin($memberNo, $loginCount)
    {
        $now = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');
        $ip = Request::getRemoteAddress();
        //@formatter:off
        $arrData = [ $now, $ip, $loginCount + 1,];
        $arrWhere = [ 'lastLoginDt', 'lastLoginIp', 'loginCnt',];
        //@formatter:on
        $this->update($memberNo, 'memNo', $arrWhere, $arrData);
    }

    /**
     * 로그인 후 회원정보 및 만료시간 세션 저장
     *
     * @param array $member
     *
     * @throws Exception
     */
    public function setSessionByLogin(array $member)
    {
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $session->set(self::SESSION_MEMBER_LOGIN, $member);
        $session->set('expireTime', time());
        if ($session->has(self::SESSION_MEMBER_LOGIN)) {
            \Logger::channel('userLogin')->info('로그인 성공', [$this->getRequestData()]);
            // 마이앱 sns 로그인 여부
            $myappBuilderInfo = gd_policy('myapp.config')['builder_auth'];
            if (\Request::isMyapp() && empty($myappBuilderInfo['clientId']) === false && empty($myappBuilderInfo['secretKey']) === false) {
                $session->set(self::SESSION_MYAPP_SNS_LOGIN, 'y');
                $session->set(self::SESSION_MYAPP_SNS_LOGIN_MESSAGE, 'y');
                $memberSession = $session->get(self::SESSION_MEMBER_LOGIN);
                // sns 타입별 자동로그인 체크
                if ($memberSession['snsTypeFl'] == 'facebook') {
                    $saveAutoLogin = \Cookie::get('saveAutoLogin');
                    if (!\Request::isMyapp()) {
                        \Cookie::del('saveAutoLogin');
                    }
                } else {
                    $saveAutoLogin = \Request::get()->get('saveAutoLogin');
                }
                if ($saveAutoLogin == 'y') {
                    $session->set(self::SESSION_MYAPP_SNS_AUTO_LOGIN, 'y');
                } else {
                    $session->del(self::SESSION_MYAPP_SNS_AUTO_LOGIN);
                }

                // 마이앱 IOS NSHTTPCookie > NSHTTPCookieExpires 만료시간 저장
                \Cookie::set('NSHTTPCookieExpires', $session->get('expireTime'));
            }
        } elseif (!$session->has(SESSION_GLOBAL_MALL) && $session->has('notDefaultStoreMember')) {
            \Logger::channel('userLogin')->warning('기준몰 회원만 로그인 가능', [$this->getRequestData()]);
            // 기준몰에서 해외몰 로그인 시에만 발생하기 때문에 이동 페이지를 루트로 설정하였음
            if ($request->isMobile()) {
                throw new AlertRedirectException('기준몰 회원만 로그인 가능합니다', null, null, '/');
            }
            throw new AlertCloseException('기준몰 회원만 로그인 가능합니다');
        }
    }

    /**
     * 로그인 비밀번호를 재검증 하는 함수
     *
     * @param $loginPwd
     * @param $data
     *
     * @return mixed
     * @throws Exception
     */
    public function credentialMemberPassword($loginPwd, $data)
    {
        // validate credential.
        //@formatter:off
        if (Digester::isValid($data['memPw'], $loginPwd)) {
            return $data;
        }
        if (App::getInstance('password')->verify($loginPwd, $data['memPw']) === false) {
            // 지우지 마세요.
            // legacy code - mysql password or md5 or old_password or sha512
            // old_passwords variable is deprecated as of MySQL 5.7.6 and will be removed in a future MySQL release.
            // old_password function remove
            // http://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_old_passwords
            $result = $this->db->query_fetch( 'select if (memPw in (password(?), md5(?), sha2(?, 512)), 1, 0) as result from ' . DB_MEMBER . ' where memNo = ?', [ 'sssi', $loginPwd, $loginPwd, $loginPwd, $data['memNo'],], false);
            if ($result['result']) {
                // password hash and update member.
                if(GodoUtils::sha256Fl()) {
                    $digesterPwd = Digester::digest($loginPwd);
                } else {
                    $digesterPwd = App::getInstance('password')->hash($loginPwd);
                }
                $this->db->bind_query('update ' . DB_MEMBER . ' set memPw = ? where memNo = ?', ['si', $digesterPwd, $data['memNo'],]);
                $data['memPw'] = $digesterPwd;

                return $data;
            } else {
                $data['errorData'] = 'Result[\' . $result[\'result\'] . \'] LoginPwd[\' . $loginPwd . \'] HashPwd[\' . App::getInstance(\'password\')->hash($loginPwd) . \'] Digester[\' . Digester::digest($loginPwd) . \']\'';
                $logger = \App::getInstance('logger');
                // 파일로그 생성시 개인정보 암호화
                if (isset($data) && is_array($data)) {
                    $encryptKey = ['cellPhone', 'email', 'memNm'];
                    foreach ($data as $dKey => $dVal) {
                        if (in_array($dKey, $encryptKey)) {
                            $data[$dKey] = \Encryptor::encryptJson($dVal);
                        }
                    }
                }
                $logger->channel('userLogin')->warning('아이디 또는 비밀번호를 다시한번 확인해 주시기 바랍니다.', [$this->getRequestData($data)]);
                throw new \Component\Member\Exception\LoginException($data['memId'], __('아이디 또는 비밀번호를 다시한번 확인해 주시기 바랍니다.'));
            }
        } else {
            if(GodoUtils::sha256Fl()) {
                $digesterPwd = Digester::digest($loginPwd);
                $this->db->bind_query('update ' . DB_MEMBER . ' set memPw = ? where memNo = ?', ['si', $digesterPwd, $data['memNo'],]);
                $data['memPw'] = $digesterPwd;
            }
            return $data;
        }
        //@formatter:on

        return $data;
    }

    /**
     * 로그인 로그 저장
     *
     * @param $memNo
     */
    public function saveLoginLog($memNo)
    {
        $arrBind = [];
        $strSQL = "SELECT sno FROM " . DB_MEMBER_LOGINLOG . " WHERE date_format(regDt,'%Y-%m-%d') = ? AND memNo = ?";
        $this->db->bind_param_push($arrBind, 's', date('Y-m-d'));
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);
        if (empty($tmp['sno']) === true) {
            $arrData['memNo'] = $memNo;
            if (Request::isMobile()) {
                $arrData['loginCntMobile'] = 1;
            } else {
                $arrData['loginCnt'] = 1;
            }
            $arrBind = $this->db->get_binding(DBTableField::tableMemberLoginlog(), $arrData, 'insert', array_keys($arrData));
            $this->db->set_insert_db(DB_MEMBER_LOGINLOG, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrData);
            unset($arrBind);
        } else {
            $arrBind = [];
            if (Request::isMobile()) {
                $arrUpdate[] = 'loginCntMobile = loginCntMobile + 1';
            } else {
                $arrUpdate[] = 'loginCnt = loginCnt + 1';
            }
            $this->db->bind_param_push($arrBind, 's', $tmp['sno']);
            $this->db->set_update_db(DB_MEMBER_LOGINLOG, $arrUpdate, 'sno = ?', $arrBind);
            unset($arrUpdate);
            unset($arrBind);
        }
    }

    /**
     * 회원정보 수정
     *
     * @param mixed $data     조건절 바인드 데이터
     * @param mixed $where    조건절 컬럼
     * @param array $arrField 업데이트 대상 컬럼
     * @param array $arrData  업데이트 바인드 데이터
     *
     * @example
     * update(10, 'memNo', ['email', 'memNm'], ['example@godo.co.kr', '고도샘플']);
     * update('example@godo.co.kr', 'email', ['nickNm', 'memNm'], ['고도닉네임샘플', '고도샘플']);
     */
    public function update($data, $where, array $arrField, array $arrData)
    {
        $arrBind = $arrUpdate = [];

        foreach ($arrField as $key => $value) {
            $arrUpdate[] = $value . '= ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes[$value], $arrData[$key]);
        }

        if (is_array($data) === true && is_array($where) === true && count($data) === count($where)) {
            $this->db->strWhere = implode(' AND', $where);
            foreach ($where as $idx => $val) {
                $fieldType = $this->fieldTypes[$val];
                $this->db->bind_param_push($arrBind, $fieldType, $data[$idx]);
            }
        } else {
            $this->db->strWhere = $where;

            if ($data !== null) {
                $this->db->strWhere = $where . ' = ?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes[$where], $data);
            }
        }

        $this->db->set_update_db(DB_MEMBER, $arrUpdate, $this->db->strWhere, $arrBind);
        $this->db->query_reset();
    }

    /**
     * 장바구니 갱신
     *
     * @param $memNo
     */
    public function refreshBasket($memNo)
    {
        $arrBind = [];

        $arrUpdate[] = 'memNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->bind_param_push($arrBind, 's', Session::get('siteKey'));
        $this->db->set_update_db(DB_CART, $arrUpdate, 'siteKey = ?', $arrBind);
        unset($arrUpdate);
        unset($arrBind);

        // 장바구니 통계 회원 정보 업데이트
        $cartStatistics['memNo'] = $memNo;
        $cartStatistics['siteKey'] = Session::get('siteKey');
        $goodsStatistics = new GoodsStatistics();
        $goodsStatistics->setCartMemberUpdateStatistics($cartStatistics);
    }

    /**
     * 아이디찾기
     *
     * @param $array    이름, 이메일, 휴대폰번호, 국가코드
     *
     * @return string 아이디
     * @throws Exception
     */
    public function findId($param)
    {
        if (Validator::required($param['userName']) === false) {
            throw new Exception(__('이름을 입력해 주시기 바랍니다.'));
        }
        $userValueArr = [];
        $session = \App::getInstance('session');
        $mall = $session->get(SESSION_GLOBAL_MALL);
        gd_isset($param['findIdFl'], 'email');

        if ($param['findIdFl'] === 'email') {
            if (Validator::email($param['userEmail'], true) === false) {
                throw new Exception(__('이메일을 입력해 주시기 바랍니다.'));
            }
            $userValueArr['userValue'] = $param['userEmail'];
        } elseif ($param['findIdFl'] == 'cellPhone') {
            if (empty($param['userCellPhoneNum'])) {
                throw new Exception(__('휴대폰번호를 입력해 주시기 바랍니다.'));
            }
            $userValueArr['cellPhoneCountryCode'] = (empty($param['cellPhoneCountryCode']) === false) ? $param['cellPhoneCountryCode'] : 'kr';
            $param['mallSno'] = gd_isset($mall['sno'], DEFAULT_MALL_NUMBER);
            $userValueArr['userValue'] = MemberUtil::phoneFormatter($param['userCellPhoneNum']);
        }

        // 일반 회원 찾기
        $findMember = function ($param, $userValueArr) {
            $db = \App::getInstance('DB');
            $db->strLimit = '1';
            if ($param['findIdFl'] == 'email') {
                $paramData = [
                    $param['userName'],
                    $userValueArr['userValue']
                ];
                $whereData = [
                    'memNm',
                    $param['findIdFl']
                ];
                $columnData = 'memId,memNm,' . $param['findIdFl'] . ',mallSno';
            } elseif ($param['findIdFl'] == 'cellPhone') {
                $paramData = [
                    $param['userName'],
                    $userValueArr['userValue'],
                    $userValueArr['cellPhoneCountryCode'],
                    $param['mallSno']
                ];
                $whereData = [
                    'memNm',
                    $param['findIdFl'],
                    'cellPhoneCountryCode',
                    'mallSno'
                ];
                $columnData = 'memId,memNm,' . $param['findIdFl'] . ',cellPhoneCountryCode,mallSno';
            }
            $data = $db->getData(DB_MEMBER, $paramData, $whereData, $columnData, true);

            return $data;
        };
        // 휴면 회원 찾기
        $findSleepMember = function ($param, $userValueArr) {
            $db = \App::getInstance('DB');
//            $db->strLimit = '1';
            $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_MEMBER_SLEEP));
            $arrBind = [
                $fieldTypes['memNm'] . $fieldTypes[$param['findIdFl']],
                $param['userName'],
                $userValueArr['userValue']
            ];
            $cellPhoneCountryCode = '';
            if ($param['findIdFl'] == 'email') {
                $cellPhoneCountryCode = '';
            } elseif ($param['findIdFl'] == 'cellPhone') {
                $cellPhoneCountryCode = ' AND m.cellPhoneCountryCode="' . $userValueArr['cellPhoneCountryCode'] . '"';
            }

            $query = 'SELECT ms.memId, m.mallSno FROM ' . DB_MEMBER_SLEEP . ' AS ms JOIN ' . DB_MEMBER . ' AS m ON ms.memNo=m.memNo' . $cellPhoneCountryCode . ' WHERE ms.memNm=? AND ms.' . $param['findIdFl'] . '=?';
            $data = $db->query_fetch($query, $arrBind);

            return $data;
        };
        $memberData = $findMember($param, $userValueArr);
        $sleepMemberData = $findSleepMember($param, $userValueArr);
        $data = array_merge($memberData, $sleepMemberData);


        if ($param['findIdFl'] == 'email') {
            if (count($data) >= 1) {
                $data = $data[0];
            }
        } else {
            if (count($data) == 1) {
                $data = $data[0];
            } else if (count($data) > 1) {
                throw new \Exception(__('이름과 휴대폰번호가 모두 동일한 회원이 있습니다. 고객센터에 문의해주세요.'), 500);
            }
        }

        if ($session->has(SESSION_GLOBAL_MALL)) {
            if ($data['mallSno'] != $mall['sno']) {
                throw new \Exception(__('회원정보를 찾을 수 없습니다.'));
            }
        } elseif ($data['mallSno'] != DEFAULT_MALL_NUMBER) {
            throw new \Exception(__('회원정보를 찾을 수 없습니다.'));
        }
        $memId = '';
        if (!empty($data['memId'])) {
            $memId = $data['memId'];
        }

        return $memId;
    }

    /**
     * @deprecated
     * @uses MemberDAO::selectMemberByOne
     * 회원정보를 반환하는 함수
     *
     * @param null   $data   bind 데이터
     * @param null   $where  bind 컬럼명
     * @param string $column 조회할 컬럼
     *
     * @param bool   $dataArray
     *
     * @return array|null|object 회원정보
     */
    public function getMember($data = null, $where = null, $column = '*', $dataArray = false)
    {
        \Logger::info(__METHOD__);
        $arrBind = [];
        $this->db->strField = $column;
        if (is_array($data) === true && is_array($where) === true && count($data) === count($where)) {
            $arrWhere = [];

            foreach ($where as $idx => $val) {
                $arrWhere[] = $val . '=?';
                $fieldType = $this->fieldTypes[$val];
                $this->db->bind_param_push($arrBind, $fieldType, $data[$idx]);
            }
            $this->db->strWhere = implode(' AND ', $arrWhere);
        } else {
            if ($data !== null) {
                $this->db->strWhere = $where . ' = ?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes[$where], $data);
            } else {
                $this->db->strWhere = $where;
            }
        }

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $arrBind, $dataArray);

        unset($arrBind, $where, $strSQL);

        return $data;
    }

    /**
     * 회원 비밀번호 수정
     *
     * @param $memId
     * @param $memPw
     */
    public function updatePassword($memId, $memPw)
    {
        $arrUpdate[] = 'memPw = ?';
        $arrUpdate[] = 'modDt = now()';
        if(GodoUtils::sha256Fl()) {
           $pw =  Digester::digest($memPw);
        } else {
            $pw = App::getInstance('password')->hash($memPw);
        }
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memPw'], $pw);
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memId'], $memId);
        $this->db->set_update_db(DB_MEMBER, $arrUpdate, 'memId = ?', $arrBind);
    }

    /**
     * 회원정보 수정(다건)
     *
     * @param array $membersNo 회원번호
     * @param array $field
     * @param array $data
     */
    public function updateMemberByMembersNo(array $membersNo, array $field, array $data)
    {
        $fields = DBTableField::getFieldTypes('tableMember');
        $arrBind = $arrUpdate = [];
        foreach ($field as $key => $value) {
            $arrUpdate[] = $value . '= ?';
            $this->db->bind_param_push($arrBind, $fields[$value], $data[$key]);
        }
        $strWhere = 'memNo IN(' . implode(',', array_fill(0, count($membersNo), '?')) . ')';
        foreach ($membersNo as $no) {
            $this->db->bind_param_push($arrBind, 'i', $no);
        }
        $this->db->set_update_db(DB_MEMBER, $arrUpdate, $strWhere, $arrBind);
    }

    /**
     * 성인인증 정보갱신
     *
     */
    public function updateAdultInfo()
    {
        if (Session::has('member')) {
            $arrUpdate[] = "adultConfirmDt = now()";
            $arrUpdate[] = "adultFl = ?";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['adultFl'], 'y');
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memNo'], Session::get('member.memNo'));
            $this->db->set_update_db(DB_MEMBER, $arrUpdate, 'memNo = ?', $arrBind);
        }
    }

    /**
     * 회원 리스트 조회 함수
     *
     * @param array  $requestParams
     * @param null   $offset
     * @param null   $limit
     *
     * @param string $column
     *
     * @return array
     */
    public function lists(array $requestParams, $offset = null, $limit = null, $column = '*')
    {
        $arrBind = $arrWhere = [];

        $this->bindParameterByList($requestParams, $arrBind, $arrWhere);

        $this->db->strField = $column;
        $this->db->strWhere = implode(' AND ', $arrWhere);
        if ($offset !== null && $limit !== null) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_MEMBER . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);
        $result = [];
        foreach ($resultSet as $index => $item) {
            $result[] = new MemberVO($item);
        }
        unset($arrBind, $arrWhere, $arrQuery, $resultSet);

        return $result;
    }

    /**
     * 회원 리스트 조회 검색항목 쿼리 바인드 함수
     *
     * @param array $requestParams
     * @param array $arrBind
     * @param array $arrWhere
     * @param null  $prefix
     */
    public function bindParameterByList(array $requestParams, array &$arrBind, array &$arrWhere, $prefix = null)
    {
        $this->db->bindParameterByKeyword(self::COMBINE_SEARCH, $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);

        $this->db->bindParameter('memberFl', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('entryPath', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('appFl', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('groupSno', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('sexFl', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('maillingFl', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('smsFl', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('calendarFl', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('marriFl', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameter('connectSns', $requestParams, $arrBind, $arrWhere, 'tableMemberSns', 'ms', 'snsTypeFl');

        $this->db->bindParameterByRange('saleCnt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByRange('saleAmt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByRange('mileage', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByRange('deposit', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByRange('loginCnt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);

        $this->db->bindParameterByDateTimeRange('entryDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByDateTimeRange('lastLoginDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByDateTimeRange('sleepWakeDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByDateTimeRange('birthDt', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);
        $this->db->bindParameterByDateTimeRange('marriDate', $requestParams, $arrBind, $arrWhere, $this->tableFunctionName, $prefix);

        $prefix = is_null($prefix) ? '' : $prefix . '.';

        // 만14세 미만회원만 보기가 체크된 경우 연령층 검색은 전체로 설정된다.
        if (gd_isset($requestParams['under14'], 'n') === 'y') {
            $under14Date = DateTimeUtils::getDateByUnderAge(14);
            $arrWhere[] = $prefix . 'birthDt > ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['birthDt'], $under14Date);
        } else {
            // 연령층
            $requestParams['age'] = gd_isset($requestParams['age']);
            if ($requestParams['age'] > 0) {
                $ageTerms = DateTimeUtils::getDateByAge($requestParams['age']);
                $arrWhere[] = $prefix . 'birthDt BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['birthDt'], $ageTerms[1]);
                $this->db->bind_param_push($arrBind, $this->fieldTypes['birthDt'], $ageTerms[0]);
            }
        }

        // 장기 미로그인
        $novisit = (int) $requestParams['novisit'];
        if ($novisit >= 0 && is_numeric($requestParams['novisit'])) {
            $arrWhere[] = 'IF(' . $prefix . 'lastLoginDt IS NULL, DATE_FORMAT(' . $prefix . 'entryDt,\'%Y%m%d\') <= ?, DATE_FORMAT(' . $prefix . 'lastLoginDt,\'%Y%m%d\') <= ?)';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $novisit . ' day')));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['lastLoginDt'], date('Ymd', strtotime('-' . $novisit . ' day')));
        }

        // 휴면회원 여부
        $arrWhere[] = $prefix . 'sleepFl != \'y\'';
    }

    /**
     * 회원, 쿠폰, 회원 SNS 테이블 데이터를 함께 조회하는 함수
     *
     * @see \Bundle\Component\Member\MemberDAO::selectListBySearch
     *
     * @param array $requestParams
     * @param null  $offset
     * @param null  $limit
     *
     * @return array|object
     */
    public function listsWithCoupon(array $requestParams, $offset = null, $limit = null)
    {
        $requestParams['offset'] = $offset;
        $requestParams['limit'] = $limit;

        return $this->memberDAO->selectListBySearch($requestParams);
    }

    /**
     * listsWithCoupon 함수의 검색결과 카운트 시 사용되는 함수
     *
     * @param array $requestParams
     *
     * @return int
     */
    public function foundRowsByListsWithCoupon(array $requestParams)
    {
        $countListBySearch = $this->memberDAO->countListBySearch($requestParams);
        StringUtils::strIsSet($countListBySearch, 0);

        return $countListBySearch;
    }

    /**
     * 회원 검색 개수
     *
     * @param array $requestParams
     *
     * @return mixed
     */
    public function getCountBySearch(array $requestParams)
    {
        return $this->memberDAO->selectListBySearchCount($requestParams);
    }

    /**
     * 메일링 수신 거부 처리
     *
     * @param $rejectEmail
     *
     * @throws Exception
     */
    public function rejectMailing($rejectEmail)
    {
        $dao = \App::load('Component\\Member\\MemberDAO');
        $arrMember = $dao->selectByAll($rejectEmail, 'email');
        $isUpdate = false;
        foreach ($arrMember as $index => $member) {
            if ($member['maillingFl'] == 'y') {
                $isUpdate = true;
                $targetMember = $member;
                break;
            }
        }
        if ($isUpdate) {
            $this->update($rejectEmail, 'email', ['maillingFl'], ['n']);
            $history = \App::load('Component\\Member\\History');
            $history->setAfter(['memNo' => $targetMember['memNo']]);    // 위에서 루프로 메일 수신동의 상태를 확인 했지만 유일값이기 때문에 첫번째 회원에게만 변경이력을 남기도록 처리함
            $history->setProcessor(\App::getInstance('session')->get(\Component\Member\Manager::SESSION_MANAGER_LOGIN . '.managerId', 'admin'));
            $history->setProcessorIp(\App::getInstance('request')->getRemoteAddress());
            $history->insertHistory(
                'maillingFl', [
                    'y',
                    'n',
                ]
            );  // 회원정보 변경 내역 저장
            $this->mailMimeAuto = \App::load('Component\\Mail\\MailMimeAuto');
            $this->mailMimeAuto->init(MailMimeAuto::REJECTEMAIL, $targetMember)->autoSend();
        } else {
            throw new Exception(__('이미 수신거부 처리되었습니다.'));
        }
    }

    /**
     * 회원가입
     *
     * @param $params
     *
     * @return \Component\Member\MemberVO
     * @throws Exception
     */
    public function join($params)
    {
        $session = \App::getInstance('session');
        $globals = \App::getInstance('globals');
        $logger = \App::getInstance('logger');

        if (isset($params['birthYear']) === true && isset($params['birthMonth']) === true && isset($params['birthDay']) === true) {
            $params['birthDt'] = $params['birthYear'].'-'.$params['birthMonth'].'-'.$params['birthDay'];
        }
        if (isset($params['marriYear']) === true && isset($params['marriMonth']) === true && isset($params['marriDay']) === true) {
            $params['marriDate'] = $params['marriYear'].'-'.$params['marriMonth'].'-'.$params['marriDay'];
        }

        $vo = $params;
        if (is_array($params)) {
            DBTableField::setDefaultData($this->tableFunctionName, $params);
            $vo = new \Component\Member\MemberVO($params);
        }

        $vo->databaseFormat();
        $vo->setEntryDt(date('Y-m-d H:i:s'));
        $vo->setGroupSno(GroupUtil::getDefaultGroupSno());

        $v = new Validator();
        $v->init();
        $v->add('agreementInfoFl', 'yn', true, '{' . __('이용약관') . '}'); // 이용약관
        $v->add('privateApprovalFl', 'yn', true, '{' . __('개인정보 수집.이용 동의 필수사항') . '}'); // 개인정보동의 이용자 동의사항
        $v->add('privateApprovalOptionFl', '', false, '{' . __('개인정보 수집.이용 동의 선택사항') . '}'); // 개인정보동의 이용자 동의사항
        $v->add('privateOfferFl', '', false, '{' . __('개인정보동의 제3자 제공') . '}'); // 개인정보동의 제3자 제공
        $v->add('privateConsignFl', '', false, '{' . __('개인정보동의 취급업무 위탁') . '}'); // 개인정보동의 취급업무 위탁
        $v->add('foreigner', '', false, '{' . __('내외국인구분') . '}'); // 내외국인구분
        $v->add('dupeinfo', '', false, '{' . __('본인확인 중복가입확인정보') . '}'); // 본인확인 중복가입확인정보
        $v->add('pakey', '', false, '{' . __('본인확인 번호') . '}'); // 본인확인 번호
        $v->add('rncheck', '', false, '{' . __('본인확인방법') . '}'); // 본인확인방법
        $v->add('under14ConsentFl', 'yn', true, '{' . __('만 14세 이상 동의') . '}'); // 만 14세 이상 동의

        $joinSession = new SimpleStorage($session->get(Member::SESSION_JOIN_INFO));
        $session->del(Member::SESSION_JOIN_INFO);
        $vo->setPrivateApprovalFl($joinSession->get('privateApprovalFl'));
        $vo->setPrivateApprovalOptionFl(json_encode($joinSession->get('privateApprovalOptionFl'), JSON_UNESCAPED_SLASHES));
        $vo->setPrivateOfferFl(json_encode($joinSession->get('privateOfferFl'), JSON_UNESCAPED_SLASHES));
        $vo->setPrivateConsignFl(json_encode($joinSession->get('privateConsignFl'), JSON_UNESCAPED_SLASHES));
        $vo->setForeigner($joinSession->get('foreigner'));
        $vo->setDupeinfo($joinSession->get('dupeinfo'));
        $vo->setPakey($joinSession->get('pakey'));
        $vo->setRncheck($joinSession->get('rncheck'));
        $vo->setUnder14ConsentFl($joinSession->get('under14ConsentFl'));
        $toArray = $vo->toArray();
        if ($v->act($toArray) === false) {
            $logger->error(implode("\n", $v->errors));
            throw new Exception(implode("\n", $v->errors));
        }

        $hasPaycoUserProfile = $session->has(GodoPaycoServerApi::SESSION_USER_PROFILE);
        $hasNaverUserProfile = $session->has(GodoNaverServerApi::SESSION_USER_PROFILE);
        $hasThirdPartyProfile = $session->has(Facebook::SESSION_USER_PROFILE);
        $hasKakaoUserProfile = $session->has(GodoKakaoServerApi::SESSION_USER_PROFILE);
        $hasWonderUserProfile = $session->has(GodoWonderServerApi::SESSION_USER_PROFILE);
        $passValidation = $hasPaycoUserProfile || $hasNaverUserProfile || $hasThirdPartyProfile || $hasKakaoUserProfile || $hasWonderUserProfile;
        \Component\Member\MemberValidation::validateMemberByInsert($vo, null, $passValidation);

        $authCellPhonePolicy = new SimpleStorage(gd_get_auth_cellphone_info());
        $ipinPolicy = new SimpleStorage(ComponentUtils::getPolicy('member.ipin'));

        //SNS 회원 가입을 진행중이고
        //본인 인증을 노출하지 않을 경우 아이핀/휴대폰 본인인증의 상태값을 미사용(n)으로 변경함.
        if ($passValidation === true && \Component\Member\MemberValidation::checkSNSMemberAuth() === 'n') {
            $ipinPolicy->set('useFl', 'n');
            $authCellPhonePolicy->set('useFl', 'n');
        }

        // 휴대폰인증시 저장된 세션정보와 실제 넘어온 파라미터 검증 (생년월일) - XSS 취약점 개선요청
        if ($authCellPhonePolicy->get('useFl', 'n') === 'y' && $session->has(Member::SESSION_DREAM_SECURITY)) {
            $dreamSession = new SimpleStorage($session->get(Member::SESSION_DREAM_SECURITY));

            $joinItem = gd_policy('member.joinitem');
            if ($joinItem['birthDt']['use'] === 'y' && $dreamSession->get('ibirth') != str_replace('-','', $vo->getBirthDt())) {
                throw new Exception(__("휴대폰 인증시 입력한 생년월일과 동일하지 않습니다."));
            }

            if ($joinItem['cellPhone']['use'] === 'y' && $dreamSession->get('phone') != str_replace('-','', $vo->getCellPhone())) {
                throw new Exception(__("휴대폰 인증시 입력한 번호와 동일하지 않습니다."));
            }

            if ($dreamSession->get('name') != $vo->getMemNm()) {
                throw new Exception(__("휴대폰 인증시 입력한 이름과 동일하지 않습니다."));
            }
        }

        if ($hasWonderUserProfile === false && $authCellPhonePolicy->get('useFl', 'n') === 'y' && $ipinPolicy->get('useFl', 'n') === 'n'&& !$session->has('simpleJoin')) {
            if (!$session->has(Member::SESSION_DREAM_SECURITY)) {
                $logger->error('Cellphone need identity verification.');
                throw new Exception(__('휴대폰 본인인증이 필요합니다.'));
            }
            $dreamSession = new SimpleStorage($session->get(Member::SESSION_DREAM_SECURITY));
            $session->del(Member::SESSION_DREAM_SECURITY);
            if (!Validator::required($dreamSession->get('DI'))) {
                $logger->error('Duplicate identification entry information does not exist.');
                throw new Exception(__('본인확인 중복가입정보가 없습니다.'));
            }
            if (!$vo->isset($vo->getDupeinfo())) {
                $vo->setDupeinfo($dreamSession->get('DI'));
            }
            if (!$vo->isset($vo->getBirthDt())) {
                $vo->setBirthDt($dreamSession->get('ibirth'));
            }
        }

        $member = $vo->toArray();
        if (empty($member['dupeinfo']) === false && MemberUtil::overlapDupeinfo($member['memId'], $member['dupeinfo'])) {
            $logger->error('Already members registered customers.');
            throw new Exception(__('이미 회원등록한 고객입니다.'));
        }
        if ($member['appFl'] == 'y') {
            $member['approvalDt'] = date('Y-m-d H:i:s');
        }

        $hasSessionGlobalMall = $session->has(SESSION_GLOBAL_MALL);
        $isUseGlobal = $globals->get('gGlobal.isUse', false);
        $logger->info(sprintf('has session global mall[%s], global use[%s]', $hasSessionGlobalMall, $isUseGlobal));
        if ($hasSessionGlobalMall && $isUseGlobal) {
            $mallSnoBySession = \Component\Mall\Mall::getSession('sno');
            $logger->info('has global mall session and has globals isUse. join member mallSno=' . $mallSnoBySession);
            $member['mallSno'] = $mallSnoBySession;
        } else {
            $logger->info('join member default mallSno');
            $member['mallSno'] = DEFAULT_MALL_NUMBER;
        }
        if ($hasPaycoUserProfile || $hasNaverUserProfile || $hasThirdPartyProfile || $hasKakaoUserProfile || $hasWonderUserProfile) {
            $memNo = $this->memberDAO->insertMemberByThirdParty($member);
            $member['memNo'] = $memNo;
        } else {
            $memNo = $this->memberDAO->insertMember($member);
            $member['memNo'] = $memNo;
        }
        if ($member['mallSno'] == DEFAULT_MALL_NUMBER) {
            $this->benefitJoin(new \Component\Member\MemberVO($member));
        } else {
            $logger->info(sprintf('can\'t benefit. your mall number is %d', $member['mallSno']));
        }
        $session->set(Member::SESSION_NEW_MEMBER, $member['memNo']);

        if ($vo->isset($member['cellPhone'])) {
            /** @var \Bundle\Component\Sms\SmsAuto $smsAuto */
            $aBasicInfo = gd_policy('basic.info');
            $aMemInfo = $this->getMemberId($memNo);
            $smsAuto = \App::load('\\Component\\Sms\\SmsAuto');
            $observer = new SmsAutoObserver();
            $observer->setSmsType(SmsAutoCode::MEMBER);
            $observer->setSmsAutoCodeType(Code::JOIN);
            $observer->setReceiver($member);
            $observer->setReplaceArguments(
                [
                    'name'      => $member['memNm'],
                    'memNm'     => $member['memNm'],
                    'memId'     => $member['memId'],
                    'appFl'     => $member['appFl'],
                    'groupNm'   => $aMemInfo['groupNm'],
                    'mileage'   => 0,
                    'deposit'   => 0,
                    'rc_mallNm' => Globals::get('gMall.mallNm'),
                    'shopUrl'   => $aBasicInfo['mallDomain'],
                ]
            );
            $smsAuto->attach($observer);
        }

        return new \Component\Member\MemberVO($member);
    }

    /**
     * 회원가입 메일 발송
     *
     * @param \Component\Member\MemberVO $vo
     */
    public function sendEmailByJoin(\Component\Member\MemberVO $vo)
    {
        if ($vo->isset($vo->getEmail())) {
            $replaceInfo = $vo->toArray();
            $mailMimeAuto = \App::load('Component\\Mail\\MailMimeAuto');
            $mailMimeAuto->init(MailMimeAuto::MEMBER_JOIN, $replaceInfo)->autoSend();
        }
    }

    /**
     * 회원가입 SMS 발송
     * 2016-10-05 yjwee 회원가입 SMS 수신동의 여부 체크 로직 제거
     * @deprecated 2017-02-08 yjwee 미사용 함수
     *
     * @param MemberVO $vo
     *
     * @throws Exception
     */
    public function sendSmsByJoin(MemberVO $vo)
    {
        if ($vo->isset($vo->getCellPhone())) {
            $this->smsAuto->setSmsType(SmsAutoCode::MEMBER);
            $this->smsAuto->setSmsAutoCodeType(Code::JOIN);
            $this->smsAuto->setReceiver($vo->getCellPhone());
            $this->smsAuto->setReplaceArguments(
                [
                    'name'  => $vo->getMemNm(),
                    'appFl' => $vo->getAppFl(),
                ]
            );
            $this->smsAuto->autoSend();
        }
    }

    /**
     * 회원가입 혜택 지급
     *
     * @param \Component\Member\MemberVO||array $vo
     * @param bool $isApproval
     */
    public function benefitJoin($vo, $isApproval = false)
    {
        if (is_array($vo)) {
            $vo = new \Component\Member\MemberVO($vo);
        }
        $benefit = \App::load('Component\\Member\\Benefit');
        $benefit->setBenefitMember($vo);
        if ($isApproval) {
            $benefit->approvalBenefitOffer();
        } else {
            $benefit->entryBenefitOffer();
        }
    }

    /**
     * @deprecated
     * @uses MemberDAO::updateMember
     *
     * 회원정보 수정
     *
     * @param       $memberNo
     * @param array $field
     * @param array $data
     */
    public function updateMemberByMemberNo($memberNo, array $field, array $data)
    {
        $arrBind = $arrUpdate = [];
        foreach ($field as $key => $value) {
            $arrUpdate[] = $value . '= ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes[$value], $data[$key]);
        }
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memNo'], $memberNo);
        $this->db->set_update_db(DB_MEMBER, $arrUpdate, 'memNo = ?', $arrBind);
    }

    /**
     * 관리자 회원등록
     *
     * @param \Component\Member\MemberVO $vo
     *
     * @return int|string
     */
    public function register(\Component\Member\MemberVO $vo)
    {
        $vo->databaseFormat();
        $vo->setEntryDt(date('Y-m-d H:i:s'));

        \Component\Member\MemberValidation::validateMemberByInsert($vo, \Component\Member\Util\MemberUtil::getRequireField(null, false), $this->isExcelUpload);

        $insertMemberData = $vo->toArray();
        if ($insertMemberData['appFl'] == 'y') {
            $insertMemberData['approvalDt'] = date('Y-m-d H:i:s');
        }
        $memberNo = $this->memberDAO->insertMember($insertMemberData);
        $vo->setMemNo($memberNo);
        $this->benefitJoin($vo);

        $smsAutoConfig = ComponentUtils::getPolicy('sms.smsAuto');
        $kakaoAutoConfig = ComponentUtils::getPolicy('kakaoAlrim.kakaoAuto');
        $kakaoLunaAutoConfig = ComponentUtils::getPolicy('kakaoAlrimLuna.kakaoAuto');
        if (gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === true && $kakaoLunaAutoConfig['useFlag'] == 'y' && $kakaoLunaAutoConfig['memberUseFlag'] == 'y') {
            $smsDisapproval = $kakaoLunaAutoConfig['member']['JOIN']['smsDisapproval'];
        }else if ($kakaoAutoConfig['useFlag'] == 'y' && $kakaoAutoConfig['memberUseFlag'] == 'y') {
            $smsDisapproval = $kakaoAutoConfig['member']['JOIN']['smsDisapproval'];
        } else {
            $smsDisapproval = $smsAutoConfig['member']['JOIN']['smsDisapproval'];
        }
        StringUtils::strIsSet($smsDisapproval, 'n');
        $sendSmsJoin = ($vo->getAppFl() == 'n' && $smsDisapproval == 'y') || $vo->getAppFl() == 'y';
        $mailAutoConfig = ComponentUtils::getPolicy('mail.configAuto');
        $mailDisapproval = $mailAutoConfig['join']['join']['mailDisapproval'];
        StringUtils::strIsSet($smsDisapproval, 'n');
        $sendMailJoin = ($vo->getAppFl() == 'n' && $mailDisapproval == 'y') || $vo->getAppFl() == 'y';
        if ($sendSmsJoin) {
            // 2016-10-06 yjwee 회원가입 SMS 수신동의 여부 체크 로직 제거
            $aMemInfo = $this->getMemberId($vo->getMemNo());
            $aBasicInfo = gd_policy('basic.info');
            $this->smsAuto->setSmsType(SmsAutoCode::MEMBER);
            $this->smsAuto->setSmsAutoCodeType(Code::JOIN);
            $this->smsAuto->setReceiver($vo->toArray());
            $this->smsAuto->setReplaceArguments(
                [
                    'name'      => $vo->getMemNm(),
                    'appFl'     => $vo->getAppFl(),
                    'memNm'     => $vo->getMemNm(),
                    'memId'     => $vo->getMemId(),
                    'groupNm'   => $aMemInfo['groupNm'],
                    'mileage'   => 0,
                    'deposit'   => 0,
                    'rc_mallNm' => Globals::get('gMall.mallNm'),
                    'shopUrl'   => $aBasicInfo['mallDomain'],
                ]
            );
            $this->smsAuto->autoSend();
        }
        if ($sendMailJoin) {
            $this->sendEmailByJoin($vo);
        }

        return $memberNo;
    }

    /**
     * 회원가입 완료 페이지에서 사용될 데이터 조회
     *
     * @param $memNo
     *
     * @return array
     * @throws AlertRedirectException
     */
    public function getJoinDataWithCheckJoinComplete($memNo)
    {
        if (Validator::number($memNo, null, null, true) === false) {
            throw new AlertRedirectException(__('회원가입 중 오류가 발생하였습니다.'), 500, null, '../member/join_method.php', 'top');
        }

        $memberInfo = $this->memberDAO->selectMemberByOne($memNo);

        $memNm = gd_isset($memberInfo['memNm'], '');
        $appFl = gd_isset($memberInfo['appFl'], '');

        if ($memNm === '' || $appFl === '') {
            throw new AlertRedirectException(__('회원정보를 찾을 수 없습니다.'), null, null, '/', 'top');
        }

        return [
            'memNm' => $memNm,
            'appFl' => $appFl,
        ];
    }

    /**
     * CRM 화면에서 보여질 회원 데이터 조회
     *
     * @param $memberNo
     *
     * @return array|object
     * @throws Exception
     */
    public function getDataByCrm($memberNo)
    {
        if (Validator::number($memberNo, null, null, true) === false) {
            throw new Exception(__('유효하지 않은 회원번호 입니다.'));
        }
        $member = $this->memberDAO->selectMemberCrm($memberNo);
        if (ArrayUtils::isEmpty($member) === true) {
            throw new Exception(__('회원을 찾을 수 없습니다.'));
        }

        return $member;
    }

    /**
     * @param boolean $isExcelUpload 엑셀 업로드로 회원등록 시 사용
     */
    public function setIsExcelUpload($isExcelUpload)
    {
        $this->isExcelUpload = $isExcelUpload;
    }

    /**
     * 회원 정보 조회 및 체크 배열 데이터 추가
     * 관리자 화면의 회원정보를 노출하는 형태로 데이터를 변환한다.
     *
     * @param $memNo
     * @param $memberData
     * @param $checked
     *
     * @throws Exception
     */
    public function getMemberDataWithChecked($memNo, &$memberData, &$checked)
    {
        if (Validator::number($memNo, null, null, true) === false) {
            throw new Exception(__('유효하지 않은 회원번호 입니다.'));
        }

        $myPage = $this->memberDAO->selectMyPage($memNo);
        $vo = new MemberVO($myPage);
        $vo->adminViewFormat();
        $memberData = $vo->toArray();
        if (empty($memberData['memNo'])) {
            throw new Exception(__('회원정보를 찾을 수 없습니다. 탈퇴 또는 회원리스트에 존재하는지 확인해 주세요.'));
        }

        Session::set(Member::SESSION_MODIFY_MEMBER_INFO, $memberData);

        $checked = SkinUtils::setChecked(
            [
                'memberFl',
                'appFl',
                'maillingFl',
                'smsFl',
                'sexFl',
                'marriFl',
                'expirationFl',
            ], $memberData
        );

        $memberData['privateApprovalOptionFl'] = json_decode(stripslashes($memberData['privateApprovalOptionFl']), true);
        $memberData['privateOfferFl'] = json_decode(stripslashes($memberData['privateOfferFl']), true);
        $memberData['privateConsignFl'] = json_decode(stripslashes($memberData['privateConsignFl']), true);
    }

    /**
     * 내 정보변경 화면에서 사용되는 회원 정보 조회
     *
     * @param $memNo
     *
     * @return array|object [memId, snsTypeFl]
     */
    public function getMyPagePassword($memNo)
    {
        $member = $this->memberDAO->selectMyPage($memNo);
        ArrayUtils::unsetDiff(
            $member, [
                'memId',
                'snsTypeFl',
            ]
        );

        return $member;
    }

    /**
     * 회원의 마지막 수신동의 재안내 내역을 조회
     *
     * @param $memberNo
     *
     * @return array
     */
    public function getLastAgreementNotificationByMember($memberNo)
    {
        $logs = $this->memberDAO->selectLastAgreementNotificationByMember($memberNo);
        $result = [];
        foreach ($logs as $log) {
            $result['lastNotificationDt'][$log['type']] = DateTimeUtils::dateFormat('Y-m-d', $log['lastNotificationDt']);
        }

        return $result;
    }

    /**
     * 회원 가입 승인 관련 문자와 메일 발송 함수
     *
     * @param array $member
     */
    public function notifyApprovalJoin($member)
    {
        $memberDAO = \App::load('Component\\Member\\MemberDAO');
        $member = $memberDAO->selectMemberByOne($member['memNo']);
        $aMemInfo = $this->getMemberId($member['memNo']);
        if ($member['appFl'] == 'y') {
            $notification = $memberDAO->selectMemberNotification(
                [
                    'memNo'      => $member['memNo'],
                    'reasonCode' => \Component\Sms\Code::APPROVAL,
                    'type'       => 'sms',
                    'dataArray'  => false,
                ]
            );
            if (empty($notification)) {
                $aBasicInfo = gd_policy('basic.info');
                $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                $smsAuto->setSmsType(SmsAutoCode::MEMBER);
                $smsAuto->setSmsAutoCodeType(\Component\Sms\Code::APPROVAL);
                $smsAuto->setReceiver($member);
                $smsAuto->setReplaceArguments(
                    [
                        'name'      => $member['memNm'],
                        'rc_mallNm' => Globals::get('gMall.mallNm'),
                        'shopUrl'   => $aBasicInfo['mallDomain'],
                        'groupNm'   => $aMemInfo['groupNm'],
                        'mileage'   => 0,
                        'deposit'   => 0,
                        'memNm'     => $member['memNm'],
                        'memId'     => $member['memId'],
                    ]
                );
                if ($smsAuto->autoSend() !== false) {
                    $log = [
                        'memNo'      => $member['memNo'],
                        'type'       => 'sms',
                        'typeLogSno' => $smsAuto->getSmsLogSno(),
                        'reasonCode' => \Component\Sms\Code::APPROVAL,
                    ];
                    $memberDAO->insertNotificationLog($log);
                }
            }
            $notification = $memberDAO->selectMemberNotification(
                [
                    'memNo'      => $member['memNo'],
                    'reasonCode' => \Component\Mail\MailMimeAuto::MEMBER_APPROVAL,
                    'type'       => 'mail',
                    'dataArray'  => false,
                ]
            );
            if (empty($notification)) {
                $historyService = \App::load('Component\\Member\\History');
                $lastAgreementDt = $historyService->getLastReceiveAgreementByMember($member['memNo']);
                $entryDt = DateTimeUtils::dateFormat('Y-m-d', $member['entryDt']);
                $mailData = [
                    'email'                      => $member['email'],
                    'memNm'                      => $member['memNm'],
                    'memId'                      => $member['memId'],
                    'smsFl'                      => $member['smsFl'],
                    'maillingFl'                 => $member['maillingFl'],
                    'modDt'                      => DateTimeUtils::dateFormat('Y-m-d', 'now'),
                    'smsLastReceiveAgreementDt'  => StringUtils::strIsSet($lastAgreementDt['lastReceiveAgreementDt']['sms'], $entryDt),
                    'mailLastReceiveAgreementDt' => StringUtils::strIsSet($lastAgreementDt['lastReceiveAgreementDt']['mail'], $entryDt),
                ];
                $mailMimeAuto = \App::load('Component\\Mail\\MailMimeAuto');
                if ($mailMimeAuto->init(\Component\Mail\MailMimeAuto::MEMBER_APPROVAL, $mailData)->autoSend()) {
                    $log = [
                        'memNo'      => $member['memNo'],
                        'type'       => 'mail',
                        'typeLogSno' => $mailMimeAuto->getMailLogSno(),
                        'reasonCode' => \Component\Mail\MailMimeAuto::MEMBER_APPROVAL,
                    ];
                    $memberDAO->insertNotificationLog($log);
                }
            }
        }
    }

    /**
     * 수기주문 ajax 페이지에서 로드되는 회원 정보
     *
     * @param $memNo
     *
     * @return array
     *
     * @throws Exception
     */
    public function getMemberDataOrderWrite($memNo)
    {
        if (Validator::number($memNo, null, null, true) === false) {
            throw new Exception(__('유효하지 않은 회원번호 입니다.'));
        }

        $arrBind = [];
        $this->db->strField = 'm.memNo, m.mallSno, m.memId, m.groupSno, m.memNm, m.zipcode, m.zonecode, m.address, m.addressSub, m.phone, m.cellPhone';
        $this->db->strField .= ', m.mileage, m.deposit';
        $this->db->strField .= ', m.busiNo, m.company, m.ceo, m.service, m.item, m.comZonecode, m.comZipcode, m.comAddress, m.comAddressSub';
        $this->db->strField .= ', mg.settleGb';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER_GROUP . ' AS mg ON m.groupSno = mg.sno';
        $this->db->strWhere = 'm.memNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $memNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' AS m ' . implode(' ', $query);
        $memData = $this->db->query_fetch($strSQL, $arrBind, false);

        $strField = DBTableField::setTableField('tableMemberInvoiceInfo');
        $strSQL = 'SELECT ' . @implode(',', $strField) . ' FROM ' . DB_MEMBER_INVOICE_INFO . ' WHERE memNo = ?';
        $_arrBind = ['i', $memNo];

        $taxData = $this->db->query_fetch($strSQL, $_arrBind, false);

        if (empty($taxData) === false) {
            $memData['busiNo'] = $taxData['taxBusiNo'];
            $memData['company'] = $taxData['company'];
            $memData['ceo'] = $taxData['ceo'];
            $memData['service'] = $taxData['service'];
            $memData['item'] = $taxData['item'];
            $memData['comZonecode'] = $taxData['comZonecode'];
            $memData['comZipcode'] = $taxData['comZipcode'];
            $memData['comAddress'] = $taxData['comAddress'];
            $memData['comAddressSub'] = $taxData['comAddressSub'];
            $memData['taxEmail'] = $taxData['email'];
        }

        return $memData;
    }

    public function getMemberSns($memberNo, $appId)
    {
        $memberSnsDAO = \App::load('\\Component\\Member\\MemberSnsDAO');

        return $memberSnsDAO->selectMemberSns($memberNo, $appId);
    }

    /**
     * 하위 클래스에서 사용하기 위한 랩핑 함수
     *
     * @param array $hackOutParams
     *
     * @throws Exception
     */
    protected function validateHackOuMember(array $hackOutParams)
    {
        $this->_validateHackOuMember($hackOutParams);
    }

    /**
     * 하위 클래스에서 사용하기 위한 랩핑 함수
     *
     * @param $applyFlag
     *
     * @throws Exception
     */
    protected function validateApplyFlag($applyFlag)
    {
        $this->_validateApplyFlag($applyFlag);
    }

    /**
     * 하위 클래스에서 사용하기 위한 랩핑 함수
     *
     * @param array $member
     */
    protected function checkAdultFlagAndUpdate(array &$member)
    {
        $this->_checkAdultFlagAndUpdate($member);
    }

    /**
     * 로그인 시 사용되는 회원정보
     *
     * @param $id
     *
     * @return array
     */
    protected function getMemberByLogin($id)
    {
        $memberWithGroup = $this->memberDAO->selectMemberWithGroup($id, 'memId');
        $loginLimit = json_decode($memberWithGroup['loginLimit'], true);
        $memberWithGroup['loginLimit'] = $loginLimit;

        return $memberWithGroup;
    }

    /**
     * 로그인 제한이 설정 및 10분이상 지났는지 확인
     *
     * @param array $member
     *
     * @return bool true 로그인 제한 및 10분이 지나지 않음
     */
    protected function isLoginLimitMember(array $member)
    {
        $loginLimit = json_decode($member['loginLimit'], true);
        if ($loginLimit['limitFlag'] == 'y') {
            return $this->isGreaterThanLimitLoginTime($loginLimit['onLimitDt']);
        }

        return false;
    }

    /**
     * 로그인 제한 시간 10분이 지났는지 확인
     *
     * @param $limitDateTime
     *
     * @return bool true 10분 미만, false 10분 이상
     */
    protected function isGreaterThanLimitLoginTime($limitDateTime)
    {
        StringUtils::strIsSet($limitDateTime, DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now'));
        $interval = DateTimeUtils::intervalDay($limitDateTime, null, 'min');

        return $interval < 10;
    }

    /**
     * 로그인 제한 관련 로그 데이터 초기화
     * 초기화에 성공하면 전달받은 회원정보의 로그인 제한 로그 값을 초기화하여 반환한다.
     *
     * @param array $member
     *
     * @return array
     */
    protected function initLimitLoginLog(array $member)
    {
        $result = [
            'affectedRows' => 0,
            'member'       => $member,
        ];
        $dao = \App::load('Component\\Member\\MemberDAO');
        $memberByUpdate = [
            'memNo'      => $member['memNo'],
            'loginLimit' => [
                'limitFlag'      => 'n',
                'onLimitDt'      => '0000-00-00 00:00:00',
                'loginFailCount' => 0,
                'loginFailLog'   => [],
            ],
        ];
        $result['affectedRows'] = $dao->updateMember($memberByUpdate, ['loginLimit'], []);;
        if ($result['affectedRows'] > 0) {
            $result['member']['loginLimit'] = $memberByUpdate['loginLimit'];
        }
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__ . ', memNo[' . $member['memNo'] . ']');

        return $result;
    }

    /**
     * 탈퇴회원 검증
     *
     * @param array $hackOutParams
     *
     * @throws Exception
     */
    private function _validateHackOuMember(array $hackOutParams)
    {
        $tmpData = $this->getDataByTable(DB_MEMBER_HACKOUT, array_values($hackOutParams), array_keys($hackOutParams));
        if (empty($tmpData['memNo']) === false) {
            \Logger::channel('userLogin')->warning('회원 탈퇴 or 탈퇴신청 회원.', [$this->getRequestData()]);
            throw new Exception(__('회원 탈퇴를 신청하였거나, 탈퇴한 회원이십니다.<br/>로그인이 제한됩니다.'), 500);
        }
        unset($tmpData, $tmpArrBind);
    }

    /**
     * 승인체크
     *
     * @param $applyFlag
     *
     * @throws Exception
     */
    private function _validateApplyFlag($applyFlag)
    {
        if ($applyFlag != 'y') {
            \Logger::channel('userLogin')->warning('본 사이트 미승인으로 인한 로그인 제한', [$this->getRequestData()]);
            throw new Exception(__('고객님은 본 사이트에서 승인되지 않아 로그인이 제한됩니다.'), 500);
        }
    }

    /**
     * 성인정보관련 , 1년이 지난경우는 재인증필요
     *
     * @param array $member
     */
    private function _checkAdultFlagAndUpdate(array &$member)
    {
        if ($member['adultFl'] == 'y' && (strtotime($member['adultConfirmDt']) < strtotime("-1 year", time()))) {
            $member['adultFl'] = "n";
        }
    }

    /**
     * 기술지원지 필요한 정보들
     *
     * @param bool $isJson
     *
     * @return string
     */
    private function getRequestData($data = [])
    {
        $data['PAGE_URL'] = Request::getDomainUrl() . Request::getRequestUri();
        $data['POST'] = Request::post()->toArray();
        unset($data['POST']['loginPwd']);
        $data['GET'] = Request::get()->toArray();
        $data['USER_AGENT'] = Request::getUserAgent();
        $data['SESSION'] = \Session::get('member');
        unset($data['SESSION']['memPw'], $data['SESSION']['memNm'], $data['SESSION']['nickNm'], $data['SESSION']['cellPhone'], $data['SESSION']['email']);
        $data['COOKIE'] = \Cookie::all();
        $data['REFERER'] = Request::getReferer();
        $data['REMOTE_ADDR'] = Request::getRemoteAddress();
        if (empty($data) === false) {
            $data['DATA'] = $data;
        }

        return $data;
    }

    /**
     * @param $memNo
     * @param $memInfo
     * @param $coupon
     * @param $eventType order|push
     */
    public function setSimpleJoinLog($memNo, $memInfo, $coupon, $eventType)
    {
        $data['memNo'] = $memNo;
        $data['memId'] = $memInfo['memId'];
        $data['appFl'] = $memInfo['appFl'];
        $data['groupSno'] = $memInfo['groupSno'];
        $data['mileage'] = $memInfo['mileage'];
        $data['memberCouponNo'] = implode(INT_DIVISION, array_column($coupon, 'memberCouponNo'));
        $data['couponNm'] = implode(STR_DIVISION, array_column($coupon, 'couponNm'));
        $data['eventType'] = $eventType;
        $arrBind = $this->db->get_binding(DBTableField::tableMemberSimpleJoinLog(), $data, 'insert', array_keys($data));
        $this->db->set_insert_db(DB_MEMBER_SIMPLE_JOIN_LOG, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
    }

    /**
     * @param $eventType order|push
     * @param $searchData
     * @param $searchPeriod
     *
     * @return array
     */
    public function getSimpleJoinLog($searchData, $searchPeriod = 6, $eventType)
    {
        gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        gd_isset($searchData['treatDate'][1], date('Y-m-d'));

        if (DateTimeUtils::intervalDay($searchData['treatDate'][0], $searchData['treatDate'][1]) > 365) {
            throw new Exception(__('1년이상 기간으로 검색하실 수 없습니다.'));
        }
        $arrBind = [];
        $arrWhere[] = ' msl.regDt BETWEEN ? AND ? ';
        $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][0] . ' 00:00:00');
        $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][1] . ' 23:59:59');

        $arrWhere[] = 'msl.eventType = ?';
        $this->db->bind_param_push($arrBind, 's', $eventType);
        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = gd_isset($searchData['pageNum'], 20); // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());
        if($searchData['page']) {
            $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];
        }

        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER . ' m ON m.memNo = msl.memNo ';
        $this->db->strField = ' msl.*, m.memNm, m.sleepFl ';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'msl.regDt desc';


        $strSQL = ' SELECT COUNT(msl.memNo) AS cnt FROM ' . DB_MEMBER_SIMPLE_JOIN_LOG .' as msl WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SIMPLE_JOIN_LOG . ' msl ' . implode(' ', $query);
        $result['data'] = $this->db->query_fetch($strSQL, $arrBind, true);
        return $result;
        // SELECT COUNT(msl.memNo) AS cnt FROM es_memberSimpleJoinLog as msl WHERE  msl.regDt BETWEEN ? AND ?  AND msl.eventType = ?
        // SELECT   msl.*, m.memNm   FROM es_memberSimpleJoinLog msl   LEFT JOIN es_member m ON m.memNo = msl.memNo    WHERE  msl.regDt BETWEEN ? AND ?  AND msl.eventType = ?    ORDER BY msl.regDt desc   LIMIT 0,10
    }

    public function setSimpleJoinPushLog($eventType)
    {
        $data['eventType'] = $eventType;
        $arrBind = $this->db->get_binding(DBTableField::tableMemberSimpleJoinPushLog(), $data, 'insert', array_keys($data));
        $this->db->set_insert_db(DB_MEMBER_SIMPLE_JOIN_PUSH_LOG, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
        //INSERT INTO es_memberSimpleJoinPushLog (eventType, regDt) VALUES ('view', now())
    }

    public function getSimpleJoinPushLog($searchData, $searchPeriod = 6) {
        gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        gd_isset($searchData['treatDate'][1], date('Y-m-d'));

        $arrBind = [];
        $arrWhere[] = ' regDt BETWEEN ? AND ? ';
        $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][0] . ' 00:00:00');
        $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][1] . ' 23:59:59');

        $this->db->strField = ' COUNT(sno) AS cnt ';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = ' SELECT '. array_shift($query) .' FROM ' . DB_MEMBER_SIMPLE_JOIN_PUSH_LOG . implode(' ', $query);
        $clickSQL = $strSQL .' AND eventType = \'click\'';
        $viewSQL = $strSQL .' AND eventType = \'view\'';
        $res['click'] = $this->db->query_fetch($clickSQL, $arrBind, false)['cnt'];
        $res['view'] = $this->db->query_fetch($viewSQL, $arrBind, false)['cnt'];
        return $res;
        // SELECT   eventType, COUNT(sno) AS cnt   FROM es_memberSimpleJoinPushLog  WHERE  regDt BETWEEN ? AND ?    GROUP BY  eventType
    }

    /**
     * 회원 비밀번호 입력 여부 (sns 회원의 경우 기본없음, 회원 수정에서 비밀번호 입력 가능)
     *
     * @param $memNo
     * @return 0 | 1
     */
    public function checkHasPassword($memNo) {
        $arrBind = [];
        $this->db->strField = ' COUNT(memNo) AS cnt ';
        $this->db->strWhere = ' memNo = ? AND memPw <> \'\' ';
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $query = $this->db->query_complete();
        $strSQL = ' SELECT '. array_shift($query) .' FROM ' . DB_MEMBER . implode(' ', $query);
        return $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
    }

    /**
     * 로그인 시도한 IP의 마지막 로그인 실패 데이터
     *
     * @param string $ip 로그인시도 접속 IP
     * @param string $IsLimitFlag 접속제한 여부 조회 (Y : 제한조회, N : 미제한조회)
     *
     * @return array
     */
    protected function selectLogIpUserLoginTry($ip, $IsLimitFlag)
    {
        if ($ip == '' || $ip == null) {
            return null;
        }
        $arrBind = [];
        $this->db->strField = 'limitFlag, onLimitDt, loginFailDt';
        $this->db->strWhere = 'loginFailIp = ? AND limitFlag = ? AND loginType = ?';
        $this->db->strOrder = 'sno DESC';
        $this->db->strLimit = '1';
        $this->db->bind_param_push($arrBind, 's', $ip);
        $this->db->bind_param_push($arrBind, 's', $IsLimitFlag);
        $this->db->bind_param_push($arrBind, 's', 'user');
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_LOG_IPLOGINTRY . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind);

        return $result[0];
    }

    /**
     * 로그인 시도시 1분 동안 몇회 시도 했는지 확인
     *
     * @param string $ip 로그인시도 접속 IP
     *
     * @return int
     */
    protected function getOneMinLoginFailCount($ip) {

        $loginFailDtStart = DateTimeUtils::dateFormat('Y-m-d H:i:s', '-1min');
        $loginFailDtEnd = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        $arrBind = [];
        $arrWhere[] = 'loginFailDt BETWEEN ? AND ? ';
        $this->db->bind_param_push($arrBind, 's', $loginFailDtStart);
        $this->db->bind_param_push($arrBind, 's', $loginFailDtEnd);

        $arrWhere[] = 'loginFailIp = ?';
        $this->db->bind_param_push($arrBind, 's', $ip);

        $arrWhere[] = 'limitFlag = ?';
        $this->db->bind_param_push($arrBind, 's', 'N');

        $arrWhere[] = 'loginType = ?';
        $this->db->bind_param_push($arrBind, 's', 'user');

        $this->db->strField = ' COUNT(loginFailIp) AS cnt ';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = ' SELECT '. array_shift($query) .' FROM ' . DB_LOG_IPLOGINTRY . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
    }

    /**
     * 로그 - 동일 IP 로그인 접속 시도 저장
     *
     * @param string $ip 로그인시도 접속 IP
     *
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    protected function setLogIpUserLoginTry($ip)
    {
        $logIpLoginTryInfo = $this->selectLogIpUserLoginTry($ip, 'N'); // 마지막 로그인 실패 시간
        $isContinuousLoginAttempts = (empty($logIpLoginTryInfo) === false) ? $this->isContinuousLoginAttempts($logIpLoginTryInfo['loginFailDt']) : false;
        $getOneMinLoginFailCount = $this->getOneMinLoginFailCount(\Request::getRemoteAddress()); // 1분전 로그인 실패 횟수
        $now = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        $data['loginFailIp'] = $ip;
        $data['loginFailDt'] = $now;
        $data['limitFlag'] = ($isContinuousLoginAttempts || $getOneMinLoginFailCount > 10) ? 'Y' : 'N';
        $data['loginType'] = 'user';

        // 현재 접속제한 상태인 경우에는 저장안함
        $isLimitFlag = $this->selectLogIpUserLoginTry($ip, 'Y');
        if ($isLimitFlag['limitFlag'] === 'Y') {
            if ($this->isCheckLoginTimeout($isLimitFlag['onLimitDt'])) {
                return;
            }
        }

        // 동일 IP 연속 로그인 시도(실패)에 대한 제한시간 설정
        if ($isContinuousLoginAttempts || $getOneMinLoginFailCount > 10) {
            $data['onLimitDt'] = $now;
        }
        $arrBind = $this->db->get_binding(DBTableField::tableLogIpLoginTry(), $data, 'insert', array_keys($data));
        $this->db->set_insert_db(DB_LOG_IPLOGINTRY, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
        \Logger::channel('userLogin')->info('logIpUserLoginTry.', [$this->getRequestData($data)]);

        return true;
    }

    /**
     * 동일 IP 연속 로그인 시도(실패)한 이전 시간이 1초 미만 인지 확인
     *
     * @param string $loginFailDt 로그 - 동일 IP 접속시도(실패) 데이터
     *
     * @return bool true 1초 미만, false 1초 이상
     */
    protected function isContinuousLoginAttempts($loginFailDt)
    {
        $interval = DateTimeUtils::intervalDay($loginFailDt, DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now'), 'sec');

        return $interval < 1;
    }

    /**
     * 동일 IP 연속 접근에 대한 로그인 제한시간 15분이 지났는지 체크
     *
     * @param string $onLimitDt 로그인 제한 시간 (마지막 접속)
     *
     * @return bool true 15분 미만, false 15분 이상
     */
    protected function isCheckLoginTimeout($onLimitDt)
    {
        $interval = DateTimeUtils::intervalDay($onLimitDt, DateTimeUtils::dateFormat('Y-m-d G:i:s', 'now'), 'min');

        return $interval < 15;
    }
}
