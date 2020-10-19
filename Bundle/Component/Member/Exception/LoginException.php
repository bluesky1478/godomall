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

namespace Bundle\Component\Member\Exception;

use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;

/**
 * Class LoginException
 * @package Bundle\Component\Member\Exception
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 *
 * 관리자, 회원 로그인 실패 시 사용
 */
class LoginException extends \Exception
{
    /** 관리자 로그인 3회 실패 경고 */
    const CODE_MANAGER_LOGIN_FAIL = 100;
    /** 관리자 로그인 5회 실패 경고(대표운영자) */
    const CODE_SUPER_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON = 200;
    /** 관리자 로그인 5회 실패 경고  */
    const CODE_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON = 300;
    /** 사용자 로그인 7회 실패 경고 */
    const CODE_MEMBER_LOGIN_FAIL = 400;
    /** 사용자 로그인 10회 실패 경고 */
    const CODE_MEMBER_LOGIN_FAIL_LIMIT_FLAG_ON = 500;

    public function __construct($id, $message = "로그인이 실패되었습니다.", $code = 0, \Throwable $previous = null)
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $logger->info(
            __CLASS__, [
                'args'      => func_get_args(),
                'directUri' => $request->getDirectoryUri(),
                'isGodoIp'  => GodoUtils::isGodoIp(),
            ]
        );

        if (GodoUtils::isGodoIp('alpha') === false) {
            if (empty($id) === false) {
                if ($this->isAdminAccess() || $request->getDirectoryUri() == 'mobileapp') {
                    $manager = $this->selectManager($id);
                    if ($this->isDefaultScmSuperManagerLoginLimit($manager)) {
                        $this->updateManagerLoginLimit(
                            [
                                'loginLimit' => $this->increaseLoginLimitWithOnLimitFlag($manager),
                                'managerId'  => $id,
                            ]
                        );
                        $code = self::CODE_SUPER_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON;
                    } elseif ($this->isManagerLoginLimit($manager)) {
                        $this->updateManagerLoginLimit(
                            [
                                'loginLimit' => $this->increaseLoginLimitWithOnLimitFlag($manager),
                                'managerId'  => $id,
                            ]
                        );
                        $code = self::CODE_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON;
                    } elseif ($this->isManagerLoginLimitWarning($manager)) {
                        $this->updateManagerLoginLimit(
                            [
                                'loginLimit' => $this->increaseLoginLimit($manager),
                                'managerId'  => $id,
                            ]
                        );
                        $code = self::CODE_MANAGER_LOGIN_FAIL;
                    } else {
                        $this->updateManagerLoginLimit(
                            [
                                'loginLimit' => $this->increaseLoginLimit($manager),
                                'managerId'  => $id,
                            ]
                        );
                    }
                } else {
                    $dao = \App::load('Component\\Member\\MemberDAO');
                    $member = $dao->selectMemberByOne($id, 'memId');
                    if (is_array($member) && $member['memId'] == $id) {
                        if ($this->isMemberLoginLimit($member)) {
                            $dao->updateMember(
                                [
                                    'memNo'      => $member['memNo'],
                                    'loginLimit' => $this->increaseLoginLimitWithOnLimitFlag($member),
                                ], ['loginLimit'], []
                            );
                            $code = self::CODE_MEMBER_LOGIN_FAIL_LIMIT_FLAG_ON;
                        } elseif ($this->isMemberLoginLimitWarning($member)) {
                            $dao->updateMember(
                                [
                                    'memNo'      => $member['memNo'],
                                    'loginLimit' => $this->increaseLoginLimit($member),
                                ], ['loginLimit'], []
                            );
                            $code = self::CODE_MEMBER_LOGIN_FAIL;
                        } else {
                            $dao->updateMember(
                                [
                                    'memNo'      => $member['memNo'],
                                    'loginLimit' => $this->increaseLoginLimit($member),
                                ], ['loginLimit'], []
                            );
                        }
                    }
                }
            }
        }

