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

namespace Bundle\Component\Member\Util;

use App;
use Bundle\Component\Payment\Payco\Payco;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Bundle\Component\Policy\PaycoLoginPolicy;
use Bundle\Component\Policy\SnsLoginPolicy;
use Bundle\Component\Policy\NaverLoginPolicy;
use Bundle\Component\Policy\WonderLoginPolicy;
use Component\Database\DBTableField;
use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoKakaoServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Member\Manager;
use Component\Member\Member;
use Component\Member\MemberVO;
use Component\Member\MyPage;
use Component\Validator\Validator;
use Component\Mall\Mall;
use Cookie;
use Encryptor;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Object\SimpleStorage;
use Framework\Object\SingletonTrait;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\DateTimeUtils;
use Logger;
use Message;
use Request;
use Session;

/**
 * Class MemberUtil
 * @package Bundle\Component\Member\Util
 * @author  yjwee
 * @method static MemberUtil getInstance
 */
class MemberUtil
{
    use SingletonTrait;

    /** 자동로그인, 로그인 아이디 저장 쿠키 ID (godomall5 auto login) */
    const COOKIE_LOGIN = 'GD5ATL';
    /** 자동로그인, 로그인 아이디 저장 쿠키내 json 키*/
    const COOKIE_LOGIN_FLAG = '__gd5a';
    const COOKIE_LOGIN_ID = '__gd5t';
    const COOKIE_LOGIN_PW = '__gd5l';
    /** 자동로그인 KEY  */
    const KEY_AUTO_LOGIN = 'gd5f';
    /** 로그인 아이디 저장 KEY */
    const KEY_SAVE_LOGIN_ID = '__gd5si';

    public function __construct() { }

    public static function saveCookieByLogin(SimpleStorage $storage)
    {
        $saveId = $storage->get('saveId', 'n');
        $saveAutoLogin = $storage->get('saveAutoLogin', 'n');
        $cookieData = [];
        if ($saveAutoLogin == 'y') {
            $cookieData[self::COOKIE_LOGIN_FLAG] = self::KEY_AUTO_LOGIN;
            $cookieData[self::COOKIE_LOGIN_ID] = \Encryptor::encrypt($storage->get('loginId'));
            $cookieData[self::COOKIE_LOGIN_PW] = \Encryptor::encrypt($storage->get('loginPwd'));
            Cookie::set(MemberUtil::COOKIE_LOGIN, json_encode($cookieData), (3600 * 24 * 10));
        } else if ($saveId == 'y' && $saveAutoLogin != 'y') {
            $cookieData[self::COOKIE_LOGIN_FLAG] = self::KEY_SAVE_LOGIN_ID;
            $cookieData[self::COOKIE_LOGIN_ID] = \Encryptor::encrypt($storage->get('loginId'));
            Cookie::set(MemberUtil::COOKIE_LOGIN, json_encode($cookieData), (3600 * 24 * 10));
        } else {
            Cookie::del(MemberUtil::COOKIE_LOGIN);
        }
    }

    /**
     * 쿠키에 저장된 자동로그인, 로그인 아이디 정보를 가져오는 함수
     *
     * @static
     * @return mixed
     */
    public static function getCookieByLogin()
    {
        $data = json_decode(Cookie::get(MemberUtil::COOKIE_LOGIN), true);
        $data[self::COOKIE_LOGIN_ID] = \Encryptor::decrypt($data[self::COOKIE_LOGIN_ID]);
        $data[self::COOKIE_LOGIN_PW] = \Encryptor::decrypt($data[self::COOKIE_LOGIN_PW]);

        return $data;
    }

    /**
     * 쿠키에 저장된 정보를 바탕으로 자동로그인 처리하는 함수
     *
     * @static
     * @throws Exception
     */
    public static function loginByCookie()
    {
        // 쿠키 가져오기 (복호화 정보 포함)
        $cookieMemberData = static::getCookieByLogin();

        // 자동로그인 체크시 실행
        if ($cookieMemberData[self::COOKIE_LOGIN_FLAG] == self::KEY_AUTO_LOGIN) {
            /** @var \Bundle\Component\Member\Member $member */
            $member = \App::load('\\Component\\Member\\Member');
            try {
                $member->login($cookieMemberData[self::COOKIE_LOGIN_ID], $cookieMemberData[self::COOKIE_LOGIN_PW]);
                $storage = new SimpleStorage();
                $storage->set('loginId', $cookieMemberData[self::COOKIE_LOGIN_ID]);
                $storage->set('loginPwd', $cookieMemberData[self::COOKIE_LOGIN_PW]);
                $storage->set('saveAutoLogin', 'y');
                static::saveCookieByLogin($storage);

                // 에이스 카운터 로그인   스크립트
                $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
                $acecounterUse = $acecounterScript->getAcecounterUseCheck();
                if ($acecounterUse) {
                    $returnScript = $acecounterScript->getLoginScript();
                    echo $returnScript;
                }
            } catch (Exception $e) {
                Logger::error($e->getMessage(), $e->getTrace());
            }
        }
    }

    public static function logoutWithCookie()
    {
        self::logout();

        $data = self::getCookieByLogin();
        if ($data[self::COOKIE_LOGIN_FLAG] == self::KEY_AUTO_LOGIN) {
            Cookie::del(MemberUtil::COOKIE_LOGIN);
        }
    }

    /**
     * 회원 로그인여부 (프론트 전용)
     *
     * @static
     * @return mixed
     */
    public static function isLogin()
    {
        $session = App::getInstance('session');

        return $session->has(Member::SESSION_MEMBER_LOGIN);
    }

    /**
     * 로그인 후 회원정보 및 만료시간 세션 저장
     *
     * @static
     *
     * @param array $member
     *
     * @deprecated 2017-01-31 yjwee 역할 중복 및 불필요 전역함수 제거
     * @uses       \Component\Member\Member::setSessionByLogin
     */
    public static function saveLoginSession(array $member)
    {
        $memberService = \App::load(\Component\Member\Member::class);
        $memberService->setSessionByLogin($member);
    }

    /**
     * 로그인 세션의 회원 이름
     *
     * @static
     * @return mixed
     */
    public static function getMemberNameBySession()
    {
        $session = App::getInstance('session');

        return $session->get('member.memNm');
    }

    /**
     * 회원 필수 필드
     *
     * @param null $key       필드명
     * @param bool $isRequire 필수 여부(false 로 넘길 경우 회원 필수 필드의 값이 모두 false로 처리됨)
     * @param int  $mallSno
     *
     * @return array 회원 필수 필드
     */
    public static function getRequireField($key = null, $isRequire = true, $mallSno = null)
    {
        $session = App::getInstance('session');
        $joinItem = \Component\Policy\JoinItemPolicy::getInstance()->getPolicy($mallSno);
        if($session->has(GodoPaycoServerApi::SESSION_USER_PROFILE)){
            $joinItem = MemberUtil::unsetDiffByPaycoLogin($joinItem);
        } else if ($session->has(GodoNaverServerApi::SESSION_USER_PROFILE)){
            $joinItem = MemberUtil::unsetDiffByNaverLogin($joinItem);
        } else if ($session->has(Facebook::SESSION_USER_PROFILE)){
            $joinItem = MemberUtil::unsetDiffByFacebookLogin($joinItem);
        }else if ($session->has(GodoKakaoServerApi::SESSION_USER_PROFILE)){
            $joinItem = MemberUtil::unsetDiffByKakaoLogin($joinItem);
        } else if ($session->has(GodoWonderServerApi::SESSION_USER_PROFILE)){
            $joinItem = MemberUtil::unsetDiffByWonderLogin($joinItem);
        }

        $require = [];

        foreach ($joinItem as $k => $v) {
            if (isset($v['require']) && $v['require'] == 'y') {
                $require[$k] = $isRequire;
            }
        }
        if ($key === null) {
            return $require;
        } else {
            return $require[$key];
        }
    }

