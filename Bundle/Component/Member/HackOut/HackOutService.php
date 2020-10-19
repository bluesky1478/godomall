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

namespace Bundle\Component\Member\HackOut;


use App;
use Bundle\Component\Godo\GodoKakaoServerApi;
use Component\Godo\GodoPaycoServerApi;
use Component\Mail\MailMimeAuto;
use Component\Member\Manager;
use Component\Member\MemberDAO;
use Component\Member\HackOut\HackOutDAO;
use Component\Member\MemberSnsDAO;
use Component\Validator\Validator;
use DateTime;
use Exception;
use Framework\Security\Digester;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Session;

/**
 * Class HackOutService
 * @package Bundle\Component\Member\HackOut
 * @author  yjwee
 */
class HackOutService
{
    private $memberDAO;
    private $hackOutDAO;
    private $memberSnsDAO;
    private $hackOut = [];
    private $managerNo;
    private $managerId;
    private $managerIp;
    private $count = 0;
    private $foundRows = 0;

    public function __construct($config = [])
    {
        $this->memberDAO = is_object($config['memberDAO']) ? $config['memberDAO'] : new MemberDAO();
        $this->hackOutDAO = is_object($config['hackOutDAO']) ? $config['hackOutDAO'] : new HackOutDAO();
        $this->memberSnsDAO = is_object($config['memberSnsDAO']) ? $config['memberSnsDAO'] : new MemberSnsDAO();
    }

    /**
     * 탈퇴리스트 조회
     *
     * @param array $params
     * @param int   $offset
     * @param int   $limit
     *
     * @return array|string
     */
    public function getHackOutList(array $params, $offset = 0, $limit = 20)
    {
        $this->hackOutDAO->setParams($params);
        $this->hackOutDAO->setOffset($offset);
        $this->hackOutDAO->setLimit($limit);

        $lists = $this->hackOutDAO->lists();
        Manager::displayListData($lists);

        $this->foundRows = $this->hackOutDAO->foundRowsByLists();
        $this->count = $this->hackOutDAO->getCount(DB_MEMBER_HACKOUT, 1, 'AS mh LEFT JOIN es_manager AS ma ON mh.managerNo = ma.sno');

        return StringUtils::htmlSpecialCharsStripSlashes(StringUtils::strIsSet($lists));
    }

