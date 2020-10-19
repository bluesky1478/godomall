<?php
/**
 * This is commercial software, only urs who have purchased a valid license
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
use Component\Mail\MailMimeAuto;
use Component\Member\Util\MemberUtil;
use Component\Validator\Validator;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Encryptor;
use Exception;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Security\Digester;
use Logger;
use Password;
use Session;
use Framework\Utility\GodoUtils;
use Framework\Object\SimpleStorage;

/**
 * Class 마이페이지 내정보수정
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class MyPage extends \Component\AbstractComponent
{
    const SESSION_MY_PAGE_PASSWORD = 'myPagePassword';
    const SESSION_MY_PAGE_MEMBER_NO = 'myPageMemberNo';
    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
    private $mailMimeAuto;
    /** @var  \Bundle\Component\Member\MemberDAO */
    private $memberDao;
    /** @var \Bundle\Component\Member\Member $member */
    private $member;

    function __construct(MailMimeAuto $mailMimeAuto = null, Member $member = null)
    {
        parent::__construct();
        if ($mailMimeAuto === null) {
            $this->mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
        }
        if ($member === null) {
            $this->member = App::load('\\Component\\Member\\Member');
        }
        $this->memberDao = new MemberDAO();
    }

    public function myInformation()
    {
        $memberData = $this->memberDao->selectMyPage(Session::get('member.memNo'));
        Session::del(Member::SESSION_MODIFY_MEMBER_INFO);
        Session::set(Member::SESSION_MODIFY_MEMBER_INFO, $memberData);

        $memberData['memPw'] = '';
        $memberData = MemberUtil::explodeDataByDelimiter($memberData, '|');
        $memberData['busiNo'] = str_replace('-', '', $memberData['busiNo']);
        $memberData['cellPhone'] = str_replace('-', '', $memberData['cellPhone']);
        $memberData['phone'] = str_replace('-', '', $memberData['phone']);
        $memberData['fax'] = str_replace('-', '', $memberData['fax']);
        $memberData['birthDt'] = str_replace('-', '', $memberData['birthDt']);
        if ($memberData['birthDt'] == '00000000') {
            $memberData['birthDt'] = '';
        }
        $memberData['marriDate'] = str_replace('-', '', $memberData['marriDate']);
        if ($memberData['marriDate'] == '00000000') {
            $memberData['marriDate'] = '';
        }
        if (StringUtils::isJson($memberData['privateApprovalOptionFl'])) {
            $memberData['privateApprovalOptionFl'] = json_decode($memberData['privateApprovalOptionFl'], true);
            foreach ($memberData['privateApprovalOptionFl'] as $key => $val){
                if($val == 'n') unset($memberData['privateApprovalOptionFl'][$key]);
            }
        }
        if (StringUtils::isJson($memberData['privateOfferFl'])) {
            $memberData['privateOfferFl'] = json_decode($memberData['privateOfferFl'], true);
            foreach ($memberData['privateOfferFl'] as $key => $val){
                if($val == 'n') unset($memberData['privateOfferFl'][$key]);
            }
        }
        if (StringUtils::isJson($memberData['privateConsignFl'])) {
            $memberData['privateConsignFl'] = json_decode($memberData['privateConsignFl'], true);
            foreach ($memberData['privateConsignFl'] as $key => $val){
                if($val == 'n') unset($memberData['privateConsignFl'][$key]);
            }
        }

        return $memberData;
    }

    /**
     * 프론트 마이페이지 회원정보 수정
     *
     * @param array $requestParams
     * @param array $memberSession
     *
     * @throws Exception
     */
    public function modify(array $requestParams, array $memberSession)
    {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__, $requestParams);
        $logger->debug(__METHOD__, $memberSession);
        // 토글형 체크박스는 체크하지 않을 경우 값이 없으므로 n으로 설정함
        gd_isset($requestParams['maillingFl'], 'n');
        gd_isset($requestParams['smsFl'], 'n');

        if ($memberSession['mallSno'] == DEFAULT_MALL_NUMBER) {
            \Component\Member\MemberValidation::checkJoinPhoneCode($requestParams);
        }

        //이용약관 체크값 설정
        $agreementData = $this->setAgreementData($requestParams);

        $requestParams = MemberUtil::combineMemberData($requestParams);
        $requestParams['privateApprovalOptionFl'] = json_encode($agreementData['privateApprovalOptionFl'], JSON_UNESCAPED_SLASHES);
        $requestParams['privateConsignFl'] = json_encode($agreementData['privateConsignFl'], JSON_UNESCAPED_SLASHES);
        $requestParams['privateOfferFl'] = json_encode($agreementData['privateOfferFl'], JSON_UNESCAPED_SLASHES);

        $passValidation = isset($memberSession['memPw']) == false && $memberSession['snsJoinFl'] == 'y';
        $requestParams = $this->validateMemberByModification($requestParams, $passValidation);
        $addExclude = [
            'memPw',
            'groupSno',
            'groupModDt',
            'groupValidDt',
        ];

        // 평생회원 이벤트
        if ($requestParams['expirationFl'] === '999') {
            $requestParams['lifeMemberConversionDt'] = date('Y-m-d H:i:s');
        }

        // 비밀번호 수정일 경우 검증
        if (gd_isset($requestParams['memPw'], '') != '') {
            $logger->info('memPw is not empty. verify current password');
            if (isset($memberSession['memNo']) === false) {
                $logger->info('modify session is empty');
                throw new Exception(__('로그인 정보가 없습니다.'));
            }

            if (Validator::number($memberSession['memNo'], null, null, true) === false) {
                $logger->info('invalid member number');
                throw new Exception(__('유효하지 않은 회원번호 입니다.'));
            }

            $memberPassword = $this->memberDao->selectPassword($memberSession['memNo']);

            if ($passValidation == false) {
                // @todo : 로그인 시점에 hash 함수로 변경하므로 legacy 체크 필요 없으나, 그래도 혹시 모르니 확인해 볼것.
                $logger->debug(
                    'check old member password with new member password', [
                        $requestParams['oldMemPw'],
                        $memberPassword['memPw'],
                    ]
                );
                $verifyPassword = App::getInstance('password')->verify($requestParams['oldMemPw'], $memberPassword['memPw']);
                if ($verifyPassword === false) {
                    if (Digester::isValid($memberPassword['memPw'], $requestParams['oldMemPw']) == false) {
                        $logger->info('not equal old password');
                        throw new Exception(__('입력하신 현재 비밀번호가 틀렸습니다.'));
                    }
                }

                if ($requestParams['memPw'] === $requestParams['oldMemPw']) {
                    $logger->info('equal old password');
                    throw new Exception(__('현재 비밀번호와 동일한 비밀번호입니다.'));
                }
            }

            if ($requestParams['memPw'] !== gd_isset($requestParams['memPwRe'], '')) {
                $logger->info('not equal new password and new password repeat');
                throw new Exception(__('비밀번호가 다릅니다. 다시 확인 바랍니다.'));
            }

            if(GodoUtils::sha256Fl()) {
                $requestParams['memPw'] = Digester::digest($requestParams['memPw']);
            } else {
                $requestParams['memPw'] = App::getInstance('password')->hash($requestParams['memPw']);
            }
            $passwordDt = new \DateTime();
            $requestParams['changePasswordDt'] = $passwordDt->format('Y-m-d H:i:s');
            unset($addExclude[array_search('memPw', $addExclude)]);
            Session::set(self::SESSION_MY_PAGE_PASSWORD, true);
            $logger->info('password verify complete');
        } else {
            $logger->info('member password is not change');
        }

        $this->memberDao->updateMember($requestParams, [], $addExclude);
        $memberWithGroup = $this->memberDao->selectMemberWithGroup($requestParams['memNo'], 'memNo');
        $this->_refreshSession($memberWithGroup);

        // 추천인 등록시 혜택 지급
        if (empty($memberSession['recommId']) && $memberSession['recommFl'] != 'y' && empty($requestParams['recommId']) == false) {
            $benefit = \App::load('Component\\Member\\Benefit');
            $benefit->benefitMoidfyRecommender($requestParams);
            unset($benefit);
        }
    }

    public function sendEmailByPasswordChange(array $requestParams, array $memberData)
    {
        if (gd_isset($requestParams['memPw'], '') !== '') {
            $mailData = [
                'memNm'    => $memberData['memNm'],
                'memId'    => $memberData['memId'],
                'changeDt' => DateTimeUtils::dateFormat('Y-m-d', 'now'),
                'email'    => $memberData['email'],
            ];
            $mailMimeAuto = new MailMimeAuto();
            $mailMimeAuto->init(MailMimeAuto::CHANGE_PASSWORD, $mailData)->autoSend();
        }
    }

    public function sendSmsByAgreementFlag(array $memberSession, array $memberData)
    {
        if ($memberSession['maillingFl'] !== $memberData['maillingFl'] || $memberSession['smsFl'] !== $memberData['smsFl']) {
            $mailData = [
                'email'      => $memberData['email'],
                'memNm'      => $memberData['memNm'],
                'smsFl'      => $memberData['smsFl'],
                'maillingFl' => $memberData['maillingFl'],
                'modDt'      => DateTimeUtils::dateFormat('Y-m-d', 'now'),
            ];
            $this->mailMimeAuto->init(MailMimeAuto::AGREEMENT, $mailData)->autoSend();
        }
    }

    /**
     * 마이페이지 내정보 수정 입력 값 검증
     *
     * @param      $requestParams
     * @param bool $passValidation
     *
     * @return mixed
     * @throws Exception
     */
    private function _validateMemberByModification($requestParams, $passValidation = false)
    {
        if (Validator::required($requestParams['memNo']) === false) {
            throw new Exception(__('회원정보가 없습니다.'));
        }
        $require = MemberUtil::getRequireField();
        $length = MemberUtil::getMinMax();
        $joinItemPolicy = \Component\Policy\JoinItemPolicy::getInstance()->getPolicy();
        StringUtils::strIsSet($joinItemPolicy['passwordCombineFl'], '');
        StringUtils::strIsSet($joinItemPolicy['busiNo']['charlen'], 10); // 사업자번호 길이

        if (isset($requestParams['birthYear']) === true && isset($requestParams['birthMonth']) === true && isset($requestParams['birthDay']) === true) {
            $requestParams['birthDt'] = $requestParams['birthYear'].'-'.$requestParams['birthMonth'].'-'.$requestParams['birthDay'];
        }
        // 데이터 조합
        MemberUtil::combineMemberData($requestParams);

        $v = new Validator();
        $v->init();
        $v->add('memId', 'userid', true, '{' . __('아이디') . '}', true, false);
        $v->add('memNo', 'number', true);
        $v->add('privateApprovalOptionFl', '', false, '{' . __('개인정보 수집 및 이용') . '}');
        $v->add('privateOfferFl', '', false, '{' . __('개인정보동의 제3자 제공') . '}');
        $v->add('privateConsignFl', '', false, '{' . __('개인정보동의 취급업무 위탁') . '}');
        if (StringUtils::strIsSet($requestParams['memPw'], '') !== '') {
            \Component\Member\MemberValidation::validateMemberPassword($requestParams['memPw']);
            $v->add('memPw', '', true, '{' . __('비밀번호') . '}');
            $v->add('memPwRe', '', true, '{' . __('비밀번호 확인') . '}');
            if (StringUtils::strIsSet($requestParams['oldMemPw'], '') != '') {
                $v->add('oldMemPw', '', true, '{' . __('현재 비밀번호') . '}');
            }
        }
        if (isset($requestParams['marriFl']) === true && $requestParams['marriFl'] == 'y') {
            if (isset($requestParams['marriYear']) === true && isset($requestParams['marriMonth']) === true && isset($requestParams['marriDay']) === true) {
                $requestParams['marriDate'] = $requestParams['marriYear'].'-'.$requestParams['marriMonth'].'-'.$requestParams['marriDay'];
            }
            $v->add('marriDate', '', $require['marriDate'], '{' . __('결혼기념일') . '}'); // 결혼기념일
        } elseif (isset($requestParams['marriFl']) === true && $requestParams['marriFl'] == 'n') {
            $v->add('marriDate', '', false, '{' . __('결혼기념일') . '}'); // 결혼기념일
            $requestParams['marriDate'] = '';
        }
        \Component\Member\MemberValidation::addValidateMember($v);
        \Component\Member\MemberValidation::addValidateMemberExtra($v, $require);
        if (isset($requestParams['memberFl']) === true && $requestParams['memberFl'] == 'business') {
            \Component\Member\MemberValidation::addValidateMemberBusiness($v, $require);
        }
        if ($joinItemPolicy['pronounceName']['use'] == 'y') {
            $v->add('pronounceName', '', $joinItemPolicy['pronounceName']['require'], '{' . __('이름(발음)') . '}');
        }
        if ($requestParams['dupeinfo'] != '') {
            $v->add('dupeinfo', '');
        }
        if ($requestParams['rncheck'] != '') {
            $v->add('rncheck', '');
        }

        if ($v->act($requestParams, true) === false) {
            if (key_exists('memPw', $v->errors) && key_exists('memPwRe', $v->errors)) {
                unset($v->errors['memPwRe']);
            }
            throw new Exception(implode("\n", $v->errors), 500);
        }

        // 닉네임 중복여부 체크
        if ($require['nickNm'] || !empty($requestParams['nickNm'])) {
            if (MemberUtil::overlapNickNm($requestParams['memId'], $requestParams['nickNm'])) {
                Logger::error(__METHOD__ . ' / ' . sprintf('%s는 이미 사용중인 닉네임입니다', $requestParams['nickNm']));
                throw new Exception(sprintf(__('%s는 이미 사용중인 닉네임입니다'), $requestParams['nickNm']));
            }
        }

        // 이메일 중복여부 체크
        if ($require['email'] || !empty($requestParams['email'])) {
            if (MemberUtil::overlapEmail($requestParams['memId'], $requestParams['email'])) {
                Logger::error(__METHOD__ . ' / ' . sprintf('%s는 이미 사용중인 이메일입니다', $requestParams['email']));
                throw new Exception(sprintf(__('%s는 이미 사용중인 이메일입니다'), $requestParams['email']));
            }
        }

        // 추천아이디 실존인물인지 체크
        if ($require['recommId'] || !empty($requestParams['recommId'])) {
            if (MemberUtil::checkRecommendId($requestParams['recommId'], $requestParams['memId']) === false) {
                throw new Exception(sprintf(__('등록된 회원 아이디가 아닙니다. 추천하실 아이디를 다시 확인해주세요.'), $requestParams['recommId']));
            }
        }

        // 사업자번호 중복여부 체크
        if ($requestParams['memberFl'] == 'business' && ($require['busiNo'] || !empty($requestParams['busiNo']))) {
            $newBusiNo = gd_remove_special_char($requestParams['busiNo']);
            $memberData = \Component\Member\MyPage::myInformation();
            $oldBusiNo = $memberData['busiNo'];

            if (strlen($newBusiNo) != $joinItemPolicy['busiNo']['charlen']) {
                throw new Exception(sprintf(__('사업자번호는 %s자로 입력해야 합니다.'), $joinItemPolicy['busiNo']['charlen']));
            }

            if ($newBusiNo != $oldBusiNo && $joinItemPolicy['busiNo']['overlapBusiNoFl'] == 'y' && MemberUtil::overlapBusiNo($requestParams['memId'], $newBusiNo)) {
                throw new Exception(sprintf(__('%s - 이미 등록된 사업자번호입니다.'), $requestParams['busiNo']));
            }
        }

        // 휴대폰인증시 저장된 세션정보와 실제 넘어온 파라미터 검증 (생년월일) - XSS 취약점 개선요청
        $authCellPhonePolicy = new SimpleStorage(gd_get_auth_cellphone_info()); // 휴대폰 인증 사용여부
        $joinItem = gd_policy('member.joinitem'); // 회원가입 필드 사용여부 체크

        if ($authCellPhonePolicy->get('useFl', 'n') === 'y' && Session::has(Member::SESSION_DREAM_SECURITY)) {
            $dreamSession = Session::get(Member::SESSION_DREAM_SECURITY);
            if ($joinItem['birthDt']['use'] === 'y' && $dreamSession['ibirth'] != str_replace('-','', $requestParams['birthDt'])) {
                throw new Exception(__("휴대폰 인증시 입력한 생년월일과 동일하지 않습니다."));
            }

            if ($joinItem['cellPhone']['use'] === 'y' && $dreamSession['phone'] != str_replace('-','', $requestParams['cellPhone'])) {
                throw new Exception(__("휴대폰 인증시 입력한 번호와 동일하지 않습니다."));
            }

            if ($dreamSession['name'] != str_replace('-','', $requestParams['memNm'])) {
                throw new Exception(__("휴대폰 인증시 입력한 이름과 동일하지 않습니다."));
            }
        }

        return $requestParams;
    }

    /**
     * validateMemberByModification wrapping 함수
     *
     * @param      $requestParams
     * @param bool $passValidation
     *
     * @return mixed
     */
    protected function validateMemberByModification($requestParams, $passValidation = false)
    {
        return $this->_validateMemberByModification($requestParams, $passValidation);
    }

    /**
     * 로그인 세션 데이터 갱신
     *
     * @param object $memberData 회원정보
     */
    private function _refreshSession($memberData)
    {
        $memInfo = MemberUtil::encryptMember($memberData);
        Session::set(Member::SESSION_MEMBER_LOGIN, $memInfo);
    }

    /**
     * 세션의 정보를 이용하여 회원정보를 호출하여 입력된 패스워드와 동일한지 체크하는 함수
     *
     * @param $input
     *
     * @return mixed
     * @throws Exception
     *
     * @deprecated 2016-12-27 yjwee 사용하지 않는 함수
     */
    public function verifyPasswordBySession($input)
    {
        $memberData = $this->memberDao->selectPassword(Session::get('member.memNo'));

        return MemberUtil::verifyPassword($input, $memberData['memPw']);
    }

    /**
     * 비밀번호 패턴 검증 검증
     *
     * @param $password
     *
     * @throws Exception
     */
    public function validatePassword($password)
    {
        if (Validator::required($password) === false) {
            throw new \Exception(__('비밀번호를 입력해주세요.'));
        } else {
            $joinItemPolicy = \Component\Policy\JoinItemPolicy::getInstance()->getPolicy(\Component\Mall\Mall::getSession('sno'));
            $minLength = $joinItemPolicy['memPw']['minlen'];
            $maxLength = $joinItemPolicy['memPw']['maxlen'];
            if ($joinItemPolicy['passwordCombineFl'] == 'default') {
                if (Validator::simplePassword($password, true, $minLength, $maxLength) === false) {
                    throw new \Exception(__('비밀번호 형식이 틀렸습니다.'));
                }
            } else if ($joinItemPolicy['passwordCombineFl'] == 'engNumEtc') {
                if (Validator::difficultPassword($password, true, $minLength, $maxLength) === false) {
                    throw new \Exception(__('비밀번호 형식이 틀렸습니다.'));
                }
            } else {
                if (Validator::password($password, true, $minLength, $maxLength) === false) {
                    throw new \Exception(__('비밀번호 형식이 틀렸습니다.'));
                }
            }
        }
    }

    /**
     * 내정보 수정 비밀번호 변경
     *
     * @param $oldPassword
     * @param $newPassword
     *
     * @throws Exception
     */
    public function changePassword($oldPassword, $newPassword)
    {
        $session = \App::getInstance('session');
        $password = \App::getInstance('password');
        $encryptor = \App::getInstance('encryptor');
        $this->validatePassword($newPassword);

        $memberSession = $session->get(Member::SESSION_MEMBER_LOGIN);

        if (Digester::isValid($encryptor->decrypt($memberSession['memPw']), $oldPassword) == false) {
            if (!$password->verify($oldPassword, $encryptor->decrypt($memberSession['memPw']))) {
                throw new Exception(__($encryptor->decrypt($memberSession['memPw'])));
            }
        }
        if ($oldPassword == $newPassword) {
            throw new Exception(__('현재 비밀번호와 동일한 비밀번호로 변경할 수 없습니다.'));
        }

        if(GodoUtils::sha256Fl()) {
            $hashPassword = Digester::digest($newPassword);
        } else {
            $hashPassword = $password->hash($newPassword);
        }
        $passwordDt = new \DateTime();
        $passwordDtFormat = $passwordDt->format('Y-m-d H:i:s');
        $arrData = [
            'memPw'            => $hashPassword,
            'changePasswordDt' => $passwordDtFormat,
            'guidePasswordDt'  => $passwordDtFormat,
        ];

        $includes = [
            'memPw',
            'changePasswordDt',
            'guidePasswordDt',
        ];
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $arrData, 'update', $includes);

        $this->db->bind_param_push($arrBind['bind'], 's', $memberSession['memId']);
        $result = $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memId = ?', $arrBind['bind']);
        if ($result > 0) {
            MemberUtil::logout();
        }
    }

    public function changePasswordLater()
    {
        $memberSession = Session::get(Member::SESSION_MEMBER_LOGIN);
        $passwordDt = new \DateTime();
        $passwordDtFormat = $passwordDt->format('Y-m-d H:i:s');
        $arrData = [
            'guidePasswordDt' => $passwordDtFormat,
        ];

        $includes = [
            'guidePasswordDt',
        ];
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $arrData, 'update', $includes);

        $this->db->bind_param_push($arrBind['bind'], 's', $memberSession['memId']);
        $result = $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memId = ?', $arrBind['bind']);

        if ($result > 0) {
            Session::set(Member::SESSION_MEMBER_LOGIN . '.guidePasswordDt', $passwordDtFormat);
        }
    }

    /**
     * 이용약관 체크값 설정
     */
    public function setAgreementData(array $requestParams)
    {
        $inform = new BuyerInform();

        $privateApprovalOptionData = $inform->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION);
        foreach ($privateApprovalOptionData as $key) {
            $arrPrivateApprovalOption[$key['sno']] = 'n';
        }
        if($requestParams['privateApprovalOptionFl']){
            foreach ($requestParams['privateApprovalOptionFl'] as $key => $val) {
                if($val == 'on') $requestParams['privateApprovalOptionFl'][$key] = 'y';
                unset($arrPrivateApprovalOption[$key]);
            }
            $requestParams['privateApprovalOptionFl'] = $requestParams['privateApprovalOptionFl'] + $arrPrivateApprovalOption;
        } else {
            $requestParams['privateApprovalOptionFl'] = $arrPrivateApprovalOption;
        }


        $privateConsignData = $inform->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN);
        foreach ($privateConsignData as $key) {
            $arrPrivateConsign[$key['sno']] = 'n';
        }
        if($requestParams['privateConsignFl']){
            foreach ($requestParams['privateConsignFl'] as $key => $val) {
                if($val == 'on') $requestParams['privateConsignFl'][$key] = 'y';
                unset($arrPrivateConsign[$key]);
            }
            $requestParams['privateConsignFl'] = $requestParams['privateConsignFl'] + $arrPrivateConsign;
        } else {
            $requestParams['privateConsignFl'] = $arrPrivateConsign;
        }

        $privateOfferData = $inform->getInformDataArray(BuyerInformCode::PRIVATE_OFFER);
        foreach ($privateOfferData as $key) {
            $arrprivateOffer[$key['sno']] = 'n';
        }
        if($requestParams['privateOfferFl']) {
            foreach ($requestParams['privateOfferFl'] as $key => $val) {
                if($val == 'on') $requestParams['privateOfferFl'][$key] = 'y';
                unset($arrprivateOffer[$key]);
            }
            $requestParams['privateOfferFl']= $requestParams['privateOfferFl'] + $arrprivateOffer;
        } else {
            $requestParams['privateOfferFl'] = $arrprivateOffer;
        }
        return $requestParams;
    }
}
