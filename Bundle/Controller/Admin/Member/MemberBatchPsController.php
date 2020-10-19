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
namespace Bundle\Controller\Admin\Member;

use App;
use Component\Member\Group\Util as GroupUtil;
use Exception;
use Framework\Debug\Exception\LayerException;
use Logger;
use Message;
use Request;

/**
 * Class 회원일괄 처리
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberBatchPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        /**
         * @var  \Bundle\Component\Member\MemberAdmin $admin
         * @var  \Bundle\Component\Mileage\Mileage    $mileage
         * @var  \Bundle\Component\Deposit\Deposit    $deposit
         */
        $admin = App::load('\\Component\\Member\\MemberAdmin');
        $mileage = App::load('\\Component\\Mileage\\Mileage');
        $deposit = App::load('\\Component\\Deposit\\Deposit');
        try {
            $mode = Request::post()->get('mode');
            $post = Request::post()->toArray();
            $searchJson = Request::post()->get('searchJson');
            $memberNo = Request::post()->get("chk");
            $groupSno = Request::post()->get('newGroupSno');

            switch ($mode) {
                case 'add_deposit':
                    $result = $deposit->addDeposit($post);
                    $this->json(
                        [
                            $result,
                        ]
                    );
                    break;
                case 'add_deposit_all':
                    $result = $deposit->addDepositAll($post, $searchJson);
                    $this->json(
                        [
                            $result,
                        ]
                    );
                    break;
                case 'remove_deposit':
                    $result = $deposit->removeDeposit($post);
                    $resultStorage = $deposit->getResultStorage();
                    $this->json(
                        [
                            $result,
                            $resultStorage->toArray(),
                        ]
                    );
                    break;
                case 'remove_deposit_all':
                    $result = $deposit->removeDepositAll($post, $searchJson);
                    $resultStorage = $deposit->getResultStorage();
                    $this->json(
                        [
                            $result,
                            $resultStorage->toArray(),
                        ]
                    );
                    break;
                case 'add_mileage':
                    $result = $mileage->addMileage($post);
                    $this->json(
                        [
                            $result,
                        ]
                    );
                    break;
                case 'all_add_mileage':
                    $result = $mileage->allAddMileage($post, $searchJson);
                    $this->json(
                        [
                            $result,
                        ]
                    );
                    break;
                case 'remove_mileage':
                    $result = $mileage->removeMileage($post);
                    $resultStorage = $mileage->getResultStorage();
                    \Logger::debug(__METHOD__, $resultStorage);
                    $this->json(
                        [
                            $result,
                            $resultStorage->toArray(),
                        ]
                    );
                    break;
                case 'all_remove_mileage':
                    $result = $mileage->allRemoveMileage($post, $searchJson);
                    $resultStorage = $mileage->getResultStorage();
                    $this->json(
                        [
                            $result,
                            $resultStorage->toArray(),
                        ]
                    );
                    break;

                case 'reasonCd_modify_mileage':
                    $result = $mileage->reasonCdModifyMileage($post);
                    $this->json(
                        [
                            $result,
                        ]
                    );
                    break;
                case 'reasonCd_modify_deposit':
                    $result = $deposit->reasonCdModifyDeposit($post);
                    $this->json(
                        [
                            $result,
                        ]
                    );
                    break;
                case 'apply_group_grade':
                    $passwordCheckFl = gd_isset(Request::post()->get('passwordCheckFl'), 'y');
                    if($passwordCheckFl != 'n') {
                        $smsSender = \App::load(\Component\Sms\SmsSender::class);
                        $smsSender->validPassword(Request::post()->get('password'));
                    }
                    $result = $admin->applyGroupGradeByMemberNo($groupSno, $memberNo);
                    $beforeMembers = $admin->getBeforeMembersByGroupBatch();
                    $members = $admin->getAfterMembersByGroupBatch();
                    $admin->writeGroupChangeHistory($beforeMembers, $members);
                    $admin->sendGroupChangeEmail($members);
                    $admin->sendGroupChangeSms($members, $passwordCheckFl);
                    $groups = GroupUtil::getGroupName();
                    $this->json(sprintf(__('%s명 중 %s명이 %s 등급으로 변경되었습니다.'), $result['total'], $result['success'], $groups[$result['groupSno']]));
                    break;
                case 'all_apply_group_grade':
                    $passwordCheckFl = gd_isset(Request::post()->get('passwordCheckFl'), 'y');
                    if($passwordCheckFl != 'n') {
                        $smsSender = \App::load(\Component\Sms\SmsSender::class);
                        $smsSender->validPassword(Request::post()->get('password'));
                    }
                    $result = $admin->allApplyGroupGradeByMemberNo($groupSno, $searchJson);
                    $beforeMembers = $admin->getBeforeMembersByGroupBatch();
                    $members = $admin->getAfterMembersByGroupBatch();
                    $admin->writeGroupChangeHistory($beforeMembers, $members);
                    $admin->sendGroupChangeEmail($members);
                    $admin->sendGroupChangeSms($members, $passwordCheckFl);
                    $groups = GroupUtil::getGroupName();
                    $this->json(sprintf(__('%s명 중 %s명이 %s 등급으로 변경되었습니다.'), $result['total'], $result['success'], $groups[$result['groupSno']]));
                    break;
                case 'approval_join':
                    $result = $admin->approvalJoinByMemberNo($memberNo);
                    $this->json(sprintf(__('%s명 중 %s명이 승인 상태로 변경되었습니다.'), $result['total'], $result['success']));
                    break;
                case 'disapproval_join':
                    $result = $admin->disapprovalJoinByMemberNo($memberNo);
                    $this->json(sprintf(__('%s명 중 %s명이 미승인 상태로 변경되었습니다.'), $result['total'], $result['success']));
                    break;
                case 'all_approval_join':
                    $result = $admin->allApprovalJoinByMemberNo($searchJson);
                    $this->json(sprintf(__('%s명 중 %s명이 승인 상태로 변경되었습니다.'), $result['total'], $result['success']));
                    break;
                case 'all_disapproval_join':
                    $result = $admin->allDisapprovalJoinByMemberNo($searchJson);
                    $this->json(sprintf(__('%s명 중 %s명이 미승인 상태로 변경되었습니다.'), $result['total'], $result['success']));
                    break;
                default:
                    throw new Exception(__('요청을 처리할 페이지를 찾을 수 없습니다.') . ', ' . $mode, 404);
                    break;
            }
        } catch (\Throwable $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if (Request::isAjax()) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
