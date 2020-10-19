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
use Globals;
use Request;
use App;

class GhostDepositorController extends \Controller\Mobile\Controller
{

    /**
     * 미확인입금자 레이어 Class
     *
     * @author    mj2
     * @copyright ⓒ 2016, NHN godo: Corp.
     */

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

            $requestGetParams['page'] = 1;
            $requestGetParams['pageNum'] = 20; // mobile 은 20개 고정

            if ($cfgGhostDepositor['bankdaUse'] == 1) { //뱅크다 중계서버디비

                $getData = $ghostDepositor->bankdaDbList($cfgGhostDepositor,$requestGetParams);
                $this->setData('total', $getData['total']);
                $loop = $getData['loop'];

            }else{ //미확인입금자 로컬디비

                $loop = $ghostDepositor->ghostDepositorDbList($cfgGhostDepositor,$requestGetParams);
                $page = \App::load('Component\\Page\\Page');
                $this->setData('total', $page->page['end']);
            }

            $this->setData('loop', $loop);
            $this->setData('searchDate', $requestGetParams['depositDate']);
            $this->setData('searchName', $requestGetParams['ghostDepositor']);
            $this->setData('req', gd_htmlspecialchars($requestGetParams));


            $locale = \Globals::get('gGlobal.locale');
            $this->setData('locale', $locale);

            if($cfgGhostDepositor['mobileDesignSkinType'] == 'select'){ // pc 동일적용
                $this->getView()->setPageName("service/ghost_depositor.php");
            }else {
                $this->getView()->setDataName('ghost_depositor/tpl/mobileCustom');
            }
            $this->setData('mobileDesignSkin', $cfgGhostDepositor['mobileDesignSkin']);

        }
        catch(\Exception $e) {
            $this->alert($e->getMessage());
        }
    }
}
