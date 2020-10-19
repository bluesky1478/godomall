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


use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Member\Util\MemberUtil;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Request;

/**
 * Class MemberSnsService
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class MemberSnsService
{
    /** @var string third party login app type */
    protected $thirdPartyAppType;
    private $dao;

    public function __construct(MemberSnsDAO $snsDAO = null)
    {
        if ($snsDAO === null) {
            $snsDAO = \App::load('Component\\Member\\MemberSnsDAO');
        }
        $this->dao = $snsDAO;
    }

    public function saveToken($uuid, $accessToken, $refreshToken)
    {
        if (Validator::required($uuid) == false) {
            throw new Exception(__('조회 정보가 없습니다.'));
        }
        $params = [
            'uuid'         => $uuid,
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
            'appId'        => $this->getAppId(),
        ];
        $this->dao->updateToken($params);
    }

    public function getMemberSns($memberNo)
    {
        return $this->dao->selectMemberSns($memberNo, $this->getAppId());
    }

    public function getMemberSnsByUUID($uuid)
    {
        if (Validator::required($uuid) == false) {
            throw new Exception(__('조회 정보가 없습니다.'));
        }

        return $this->dao->selectMemberByUUID($uuid, $this->getAppId());
    }

    public function joinBySns($memberNo, $uuid, $accessToken, $snsTypeFl = '')
    {
        if (!Validator::required($memberNo) || $memberNo < 1) {
            throw new Exception('회원가입 중 문제가 발생하였습니다.');
        }
        if (!Validator::required($uuid)) {
            throw new Exception(__('조회 정보가 없습니다.'));
        }
        if (!Validator::required($memberNo)) {
            throw new Exception(__('회원 정보가 없습니다.'));
        }
        if (!Validator::required($snsTypeFl)) {
            throw new Exception(__('SNS 타입이 없습니다.'));
        }
        if (self::hasSnsMember($uuid)) {
            throw new Exception(__('이미 가입한 회원입니다.'));
        }

        $insertPayco = $this->dao->insertMemberSns($memberNo, $uuid, 'y', $accessToken, $snsTypeFl, $this->getAppId());

        if ($insertPayco < 1) {
            throw new Exception(__('회원 가입 중 오류가 발생하였습니다.'));
        }
    }

    public function hasSnsMember($uuid)
    {
        if (!Validator::required($uuid)) {
            throw new Exception(__('조회 정보가 없습니다.'));
        }

        $member = $this->dao->selectMemberByUUID($uuid, $this->getAppId());

        return $this->validateMemberSns($member);
    }

    /**
     * validateMemberSns
     *
     * @param array $memberSns
     *
     * @return bool
     */
    public function validateMemberSns(array $memberSns)
    {
        return (isset($memberSns['sno']) && $memberSns['sno'] > 0);
    }

    public function connectSns($memberNo, $uuid, $accessToken, $snsTypeFl = '')
    {
        if (!Validator::required($uuid)) {
            throw new Exception(__('조회 정보가 없습니다.'));
        }
        if (!Validator::required($memberNo)) {
            throw new Exception(__('회원 정보가 없습니다.'));
        }
        if (!Validator::required($snsTypeFl)) {
            throw new Exception(__('SNS 타입이 없습니다.'));
        }
        if (self::hasSnsMember($uuid)) {
            throw new Exception(__('이미 가입한 회원입니다.'));
        }

        $insertPayco = $this->dao->insertMemberSns($memberNo, $uuid, 'n', $accessToken, $snsTypeFl, $this->getAppId());

        if ($insertPayco < 1) {
            throw new Exception(__('페이코 연결 중 오류가 발생하였습니다.'));
        }
    }

    public function disconnectSns($memberNo)
    {
        if (!Validator::required($memberNo)) {
            throw new Exception(__('회원 정보가 없습니다.'));
        }

        $this->dao->deleteMemberSns($memberNo);
    }

    /**
     * sns 회원가입 정보를 통한 회원 로그인 처리
     *
     * @param $uuid
     *
     * @throws AlertRedirectException
     * @throws Exception
     */
    public function loginBySns($uuid)
    {
        $logger = \App::getInstance('logger');
        $member = $this->dao->selectLoginInfoByUUID($uuid, $this->getAppId());
        $loginLimit = json_decode($member['loginLimit'], true);
        $member['loginLimit'] = $loginLimit;

        if ($member['hackOutSno'] != null) {
            throw new Exception(__('회원 탈퇴를 신청하였거나, 탈퇴한 회원이십니다.<br/>로그인이 제한됩니다.'), 500);
        }

        if ($member['sleepFl'] == 'y') {
            $logger->info(sprintf('Dormant membership restoration is required. memNo[%s], memId[%s], sleepFl[%s], uuid[%s]', $member['memNo'], $member['memId'], $member['sleepFl'], $uuid));
            $session = \App::getInstance('session');
            //@formatter:off
            $session->set(MemberSleep::SESSION_WAKE_INFO, ['memId' => $member['memId'], 'memPw' => $member['memPw']]);
            //@formatter:on
            $target = (Request::isMobile()) ? 'parent' : 'opener';
            throw new AlertRedirectCloseException(__('휴면회원 해제가 필요합니다.'), 401, null, '../../member/wake.php', $target);
        }

        if ($member['appFl'] != 'y') {
            $logger->info(sprintf('Your login is restricted because you are not authorized on this site. memNo[%s], memId[%s], appFl[%s], uuid[%s]', $member['memNo'], $member['memId'], $member['appFl'], $uuid));
            $target = (Request::isMobile()) ? 'parent' : 'opener';
            throw new AlertRedirectCloseException(__('고객님은 본 사이트에서 승인되지 않아 로그인이 제한됩니다.'), 500, null, '../../member/login.php', $target);
        }

        if ($member['loginLimit']['limitFlag'] == 'y') {
            if ($this->isGreaterThanLimitLoginTime($member['loginLimit']['onLimitDt'])) {
                $logger->info('로그인이 제한된 회원입니다.', ['loginLimit' => $member['loginLimit']]);
                throw new \Component\Member\Exception\LoginLimitException('로그인이 제한되었습니다. 10분 후에 시도해 주세요.', 500);
            } else {
                $initLimitLoginLog = $this->initLimitLoginLog($member);
                $member = $initLimitLoginLog['member'];
                unset($initLimitLoginLog);
            }
        }

        if ($member['adultFl'] == 'y' && (strtotime($member['adultConfirmDt']) < strtotime("-1 year", time()))) {
            $member['adultFl'] = "n";
        }

        $encrypt = MemberUtil::encryptMember($member);

        $memberService = new Member();
        $memberService->saveLoginLog($encrypt['memNo']);
        $memberService->refreshMemberByLogin($encrypt['memNo'], $encrypt['loginCnt']);
        $memberService->refreshBasket($encrypt['memNo']);
        $memberService->setSessionByLogin($encrypt);
    }

    /**
     * @param string $thirdPartyAppType facebook, payco
     */
    public function setThirdPartyAppType(string $thirdPartyAppType)
    {
        $this->thirdPartyAppType = $thirdPartyAppType;
    }

    /**
     * getAppId
     *
     * @return array|string
     * @throws Exception
     */
    protected function getAppId()
    {
        $snsLoginPolicy = \App::load('Component\\Policy\\SnsLoginPolicy');
        StringUtils::strIsSet($this->thirdPartyAppType, '');
        if (empty($this->thirdPartyAppType)) {
            $session = \App::getInstance('session');
            if ($session->has(GodoPaycoServerApi::SESSION_USER_PROFILE)) {
                $this->setThirdPartyAppType('payco');
            } elseif ($session->has(Facebook::SESSION_USER_PROFILE) || $session->has(Facebook::SESSION_METADATA)) {
                $this->setThirdPartyAppType($snsLoginPolicy::FACEBOOK);
            } else {
                return 'godo';
            }
        }

        return $snsLoginPolicy->getAppId($this->thirdPartyAppType);
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
}
