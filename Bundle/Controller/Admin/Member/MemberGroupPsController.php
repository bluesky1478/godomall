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

use Component\Member\Group\GroupService;
use Component\Member\Group\Util;
use Component\Member\MemberGroup;
use Component\Policy\GroupPolicy;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Http\Response;
use Framework\Utility\ComponentUtils;
use Logger;
use Message;
use Request;

/**
 * Class 관리자-회원등급 처리 컨트롤러
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberGroupPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        /**
         * @var \Bundle\Component\Member\MemberGroup $memberGroup
         * @var \Bundle\Component\Policy\Policy      $policy
         */
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');
        $groupService = new GroupService();
        try {
            $requestPostParams = Request::post()->all();
            switch (Request::request()->get('mode')) {
                case MemberGroup::MODE_REGISTER:
                    $groupService->saveGroup();
                    $this->layer(__('저장이 완료되었습니다.'), 'parent.window.location=\'../member/member_group_modify.php?sno=' . Request::post()->get('sno') . '\'');
                    break;
                case MemberGroup::MODE_MODIFY:
                    $groupService->saveGroup();
                    $this->layer(__('저장이 완료되었습니다.'), 'parent.window.location=\'../member/member_group_modify.php?sno=' . Request::post()->get('sno') . '\'');
                    break;
                case 'delete':
                    foreach ($requestPostParams['chk'] as $sno) {
                        $memberGroup->deleteBySno($sno);
                    }
                    $this->json(__('삭제 되었습니다.'));
                    break;
                case 'appraisal':
                    /** @var \Bundle\Component\Member\Group\Appraisal $appraisal */

                    $appraisal = \App::load('\\Component\\Member\\Group\\Appraisal');
                    $appraisal->setRemoteAddress(Request::getRemoteAddress());
                    ini_set('max_execution_time', 0);
                    $appraisal->appraisalGroupBySearch();
                    $appraisal->appraisalMailSend();
                    if($appraisal->policy['couponConditionComplete'] == 'y' && $appraisal->policy['couponConditionCompleteChange'] != 'y') {
                        $appraisal->applyCouponAllMember();
                    }
                    $smsAuto = \App::load('\\Component\\Sms\\SmsAuto');
                    $smsAuto->reserveNotify(date("Y-m-d H:i:s", strtotime("+10 minutes")));
                    $this->json(__('평가가 완료되었습니다.'));
                    break;
                case 'appraisal_rule':
                    $groupPolicy = new GroupPolicy();
                    $groupPolicy->saveAppraisal();
                    $groupService->saveGroupStandard();
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'overlapGroupNm':
                    if ($groupService->checkOverlapGroupName()) {
                        throw new Exception(sprintf(__('%s는 이미 등록된 등급이름입니다'), Request::post()->get('groupNm')));
                    }
                    $this->json(__('사용가능한 등급이름입니다.'));
                    break;
                case 'modifyLabel':
                    $groupPolicy = new GroupPolicy();
                    $groupPolicy->saveGroupLabel();
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'modifyCoupon':
                    $groupPolicy = new GroupPolicy();
                    $groupPolicy->saveCouponCondition();
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'sort':
                    $snoArray = explode(',', $requestPostParams['snoArray']);
                    $cnt = count($snoArray);
                    foreach ($snoArray as $idx => $sno) {
                        $arrData = [];
                        $arrData['sno'] = $sno;
                        $arrData['groupSort'] = $cnt - $idx;
                        $memberGroup->modifySort($arrData);
                    }
                    $this->json(__('등급순서가 변경되었습니다.'));
                    break;
                case 'groupIconHttpPath':
                    $this->json(Util::getGroupIconHttpPath());
                    break;
                case 'groupImageHttpPath':
                    $this->json(Util::getGroupImageHttpPath());
                    break;
                case 'getGroupCoupon':
                    $group = $memberGroup->getGroupViewToArray(Request::request()->get('sno'));
                    $couponNo = explode(INT_DIVISION, $group['groupCoupon']);
                    $this->json(['result' => 'success', 'couponNo' => $couponNo]);
                    break;
                default:
                    throw new AlertRedirectException(__('해당 요청을 수행할 수 없습니다.'), Response::HTTP_NOT_FOUND, null, Request::getReferer());
                    break;
            }
        } catch (Exception $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if (Request::isAjax()) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new LayerNotReloadException($e->getMessage());
            }
        }
    }
}