    /**
     * 회원사용필드 최대 최소값
     *
     * @param int $mallSno
     *
     * @return array 항목의 최대/최소값
     */
    public static function getMinMax($mallSno = null)
    {
        $joinItem = \Component\Policy\JoinItemPolicy::getInstance()->getPolicy($mallSno);
        \Logger::info(__METHOD__, $joinItem);
        $arrLength = [];
        foreach ($joinItem as $k => $v) {
            if (isset($v['minlen'])) {
                $arrLength[$k]['minlen'] = $v['minlen'];
            }
            if (isset($v['maxlen'])) {
                $arrLength[$k]['maxlen'] = $v['maxlen'];
            }
        }

        return $arrLength;
    }

    /**
     * 로그아웃
     *
     * @author artherot,sunny
     */
    public static function logout()
    {
        $session = \App::getInstance('session');
        self::logoutPayco();
        self::logoutNaver();
        self::logoutLivefeed();
        self::logoutKakao();
        self::logoutWonder();
        if (Manager::isAdmin()) {
            $session->del(Member::SESSION_MEMBER_LOGIN);
            $session->del(Member::SESSION_MODIFY_MEMBER_INFO);
            $session->del(Member::SESSION_NEW_MEMBER);
            $session->del(Member::SESSION_JOIN_INFO);
            $session->del(Member::SESSION_DREAM_SECURITY);
            $session->del(Member::SESSION_IPIN);
            $session->del(Member::SESSION_USER_CERTIFICATION);
            $session->del(Member::SESSION_CHECK_AGE_AUTH);
            $session->del(MyPage::SESSION_MY_PAGE_PASSWORD);
            $session->del(Facebook::SESSION_ACCESS_TOKEN);
            $session->del(Facebook::SESSION_METADATA);
            $session->del(Facebook::SESSION_USER_PROFILE);
            self::logoutGuest();
        } else {
            $session->clear();
        }
    }

    /**
     * 로그아웃 비회원
     *
     * @author artherot
     */
    public static function logoutGuest()
    {
        if (Session::has('guest')) {
            Session::del('guest');
        }
    }

