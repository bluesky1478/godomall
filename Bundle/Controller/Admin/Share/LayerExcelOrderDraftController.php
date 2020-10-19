<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Component\Excel\ExcelForm;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\ArrayUtils;
use Session;

class LayerExcelOrderDraftController extends \Controller\Admin\Controller
{
    public function index()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_ORDERDRAFTEXCEL) === false) { //플러스샵 설치 유무
            throw new LayerException('플러스샵 설치가 필요합니다.');
        }

        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $getValue = $request->get()->toArray();

        try {
            $excelForm = \App::load('Component\\Excel\\ExcelForm');

            if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.orderExcelDown') != 'y') {
                throw new LayerException(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
            }

            $menu = 'orderDraft';
            $location = 'order_list_pay';
            $downloadReasonList = gd_code(ExcelForm::EXCEL_DOWNLOAD_REASON_CODE_ORDER);
            $this->getView()->setDefine('layout', 'layout_layer.php');

            $this->setData('menu', 'orderDraft');
            $this->setData('location', $location);
            $this->setData('targetListForm', $getValue['targetListForm']);
            $this->setData('targetListSno', $getValue['targetListSno']);
            $this->setData('targetForm', $getValue['targetForm']);
            $this->setData('searchCount', $getValue['searchCount']);
            $this->setData('totalCount', $getValue['totalCount']);

            $this->setData('orderStateMode', $getValue['orderStateMode']);

            $request->get()->set('menu', $menu);
            $request->get()->set('location', $location);

            $formList = $excelForm->getExcelFormList();
            $this->setData('formList', $formList);
            $this->setData('excelFileName', urlencode($excelForm->locationList[$menu][$location]));
            $this->setData('managerId', $session->get(\Component\Member\Manager::SESSION_MANAGER_LOGIN . '.managerId'));
            $this->setData('reasonList', ArrayUtils::changeKeyValue($downloadReasonList));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_excel_order_draft.php');
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