    /**
     * 탈퇴정보 조회
     *
     * @param $sno
     *
     * @return array|object
     * @throws Exception
     */
    public function getHackOutBySno($sno)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), __('일련번호')));
        }
        $this->hackOut = $this->hackOutDAO->getHackOutBySno($sno);

        Manager::displayListData($this->hackOut);

        return $this->hackOut;
    }

    /**
     * 탈퇴정보 삭제
     *
     * @param $sno
     *
     * @throws Exception
     */
    public function deleteHackOutBySno($sno)
    {
        if (Validator::number($sno, null, null, true) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), __('일련번호')));
        }
        $this->hackOutDAO->deleteHackOutBySno($sno);
    }

    /**
     * 재가입 가능 아이디 체크
     *
     * @param      $memberId
     * @param bool $required
     *
     * @return bool
     * @throws Exception
     */
    public function checkRejoinByMemberId($memberId, $required = true)
    {
        if ($this->_allowRejoinByPolicy()) {
            return true;
        }

        $hackOutMember = $this->_getHackOutByMemberId($memberId, $required);
        if (count($hackOutMember) < 1) {
            return true;
        }

        if ($this->_allowRejoinByHackOutDateTime()) {
            return true;
        }

        $policy = gd_policy('member.join');
        throw new Exception(sprintf(__('회원탈퇴 후 %s일 동안 재가입할 수 없습니다. 회원님은 %s에 탈퇴하셨습니다.'), $policy['rejoin'], substr($this->hackOut['hackDt'], 0, 10)));
    }

    /**
     * 재가입 정책 확인
     *
     * @return bool
     */
    private function _allowRejoinByPolicy()
    {
        $policy = gd_policy('member.join');

        return $policy['rejoinFl'] === 'n' || gd_isset($policy['rejoin'], 0) < 1;
    }

    /**
     * 재가입 아이디 확인
     *
     * @param      $memberId
     * @param bool $required
     *
     * @return array|object
     * @throws Exception
     */
    private function _getHackOutByMemberId($memberId, $required = true)
    {
        if (Validator::userid($memberId, $required, false) === false) {
            throw new Exception(sprintf(__('%s 인자가 잘못되었습니다.'), __('아이디')));
        }
        $this->hackOut = $this->hackOutDAO->getHackOutByMemberId($memberId);

        return $this->hackOut;
    }

    /**
     * 재가입 가능 기간 확인
     *
     * @return bool
     */
    private function _allowRejoinByHackOutDateTime()
    {
        $policy = gd_policy('member.join');

        if ($policy['rejoinFl'] == 'n') {
            return true;
        }

        $date = new DateTime(); // 오늘 00시00분
        $date->setTime(0, 0);
        $date2 = DateTime::createFromFormat('Y-m-d H:i:s', $this->hackOut['hackDt']);    // 탈퇴일시
        $rejoinInterval = new \DateInterval('P' . $policy['rejoin'] . 'D');
        $date2->add($rejoinInterval);  // 탈퇴일시+재가입불가기간

        // 오늘 00시00분의 timestamp 보다 탈퇴일시+재가입불가기간의 timestap 가 작을 경우 가입 가능 처리
        if ($date->getTimestamp() > $date2->getTimestamp()) {
            return true;
        }

        return false;
    }

    /**
     * 사용자 회원 탈퇴
     *
     * @param $params
     * @param $memNo
     * @param $memId
     * @param $regIp
     *
     * @throws Exception
     */
    public function userHackOutByParams($params, $memNo, $memId, $regIp)
    {
        $password = App::getInstance('password');
        $session = \App::getInstance('session');

        if (StringUtils::strIsSet($memNo, '') == '') {
            throw new Exception(__('회원번호는 필수 입니다.'));
        }
        if ($memId == '') {
            throw new Exception(__('아이디가 없습니다.'));
        }
        if ($regIp == '') {
            throw new Exception(__('처리자 정보가 없습니다.'));
        }

        if (isset($params['reasonCd']) && is_array($params['reasonCd']) === true) {
            $params['reasonCd'] = (implode('', $params['reasonCd']) == '' ? '' : '|' . implode('|', $params['reasonCd']) . '|');
        }

        $member = $this->memberDAO->selectMemberByOne($memNo);
        if ((empty($member) || Digester::isValid($member['memPw'], $params['memPw']) == false) && $params['snsType'] != 'naver' && $params['snsType'] != 'kakao') {
            if ((empty($member) || $password->verify($params['memPw'], $member['memPw']) === false) && $params['snsType'] != 'naver' && $params['snsType'] != 'kakao') {
                throw new Exception(__('비밀번호가 다릅니다. 다시 확인 바랍니다.'));
            }
        }

        if ($member['deposit'] > 0) {
            throw new Exception(sprintf(__('현재 예치금을 %s 보유중입니다. 보유중인 예치금이 있는 회원은 탈퇴하실 수 없습니다.'), NumberUtils::currencyDisplay($member['deposit'])));
        }

        $v = new Validator();
        $v->add('memPw', 'password', true, '{' . __('비밀번호') . '}'); // 비밀번호
        $v->add('reasonCd', '', false, '{' . __('탈퇴사유') . '}'); // 탈퇴사유
        $v->add('reasonDesc', '', false, '{' . __('남기실 말씀') . '}'); // 남기실 말씀
        $v->add('hackType', '', true, '{' . __('탈퇴구분') . '}');
        $v->add('memNo', 'number', true, '{' . __('회원번호') . '}');
        $v->add('memId', 'userId', true, '{' . __('회원아이디') . '}');
        $v->add('dupeinfo', '');
        $v->add('reasonCd', '');
        $v->add('reasonDesc', '');
        $v->add('hackDt', '', true, '{' . __('탈퇴일') . '}');
        $v->add('regIp', '', true);
        $v->add('rejoinFl', 'yn', true, '{' . __('재가입여부') . '}');
        $v->add('mallSno', 'number', true, '{' . __('상점번호') . '}');

        $params['memNo'] = $memNo;
        $params['memId'] = $memId;
        $params['regIp'] = $regIp;
        $params['dupeinfo'] = $member['dupeinfo'];
        $params['hackType'] = 'directSelf';
        $params['hackDt'] = date('Y-m-d H:i:s');
        $params['rejoinFl'] = $this->_getReJoinFlag();
        $params['mallSno'] = StringUtils::strIsSet($member['mallSno'], '');

        $memberSession = $session->get(\Component\Member\Member::SESSION_MEMBER_LOGIN);
        if (isset($memberSession['accessToken'])) {
            $paycoApi = new GodoPaycoServerApi();
            $paycoApi->removeServiceOff($memberSession['accessToken']);

            $kakaoApi = new GodoKakaoServerApi();
            $kakaoToken = $session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
            $kakaoApi->unlink($kakaoToken['access_token']);
            $this->memberSnsDAO->deleteMemberSns($params['memNo']);
        }
        $this->hackOutDAO->setParams($params);
        $this->hackOutDAO->insertHackOutWithDeleteMemberByParams();
        if ($member['email'] != '') {
            $this->_sendHackOutAutoMail($member);
        }
    }

    /**
     * 회원탈퇴 정보 추가
     *
     * @param        $memberNo
     * @param string $hackType
     *
     * @throws Exception
     */
    public function insertHackOut($memberNo, $hackType = 'directManager')
    {
        $params = $this->hackOut;
        $params['memNo'] = $memberNo;
        $params['hackType'] = $hackType;
        $params['managerId'] = $this->managerId;
        $params['managerNo'] = $this->managerNo;
        $params['managerIp'] = $this->managerIp;
        $params['step'] = 'done';
        $params['hackDt'] = date('Y-m-d H:i:s');

        $policy = gd_policy('member.join');
        // 정책의 재가입 기간제한이 사용안함이면 재가입이 가능하기때문에 y로 설정한다.
        $arrData['rejoinFl'] = $policy['rejoinFl'] === 'n' ? 'y' : 'n';

        $v = new Validator();
        $v->add('hackType', '');
        $v->add('step', '');
        $v->add('memNo', 'number', true, '{' . __('회원번호') . '}');
        $v->add('reasonCd', '');
        $v->add('reasonDesc', '');
        $v->add('managerId', '');
        $v->add('managerNo', '');
        $v->add('managerIp', '');
        $v->add('hackDt', '');
        $v->add('regIp', '');
        $v->add('rejoinFl', 'yn', true);
        if ($v->act($arrData, true) === false) {
            throw new Exception(implode("\n", $v->errors));
        }

        $count = $this->hackOutDAO->getCount(DB_MEMBER, '1', 'WHERE memNo=' . $memberNo);
        if ($count < 1) {
            throw new Exception(__('회원정보를 찾을 수 없습니다.'));
        }

        $this->hackOutDAO->setParams($params);
        $this->hackOutDAO->insertHackOut();
    }

    /**
     * 탈퇴회원 리스트 조회
     *
     * @param array $memNos
     *
     * @throws Exception
     */
    public function hackOutByMemberList(array $memNos)
    {
        $resolver = App::getInstance('ControllerNameResolver');
        $session = App::getInstance('session');

        // 운영자 기능권한의 회원탈퇴 권한 없음 - 관리자페이지에서만
        $thisCallController = $resolver->getControllerRootDirectory();
        if ($thisCallController == 'admin' && $session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.memberHack') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }
        foreach ($memNos as $index => $memNo) {
            if (StringUtils::strIsSet($memNo, '') == '') {
                throw new Exception(__('회원번호는 필수 입니다.'));
            }
            if ($this->managerNo == '') {
                throw new Exception(__('관리자 번호가 없습니다.'));
            }
            if ($this->managerId == '') {
                throw new Exception(__('관리자 아이디가 없습니다.'));
            }
            if ($this->managerIp == '') {
                throw new Exception(__('처리자 정보가 없습니다.'));
            }

            $v = new Validator();
            $v->add('hackType', '', true, '{' . __('탈퇴구분') . '}');
            $v->add('memNo', 'number', true, '{' . __('회원번호') . '}');
            $v->add('memId', 'userId', true, '{' . __('회원아이디') . '}');
            $v->add('dupeInfo', '');
            $v->add('reasonCd', '');
            $v->add('reasonDesc', '');
            $v->add('managerNo', '', true, '{' . __('관리자 번호') . '}');
            $v->add('managerId', '', true, '{' . __('관리자 ID') . '}');
            $v->add('managerIp', '', true, '{' . __('관리자 IP') . '}');
            $v->add('hackDt', '', true, '{' . __('탈퇴일') . '}');
            $v->add('regIp', '', true);
            $v->add('rejoinFl', 'yn', true, '{' . __('재가입여부') . '}');
            $v->add('accessToken', '');
            $v->add('mallSno', 'number', true, '{' . __('상점번호') . '}');

            $member = $this->memberDAO->selectMemberWithGroup($memNo, 'memNo');
            $params = [];
            $params['rejoinFl'] = $this->_getReJoinFlag();
            $params['memNo'] = $memNo;
            $params['memId'] = $member['memId'];
            $params['dupeInfo'] = $member['dupeInfo'];
            $params['hackType'] = 'directManager';
            $params['managerNo'] = $this->managerNo;
            $params['managerId'] = $this->managerId;
            $params['managerIp'] = $this->managerIp;
            $params['regIp'] = $this->managerIp;
            $params['hackDt'] = date('Y-m-d H:i:s');
            $params['mallSno'] = $member['mallSno'];

            if ($v->act($params, true) === false) {
                throw new Exception(implode("\n", $v->errors));
            }

            if (StringUtils::strIsSet($member['accessToken'], '') != '') {
                $paycoApi = new GodoPaycoServerApi();
                $paycoApi->removeServiceOff($member['accessToken']);
                $kakaoApi = new GodoKakaoServerApi();
                $kakaoApi->unlink($session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN)['access_token']);
            }
            StringUtils::strIsSet($member['snsTypeFl'], '');
            if ($member['snsTypeFl'] == 'payco' || $member['snsTypeFl'] == 'facebook' || $member['snsTypeFl'] == 'naver' || $member['snsTypeFl'] == 'kakao' || $member['snsTypeFl'] == 'wonder') {
                $this->memberSnsDAO->deleteMemberSns($memNo);
            }
            $this->hackOutDAO->setParams($params)->insertHackOutWithDeleteMemberByParams();
            if ($member['email'] != '') {
                $this->_sendHackOutAutoMail($member);
            }
        }
    }

    /**
     * 재가입 정책 사용여부 반환
     *
     * @return string
     */
    private function _getReJoinFlag()
    {
        $policy = gd_policy('member.join');

        return ($policy['rejoinFl'] === 'n') ? 'y' : 'n';
    }

    /**
     * 재가입 정책 사용여부 반환 랩핑 클래스
     *
     * @return string
     */
    protected function getRejoinFlag()
    {
        return $this->_getReJoinFlag();
    }

    /**
     * deleteMember
     *
     * @return bool
     * @throws Exception
     * @deprecated
     */
    public function deleteMember()
    {
        $params = $this->hackOut;
        // 회원 번호 체크
        if (!Validator::required($params['memNo'])) {
            throw new Exception(__('유효하지 않은 회원번호 입니다.'));
        }

        $memberParams = [
            'memNo' => $params['memNo'],
        ];
        $member = $this->hackOutDAO->getMember($memberParams);

        if ($member['memId'] != '') {
            $this->hackOutDAO->deleteMember();

            if ($member['email'] != '') {
                $this->_sendHackOutAutoMail($member);
            }
        }

        return true;
    }

    private function _sendHackOutAutoMail(array $params)
    {
        $mailData = [
            'memId' => $params['memId'],
            'memNm' => $params['memNm'],
            'email' => $params['email'],
        ];

        /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
        $mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
        $mailMimeAuto->init(MailMimeAuto::MEMBER_HACKOUT, $mailData, $params['mallSno'])->autoSend();
    }

    /**
     * sendHackOutAutoMail
     *
     * @param array $params
     */
    protected function sendHackOutAutoMail(array $params)
    {
        $this->_sendHackOutAutoMail($params);
    }

    /**
     * 재가입 허용
     *
     * @param array $reJoins
     */
    public function allowReJoin(array $reJoins)
    {
        $this->hackOutDAO->setParams($reJoins);
        $this->hackOutDAO->updateReJoinFlag();
    }

    /**
     * 재가입 대상 조회
     *
     * @param null $date
     *
     * @return array|null|object
     */
    public function getReJoin($date = null)
    {
        $policy = gd_policy('member.join');
        $this->hackOutDAO->setParams($policy);

        return $this->hackOutDAO->getReJoin($date);
    }

    /**
     * 탈퇴정보 수정
     *
     * @param $params
     *
     * @throws Exception
     */
    public function updateHackOut($params)
    {
        $params['managerNo'] = $this->managerNo;
        $params['managerId'] = $this->managerId;
        $params['managerIp'] = $this->managerIp;

        $validator = new Validator();
        $validator->add('sno', 'number', true);
        $validator->add('reasonDesc', '');
        $validator->add('adminMemo', '');
        $validator->add('managerNo', '');
        $validator->add('managerId', '');
        $validator->add('managerIp', '');
        if ($validator->act($params, true) === false) {
            throw new Exception(implode("\n", $validator->errors));
        }
        $this->hackOutDAO->setParams($params);
        $this->hackOutDAO->updateHackOut();
    }

    /**
     * getCount
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * 소셜회원 정보 조회
     *
     * @param $memberNo
     *
     * @return array|object
     */
    public function getMemberSns($memberNo)
    {
        return $this->memberSnsDAO->selectMemberSns($memberNo);
    }

    /**
     * @return array
     */
    public function getHackOut()
    {
        return $this->hackOut;
    }

    /**
     * @param mixed $managerId
     */
    public function setManagerId($managerId)
    {
        $this->managerId = $managerId;
    }

    /**
     * @param mixed $managerIp
     */
    public function setManagerIp($managerIp)
    {
        $this->managerIp = $managerIp;
    }

    /**
     * @param mixed $managerNo
     */
    public function setManagerNo($managerNo)
    {
        $this->managerNo = $managerNo;
    }

    /**
     * @return int
     */
    public function getFoundRows()
    {
        return $this->foundRows;
    }

    /**
     * @return mixed
     */
    public function getManagerId()
    {
        return $this->managerId;
    }

    /**
     * @return mixed
     */
    public function getManagerIp()
    {
        return $this->managerIp;
    }

    /**
     * @return mixed
     */
    public function getManagerNo()
    {
        return $this->managerNo;
    }
}
