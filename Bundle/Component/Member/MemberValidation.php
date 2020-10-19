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
use Component\Godo\GodoPaycoServerApi;
use Component\Member\Util\MemberUtil;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Security\Token;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Globals;
use Logger;
use Message;
use Session;

/**
 * Class 회원 검증 클래스
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class MemberValidation
{

    /**
     * 회원 추가정보 검증 추가
     *
     * @static
     *
     * @param \Bundle\Component\Validator\Validator $validator
     * @param                                       $require
     */
    public static function addValidateMemberExtra(&$validator, $require)
    {
        $joinItem = gd_policy('member.joinitem');

        $validator->add('ex1', '', $require['ex1'], '{' . gd_isset($joinItem['ex1']['name'], '추가1') . '}'); // 추가1
        $validator->add('ex2', '', $require['ex2'], '{' . gd_isset($joinItem['ex2']['name'], '추가2') . '}'); // 추가2
        $validator->add('ex3', '', $require['ex3'], '{' . gd_isset($joinItem['ex3']['name'], '추가3') . '}'); // 추가3
        $validator->add('ex4', '', $require['ex4'], '{' . gd_isset($joinItem['ex4']['name'], '추가4') . '}'); // 추가4
        $validator->add('ex5', '', $require['ex5'], '{' . gd_isset($joinItem['ex5']['name'], '추가5') . '}'); // 추가5
        $validator->add('ex6', '', $require['ex6'], '{' . gd_isset($joinItem['ex6']['name'], '추가6') . '}'); // 추가6
    }

    /**
     * 사업자 회원 검증 추가
     *
     * @static
     *
     * @param \Bundle\Component\Validator\Validator $validator
     * @param                                       $require
     */
    public static function addValidateMemberBusiness(&$validator, $require)
    {
        $validator->add('company', 'memberNameGlobal', $require['company'], '{' . __('회사명') . '}'); // 회사명
        $validator->add('busiNo', '', $require['busiNo'], '{' . __('사업자번호') . '}'); // 사업자번호
        $validator->add('ceo', '', $require['ceo'], '{' . __('대표자명') . '}'); // 대표자명
        $validator->add('service', '', $require['service'], '{' . __('업체') . '}'); // 업체
        $validator->add('item', '', $require['item'], '{' . __('종목') . '}'); // 종목
        $validator->add('comZipcode', '', '', '{' . __('사업장우편번호') . '}'); // 사업장우편번호
        $validator->add('comZonecode', '', $require['comZonecode'], '{' . __('사업장우편번호') . '}'); // 사업장우편번호
        $validator->add('comAddress', '', $require['comAddress'], '{' . __('사업장주소') . '}'); // 사업장주소
        $validator->add('comAddressSub', '', $require['comAddress'], '{' . __('사업장상세주소') . '}'); // 사업장상세주소
    }

    /**
     * 회원 패스워드 검증
     *
     * @param     $password
     *
     * @return string
     * @throws Exception
     */
    public static function validateMemberPassword($password)
    {
        $joinItem = ComponentUtils::getPolicy('member.joinitem');
        $patterns = Validator::getPasswordPattern();

        if (Validator::required($password) === false) {
            throw new Exception(__("패스워드는 필수 항목입니다."));
        }
        $minLength = $joinItem['memPw']['minlen'];
        if (Validator::minlen($minLength, $password, true) === false) {
            throw new Exception(sprintf(__("패스워드는 %s자 이상 입력하셔야합니다."), $minLength), 500);
        }
        $maxLength = $joinItem['memPw']['maxlen'];
        if (Validator::maxlen($maxLength, $password, true) === false) {
            throw new Exception(sprintf(__("패스워드는 %s자 이하 입력하셔야합니다."), $maxLength), 500);
        }
        if(Validator::pattern('/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\_\+\-\=\`\~]+$/', $password, true) === false){
            throw new Exception(__("사용불가한 문자가 포함되어 있습니다. (사용가능 특수문자 : !@#$%^&*()_+-=`~)"), 500);
        }
        if (Validator::pattern($patterns['alpha'], $password, true) && Validator::pattern($patterns['numeric'], $password, true) && Validator::pattern($patterns['special'], $password, true)) {
            return __('안전한 비밀번호 입니다.');
        }

        if ($joinItem['passwordCombineFl'] == 'engNumEtc') {
            throw new Exception(sprintf(__('사용불가! 영문대/소문자, 숫자, 특수문자 중 3가지 이상 조합하세요.')), 500);
        }

        $isWeakPassword = false;
        if ($joinItem['passwordCombineFl'] == 'default') {
            $isWeakPassword = (Validator::pattern($patterns['alpha'], $password, true) || Validator::pattern($patterns['numeric'], $password, true));
        } else if ($joinItem['passwordCombineFl'] == 'engNum') {
            $isWeakPassword = (Validator::pattern($patterns['alpha'], $password, true) && Validator::pattern($patterns['numeric'], $password, true));
        } else {
            $isWeakPassword = (
                (Validator::pattern($patterns['alpha'], $password, true) && Validator::pattern($patterns['numeric'], $password, true)) ||
                (Validator::pattern($patterns['alpha'], $password, true) && Validator::pattern($patterns['special'], $password, true)) ||
                (Validator::pattern($patterns['numeric'], $password, true) && Validator::pattern($patterns['special'], $password, true))
            );
        }
        if ($isWeakPassword) {
            return __('안전도 낮음 예상하기 쉬운 비밀번호 입니다.');
        }

        throw new Exception(sprintf(__('사용불가! 영문대/소문자, 숫자, 특수문자 중 2가지 이상 조합하세요.')), 500);
    }

    /**
     * 회원가입 페이지 토큰 체크
     *
     * @static
     *
     * @param array $requestParams
     *
     * @throws AlertRedirectException
     * @throws Exception
     */
    public static function checkJoinToken(array $requestParams)
    {
        if (Token::check('token', $requestParams, false, 60 * 60, true) === false) {
            throw new AlertRedirectException(__('잘못된 경로로 접근했습니다.'), null, null, '../member/join_method.php', 'top');
        }
    }

    /**
     * 회원가입 시 가입 유형 선택 체크
     *
     * @static
     * @throws AlertRedirectException
     */
    public static function checkJoinMemberType()
    {
        $session = Session::get(Member::SESSION_JOIN_INFO);
        if ($session['memberFl'] == '') {
            throw new AlertRedirectException(__('가입방법을 선택하지 않으셨습니다.'), null, null, '../member/join_method.php', 'top');
        }
    }

    /**
     * 회원가입 시 필수 약관 동의 여부 체크
     *
     * @static
     *
     * @param $agreementInfoFl
     * @param $privateApprovalFl
     *
     * @throws AlertRedirectException
     */
    public static function checkJoinAgreement($agreementInfoFl, $privateApprovalFl)
    {
        if ($agreementInfoFl != 'y' || $privateApprovalFl != 'y') {
            throw new AlertRedirectException(__('약관의 필수항목에 동의해주세요.'), null, null, '../member/join_method.php', 'top');
        }
    }

    /**
     * 회원가입 시 세션의 필수 약관 동의 여부 체크
     *
     * @static
     * @throws AlertRedirectException
     */
    public static function checkJoinAgreementBySession()
    {
        $joinInfo = Session::get(Member::SESSION_JOIN_INFO);
        \Component\Member\MemberValidation::checkJoinAgreement($joinInfo['agreementInfoFl'], $joinInfo['privateApprovalFl']);
    }

    /**
     * 회원가입 진입 시 토큰, 회원유형, 필수 약관 동의 체크
     *
     * @static
     *
     * @param array $requestParams
     *
     * @throws AlertRedirectException
     */
    public static function checkJoin(array $requestParams)
    {
        \Component\Member\MemberValidation::checkJoinToken($requestParams);
        \Component\Member\MemberValidation::checkJoinMemberType();
        \Component\Member\MemberValidation::checkJoinAgreement($requestParams['agreementInfoFl'], $requestParams['privateApprovalFl']);
    }

    /**
     * 전화번호, 휴대폰번호, 팩스번호의 통신사, 지역번호 체크
     *
     * @static
     *
     * @param $requestParams
     *
     * @return mixed
     * @throws Exception
     */
    public static function checkJoinPhoneCode($requestParams)
    {
        // 전화번호 통신사, 지역번호 체크
        if (empty($requestParams['cellPhone']) === false && in_array(StringUtils::getPhoneCodeByNumber($requestParams['cellPhone']), gd_phone_area()) === false) {
            throw new Exception(__('입력하신 통신사 번호는 없는 번호입니다.'));
        }
        if (empty($requestParams['phone']) === false && in_array(StringUtils::getPhoneCodeByNumber($requestParams['phone']), gd_phone_area(false)) === false) {
            throw new Exception(__('입력하신 지역 번호는 없는 번호입니다.'));
        }
        if (empty($requestParams['fax']) === false && in_array(StringUtils::getPhoneCodeByNumber($requestParams['fax']), gd_phone_area(false)) === false) {
            throw new Exception(__('입력하신 팩스 지역 번호는 없는 번호입니다.'));
        }

        return $requestParams;
    }

    /**
     * 닉네임, 이메일 중복체크
     * 추천인 아이디 존재여부 체크
     *
     * @static
     *
     * @param            $arrData
     * @param null|array $require 가입항목 필수 여부
     *
     * @throws Exception
     */
    public static function validateMember($arrData, $require = null)
    {
        if (is_null($require)) {
            $require = MemberUtil::getRequireField();
        }

        // 닉네임 중복여부 체크
        if ($require['nickNm'] || !empty($arrData['nickNm'])) {
            if (MemberUtil::overlapNickNm($arrData['memId'], $arrData['nickNm'])) {
                Logger::error(__METHOD__ . ' / ' . sprintf(__('%s는 이미 사용중인 닉네임입니다'), $arrData['nickNm']));
                throw new Exception(sprintf(__('%s는 이미 사용중인 닉네임입니다'), $arrData['nickNm']));
            }
        }

        // 이메일 중복여부 체크
        if ($require['email'] || !empty($arrData['email'])) {
            if (MemberUtil::overlapEmail($arrData['memId'], $arrData['email'])) {
                Logger::error(__METHOD__ . ' / ' . sprintf('%s는 이미 사용중인 이메일입니다', $arrData['email']));
                throw new Exception(sprintf(__('%s는 이미 사용중인 이메일입니다'), $arrData['email']));
            }
        }

        // 추천아이디 실존인물인지 체크
        if ($require['recommId'] || !empty($arrData['recommId'])) {
            if (MemberUtil::checkRecommendId($arrData['recommId'], $arrData['memId']) === false) {
                throw new Exception(sprintf(__('등록된 회원 아이디가 아닙니다. 추천하실 아이디를 다시 확인해주세요.'), $arrData['recommId']));
            }
        }
    }

    /**
     * 회원 가입 수정 공통 적용될 검증항목 추가 함수
     *
     * @static
     *
     * @param Validator  $v
     * @param null|array $require 가입항목 필수 여부
     */
    public static function addValidateMember(&$v, $require = null)
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        if (is_null($require)) {
            $require = MemberUtil::getRequireField();
        }
        $v->add('memberFl', 'pattern', true, '{' . __('회원구분') . '}', '/^(business|personal|under14)$/'); // 회원구분(프론트에서 사용하던 구분패턴)
        $v->add('groupSno', 'number'); // 등급레벨
        $v->add('groupModDt', ''); // 등급수정일
        $v->add('groupValidDt', ''); // 등급유효일
        $v->add('nickNm', '', $require['nickNm'], '{' . __('닉네임') . '}'); // 닉네임
        $v->add('sexFl', 'pattern', $require['sexFl'], '{' . __('남자과 여자 중에서만 택일하세요.') . '}', '/^[.mw]+$/'); // 성별
        $v->add('birthDt', '', $require['birthDt'], '{' . __('생년월일') . '}'); // 생년월일
        $v->add('calendarFl', 'pattern', $require['calendarFl'], '{' . __('양력과 음력 중에서만 택일하세요.') . '}', '/^[.sl]+$/'); // 양력,음력
        $v->add('email', 'email', $require['email'], '{' . __('이메일') . '}'); // 이메일
        $v->add('zonecode', '', $require['address'], '{' . __('우편번호') . '}'); // 새우편번호
        $v->add('zipcode', '', '', '{' . __('구우편번호') . '}'); // 우편번호
        $v->add('address', '', $require['address'], '{' . __('주소') . '}'); // 주소
        $v->add('addressSub', '', $require['address'], '{' . __('상세주소') . '}'); // 상세주소
        $v->add('phoneCountryCode', 'phoneCountryCode', $require['phone'], '{' . __('전화번호 국가코드') . '}'); // 전화번호 국가코드
        $v->add('cellPhoneCountryCode', 'cellPhoneCountryCode', $require['cellPhone'], '{' . __('휴대폰 국가코드') . '}'); // 휴대폰 국가코드
        $v->add('fax', 'phone', $require['fax'], '{' . __('팩스번호') . '}'); // 팩스번호
        $v->add('maillingFl', '', $require['maillingFl'], '{' . __('메일수신동의') . '}'); // 메일수신동의
        $v->add('smsFl', '', $require['smsFl'], '{' . __('SMS수신동의') . '}'); // SMS수신동의
        $v->add('marriFl', 'yn', $require['marriFl'], '{' . __('결혼여부') . '}'); // 결혼여부
        $v->add('job', '', $require['job'], '{' . __('직업') . '}'); // 직업
        $v->add('interest', '', $require['interest'], '{' . __('관심분야') . '}'); // 관심분야
        $v->add('memo', '', $require['memo'], '{' . __('남기는말') . '}'); // 남기는말
        $v->add('recommId', 'recommid', $require['recommId'], '{' . __('추천인ID') . '}'); // 추천인ID
        $v->add('expirationFl', 'number', $require['expirationFl'], '{' . __('휴면회원 방지기간') . '}'); // 휴면회원 방지기간
        if ($session->has(SESSION_GLOBAL_MALL)) {
            $isAdminModifyGlobalSession = $session->get(SESSION_GLOBAL_MALL . '.isAdminModify', false);
            $logger->info(sprintf('add member validation %s', $isAdminModifyGlobalSession));
            if ($isAdminModifyGlobalSession) {
                // 관리자에서 처리하는 부분이 있는 경우에 생성된 세션이기때문에 제거처리함.
                $session->del(SESSION_GLOBAL_MALL);
            }
            $logger->info('member join validate add global name, phone, cellPhone');
            $v->add('memNm', 'memberNameGlobal', true, '{' . __('이름') . '}'); // 이름
            $v->add('phone', '', $require['phone'], '{' . __('전화번호') . '}'); // 전화번호
            $v->add('cellPhone', '', $require['cellPhone'], '{' . __('휴대폰') . '}'); // 휴대폰
        } else {
            $logger->info('member join validate add default name, phone, cellPhone');
            $v->add('memNm', 'memberNameGlobal', true, '{' . __('이름') . '}'); // 이름
            $v->add('phone', 'phone', $require['phone'], '{' . __('전화번호') . '}'); // 전화번호
            $v->add('cellPhone', 'phone', $require['cellPhone'], '{' . __('휴대폰') . '}'); // 휴대폰
        }
    }

    /**
     * 회원 등록/가입 검증 함수
     *
     * @param \Component\Member\MemberVo $vo
     *
     * @param null                       $require
     * @param bool                       $passValidation
     *
     * @return mixed
     * @throws Exception
     */
    public static function validateMemberByInsert(\Component\Member\MemberVo $vo, $require = null, $passValidation = false)
    {
        $logger = App::getInstance('logger');
        $session = App::getInstance('session');
        $logger->info('Start membership verification.');
        if (is_null($require)) {
            $require = MemberUtil::getRequireField();
        }

        $joinPolicy = ComponentUtils::getPolicy('member.join');
        StringUtils::strIsSet($joinPolicy['appUseFl'], 'n');
        StringUtils::strIsSet($joinPolicy['under14Fl'], 'n');
        StringUtils::strIsSet($joinPolicy['limitAge'], 19);
        $joinItemPolicy = ComponentUtils::getPolicy('member.joinitem');
        StringUtils::strIsSet($joinItemPolicy['passwordCombineFl'], '');
        StringUtils::strIsSet($joinItemPolicy['busiNo']['charlen'], 10); // 사업자번호 길이

        $isBusiness = $vo->getMemberFl() === 'business';    // 사업자 가입 체크
        $isApplyWait = $joinPolicy['appUseFl'] == 'y';    // 승인 여부 체크 n 일 경우 바로 가입
        $isCompanyWait = $isBusiness && $joinPolicy['appUseFl'] == 'company';
        $isRejectAge = $joinPolicy['under14Fl'] !== 'n';  // 가입 연령제한 n 일 경우 제한하지 않음
        $isIpinUse = ComponentUtils::useIpin();
        $isAuthCellPhone = ComponentUtils::useAuthCellphone();
        $logger->info(sprintf('Check whether you are approved for membership. memberFl[%s], appUseFl[%s], under14Fl[%s], useIpin[%s], useAuthCellphone[%s]', $vo->getMemberFl(), $joinPolicy['appUseFl'], $joinPolicy['under14Fl'], $isIpinUse, $isAuthCellPhone));

        if ((gd_is_admin() && $session->get('isFront') != 'y') || $session->get('simpleJoin') == 'y') {
            $vo->setAppFl($vo->getAppFl());
        } else {
            $vo->setAppFl('y');
            if ($isApplyWait || $isCompanyWait) {
                $logger->info('Wait for business member approval');
                $vo->setAppFl('n');
            }
            if ($isRejectAge && !($isIpinUse || $isAuthCellPhone)) {
                if (!Validator::required($vo->getBirthDt())) {
                    $logger->info('There is an age limit at the time of enrollment. Please enter your date of birth.');
                    throw new Exception(__('가입 시 연령제한이 있습니다. 생년월일을 입력해주세요.'));
                }

                $birthDt = $vo->getBirthDt(true)->format('Ymd');
                if($vo->getCalendarFl() == 'l') {
                    $birthDt = ComponentUtils::getSolarDate($vo->getBirthDt(true)->format('Y-m-d'));
                    $birthDt = str_replace('-', '', $birthDt);
                }
                $age = DateTimeUtils::age($birthDt, 1);
                $yearAge = DateTimeUtils::age($birthDt, ''); // 연 나이(현재연도 - 출생연도) 가져오기

                if ($joinPolicy['limitAge'] > $age && $yearAge != 19) {
                    if ($joinPolicy['under14Fl'] === 'no') {
                        $logger->info('It is an age that can not be registered.');
                        throw new Exception(__('회원가입이 불가능한 연령입니다.'));
                    }
                    $vo->setAppFl('n');
                }
            }
        }

        if ($passValidation == false) {
            \Component\Member\MemberValidation::validateMemberPassword($vo->getMemPw());
        }

        $length = MemberUtil::getMinMax();

        $v = new Validator();
        $v->init();
        $v->add('memId', 'userid', true, '{' . __('아이디') . '}'); // 아이디
        $v->add('memId', 'minlen', true, '{' . __('아이디 최소길이') . '}', $length['memId']['minlen']); // 아이디 최소길이
        $v->add('memId', 'maxlen', true, '{' . __('아이디 최대길이') . '}', $length['memId']['maxlen']); // 아이디 최대길이
        $v->add('appFl', 'yn', false, '{' . __('가입승인') . '}'); // 가입승인
        $v->add('entryBenefitOfferFl', 'yn', false, '{' . __('가입혜택지급') . '}'); // 가입혜택지급
        $v->add('entryDt', '', false, '{' . __('회원가입일') . '}'); // 회원가입일
        if ($vo->getmarriFl() == 'y') {
            $v->add('marriDate', 'date', $require['marriDate'], '{' . __('결혼기념일') . '}'); // 결혼기념일
        }
        \Component\Member\MemberValidation::addValidateMember($v, $require);
        \Component\Member\MemberValidation::addValidateMemberExtra($v, $require);
        if ($isBusiness) {
            \Component\Member\MemberValidation::addValidateMemberBusiness($v, $require);
        }
        if ($joinItemPolicy['pronounceName']['use'] == 'y') {
            $v->add('pronounceName', '', $joinItemPolicy['pronounceName']['require'], '{' . __('이름(발음)') . '}');
        }

        if ($v->act($vo->toArray(), true) === false && $session->get('simpleJoin') != 'y') {
            $logger->info(__METHOD__ . ', has session_user_profile=>' . $session->has(GodoPaycoServerApi::SESSION_USER_PROFILE), $v->errors);
            throw new Exception(implode("\n", $v->errors), 500);
        }

        // 거부 아이디 필터링
        if (StringUtils::findInDivision(strtoupper($vo->getMemId()), strtoupper($joinPolicy['unableid']))) {
            throw new Exception(sprintf(__('%s는 사용이 제한된 아이디입니다'), $vo->getMemId()));
        }

        // 아이디 중복여부 체크
        if (MemberUtil::overlapMemId($vo->getMemId())) {
            throw new Exception(sprintf(__('%s는 이미 등록된 아이디입니다'), $vo->getMemId()));
        }

        // 닉네임 중복여부 체크
        if ($vo->isset($vo->getNickNm())) {
            if (MemberUtil::overlapNickNm($vo->getMemId(), $vo->getNickNm())) {
                throw new Exception(sprintf(__('%s는 이미 사용중인 닉네임입니다'), $vo->getNickNm()));
            }
        }

        // 이메일 중복여부 체크
        if ($vo->isset($vo->getEmail())) {
            if (MemberUtil::overlapEmail($vo->getMemId(), $vo->getEmail())) {
                throw new Exception(sprintf(__('%s는 이미 사용중인 이메일입니다'), $vo->getEmail()));
            }
        }

        // 추천아이디 실존인물인지 체크
        if ($vo->isset($vo->getRecommId())) {
            if (MemberUtil::checkRecommendId($vo->getRecommId(), $vo->getMemId()) === false) {
                throw new Exception(sprintf(__('등록된 회원 아이디가 아닙니다. 추천하실 아이디를 다시 확인해주세요.'), $vo->getRecommId()));
            }
        }

        // 사업자번호 중복여부 체크
        if ($isBusiness && $vo->isset($vo->getBusiNo())) {
            if (strlen(gd_remove_special_char($vo->getBusiNo())) != $joinItemPolicy['busiNo']['charlen']) {
                throw new Exception(sprintf(__('사업자번호는 %s자로 입력해야 합니다.'), $joinItemPolicy['busiNo']['charlen']));
            }
            if ($joinItemPolicy['busiNo']['overlapBusiNoFl'] == 'y' && MemberUtil::overlapBusiNo($vo->getMemId(), $vo->getBusiNo())) {
                throw new Exception(sprintf(__('이미 등록된 사업자번호입니다.'), $vo->getBusiNo()));
            }
        }

        /** @var \Bundle\Component\Member\HackOut\HackOutService $hackOutService */
        $hackOutService = App::load('\\Component\\Member\\HackOut\\HackOutService');
        $hackOutService->checkRejoinByMemberId($vo->getMemId());
    }

    /**
     * 회원가입 시 연령제한이 있는 경우 아이핀, 휴대폰 본인인증을 사용하는지 여부
     * 또는 생년월일 항목을 필수로 받게끔 설정되었는지 확인하는 함수
     * 연령제한이 있으면서 본인인증 또는 생년월일을 필수로 설정한 경우 true를 반환한다.
     *
     * @static
     *
     * @param null $under14Fl
     * @param null $birthdayUse
     * @param null $birthdayRequire
     * @param null $isAuthCellPhone
     * @param null $isIpin
     *
     * @return bool
     */
    public static function checkUnder14Policy($under14Fl = null, $birthdayUse = null, $birthdayRequire = null, $isAuthCellPhone = null, $isIpin = null, $useFlKcp = null)
    {
        \Logger::info(__METHOD__, func_get_args());
        if (is_null($under14Fl)) {
            $under14Fl = Globals::get('gSite.member.join.under14Fl');
        }

        if (is_null($birthdayUse)) {
            $birthdayUse = Globals::get('gSite.member.joinitem.birthDt.use');
        }

        if (is_null($birthdayRequire)) {
            $birthdayRequire = Globals::get('gSite.member.joinitem.birthDt.require');
        }

        if (is_null($isAuthCellPhone)) {
            $isAuthCellPhone = ComponentUtils::useAuthCellphone();
        }

        if (is_null($isIpin)) {
            $isIpin = ComponentUtils::useIpin();
        }

        $useAgeLimit = $under14Fl != 'n';

        if (!$useAgeLimit) {
            return true;
        }
        $useRequireBirthday = ($birthdayUse == 'y' && $birthdayRequire == 'y');
        $useAuth = ($isAuthCellPhone || $useFlKcp) || $isIpin;

        return $useAgeLimit && ($useRequireBirthday || $useAuth);
    }

    public function isUnableId($memberId)
    {
        $policy = gd_policy('member.join');

        return StringUtils::findInDivision(strtoupper($memberId), strtoupper($policy['unableid']));
    }

    /**
     * 간편 로그인으로 회원가입 진행 시 본인인증 제외 설정 여부
     *
     * @param null $memberAuthFl
     * @param null $under14Fl
     *
     * @return bool
     */
    public static function checkSNSMemberAuth() {
        $snsMemberAuthFl = Globals::get('gSite.member.join.snsMemberAuthFl');
        $snsMemberAuthFl = gd_isset($snsMemberAuthFl, 'y');
        $requireFl = self::checkRequireSNSMemberAuth($snsMemberAuthFl);
        if ($snsMemberAuthFl === 'y' || $requireFl === true) {
            return 'y';
        } else {
            return 'n';
        }
    }

    /**
     * 간편 로그인으로 회원가입 진행 시
     * 본인인증 제외 설정을 필수로 사용해야되는지에 대한 여부.
     *
     * @param null $snsMemberAuthFl
     * @param null $under14Fl
     *
     * @return bool
     */
    public static function checkRequireSNSMemberAuth($snsMemberAuthFl = null, $under14Fl = null, $birthdayUse = null, $birthdayRequire = null)
    {
        if (is_null($snsMemberAuthFl) === true) {
            $snsMemberAuthFl = Globals::get('gSite.member.join.snsMemberAuthFl');
            $snsMemberAuthFl = gd_isset($snsMemberAuthFl, 'y');
        }
        if (is_null($under14Fl) === true) {
            $under14Fl = Globals::get('gSite.member.join.under14Fl');
            $under14Fl = gd_isset($under14Fl, 'n');
        }
        if (is_null($birthdayUse) === true) {
            $birthdayUse = Globals::get('gSite.member.joinitem.birthDt.use');
        }
        if (is_null($birthdayRequire) === true) {
            $birthdayRequire = Globals::get('gSite.member.joinitem.birthDt.require');
        }
        $birthdayFl = 'n';
        if ($birthdayUse === 'y' && $birthdayRequire === 'y') {
            $birthdayFl = 'y';
        }
        return ($snsMemberAuthFl === 'n' && $under14Fl !== 'n' && $birthdayFl === 'n');
    }

    /**
     * checkSNSmemberJoin
     * 간편 로그인을 통한 회원 가입 여부
     *
     * @return bool
     */
    public static function checkSNSmemberJoin() {
        $naverLoginPolicy = gd_policy('member.naverLogin');
        $kakaoLoginPolicy = gd_policy('member.kakaoLogin');
        $hasPaycoUserProfile = \Session::has(\Component\Godo\GodoPaycoServerApi::SESSION_USER_PROFILE);
        $hasNaverUserProfile = (\Session::has(\Component\Godo\GodoNaverServerApi::SESSION_USER_PROFILE) && $naverLoginPolicy['useFl'] === 'y');
        $hasThirdPartyProfile = \Session::has(\Component\Facebook\Facebook::SESSION_USER_PROFILE);
        $hasKakaoUserProfile = (\Session::has(\Component\Godo\GodoKakaoServerApi::SESSION_USER_PROFILE) && $kakaoLoginPolicy['useFl'] === 'y');
        $hasWonderUserProfile = \Session::has(\Component\Godo\GodoWonderServerApi::SESSION_USER_PROFILE);
        return ($hasPaycoUserProfile || $hasNaverUserProfile || $hasThirdPartyProfile || $hasKakaoUserProfile || $hasWonderUserProfile);
    }

    /**
     * checkGuestAuthService
     * 비회원 주문제한 > 주문자 본인인증 서비스 사용 여부
     *
     * @param null $isAuthCellPhone , $useFlKcp, $isIpin, $isGuestUnder14Fl
     *
     * @return bool
     */
    public static function checkGuestAuthService($isAuthCellPhone = null,  $useFlKcp = null, $isIpin = null, $isGuestUnder14Fl = null) {
        if (is_null($isAuthCellPhone)) {
            $isAuthCellPhone = ComponentUtils::useAuthCellphone();
        }

        if (is_null($isIpin)) {
            $isIpin = ComponentUtils::useIpin();
        }
        $memberAccessPolicy = gd_policy('member.access');
        if ($memberAccessPolicy['guestUnder14Fl'] == 's'){
            $useAuth = ($isAuthCellPhone || $useFlKcp) || $isIpin;
        }
        else if ($isGuestUnder14Fl === true) {
            $useAuth = $isGuestUnder14Fl && ($isAuthCellPhone || $isIpin);
        }
        else {
            return true;
        }
        return $useAuth;
    }
}
