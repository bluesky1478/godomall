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

namespace Bundle\Controller\Front\Member;

use Component\Database\DBTableField;
use Component\Member\Member;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Http\Response;
use Framework\Utility\ComponentUtils;


/**
 * Class FindPsController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class FindPsController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        try {
            /** @var  \Bundle\Component\Member\Member $memberService */
            $memberService = \App::load('\\Component\\Member\\Member');
            switch ($request->post()->get('mode')) {
                // 아이디 찾기
                case 'findId':
                    $postValue = $request->post()->all();
                    $memberId = $memberService->findId($postValue);
                    $result = (!empty($memberId) && is_string($memberId));

                    $this->json(
                        [
                            'result'   => $result,
                            'message'  => $result ? __('아이디를 찾았습니다.') : __('일치하는 회원정보가 없습니다. 다시 입력해 주세요.'),
                            'memberId' => $memberId,
                        ]
                    );
                    break;
                case 'find_member':
                    $message = __('회원정보를 찾을 수 없습니다.');
                    if ($request->post()->get('memberName')) {
                        $memberId = $request->post()->get('memberId', '');
                        $memberName = $request->post()->get('memberName', '');

                        $arrWhere = [
                            'memId',
                            'memNm',
                        ];
                        $arrData = [
                            $memberId,
                            $memberName
                        ];
                    } else {
                        $memberId = $request->post()->get('memberId', '');

                        $arrWhere = [
                            'memId'
                        ];
                        $arrData = [
                            $memberId
                        ];
                    }


                    $memberData = $memberService->getDataByTable(DB_MEMBER, $arrData, $arrWhere, 'memNo, memId, memNm, email, cellPhone, dupeinfo, rncheck, pakey, sleepFl, mallSno');
                    $sleepPolicy = ComponentUtils::getPolicy('member.sleep');
                    if($memberData['sleepFl'] == 'y'){
                        if($sleepPolicy['wakeType'] != 'normal') {
                            $session->set(
                                SESSION_WAKE_INFO, [
                                    'memId' => $memberId,
                                ]
                            );
                            $message = __('휴면회원 해제가 필요합니다.') . ' <a href="../member/wake.php" style="color:#ab3e55; text-decoration:underline; margin:0px;">'.__('휴면 해제하기') . '</a>';
                            throw new Exception($message);
                        } else {
                            $message = 'normal';
                            throw new Exception($message);
                            //throw new AlertBackException('onetwothreeCJ대한통운 설정 메뉴에서 대한통운 계약정보를 먼저 설정해주세요.');
                        }
                    }
                    if (($memberData['email'] == '' && $memberData['cellPhone'] == '')) {
                        $db = \App::getInstance('DB');
                        $fieldTypes = DBTableField::getFieldTypes(DBTableField::getFuncName(DB_MEMBER_SLEEP));
                        $arrBind = [
                            $fieldTypes['memId'],
                            $memberId,
                        ];
                        $sleepMember = $db->query_fetch('SELECT ms.memNo, ms.memId, ms.memNm, ms.email, ms.cellPhone, m.mallSno, m.sleepFl FROM ' . DB_MEMBER_SLEEP . ' AS ms JOIN ' . DB_MEMBER . ' AS m ON ms.memNo=m.memNo WHERE ms.memId=? LIMIT 1', $arrBind, false);
                        if (!empty($sleepMember)) {
                            $memberData = $sleepMember;
                        }
                    }
                    if ($session->has(SESSION_GLOBAL_MALL)) {
                        $mall = $session->get(SESSION_GLOBAL_MALL);
                        if ($mall['sno'] != $memberData['mallSno']) {
                            throw new Exception($message);
                        }
                    } elseif ($memberData['mallSno'] != DEFAULT_MALL_NUMBER) {
                        throw new Exception($message);
                    }
                    if (!strcasecmp($memberData['memId'], $memberId)) {
                        $session->set(Member::SESSION_USER_CERTIFICATION, $memberData);
                        $message = __('회원정보를 찾았습니다.');
                        $this->json($message);
                    } else {
                        throw new Exception($message);
                    }
                    break;
                default:
                    throw new AlertRedirectException(__('요청을 찾을 수 없습니다.'), Response::HTTP_NOT_FOUND, null, $request->getReferer());
                    break;
            }
        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (Exception $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if ($request->isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertOnlyException($e->getMessage());
            }
        }
    }
}

