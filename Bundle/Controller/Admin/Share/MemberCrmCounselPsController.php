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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Request;
use Session;
use Logger;

/**
 * Class 관리자-회원CRM 상담내역 처리
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class MemberCrmCounselPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Component\Member\Counsel $counsel */
        $counsel = \App::load('\\Component\\Member\\Counsel');
        try {
            switch (Request::post()->get('mode')) {
                case 'list' :
                    // 상담 내역 리스트
                    $data = $counsel->getCounselList(gd_isset(Request::post()->get('memNo')), gd_isset(Request::post()->get('page'), 1));
                    $this->json($data);
                    break;
                case 'register':
                    Logger::debug(__METHOD__);
                    Request::post()->set('managerNo', Session::get('manager.sno'));
                    $requestPostParams = Request::post()->all();
                    // 상담 내역 저장
                    $counsel->save($requestPostParams);
                    $this->json('저장되었습니다.');
                    break;
                case 'update':
                    Logger::debug(__METHOD__);
                    Request::post()->set('managerNo', Session::get('manager.sno'));
                    $requestPostParams = Request::post()->all();
                    // 상담 내역 수정
                    $counsel->update($requestPostParams);
                    $this->json('수정되었습니다.');
                    break;
                case 'delete':
                    Logger::debug(__METHOD__);
                    Request::post()->set('managerNo', Session::get('manager.sno'));
                    $requestPostParams = Request::post()->all();
                    // 상담 내역 삭제
                    $counsel->delete($requestPostParams);
                    $this->json('삭제되었습니다.');
                    break;
                default:
                    throw new Exception(__('잘못된 요청입니다.'));
                    break;
            }
        } catch (Exception $e) {
            if (Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertOnlyException($e->getMessage());
            }
        }
    }
}
