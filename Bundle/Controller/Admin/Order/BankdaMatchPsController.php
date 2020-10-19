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

use Component\Bankda\Bankda;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\HttpException;
use Request;

class BankdaMatchPsController extends \Controller\Admin\Controller
{

    /**
     * 입금조회/실시간입금확인 처리
     *
     * @author sunny
     * @version 1.0
     * @since 1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        try {
            switch (Request::request()->get('mode')) {
                // 통장입금내역 조회
                case 'accountList':
                    $bk = new Bankda();
                    $res = $bk->accountList(Request::get()->toArray());
                    echo $res;
                    exit();
                    break;
                // 실시간 입금확인실행(비교,Matching)
                case 'bankMatching':
                    $bk = new Bankda();
                    $res = $bk->bankMatching(Request::get()->toArray());
                    echo $res;
                    exit();
                    break;
                // 입금내역수정
                case 'bankUpdate':
                    $bk = new Bankda();
                    $res = $bk->bankUpdate(Request::get()->toArray());
                    echo $res;
                    exit();
                    break;

                // 수동매칭 주문리스트 조회
                case 'bankManualOrderList':
                    $bk = new Bankda();
                    $getValue = Request::get()->toArray();
                    $res = $bk->bankManualOrderList($getValue);
                    if($res) {
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    }
                    exit();
                    break;

                // 수동매칭 관리자 조회
                case 'bankManualManagerInfo':
                    $bk = new Bankda();
                    $getValue = Request::post()->toArray();
                    $res = $bk->bankManualManagerInfo($getValue['bankdaData']);
                    if($res) {
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    }
                    exit();
                    break;

                // 입금내역 메모 조회
                case 'bankAdminMemoArraySelect':
                    $bk = new Bankda();
                    $getValue = Request::post()->toArray();
                    $res = $bk->bankAdminMemoArraySelect($getValue['bankdaData']);
                    if($res) {
                        echo json_encode($res, JSON_UNESCAPED_UNICODE);
                    }
                    exit();
                    break;
                // 입금내역 메모 삽입 / 수정
                case 'bankdaAdminMemo':
                    $bk = new Bankda();
                    $res = $bk->bankAdminMemoInsert(Request::post()->toArray());
                    $this->layerNotReload(__('저장이 완료되었습니다.'));
                    exit();
                    break;
            }
        } catch (Except $e) {
            $e->actLog();
            throw new HttpException($e->ectMessage, 500);
        }
    }
}
