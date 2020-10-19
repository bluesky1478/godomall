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

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertCloseException;

/**
 * Class 관리자-회원-메일 관리-처리 컨트롤러
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MailPsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertCloseException
     */
    public function index()
    {
        $request = \App::getInstance('request');
        try {
            /**
             * @var  \Bundle\Component\Mail\Pmail     $pMail
             * @var  \Bundle\Component\Mail\MailAdmin $mailAdmin
             * @var  \Bundle\Component\Mail\MailLog   $mailLog
             */
            $mailAdmin = \App::load('\\Component\\Mail\\MailAdmin');
            $mailLog = \App::load('\\Component\\Mail\\MailLog');
            $pMail = \App::load('\\Component\\Mail\\Pmail');

            $requestPostParams = $request->post()->all();

            switch ($requestPostParams['mode']) {
                case 'configPmail':
                    $pMail->saveConfigPmail($requestPostParams);
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'saveAutoConfig':
                    $mailAutoPolicy = \App::load('Component\\Policy\\MailAutoPolicy');
                    $mailAutoPolicy->saveAutoMailConfig($requestPostParams);
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'addSearchResult':
                    // 검색조건에 따른 대상회원 선택
                    $addMemberList = $mailAdmin->getAddMemberListBySearchResult($requestPostParams);
                    $this->json($addMemberList);
                    break;
                case 'mailSend':
                    // 메일 발송
                    $result = $mailAdmin->mailSend($requestPostParams);
                    $this->json(sprintf(__('총 %s건 중 %s 성공, %s 실패하였습니다.'), $result['total'], $result['success'], $result['fail']));
                    break;
                case 'deleteMailLog':
                    $chk = $request->post()->get('chk', []);
                    $mailLog->deleteMailLog($chk);
                    $this->json(__('삭제 되었습니다.'));
                    break;
                case 'viewMailLog':
                    $chk = $request->post()->get('chk');
                    $mailContents = $mailLog->getMailLog($chk);
                    $this->json($mailContents);
                    break;
                case 'stibeeSend':
                    $stibee = \App::load('\\Component\\Mail\\MailStibee');
                    $stibee->syncCurl($requestPostParams);
                    $requestPostParams['type'] = 'debug';
                    $stibee->getMemberData($requestPostParams);
                    break;
                case 'stibeeSendRemotePost':
                    $stibee = \App::load('\\Component\\Mail\\MailStibee');
                    $stibee->sync($requestPostParams);
                    break;
                case 'checkApproval':
                    $configPmail = $pMail->getMailConfigPmail();
                    if($configPmail['approvalFl'] == 'y') {
                        $this->json(true);
                    } else {
                        $this->json(false);
                    }
                    break;
                default:
                    throw new \Exception("Not Found Mode");
                    break;
            }
        } catch (\Exception $e) {
            if ($request->isAjax()) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertBackException($e->getMessage());
            }
        }
    }
}