        switch ($code) {
            case self::CODE_MANAGER_LOGIN_FAIL:
                $message = '로그인을 3회 실패하셨습니다. 5회 이상 실패 시 접속이 제한됩니다.';
                break;
            case self::CODE_SUPER_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON:
                $message = '로그인을 5회 이상 실패하여 접속이 제한되었습니다. 고도 회원정보로 인증 후 로그인이 가능합니다.';
                break;
            case self::CODE_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON:
                $message = '로그인을 5회 이상 실패하여 접속이 제한되었습니다. 본사 대표운영자에게 문의해주세요.';
                break;
            case self::CODE_MEMBER_LOGIN_FAIL:
                $message = '로그인을 7회 실패하셨습니다. 10회 이상 실패 시 접속이 제한됩니다.';
                break;
            case self::CODE_MEMBER_LOGIN_FAIL_LIMIT_FLAG_ON:
                $message = '로그인을 10회 이상 실패하여 10분 동안 접속이 제한됩니다.';
                break;
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * 대상의 로그인 제한 정보에 실패 카운트와 실패 시점을 추가
     *
     * @param array $params
     *
     * @return array
     */
    protected function increaseLoginLimit(array $params)
    {
        $loginLimitById = json_decode($params['loginLimit'], true);
        // 카운트 후 정보 업데이트
        $loginLimitById['loginFailCount'] += 1;
        $loginLimitById['loginFailLog'][] = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        return $loginLimitById;
    }

    /**
     * increaseLoginLimit 결과에 로그인 제한 플래그를 설정
     *
     * @param array $params
     *
     * @return array
     */
    protected function increaseLoginLimitWithOnLimitFlag(array $params)
    {
        $loginLimit = $this->increaseLoginLimit($params);
        $loginLimit['limitFlag'] = 'y';
        $loginLimit['onLimitDt'] = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        return $loginLimit;
    }

    protected function isMemberLoginLimit(array $member)
    {
        return ((json_decode($member['loginLimit'], true)['loginFailCount'] + 1) >= 10);
    }

    protected function isMemberLoginLimitWarning(array $member)
    {
        return ((json_decode($member['loginLimit'], true)['loginFailCount'] + 1) == 7);
    }

    protected function isManagerLoginLimitWarning($manager)
    {
        return ((json_decode($manager['loginLimit'], true)['loginFailCount'] + 1) == 3);
    }

    protected function isManagerLoginLimit($manager)
    {
        return ((json_decode($manager['loginLimit'], true)['loginFailCount'] + 1) >= 5);
    }

    protected function isDefaultScmSuperManagerLoginLimit($manager)
    {
        return ($manager['scmNo'] == 1) && ($manager['isSuper'] == 'y') && ((json_decode($manager['loginLimit'], true)['loginFailCount'] + 1) >= 5);
    }

    /**
     * 관리자 로그인 여부 체크
     *
     * @return bool
     */
    protected function isAdminAccess()
    {
        return \App::getInstance('request')->getSubdomain() == DOMAIN_USEABLE_LIST['admin'];
    }

    /**
     * 관리자 정보 수정
     *
     * @param array $params
     */
    protected function updateManagerLoginLimit(array $params)
    {
        $db = \App::getInstance('DB');
        $tableField = \App::load('Component\\Database\\DBTableField');
        $managerTableFields = $tableField::getFieldTypes($tableField::getFuncName(DB_MANAGER));
        $db->query_reset();
        $arrBind = $db->get_binding($tableField::tableManager(), $params, 'update', ['loginLimit']);
        $db->bind_param_push($arrBind['bind'], $managerTableFields['managerId'], $params['managerId']);
        $db->set_update_db(DB_MANAGER, $arrBind['param'], 'managerId=?', $arrBind['bind'], false);
        unset($arrBind);
    }

    /**
     * 관리자 정보 조회
     *
     * @param $id
     *
     * @return array
     */
    protected function selectManager($id)
    {
        $db = \App::getInstance('DB');
        $tableField = \App::load('Component\\Database\\DBTableField');
        $managerTableFields = $tableField::getFieldTypes($tableField::getFuncName(DB_MANAGER));
        $db->bind_param_push($arrBind, $managerTableFields['managerId'], $id);
        $resultSet = $db->query_fetch('SELECT * FROM ' . DB_MANAGER . ' WHERE managerId=?', $arrBind, false);
        unset($arrBind);

        return $resultSet;
    }
}
