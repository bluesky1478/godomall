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

use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

/**
 * 회원정보 수정 이벤트 처리
 *
 * @author haky <haky2@godo.co.kr>
 */
class MemberModifyEventPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postValue = Request::post()->xss()->toArray();
        $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');

        try {
            switch ($postValue['mode']) {
                // 등록 및 수정
                case 'register':
                    $modifyEvent->registerMemberModifyEvent($postValue);
                    $this->layer(__('저장 되었습니다.'), 'top.location.href="../member/member_modify_event_list.php";');
                    break;
                case 'modify':
                    $modifyEvent->modifyMemberModifyEvent($postValue);
                    $this->layer(__('저장 되었습니다.'), 'top.location.href="../member/member_modify_event_list.php";');
                    break;
                case 'deleteEvent':
                    $modifyEvent->deleteMemberModifyEvent($postValue['eventListCheck']);
                    $this->json(__('삭제 되었습니다.'));
                    break;
                case 'closeEvent':
                    $modifyEvent->closeMemberModifyEvent($postValue['eventListCheck']);
                    $this->json(__('종료 되었습니다.'));
                    break;
                case 'deleteResult':
                    $modifyEvent->deleteMemberModifyEventResult($postValue['eventResultCheck']);
                    $this->json(__('삭제 되었습니다.'));
                    break;
                default:
                    break;
            }
            exit();
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
    }
}