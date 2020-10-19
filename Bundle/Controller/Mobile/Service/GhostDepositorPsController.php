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

namespace Bundle\Controller\Mobile\Service;
use Component\Design\SkinDesign;
use Component\Design\DesignPopup;
use Component\Database\DBTableField;
use Framework\Debug\Exception\AlertCloseException;
use Request;
use App;

class GhostDepositorPsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        try {

            $requestGetParams = Request::get()->all();
            $ghostDepositor = \App::load('\\Component\\Bankda\\BankdaGhostDepositor');
            $cfgGhostDepositor = $ghostDepositor -> getGhostDepositorPolicy();

            if ($cfgGhostDepositor['use'] != 1) {
                throw new AlertCloseException(__('사용할 수 없습니다.'));
                exit;
            }

            $requestGetParams['page'] = (isset($requestGetParams['page']) && $requestGetParams['page'] > 0) ? $requestGetParams['page'] : 1;
            $requestGetParams['pageNum'] = 20; // mobile 은 20개 고정

            if ($cfgGhostDepositor['bankdaUse'] == 1) { //뱅크다 중계서버디비

                $getData = $ghostDepositor->bankdaDbList($cfgGhostDepositor,$requestGetParams);
                $this->setData('total', $getData['total']);
                $loop = $getData['loop'];
                $total = $getData['total'];

            }else{ //미확인입금자 로컬디비

                $loop = $ghostDepositor->ghostDepositorDbList($cfgGhostDepositor,$requestGetParams);
                $page = \App::load('Component\\Page\\Page');
                $this->setData('total', $page->page['end']);
                $total = $page->page['end'];
            }

            $loop = $ghostDepositor->setHideDataProc($cfgGhostDepositor,$loop); // 입금은행, 입금금액 숨김 처리
            $return['data'] = $loop;
            $return['total'] = $total;

            echo json_encode($return);
            //$this->setData('loop', $loop);
            //$this->setData('ghostDepositorSame', $cfgGhostDepositor['ghostDepositorSame']);

        }
        catch(\Exception $e) {
            $this->alert($e->getMessage());
        }
        exit;
    }
}
