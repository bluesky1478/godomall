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

namespace Bundle\Component\Policy;

use Component\Member\Manager;
use Component\Scm\ScmAdmin;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;
use Component\Excel\ExcelForm;

/**
 * Class ManageSecurityPolicy
 * @package Bundle\Component\Policy
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class ManageSecurityPolicy extends \Component\Policy\Policy
{
    const KEY = 'manage.security';
    protected $policy = [];

    public function useSecurityLogin()
    {
        $securityLogin = $this->getValue()['smsSecurity'];
        StringUtils::strIsSet($securityLogin, 'n');

        return $securityLogin === 'y';
    }

    public function getValue($name = self::KEY, $mallSno = DEFAULT_MALL_NUMBER)
    {
        if (\count($this->policy) < 1) {
            $this->policy = parent::getValue($name, $mallSno);
        }

        return $this->policy;
    }

    public function hasAuthenticationLoginPeriod()
    {
        $authLoginPeriod = $this->getValue()['authLoginPeriod'];
        StringUtils::strIsSet($authLoginPeriod, 0);

        return $authLoginPeriod > 0;
    }

    public function getValidAuthDate()
    {
        $authLoginPeriod = $this->getValue()['authLoginPeriod'];
        StringUtils::strIsSet($authLoginPeriod, 0);

        return DateTimeUtils::dateFormat('Ymd', '+' . $authLoginPeriod . ' day');
    }

    /**
     * 다운로드 요청한 엑셀이 보안범위 포함 여부
     *
     * @param string $type 보안범위
     * @param string $location
     *
     * @return bool
     */
    public function useExcelSecurityScope(string $type, string $location): bool
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $policy = $this->getValue(self::KEY);

        $manager = $session->get(Manager::SESSION_MANAGER_LOGIN, ['isProvider' => false]);

        $scope = $manager['isProvider'] ? $policy['excel']['scope']['provider'] : $policy['excel']['scope']['company'];
        $excelUse = $policy['excel']['use'] === 'y';
        $tmpType = $type;
        if (GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW) && $tmpType === 'plusreview') {
            $tmpType = 'board';
        }
        $excelScope = \in_array($tmpType, $scope, true) === true;
        $excelForm = \App::load(ExcelForm::class);
        $excelLocation = array_key_exists($location, $excelForm->locationList[$type]);

        if (array_key_exists('csSno', $manager)) {
            $excelLocation = false;
        }

        $logFormat = 'use excel security scope check use[%s], scope[%s], location[%s]';
        $context = [
            $type,
            $location,
            $scope,
        ];
        $logger->info(sprintf($logFormat, $excelUse, $excelScope, $excelLocation), $context);

        return $excelUse && $excelScope && $excelLocation;
    }

    /**
     * 엑셀 다운로드 시 아이피 인증을 사용 여부
     *
     * @return bool
     */
    public function useSecurityIp(): bool
    {
        $policy = $this->getValue(self::KEY);

        return (\in_array('ip', $policy['excel']['auth'], true) === true)
            && (\is_array($policy['ipExcel']) && \count($policy['ipExcel']) > 0) === true;
    }

    /**
     * 현재 요청 ip 가 엑셀보안 ip 등록 여부
     *
     * @return bool
     */
    public function notAuthenticationIp(): bool
    {
        $request = \App::getInstance('request');
        $policy = $this->getValue(self::KEY);
        $remote = $request->getRemoteAddress();
        foreach ($policy['ipExcel'] as $ipKey => $ipVal) {
            if (ComponentUtils::validateRemoteAddress($remote, $ipVal, $policy['ipExcelBandWidth'][$ipKey]) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 엑셀 다운로드를 위한 인증 시 필요한 정보가 인증되었는지 여부 확인
     *
     * @return bool
     */
    public function requireAuthInfo(): bool
    {
        $session = \App::getInstance('session');
        $manager = $session->get(\Component\Member\Manager::SESSION_MANAGER_LOGIN);
        StringUtils::strIsSet($manager['email'], '');
        StringUtils::strIsSet($manager['cellPhone'], '');
        StringUtils::strIsSet($manager['isEmailAuth'], 'n');
        StringUtils::strIsSet($manager['isSmsAuth'], 'n');

        $requireEmail = ($manager['email'] === '') || ($manager['isEmailAuth'] === 'n');
        $requireCellPhone = ($manager['cellPhone'] === '') || ($manager['isSmsAuth'] === 'n');

        return ($this->useOnlyEmailAuth() && $requireEmail)
            || ($this->useOnlySmsAuth() && $requireCellPhone)
            || ($this->useEmailAuthAndSmsAuth() && ($requireEmail && $requireCellPhone));
    }

    /**
     * 이메일 인증만 사용 여부
     *
     * @return bool
     */
    public function useOnlyEmailAuth(): bool
    {
        return $this->useEmailAuth() && !$this->useSmsAuth();
    }

    /**
     * 이메일 인증 사용 여부
     *
     * @return bool
     */
    public function useEmailAuth(): bool
    {
        $policy = $this->getValue(self::KEY);

        return \in_array('email', $policy['excel']['auth'], true) === true;
    }

    /**
     * SMS 인증 사용 여부
     *
     * @return bool
     */
    public function useSmsAuth(): bool
    {
        $policy = $this->getValue(self::KEY);

        return \in_array('sms', $policy['excel']['auth'], true) === true;
    }

    /**
     * 운영자 정보 SMS 인증만 사용 여부
     *
     * @return bool
     */
    public function useOnlySmsAuth(): bool
    {
        return !$this->useEmailAuth() && $this->useSmsAuth() && !$this->isSmsAuthGodo();
    }

    /**
     * 고도회원 SMS 인증 사용 여부
     *
     * @return bool
     */
    public function isSmsAuthGodo(): bool
    {
        $policy = $this->getValue(self::KEY);

        return $policy['excel']['smsAuth'] === 'godo';
    }

    /**
     * 이메일 인증, 운영자 정보 SMS 인증 사용 여부
     *
     * @return bool
     */
    public function useEmailAuthAndSmsAuth(): bool
    {
        return $this->useEmailAuth() && $this->useSmsAuth() && !$this->isSmsAuthGodo();
    }

    /**
     * 정책에 저장된 값과 인증 결과를 확인하여 엑셀 다운로드
     * 가능여부 확인
     *
     * @return bool
     */
    public function requireAuthorizeExcelDownload(): bool
    {
        $session = \App::getInstance('session');
        if ($this->useOnlyEmailAuth()) {
            $session->set(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, ['requireExcelAuthEmail' => true]);

            return true;
        }

        if ($this->useOnlySmsAuth()) {
            $session = \App::getInstance('session');
            $session->set(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, ['requireExcelAuthSms' => true]);

            return true;
        }

        if ($this->useEmailAuthAndSmsAuth()) {
            $value = [
                'requireExcelAuthEmail' => true,
                'requireExcelAuthSms'   => true,
            ];
            $session->set(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, $value);

            return true;
        }

        if ($this->useOnlySmsAuthGodo()) {
            $session->set(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, ['requireExcelAuthSmsGodo' => true]);

            return true;
        }

        if ($this->useEmailAuthAndSmsAuthGodo()) {
            $value1 = [
                'requireExcelAuthEmail'   => true,
                'requireExcelAuthSmsGodo' => true,
            ];
            $session->set(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH, $value1);

            return true;
        }

        return false;
    }

    /**
     * 고도회원 정보 SMS 인증만 사용 여부
     *
     * @return bool
     */
    public function useOnlySmsAuthGodo(): bool
    {
        return !$this->useEmailAuth() && $this->useSmsAuth() && $this->isSmsAuthGodo();
    }

    /**
     * 이메일 인증, 고도회원 정보 SMS 인증 사용 여부
     *
     * @return bool
     */
    public function useEmailAuthAndSmsAuthGodo(): bool
    {
        return $this->useEmailAuth() && $this->useSmsAuth() && $this->isSmsAuthGodo();
    }

    /**
     * 엑셀다운로드 인증 정보 확인
     *
     * @return bool
     */
    public function hasAuthorize()
    {
        $auth = $this->getSecurityAuthSession();
        foreach ($auth as $index => $item) {
            if ($item === true && substr($index, 0, 3) === 'has') {
                $authDateTime = $auth[$index . 'DateTime'];
                $intervalDay = DateTimeUtils::intervalDay($authDateTime);
                if ($intervalDay > 0) {
                    $session = \App::getInstance('session');
                    $session->del(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH);       // 1일 지난 세션 제거
                    break;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * 엑셀 다운로드를 위한 이메일 또는 SMS 인증 결과 반환
     *
     * @return array
     */
    protected function getSecurityAuthSession(): array
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $auth = $session->get(\Component\Excel\ExcelForm::SESSION_SECURITY_AUTH);
        $logger->info('excel security auth session', $auth);
        StringUtils::strIsSet($auth['hasExcelAuthSmsGodo'], false);
        StringUtils::strIsSet($auth['hasExcelAuthSms'], false);
        StringUtils::strIsSet($auth['hasExcelAuthEmail'], false);
        $logger->info('excel security auth session', $auth);

        return $auth;
    }

    /**
     * 로그인 보안인증 수단 반환
     *
     * @return array
     */
    public function getLoginSecuritySelect()
    {
        $policy = $this->getValue(self::KEY);
        $securitySelect = [
            'smsReSend' => '휴대폰',
            'emailSend' => '이메일',
        ];

        if ($policy['emailSecurityFl'] !== 'y') {
            unset($securitySelect['emailSend']);
        }

        if ($policy['smsSecurityFl'] !== 'y') {
            unset($securitySelect['smsReSend']);
        }

        return $securitySelect;
    }
}
