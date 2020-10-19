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

namespace Bundle\Controller\Front\Service;
use Component\Design\SkinDesign;
use Component\Design\DesignPopup;
use Component\Database\DBTableField;
use Framework\Debug\Exception\AlertCloseException;
use Globals;
use Request;
use Cookie;
use App;

class PopupGhostDepositorController extends \Controller\Front\Controller
{

    /**
     * 미확인입금자 팝업 Class
     *
     * @author    cjb3333
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

            $requestGetParams['page'] = (isset($requestGetParams['page']) && $requestGetParams['page'] > 0) ? $requestGetParams['page'] : 1;

            if ($cfgGhostDepositor['bankdaUse'] == 1) { //뱅크다 중계서버디비

                $getData = $ghostDepositor->bankdaDbList($cfgGhostDepositor,$requestGetParams);
                $this->setData('paging', $getData['paging']);
                $loop = $getData['loop'];

            }else{ //미확인입금자 로컬디비

                $loop = $ghostDepositor->ghostDepositorDbList($cfgGhostDepositor,$requestGetParams);
                $page = \App::load('Component\\Page\\Page');
                $this->setData('page', $page);
            }

            $loop = $ghostDepositor->setHideDataProc($cfgGhostDepositor,$loop); // 입금은행, 입금금액 숨김 처리

            $this->setData('loop', $loop);
            $this->setData('bankdaUse', $cfgGhostDepositor['bankdaUse']);
            $this->setData('designSkin', $cfgGhostDepositor['designSkin']);
            $this->setData('searchDate', $requestGetParams['depositDate']);
            $this->setData('searchName', $requestGetParams['ghostDepositor']);

            $locale = \Globals::get('gGlobal.locale');
            // 날짜 픽커를 위한 스크립트와 스타일 호출
            $this->addCss([
                'plugins/bootstrap-datetimepicker.min.css',
                'plugins/bootstrap-datetimepicker-standalone.css',
            ]);
            $this->addScript([
                'moment/moment.js',
                'moment/locale/' . $locale . '.js',
                'jquery/datetimepicker/bootstrap-datetimepicker.min.js',
            ]);

            $this->getView()->setDefine('header', 'outline/_share_header.html');
            $this->getView()->setDefine('footer', 'outline/_share_footer.html');

            if($cfgGhostDepositor['designSkinType'] == 'select'){
               $this->getView()->setPageName("service/ghost_depositor.php");
            }else{
               $this->getView()->setDataName('ghost_depositor/tpl/custom');
            }

        }
        catch(\Exception $e) {
            $this->alert($e->getMessage());
        }
    }
}