    public static function logoutPayco()
    {
        $member = Session::get(Member::SESSION_MEMBER_LOGIN);
        if ($member['snsTypeFl'] == 'payco' && empty($member['accessToken']) === false) {
            $paycoApi = new GodoPaycoServerApi();
            $paycoApi->serviceOff($member['accessToken']);
        }
        if (Session::has(GodoPaycoServerApi::SESSION_ACCESS_TOKEN)) {
            Session::del(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
        }
    }

    public static function logoutNaver()
    {
        if (Session::has(GodoNaverServerApi::SESSION_ACCESS_TOKEN)) {
            Session::del(GodoNaverServerApi::SESSION_ACCESS_TOKEN);
        }
        if (Session::has(GodoNaverServerApi::SESSION_NAVER_HACK)) {
            Session::del(GodoNaverServerApi::SESSION_NAVER_HACK);
        }
    }

    public static function logoutLivefeed()
    {
        if (Session::has('livefeedData')) {
            Session::del('livefeedData');
        }
    }

    public static function logoutKakao()
    {
        $member = Session::get(Member::SESSION_MEMBER_LOGIN);
       if($member['snsTypeFl'] == 'kakao' && empty($member['accessToken']) === false) {
           $kakaoApi = new GodoKakaoServerApi();
           $kakaoApi->logout($member['accessToken']);
       }
       if(Session::has(GodoKakaoServerApi::SESSION_ACCESS_TOKEN)){
           Session::del(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
       }
       if(Session::has(GodoKakaoServerApi::SESSION_KAKAO_HACK)){
           Session::del(GodoKakaoServerApi::SESSION_KAKAO_HACK);
       }
        if(Session::has(GodoKakaoServerApi::SESSION_USER_PROFILE)){
            Session::del(GodoKakaoServerApi::SESSION_USER_PROFILE);
        }
    }

    public static function logoutWonder()
    {
        if(Session::has(GodoWonderServerApi::SESSION_ACCESS_TOKEN)) {
            Session::del(GodoWonderServerApi::SESSION_ACCESS_TOKEN);
        }
        if(Session::has(GodoWonderServerApi::SESSION_WONDER_HACK)) {
            Session::has(GodoWonderServerApi::SESSION_WONDER_HACK);
        }
        if(Session::has(GodoWonderServerApi::SESSION_USER_PROFILE)) {
            Session::del(GodoWonderServerApi::SESSION_USER_PROFILE);
        }
    }

    /**
     * 생일, 이메일, 전화번호 등의 데이터 결합
     *
     * @param $arrData
     *
     * @return mixed
     */
    public static function combineMemberData(&$arrData)
    {
        $arrKeys = array_keys($arrData);
        $vo = new MemberVO($arrData);
        $vo->databaseFormat();
        $arrData = $vo->toArray();
        ArrayUtils::unsetDiff($arrData, $arrKeys);
        return $arrData;
    }

    /**
     * 쿼리 실행 결과 로우 수와 $ref의 값을 비교하는 함수
     *
     * @param $strSQL
     * @param $arrBind
     * @param $ref
     *
     * @return bool num_rows() > $ref ? true : false
     */
    public static function isGreaterThanNumRows($strSQL, $arrBind, $ref)
    {
        /** @var \Framework\Database\DBTool $db */
        $db = App::load('DB');
        $db->query_fetch($strSQL, $arrBind);
        $isNumRows = $db->num_rows() > $ref;
        if ($isNumRows) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 회원 필드 정보
     *
     * @param int $mallSno
     * @param string $callPath (mypage : 회원정보 수정시에는 SNS 회원가입 설정 사용안하기 위해 추가)
     *
     * @return array 회원 필드 정보
     */
    public static function getJoinField($mallSno = null, $callPath = null)
    {
        // 회원 가입 항목
        $field = \Component\Policy\JoinItemPolicy::getInstance()->getPolicy($mallSno);

        // 관심분야
        $field['interest']['data'] = \Component\Code\Code::getGroupItems('01001', $mallSno);

        // 직업
        $field['job']['data'] = \Component\Code\Code::getGroupItems('01002', $mallSno);

        // 메일도메인
        $field['email']['data'] = SkinUtils::getMailDomain();
        $field['email']['data'] = array_merge(['self' => __('직접입력')], $field['email']['data']);

        // 회원등급
        $field['groupSno']['data'] = \Component\Member\Group\Util::getGroupName();

        $field['ex'] = [];
        for ($i = 1; $i < 7; $i++) {
            $k = 'ex' . $i;
            $v = StringUtils::strIsSet($field[$k]);
            if (empty($field[$k]['use']) === false && $field[$k]['use'] == 'y') {
                $field['ex'][$k] = [
                    'title'   => StringUtils::strIsSet($v['name'], __('추가') . $i),
                    'type'    => StringUtils::strIsSet($v['type']),
                    'items'   => explode(',', StringUtils::strIsSet($v['value'])),
                    'require' => StringUtils::strIsSet($v['require']),
                ];
            }
        }

        // 부가항목 사용여부
        //@formatter:off
        $optionsFieldName = ['fax', 'recommId', 'birthDt', 'sexFl', 'marriFl', 'job', 'interest', 'expirationFl', 'memo', 'ex', 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'];
        //@formatter:on

        foreach ($optionsFieldName as $value) {
            if (array_key_exists($value, $field)) {
                if ($field[$value]['use'] == 'y') {
                    $field['options']['use'] = 'y';
                    break;
                }
            }
        }

        if ($callPath === 'mypage') { // 회원정보 수정시에는 SNS 회원가입 설정 사용안함
            return $field;
        } else {
            $session = App::getInstance('session');
            if($session->has(GodoPaycoServerApi::SESSION_USER_PROFILE)){
                return MemberUtil::unsetDiffByPaycoLogin($field);
            } else if ($session->has(GodoNaverServerApi::SESSION_USER_PROFILE)) {
                return MemberUtil::unsetDiffByNaverLogin($field);;
            } else if($session->has(Facebook::SESSION_USER_PROFILE)) {
                return MemberUtil::unsetDiffByFacebookLogin($field);
            } else if($session->has(GodoKakaoServerApi::SESSION_USER_PROFILE)){
                return MemberUtil::unsetDiffByKakaoLogin($field);
            } else if($session->has(GodoWonderServerApi::SESSION_USER_PROFILE)) {
                return MemberUtil::unsetDiffByWonderLogin($field);
            }
            else {
                return $field;
            }
        }

    }

    /**
     * 페이코 가입에 필요하지 않은 항목을 제거
     *
     * @static
     *
     * @param array $fields
     *
     * @return array
     */
    public static function unsetDiffByPaycoLogin(array $fields)
    {
        $session = App::getInstance('session');
        $mall = gd_isset($session->get(SESSION_GLOBAL_MALL), DEFAULT_MALL_NUMBER);

        if($mall['sno'] > 1){ // 해외몰
            $paycoPolicy = gd_policy(PaycoLoginPolicy::KEY, $mall['sno']);
            $joinItemPolicy = \App::load('\\Component\\Policy\\JoinItemPolicy')->getJoinPolicyDisplay($mall['sno']);
        }else {
            $paycoPolicy = gd_policy(PaycoLoginPolicy::KEY);
            $joinItemPolicy = \App::load('\\Component\\Policy\\JoinItemPolicy')->getJoinPolicyDisplay($mall);
        }

        $policyArr = [];

        // 부가정보, 추가정보 미선택시 배열에서 제거
        if($paycoPolicy['supplementInfo'] == 'n') {
            unset($joinItemPolicy['supplementInfo']);
        }
        if ($paycoPolicy['additionalInfo'] == 'n') {
            unset($joinItemPolicy['additionInfo']);
        }
        unset($joinItemPolicy['businessInfo']); //페이코 사업자회원가입 미지원

        foreach($joinItemPolicy as $key => $val) {
            foreach ($val as $key2) {
                foreach (array_keys($key2) as $val2) {
                    $policyArr[] = $val2;
                }
            }
        }

        // 부가항목 사용여부
        //@formatter:off
        $optionsFieldName = ['fax', 'recommId', 'birthDt', 'sexFl', 'marriFl', 'job', 'interest', 'expirationFl', 'memo'];
        $optionsFieldName2 = [ 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'];
        //@formatter:on

        if ($session->has(GodoPaycoServerApi::SESSION_USER_PROFILE)) {
            if($paycoPolicy['simpleLoginFl']=='y') { // 간편로그인 선택시
                if($fields['birthDt']['use'] == 'y' || $fields['sexFl']['use'] == 'y') {
                    //@formatter:off
                    ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'options', 'maillingFl', 'smsFl']);
                    //@formatter:on
                } else {
                    //@formatter:off
                    ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'maillingFl', 'smsFl']);
                    //@formatter:on
                }
            } else if($paycoPolicy['simpleLoginFl'] == 'n') { // 일반 로그인 선택시
                foreach ($optionsFieldName as $value) {       // 부가정보 추가.
                    if (in_array($value, $policyArr)) {
                        $policyArr[] .= 'options';
                        break;
                    }
                }
                foreach($optionsFieldName2 as $value){ // ex1 ~ ex6 추가
                    if(in_array($value, $policyArr)){
                        $policyArr[] .= 'options';
                        $policyArr[] .= 'ex';
                        break;
                    }
                }
                //@formatter:off
                ArrayUtils::unsetDiff($fields, $policyArr);
                //@formatter:on
            }
        }
        return $fields;
    }
    /**
     * 페이스북 아이디로 간편회원가입에 필요하지 않은 항목을 제거
     *
     * @static
     *
     * @param array $fields
     *
     * @return array
     */
    public static function unsetDiffByFacebookLogin(array $fields)
    {
        $session = App::getInstance('session');
        $mall = gd_isset($session->get(SESSION_GLOBAL_MALL), DEFAULT_MALL_NUMBER);

        if($mall['sno'] >1) {
            $facebookPolicy = gd_policy(SnsLoginPolicy::KEY, $mall['sno']);
        } else {
            $facebookPolicy = gd_policy(SnsLoginPolicy::KEY);
        }

        //현재 회원가입항목 설정된값
        $joinItemPolicy = \App::load('\\Component\\Policy\\JoinItemPolicy')->getJoinPolicyDisplay($mall['sno']);
        $policyArr = [];

        if($facebookPolicy['supplementInfo'] == 'n') {
            unset($joinItemPolicy['supplementInfo']);
        }
        if ($facebookPolicy['additionalInfo'] == 'n') {
            unset($joinItemPolicy['additionInfo']);
        }
        foreach($joinItemPolicy as $key => $val){
            foreach ($val as $key2) {
                foreach (array_keys($key2) as $val2) {
                    $policyArr[] = $val2;
                }
            }
        }

        // 부가항목 사용여부
        //@formatter:off
        $optionsFieldName = ['fax', 'recommId', 'birthDt', 'sexFl', 'marriFl', 'job', 'interest', 'expirationFl', 'memo'];
        $optionsFieldName2 = [ 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'];
        //@formatter:on

        if($facebookPolicy['simpleLoginFl']=='y') { // 간편로그인 사용시
            if($fields['birthDt']['use'] == 'y' || $fields['sexFl']['use'] == 'y') {
                //@formatter:off
                ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'options', 'maillingFl', 'smsFl']);
                //@formatter:on
            } else {
                //@formatter:off
                ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'maillingFl', 'smsFl']);
                //@formatter:on
            }
        } else if($facebookPolicy['simpleLoginFl'] == 'n') { //일반 로그인 사용시
            foreach ($optionsFieldName as $value) {          // 부가정보 추가.
                if (in_array($value, $policyArr)) {
                    $policyArr[] .= 'options';
                    break;
                }
            }
            foreach($optionsFieldName2 as $value){ // ex1 ~ ex6 추가
                if(in_array($value, $policyArr)){
                    $policyArr[] .= 'options';
                    $policyArr[] .= 'ex';
                    break;
                }
            }
            ArrayUtils::unsetDiff($fields, $policyArr);
            //@formatter:on
        }

        // 페이스북 사업자 일반 회원가입 선택시 지원 - 해외몰 사업자 가입 불가, 간편회원가입 사용시 사업자 가입 불가
        if($joinItemPolicy['businessMember']['use'] == 'y' && $facebookPolicy['simpleLoginFl'] == 'n') {
            if ($facebookPolicy['businessInfo'] == 'y' && $mall == DEFAULT_MALL_NUMBER) {
                $fields['businessinfo']['use'] = 'y';
            }
        }

        return $fields;
    }
    /**
     * 네이버 아이디로 간편회원가입에 필요하지 않은 항목을 제거
     *
     * @static
     *
     * @param array $fields
     *
     * @return array
     */
    public static function unsetDiffByNaverLogin(array $fields)
    {
        $session = App::getInstance('session');
        $naverPolicy = gd_policy(NaverLoginPolicy::KEY);
        $mall = gd_isset($session->get(SESSION_GLOBAL_MALL), DEFAULT_MALL_NUMBER);

        $joinItemPolicy = \App::load('\\Component\\Policy\\JoinItemPolicy')->getJoinPolicyDisplay($mall);
        $policyArr = [];

        // 부가정보, 추가정보 미선택시 배열에서 제거
        if($naverPolicy['supplementInfo'] == 'n') {
            unset($joinItemPolicy['supplementInfo']);
        }
        if ($naverPolicy['additionalInfo'] == 'n'){
            unset($joinItemPolicy['additionInfo']);
        }
        unset($joinItemPolicy['businessInfo']); // 네이버아이디로그인 사업자회원가입 미지원

        foreach($joinItemPolicy as $key => $val){
            foreach ($val as $key2) {
                foreach (array_keys($key2) as $val2) {
                    $policyArr[] = $val2;
                }
            }
        }
        // 부가항목 사용여부
        //@formatter:off
        $optionsFieldName = ['fax', 'recommId', 'birthDt', 'sexFl', 'marriFl', 'job', 'interest', 'expirationFl', 'memo'];
        $optionsFieldName2 = [ 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'];
        //@formatter:on

        if ($session->has(GodoNaverServerApi::SESSION_USER_PROFILE)) {
            //@formatter:off
            if($naverPolicy['simpleLoginFl']=='y') { // 간편로그인 사용시
                if($fields['birthDt']['use'] == 'y' || $fields['sexFl']['use'] == 'y') {
                    //@formatter:off
                    ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'options', 'maillingFl', 'smsFl']);
                    //@formatter:on
                } else {
                    //@formatter:off
                    ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'maillingFl', 'smsFl']);
                    //@formatter:on
                }
            } elseif($naverPolicy['simpleLoginFl'] == 'n') { //일반 로그인 사용시
                foreach ($optionsFieldName as $value) {      // 부가정보 추가.
                    if (in_array($value, $policyArr)) {
                        $policyArr[] .= 'options';
                        break;
                    }
                }
                foreach($optionsFieldName2 as $value){ // ex1 ~ ex6 추가
                    if(in_array($value, $policyArr)){
                        $policyArr[] .= 'options';
                        $policyArr[] .= 'ex';
                        break;
                    }
                }
                ArrayUtils::unsetDiff($fields, $policyArr);
                //@formatter:on
            }
        }

        return $fields;
    }

    /**
     * 카카오톡 아이디로 간편회원가입에 필요하지 않은 항목을 제거
     */
    public static function unsetDiffByKakaoLogin(array $fields)
    {
        $session = App::getInstance('session');
        $kakaoPolicy = gd_policy(KakaoLoginPolicy::KEY);
        $mall = gd_isset($session->get(SESSION_GLOBAL_MALL), DEFAULT_MALL_NUMBER);

        $joinItemPolicy = \App::load('\\Component\\Policy\\JoinItemPolicy')->getJoinPolicyDisplay($mall);
        $policyArr = [];

        // 부가정보, 추가정보 미선택시 배열에서 제거
        if($kakaoPolicy['supplementInfo'] == 'n') {
            unset($joinItemPolicy['supplementInfo']);
        }
        if ($kakaoPolicy['additionalInfo'] == 'n'){
            unset($joinItemPolicy['additionInfo']);
        }

        foreach($joinItemPolicy as $key => $val){
            foreach ($val as $key2) {
                foreach (array_keys($key2) as $val2) {
                    $policyArr[] = $val2;
                }
            }
        }
        // 부가항목 사용여부
        //@formatter:off
        $optionsFieldName = ['fax', 'recommId', 'birthDt', 'sexFl', 'marriFl', 'job', 'interest', 'expirationFl', 'memo'];
        $optionsFieldName2 = [ 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'];
        //@formatter:on

        if ($session->has(GodoKakaoServerApi::SESSION_USER_PROFILE)) {
            //@formatter:off
            if($kakaoPolicy['simpleLoginFl']=='y') { // 간편로그인 사용시
                if($fields['birthDt']['use'] == 'y' || $fields['sexFl']['use'] == 'y') {
                    //@formatter:off
                    ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'options', 'maillingFl', 'smsFl']);
                    //@formatter:on
                } else {
                    //@formatter:off
                    ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'maillingFl', 'smsFl']);
                    //@formatter:on
                }
            } elseif($kakaoPolicy['simpleLoginFl'] == 'n') { //일반 로그인 사용시
                foreach ($optionsFieldName as $value) {      // 부가정보 추가.
                    if (in_array($value, $policyArr)) {
                        $policyArr[] .= 'options';
                        break;
                    }
                }
                foreach($optionsFieldName2 as $value){ // ex1 ~ ex6 추가
                    if(in_array($value, $policyArr)){
                        $policyArr[] .= 'options';
                        $policyArr[] .= 'ex';
                        break;
                    }
                }
                ArrayUtils::unsetDiff($fields, $policyArr);
                //@formatter:on
            }
        }

        if($joinItemPolicy['businessMember']['use'] == 'y' && $kakaoPolicy['simpleLoginFl'] == 'n') {
            if ($kakaoPolicy['businessInfo'] == 'y' && $mall == DEFAULT_MALL_NUMBER) {
                $fields['businessinfo']['use'] = 'y';
            }
        }

        return $fields;
    }

    public static function unsetDiffByWonderLogin(array $fields)
    {
        $session = App::getInstance('session');
        $wonderPolicy = gd_policy(WonderLoginPolicy::KEY);
        $mall = gd_isset($session->get(SESSION_GLOBAL_MALL), DEFAULT_MALL_NUMBER);

        $joinItemPolicy = \App::load('\\Component\\Policy\\JoinItemPolicy')->getJoinPolicyDisplay($mall);
        $policyArr = [];

        // 부가정보, 추가정보 미선택시 배열에서 제거
        if($wonderPolicy['supplementInfo'] == 'n') {
            unset($joinItemPolicy['supplementInfo']);
        }
        if ($wonderPolicy['additionalInfo'] == 'n'){
            unset($joinItemPolicy['additionInfo']);
        }

        foreach($joinItemPolicy as $key => $val){
            foreach ($val as $key2) {
                foreach (array_keys($key2) as $val2) {
                    $policyArr[] = $val2;
                }
            }
        }
        // 부가항목 사용여부
        //@formatter:off
        $optionsFieldName = ['fax', 'recommId', 'birthDt', 'sexFl', 'marriFl', 'job', 'interest', 'expirationFl', 'memo'];
        $optionsFieldName2 = [ 'ex1', 'ex2', 'ex3', 'ex4', 'ex5', 'ex6'];
        //@formatter:on

        if ($session->has(GodoWonderServerApi::SESSION_USER_PROFILE)) {
            //@formatter:off
            if($fields['birthDt']['use'] == 'y' || $fields['sexFl']['use'] == 'y') {
                //@formatter:off
                ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'options', 'maillingFl', 'smsFl']);
                //@formatter:on
            } else {
                //@formatter:off
                ArrayUtils::unsetDiff($fields, ['memId', 'email', 'cellPhone', 'memNm', 'memPw', 'sexFl', 'birthDt', 'calendarFl', 'maillingFl', 'smsFl']);
                //@formatter:on
            }
            //@formatter:on
        }
        return $fields;
    }

    /**
     * 구분자를 이용해 문자열의 값을 배열로 반환. 배열의 경우 재귀호출함.
     *
     * @param            $data
     * @param            $delimiter
     * @param array|null $target
     *
     * @return array
     */
    public static function explodeDataByDelimiter($data, $delimiter, array $target = null)
    {
        if (is_array($data) === true) {
            foreach ($data as $key => $value) {
                if ($target === null) {
                    $data[$key] = static::explodeDataByDelimiter($value, $delimiter);
                } else {
                    if (in_array($key, $target) === true) {
                        $data[$key] = static::explodeDataByDelimiter($value, $delimiter);
                    }
                }
            }
        } else {
            if (strpos($data, $delimiter) !== false) {
                $data = ArrayUtils::removeEmpty(explode($delimiter, $data));
            }
        }

        return $data;
    }

    /**
     * 관리자-회원정보 추가필드 html 생성
     *
     * @static
     *
     * @param array $fieldValue 추가필드 값
     *
     * @param null  $mallSno
     *
     * @return string
     */
    public static function makeExtraField(array $fieldValue = [], $mallSno = null)
    {
        if ($mallSno === null && $fieldValue['mallSno'] > DEFAULT_MALL_NUMBER) {
            $mallSno = $fieldValue['mallSno'];
        }
        $joinField = \Component\Policy\JoinItemPolicy::getInstance()->getPolicy($mallSno);

        $html[] = '<tr>';
        for ($i = 1; $i < 7; $i++) {
            $key = 'ex' . $i;
            $value = $joinField[$key];
            $title = '추가' . $i;
            if ($value['use'] != 'y') {
                continue;
            }
            if ($value['name']) {
                $title .= '<br /><span class="nobold">(' . $value['name'] . ')</span>';
            }
            if ($i != 1 && $i % 2 != 0) {
                $html[] = '</tr><tr>';
            }
            $html[] = '<th class="input_title r_space ex">' . $title . '</th>';
            $html[] = '<td>';
            $tmpArray = explode(',', $value['value']);
            switch ($value['type']) {
                default :
                    $html[] = '<input type="text" name="' . $key . '" class="form-control" value="' . $fieldValue[$key] . '"/>';
                    break;
                case 'SELECT':
                    $html[] = gd_select_box($key, $key, gd_array_change_key_value($tmpArray), null, $fieldValue[$key], '=선택=');
                    break;
                case 'RADIO':
                    $html[] = gd_radio_box($key, array_combine(array_values($tmpArray), array_values($tmpArray)), $fieldValue[$key]);
                    break;
                case 'CHECKBOX':
                    $html[] = gd_check_box($key . '[]', array_combine(array_values($tmpArray), array_values($tmpArray)), $fieldValue[$key]);
                    break;
            }
            $html[] = '</td>';
        }

        return implode('', $html);
    }

    /**
     * 로그인 되었는 지를 체크 (회원/비회원)
     *
     * @author artherot
     * @return bool => false, 회원 => member, 비회원 => guest)
     */
    public static function checkLogin()
    {
        $result = false;
        if (Session::has('member')) {
            $result = 'member';
        } else {
            if (Session::has('guest')) {
                $result = 'guest';
            }
        }

        return $result;
    }

    /**
     * 성인인증 여부 체크
     *
     * @author artherot
     * @return bool => false, 회원 => member, 비회원 => guest)
     */
    public static function checkAdult()
    {
        if ((gd_use_ipin() || gd_use_auth_cellphone()) && (!Session::has('certAdult') && (!Session::has('member') || (Session::has('member') && Session::get('member.adultFl') != 'y')))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 사용자가 입력한 비밀번호와 암호화된 비밀번호를 비교하는 함수
     *
     * @static
     *
     * @param $input
     * @param $password
     *
     * @return mixed
     * @throws Exception
     */
    public static function verifyPassword($input, $password)
    {
        if (Validator::password($input, true) === false) {
            throw new Exception(__('비밀번호 검증 오류입니다.'), 400);
        }

        return App::getInstance('password')->verify($input, $password);
    }

    /**
     * 현재 로그인된 세션의 데이터베이스 회원 정보를 반환하는 함수
     *
     * @param string $column
     *
     * @return object
     * @throws Exception
     */
    public static function getMemberBySession($column = '*')
    {
        if (MemberUtil::isLogin() === false) {
            throw new AlertRedirectException(__('로그인된 회원정보를 찾을 수 없습니다.'), 401, null, '../member/login.php', 'top');
        }
        $db = App::load('DB');
        $fields = DBTableField::getFieldTypes('tableMember');
        $db->strField = $column;
        $db->strWhere = 'appFl = \'y\' AND sleepFl = \'n\' AND memNo = ? AND memId = ? AND memPw = ?';
        $arrBind = [];
        $db->bind_param_push($arrBind, $fields['memNo'], Session::get('member.memNo'));
        $db->bind_param_push($arrBind, $fields['memId'], Session::get('member.memId'));
        $db->bind_param_push($arrBind, $fields['memPw'], Encryptor::decrypt(Session::get('member.memPw')));

        $query = $db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);
        $data = $db->slave()->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $data;
    }

    /**
     * 회원정보 암호화
     *
     * @static
     *
     * @param $data
     *
     * @return array
     */
    public static function encryptMember($data)
    {
        $arrEncode = ['memPw'];
        $memInfo = [];
        foreach ($data as $key => $val) {
            if (in_array($key, $arrEncode)) {
                if (empty($val)) {
                    continue;
                }
                $val = Encryptor::encrypt($val);
            }
            $memInfo[$key] = StringUtils::htmlSpecialCharsAddSlashes($val);
        }

        return $memInfo;
    }

    /**
     * 이메일 중복 확인. 이미 해당 이메일을 사용 중인 아이디일 경우 중복되지 않은 것으로 판단한다.
     *
     * @static
     *
     * @param string $memId
     * @param string $email
     *
     * @return bool true 중복된 이메일, false 중복되지 않거나 해당 아이디가 사용 중인 이메일
     * @throws Exception
     */
    public static function overlapEmail($memId, $email)
    {
        if (Validator::required($memId) === false) {
            throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('아이디')));
        }

        if (Validator::email($email, true) === false) {
            throw new Exception(sprintf(__('입력된 %s은(는) 잘못된 형식입니다.'), __('이메일')));
        }

        $fields = DBTableField::getFieldTypes('tableMember');
        $strSQL = 'SELECT memId FROM ' . DB_MEMBER . ' where memId != ? and email = ?';
        $arrBind = [];
        $db = App::load('DB');
        $db->bind_param_push($arrBind, $fields['memId'], $memId);
        $db->bind_param_push($arrBind, $fields['email'], $email);

        return MemberUtil::isGreaterThanNumRows($strSQL, $arrBind, 0);
    }

    /**
     * 이메일 중복 확인. 이메일 정보로만 등록된 이메일이 있는지 여부를 판단한다.
     *
     * @param string $email
     *
     * @return bool true 중복된 이메일, false 중복되지 않은 이메일
     * @throws Exception
     */
    public function simpleOverlapEmail($email, $memId): bool
    {
        if (Validator::email($email, true) === false) {
            throw new Exception(sprintf(__('입력된 %s은(는) 잘못된 형식입니다.'), __('이메일')));
        }
        $member = \App::load('Component\\Member\\MemberDAO')->selectModifyMemberByOne($email, 'email', $memId);

        return StringUtils::strIsSet($member['memNo'], 0) > 0;
    }

    /**
     * 사업자번호 중복 확인
     *
     * @static
     *
     * @param $memId
     * @param $busiNo
     *
     * @return bool
     * @throws Exception
     */
    public static function overlapBusiNo($memId, $busiNo)
    {
        if (Validator::required($busiNo) === false) {
            throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('사업자번호')));
        }

        $busiNo = self::businessNumberFormatter($busiNo);
        $fields = DBTableField::getFieldTypes('tableMember');
        $strSQL = 'SELECT busiNo FROM ' . DB_MEMBER . ' where memId != ? and busiNo = ?';
        $arrBind = [];
        $db = App::load('DB');
        $db->bind_param_push($arrBind, $fields['memId'], $memId);
        $db->bind_param_push($arrBind, $fields['busiNo'], $busiNo);

        return MemberUtil::isGreaterThanNumRows($strSQL, $arrBind, 0);
    }

    /**
     * 사업자번호 중복 확인. 사업자번호 정보로만 등록된 사업자번호가 있는지 여부를 판단한다.
     *
     * @param string $busiNo
     *
     * @return bool true 중복된 사업자번호, false 중복되지 않은 사업자번호
     * @throws Exception
     */
    public function simpleOverlapBusiNo($busiNo, $memId)
    {
        if (Validator::required($busiNo) === false) {
            throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('사업자번호')));
        }

        $busiNo = self::businessNumberFormatter($busiNo);
        $member = \App::load('Component\\Member\\MemberDAO')->selectModifyMemberByOne($busiNo, 'busiNo', $memId);

        if (ArrayUtils::dimension($member) > 1) {
            return true;
        } else {
            return StringUtils::strIsSet($member['busiNo'], 0) > 0;;
        }
    }

    /**
     * 닉네임 중복 확인
     *
     * @static
     *
     * @param $memId
     * @param $nickNm
     *
     * @return bool
     * @throws Exception
     */
    public static function overlapNickNm($memId, $nickNm)
    {
        $length = MemberUtil::getMinMax();

        if (Validator::required($nickNm) === false) {
            throw new Exception(sprintf(__('필수항목을 입력해주세요. : %s'), __('닉네임')));
        }
        if (Validator::minlen(gd_isset($length['nickNm']['minlen'], 4), $nickNm, false, 'UTF-8') === false) {
            throw new Exception(sprintf(Validator::TEXT_MINLEN_INVALID, __('닉네임'), $length['nickNm']['minlen']));
        }
        if (Validator::maxlen(gd_isset($length['nickNm']['maxlen'], 20), $nickNm, false, 'UTF-8') === false) {
            throw new Exception(sprintf(Validator::TEXT_MAXLEN_INVALID, __('닉네임'), $length['nickNm']['maxlen']));
        }

        $fields = DBTableField::getFieldTypes('tableMember');
        $strSQL = 'SELECT memId FROM ' . DB_MEMBER . ' where memId != ? and nickNm = ?';
        $arrBind = [];
        $db = App::load('DB');
        $db->bind_param_push($arrBind, $fields['memId'], $memId);
        $db->bind_param_push($arrBind, $fields['nickNm'], $nickNm);

        return MemberUtil::isGreaterThanNumRows($strSQL, $arrBind, 0);
    }

    /**
     * 아이디 중복 확인
     *
     * @static
     *
     * @param string $memId 회원아이디
     *
     * @return bool
     * @throws Exception
     */
    public static function overlapMemId($memId)
    {
        \Logger::info(__METHOD__);
        // 회원사용필드 최대 최소값
        $length = MemberUtil::getMinMax();

        if (Validator::userid($memId, true, false) === false) {
            throw new Exception(__('아이디는 영문, 숫자, 특수문자(-),(_),(.),(@)만 입력하실 수 있습니다.(@는 1개만 입력 가능합니다.)'));
        }
        if (Validator::minlen(gd_isset($length['memId']['minlen'], 4), $memId) === false) {
            throw new Exception(sprintf(__('입력된 아이디 길이가 너무 짧습니다.'), __('아이디'), $length['memId']['minlen']));
        }
        if (Validator::maxlen(gd_isset($length['memId']['maxlen'], 30), $memId) === false) {
            throw new Exception(sprintf(Validator::TEXT_MAXLEN_INVALID, __('아이디'), $length['memId']['maxlen']));
        }

        // 거부 아이디 필터링
        $joinPolicy = gd_policy('member.join');
        if (StringUtils::findInDivision(strtoupper($memId), strtoupper($joinPolicy['unableid']))) {
            throw new Exception(sprintf(__('%s는 사용이 제한된 아이디입니다'), $memId));
        }

        /** @var \Bundle\Component\Member\HackOut\HackOutService $hackOutService */
        $hackOutService = App::load('\\Component\\Member\\HackOut\\HackOutService');
        $hackOutService->checkRejoinByMemberId($memId);

        /** @var \Bundle\Component\Member\Member $member */
        $member = App::load('\\Component\\Member\\Member');
        $memberInfo = $member->getMember($memId, 'memId');

        return empty($memberInfo['memId']) === false;
    }

    /**
     * 추천 아이디 체크
     *
     * @static
     *
     * @param $memId
     * @param $recommId
     *
     * @return bool
     * @throws Exception
     */
    public static function checkRecommendId($recommId, $memId = null)
    {
        $logger = \App::getInstance('logger');
        if (Validator::recommid($recommId, true, false) === false) {
            $logger->info('validator user id fail');
            throw new Exception(__('추천 아이디를 다시 확인해 주세요.'));
        }
        if (empty($recommId)) {
            $logger->info('empty recommend id');

            return true;
        } else {
            $memberDAO = \Component\Member\MemberDAO::getInstance();
            $recommMember = $memberDAO->selectMemberByOne($recommId, 'memId');
            if ($memId !== null) {
                // 본인을 추천하는지 체크
                if ($recommId == $memId) {
                    $logger->info(sprintf('equal recommend id[%s] and user id[%s]', $recommId, $memId));

                    return false;
                }
                if (\App::getInstance('session')->has(SESSION_GLOBAL_MALL)) {
                    $member = ['mallSno' => \Component\Mall\Mall::getSession('sno')];
                } else {
                    $logger->info('has not global mall session');
                    $member = $memberDAO->selectMemberByOne($memId, 'memId');
                    if (empty($member)) {
                        $logger->info(sprintf('empty member by id[%s]', $memId));
                        $member = ['mallSno' => DEFAULT_MALL_NUMBER];
                    }
                }
                // 동일한 상점인지 체크
                if ($recommMember['mallSno'] != $member['mallSno']) {
                    $logger->info(sprintf('not equal recommend mall number[%d] and user mall number[%d]', $recommMember['mallSno'], $member['mallSno']));

                    return false;
                }
            }

            return empty($recommMember['memId']) === false;
        }
    }

    /**
     * 회원가입 시 나이체크
     *
     * @param $age string 나이
     *
     * @return string
     */
    public static function checkJoinAuth($age)
    {
        $result = 'y';
        $joinPolicy = ComponentUtils::getPolicy('member.join');
        if ($joinPolicy['under14Fl'] != 'n') {  //가입연령제한하지 않음이 아닌경우
            if ($joinPolicy['limitAge']) {
                if ($joinPolicy['limitAge'] > (int) $age) {    //미만인경우
                    if ($joinPolicy['under14Fl'] == 'y') {
                        $result = 'adminApply';
                    } else if ($joinPolicy['under14Fl'] == 'no') {
                        $result = 'n';
                    }
                }
            }
        }

        return $result;
    }


    /**
     * 인증키값 중복체크
     *
     * @param $dupeInfo
     * @param $cellPhone
     * @param $memNm
     *
     * @return bool
     */
    public static function isExistsDupeInfo($dupeInfo, $cellPhone = null, $memNm = null)
    {
        /** @var \Bundle\Component\Member\Member $member */
        $member = App::load('\\Component\\Member\\Member');

        // 이마트 보안 취약점 요청사항 휴대폰인증 중복체크시 인증시 사용한 핸드폰번호와 이름포함 동일여부 체크
        if ($cellPhone && $memNm) {
            $memberParams = [
                'dupeInfo' => $dupeInfo,
                'cellPhone' => StringUtils::numberToCellPhone($cellPhone),
                'memNm' => $memNm,
            ];

            return $member->getDataByTable(DB_MEMBER, array_values($memberParams), array_keys($memberParams), 'count(memNo) as cnt')['cnt'] > 0;
        } else {
            return $member->getMember($dupeInfo, 'dupeinfo', 'count(memNo) as cnt')['cnt'] > 0;
        }
    }

    /**
     * 회원 재가입 체크
     *
     * @param $dupeInfo
     *
     * @return bool
     */
    public static function isReJoinByDupeinfo($dupeInfo)
    {
        $db = App::load('DB');
        $cfgJoin = gd_policy('member.join');
        if (gd_isset($cfgJoin['rejoinFl']) == 'y' && gd_isset($cfgJoin['rejoin'], 0) > 0) {
            $where = ['dupeinfo=?'];
            $arrBind = [];
            $db->bind_param_push($arrBind, 's', $dupeInfo);
            $reJoinDt = date('Ymd', time() - (($cfgJoin['rejoin'] - 1) * 86400));
            array_push($where, "date_format(regDt, '%Y%m%d') >= ?");
            $db->bind_param_push($arrBind, 's', $reJoinDt);
            $strSQL = 'SELECT count(sno) as cnt FROM ' . DB_MEMBER_HACKOUT . ' WHERE   ' . implode(' AND ', $where);
            $count = $db->query_fetch($strSQL, $arrBind, false)['cnt'];
            if ($count > 0) {
                return false;
            }
        }

        return true;
    }


    /**
     * 비회원 로그인
     *
     * @author artherot
     */
    public static function guest()
    {
        // 회원 로그인 체크
        if (Session::has(Member::SESSION_MEMBER_LOGIN)) {
            throw new Exception(__('회원으로 로그인 된 상태입니다.'));
        }

        // 비회원 세션 정보
        Session::set('guest.login', 'ok');
    }

    /**
     * 성인 비회원 로그인
     *
     * @author sunny
     *
     * @param $data
     *
     * @throws Exception
     */
    public static function adultGuest($data)
    {
        // 회원 로그인 체크
        if (Session::has('member')) {
            throw new Exception(__('회원으로 로그인 된 상태입니다.'));
        }

        // Validation
        $validator = new Validator();
        $validator->add('rncheck', '', true);
        $validator->add('authName', '');
        $validator->add('pakey', '');
        $validator->add('birthday', '');
        $validator->add('sex', '');
        $validator->add('dupeinfo', '');
        $validator->add('foreigner', '');
        $validator->add('phoneNum', '');
        if ($validator->act($data, true) === false) {
            throw new Exception(__('%s 항목이 잘못 되었습니다.'), implode("\n", $validator->errors));
        }

        // 비회원 로그인
        MemberUtil::guest();

        // 본인확인정보
        if ($data['rncheck'] == 'ipin') {
            Session::set(
                'certAdult', [
                    'rncheck'   => $data['rncheck'],
                    'authName'  => $data['authName'],
                    'pakey'     => $data['pakey'],
                    'birthday'  => $data['birthday'],
                    'sex'       => $data['sex'],
                    'dupeinfo'  => $data['dupeinfo'],
                    'foreigner' => $data['foreigner'],
                ]
            );
        } elseif ($data['rncheck'] == 'authCellphone') {
            Session::set(
                'certAdult', [
                    'rncheck'   => $data['rncheck'],
                    'authName'  => $data['authName'],
                    'pakey'     => $data['pakey'],
                    'birthday'  => $data['birthday'],
                    'sex'       => $data['sex'],
                    'dupeinfo'  => $data['dupeinfo'],
                    'foreigner' => $data['foreigner'],
                    'phoneNum'  => $data['phoneNum'],
                ]
            );
        }
    }

    /**
     * 비회원 주문 로그인
     * 비밀번호는 Password 해쉬처리 되어 저장됩니다.
     *
     * @author artherot
     *
     * @param string $orderNo 주문번호
     * @param string $orderNm 주문자명
     */
    public static function guestOrder($orderNo = null, $orderNm = null)
    {
        // 비회원 로그인
        if (!Session::has('guest')) {
            MemberUtil::guest();
        }

        // 비회원 주문번호가 있는 경우
        if (is_null($orderNo) === false) {
            Session::set('guest.orderNo', $orderNo);
        }

        // 비회원 패스워드가 있는 경우
        if (is_null($orderNm) === false) {
            Session::set('guest.orderNm', $orderNm);
        }
    }


    /**
     * 중복가입확인정보 중복 확인
     *
     * @param string $memId    회원아이디
     * @param string $dupeinfo 중복가입확인정보
     *
     * @return bool
     * @throws Exception
     */
    public static function overlapDupeinfo($memId, $dupeinfo)
    {
        /** @var \Framework\Database\DBTool $db */
        $db = App::load('DB');
        $fields = DBTableField::getFieldTypes('tableMember');

        // Validation
        if (Validator::required($dupeinfo) === false) {
            throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('중복가입확인정보')));
        }
        if (Validator::userid($memId, true) === false) {
            throw new Exception(__('아이디는 영문, 숫자, 특수문자(-),(_),(.),(@)만 입력하실 수 있습니다.(@는 1개만 입력 가능합니다.)'));
        }

        $arrBind = [];
        $strSQL = 'SELECT memId FROM ' . DB_MEMBER . ' where memId != ? and dupeinfo = ?';
        $db->bind_param_push($arrBind, $fields['memId'], $memId);
        $db->bind_param_push($arrBind, $fields['dupeinfo'], $dupeinfo);

        return MemberUtil::isGreaterThanNumRows($strSQL, $arrBind, 0);
    }

    /**
     * getRecommendCount
     *
     * @static
     *
     * @param $recommId
     *
     * @return int
     * @throws Exception
     */
    public static function getRecommendCount($recommId)
    {
        if (Validator::required($recommId) === false) {
            throw new Exception(sprintf(__('%s 항목이 잘못 되었습니다.'), __('추천회원아이디')));
        }

        /** @var \Framework\Database\DBTool $db */
        $db = App::load('DB');

        $strSQL = 'SELECT memNo FROM ' . DB_MEMBER . ' where recommId = $recommId';
        $db->query_fetch($strSQL);

        return $db->num_rows();
    }


    /**
     * 회원 가입 전 인증 및 약관 관련 정보를 세션에 저장한다.
     *
     * @param $arrData
     *
     * @return mixed
     */
    public static function saveJoinInfoBySession($arrData)
    {
        $joinSession = Session::get(Member::SESSION_JOIN_INFO);
        $data['memberFl'] = $joinSession['memberFl'];
        $data['agreementInfoFl'] = gd_isset($arrData['agreementInfoFl'], 'n');
        $data['privateApprovalFl'] = gd_isset($arrData['privateApprovalFl'], 'n');
        $data['under14ConsentFl'] = gd_isset($arrData['under14ConsentFl'], 'n');
        
        //이용약관 체크값 설정
        $myPage = \App::load('\\Component\\Member\\MyPage');
        $agreementData = $myPage->setAgreementData($arrData);
        $data['privateApprovalOptionFl'] = gd_isset($agreementData['privateApprovalOptionFl']);
        $data['privateOfferFl'] = gd_isset($agreementData['privateOfferFl']);
        $data['privateConsignFl'] = gd_isset($agreementData['privateConsignFl']);

        // 인증설정체크해서 데이터 추가 useDataJoinFl
        $dreamInfo = gd_policy('member.auth_cellphone');
        $kcpInfo = gd_policy('member.auth_cellphone_kcp');
        if ($dreamInfo['useFl'] == 'y' || $kcpInfo['useFlKcp'] == 'y') {
            $data['memNm'] = gd_isset($arrData['nice_nm']);    //이름
            $data['cellPhone'] = gd_isset($arrData['mobile']);    //전화번호
            $data['mobileService'] = gd_isset($arrData['mobileService']);    //통신사
            $data['sexFl'] = gd_isset(strtolower($arrData['sex']));    //성별
            $data['birthDt'] = gd_isset($arrData['birthday']);    //생년월일
            $data['birthYear'] = DateTimeUtils::dateFormat('Y', gd_isset($arrData['birthday']));
            $data['birthMonth'] = DateTimeUtils::dateFormat('m', gd_isset($arrData['birthday']));
            $data['birthDay'] = DateTimeUtils::dateFormat('d', gd_isset($arrData['birthday']));
            if ($arrData['rncheck'] != 'ipin' && (($dreamInfo['useFl'] == 'y' && $dreamInfo['useDataJoinFl'] == 'y') || ($kcpInfo['useFlKcp'] == 'y' && $kcpInfo['useDataJoinFlKcp'] == 'y'))) {
                //SNS 간편로그인 가입으로 진행중이고, 본인인증 사용을 제외할 경우
                //항목 INPUT 값을 사용자가 입력할 수 있음 (dooray 참고 : 2533914246727045744)
                if (\Component\Member\MemberValidation::checkSNSmemberJoin() === true && \Component\Member\MemberValidation::checkSNSMemberAuth() === 'n') {
                    $data['authReadOnly'] = '';    //readonly처리
                    $data['authDisabled'] = '';    //disabled처리
                    $data['authRequired'] = ' required';    //필수클래스값처리
                    $data['authClassRequired'] = ' class="important"';    //필수클래스값처리
                } else {
                    $data['authReadOnly'] = ' readonly';    //readonly처리
                    $data['authDisabled'] = ' disabled';    //disabled처리
                    $data['authRequired'] = ' required';    //필수클래스값처리
                    $data['authClassRequired'] = ' class="important"';    //필수클래스값처리
                }
            }
        }
        $data['rncheck'] = gd_isset($arrData['rncheck']);    //인증타입
        $data['pakey'] = gd_isset($arrData['pakey']);
        $data['dupeinfo'] = gd_isset($arrData['dupeinfo']);    //인증
        $data['foreigner'] = gd_isset($arrData['foreigner']);    //외국인여부
        $data['adultFl'] = gd_isset($arrData['adultFl']);    //성인여부
        Session::set(Member::SESSION_JOIN_INFO, $data);

        return $data;
    }

    /**
     * @deprecated 2016-12-27 yjwee 미사용 함수 입니다. 아래의 방법을 참고하세요
     * @uses       gd_policy('member.joinitem')
     * @uses       \Component\Policy\JoinItemPolicy::getInstance()->getPolicy($mallSno|null)
     * @uses       \Framework\Utility\ComponentUtils::getPolicy('member.joinitem')
     */
    public static function getPolicyJoinitem()
    {
        return gd_policy('member.joinitem');
    }

    /**
     * 로그인 후 이동할 주소 반환
     *
     * @static
     * @return array|mixed|null|string
     */
    public static function getLoginReturnURL()
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $returnUrl = $request->request()->get('returnUrl');
        $phpSelf = $request->getPhpSelf();
        if (empty($returnUrl) || strpos($returnUrl, "member_ps") !== false || $returnUrl == 'undefined') {
            $returnUrl = $request->getReferer();
        }
        if (strpos($returnUrl, 'login') > -1 || strpos($returnUrl, 'logout') > -1) {
            if (count(explode('/', $phpSelf)) === 4) {    // 2017-03-28 yjwee request_uri 에 따라서 다르게 처리함
                $returnUrl = '../../main/index.php';
            } else {
                $returnUrl = '../main/index.php';
            }
        }
        $logger->info(sprintf('returnUrl is [%s], phpSelf[%s]', $returnUrl, $phpSelf));

        return $returnUrl;
    }

    /**
     * 관리자 회원 리스트의 검색창 체크박스 체크 처리 함수
     *
     * @static
     *
     * @param array $params
     *
     * @return mixed
     */
    public static function checkedByMemberListSearch(array $params)
    {
        $params['connectSns'] = $params['connectSns'] ?? '';
        $params['marriFl'] = $params['marriFl'] ?? '';
        $params['memberFl'] = $params['memberFl'] ?? '';
        $params['entryPath'] = $params['entryPath'] ?? '';
        $params['appFl'] = $params['appFl'] ?? '';
        $params['sexFl'] = $params['sexFl'] ?? '';
        $params['maillingFl'] = $params['maillingFl'] ?? '';
        $params['smsFl'] = $params['smsFl'] ?? '';
        $params['calendarFl'] = $params['calendarFl'] ?? '';
        $params['expirationFl'] = $params['expirationFl'] ?? '';

        $checked['connectSns'][$params['connectSns']] =
        $checked['marriFl'][$params['marriFl']] =
        $checked['memberFl'][$params['memberFl']] =
        $checked['entryPath'][$params['entryPath']] =
        $checked['appFl'][$params['appFl']] =
        $checked['sexFl'][$params['sexFl']] =
        $checked['maillingFl'][$params['maillingFl']] =
        $checked['smsFl'][$params['smsFl']] =
        $checked['calendarFl'][$params['calendarFl']] =
        $checked['expirationFl'][$params['expirationFl']] = 'checked="checked"';

        if (StringUtils::strIsSet($params['under14'], 'n') === 'y') {
            $checked['under14'][$params['under14']] = 'checked="checked"';
        }
        if ($params['mallSno'] !== null) {
            $checked['mallSno'][$params['mallSno']] = 'checked="checked"';
        }

        return $checked;
    }

    /**
     * 관리자 회원 리스트의 검색창 셀렉트박스 셀렉트 처리 함수
     *
     * @static
     *
     * @param array $requestParams
     *
     * @return mixed
     */
    public static function selectedByMemberListSearch(array $requestParams)
    {
        $requestParams['age'] = $requestParams['age'] ?? '';
        $requestParams['groupSno'] = $requestParams['groupSno'] ?? '';
        $requestParams['expirationDay'] = $requestParams['expirationDay'] ?? '';

        $selected['sort'][gd_isset($requestParams['sort'], 'entryDt desc')] = $selected['memberType'][gd_isset($requestParams['memberType'], 'select')] = $selected['groupSno'][$requestParams['groupSno']] = $selected['expirationDay'][$requestParams['expirationDay']] ='selected="selected"';

        if (gd_isset($requestParams['under14'], 'n') !== 'y') {
            $selected['age'][$requestParams['age']] = 'selected="selected"';
        }

        return $selected;
    }

    /**
     * 회원정보 이메일 형식 변환
     *
     * @static
     *
     * @param array $email
     *
     * @return array|string
     */
    public static function emailFormatter($email)
    {
        if (self::isset($email) && is_array($email) === true) {
            $email = (implode('', $email) == '' ? '' : implode('@', $email));
        }

        return $email;
    }

    /**
     * 회원정보 전화번호 형식 변환
     *
     * @static
     *
     * @param $number
     *
     * @return string
     */
    public static function phoneFormatter($number)
    {
        if (self::isset($number) && is_array($number) === true) {
            $number = (implode('', $number) == '' ? '' : implode('-', $number));
        } else if (strpos($number, '-') === false) {
            $number = StringUtils::numberToPhone($number);
        }

        return $number;
    }

    /**
     * 회원정보 사업자 번호 형식 변환
     *
     * @static
     *
     * @param $number
     *
     * @return mixed|string
     */
    public static function businessNumberFormatter($number)
    {
        if (self::isset($number)) {
            if (is_array($number)) {
                $number = implode('', $number) == '' ? '' : implode('-', $number);
            } else {
                $number = StringUtils::numberToBusiness($number);
            }
        }

        return $number;
    }

    /**
     * 회원정보 년월일 날짜 형식 변환
     *
     * @static
     *
     * @param $date
     *
     * @return string
     */
    public static function dateYmdDashFormatter($date)
    {
        if (self::isset($date) && !Validator::date($date)) {
            $date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }

        return $date;
    }

    /**
     * 값 검증 isset && is_null && empty
     *
     * @static
     *
     * @param $value
     *
     * @return bool true 인 경우 값이 존재
     */
    public static function isset($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return isset($value) && is_null($value) === false && empty($value) === false;
    }

    /**
     * 추가정보 및 구분자 | 를 사용하는 필드 형식 변환
     *
     * @static
     *
     * @param array $value
     *
     * @return array|string
     */
    public static function extraFormatter($value)
    {
        if (isset($value) && is_array($value) === true) {
            $value = (implode('', $value) == '' ? '' : '|' . implode('|', $value) . '|');
        }

        return $value;
    }

    /**
     * 로그인 세션의 회원정보가 기본몰 회원인지 여부를 체크하는 함수
     *
     * @return bool
     */
    public function isDefaultMallMemberSession()
    {
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        $request = \App::getInstance('request');
        if ($request->getSubdomain() == 'm') {
            return true;
        }
        $sessionName = \Component\Member\Member::SESSION_MEMBER_LOGIN;
        if (!$session->has($sessionName)) {
            $logger->info(sprintf('has not session %s', $sessionName));

            return false;
        }
        $memberSession = $session->get($sessionName);
        $result = $memberSession['mallSno'] == DEFAULT_MALL_NUMBER;
        if (!$result) {
            $logger->info(sprintf('login member mall number is %d', $memberSession['mallSno']));
        }

        return $result;
    }
}
