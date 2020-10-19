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
namespace Bundle\Controller\Admin\Order;
use Component\File\StorageHandler;
use Message;
use Request;


class BankdaNoMatchPsController extends \Controller\Admin\Controller
{

    /**
     * 미확인 입금자 설정 관리 저장 처리
     *
     * @author  cjb3333
     * @copyright ⓒ 2016, NHN godo: Corp.

     */
    public function index()
    {
        // --- POST 값 처리
        $requestPostParams = Request::post()->all();
        $ghostDepositor = \App::load('\\Component\\Bankda\\BankdaGhostDepositor');

        switch ($requestPostParams['mode']) {

            // --- 미확인 입금자 등록
            case 'insert':

            $ghostDepositor->registerGhostDepositor($requestPostParams);
            $rs['result'] = true;
            $this->json($rs);

            break;

            // --- 미확인 입금자 삭제
            case 'delete':

            $chk = Request::post()->get('chk');

            foreach($chk as $delSno) {
                $ghostDepositor->deleteGhostDepositor($delSno);
            }
            $rs['result'] = true;
            $this->json($rs);

            break;

            // --- 미확인 입금자 리스팅
            case 'load':

            $res = $ghostDepositor->loadGhostDepositor($requestPostParams);
            $this->json($res);

            break;

            // --- 미확인 입금자 리스트 관리 설정
            case 'config':

            $res = $ghostDepositor->configGhostDepositor($requestPostParams);
            if($res) $this->layer(__('저장이 완료되었습니다.'));

            case 'download':

            $res = $ghostDepositor->loadGhostDepositor($requestPostParams);
            $ghostDepositor->downloadGhostDepositor($res['body']);

            default:
            break;
        }

        exit;
    }
}
