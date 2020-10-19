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

use Globals;
use Request;
use Session;
use Component\Member\Manager;

class OrderDraftExcelRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $this->addScript([
                'bootstrap/bootstrap-table.js',
                'jquery/jquery.tablednd.js',
                'bootstrap/bootstrap-table-reorder-rows.js',
            ]);

            $this->getView()->setDefine('layout', 'layout_blank.php');

            $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
            $data = $excelForm->getDataExcelForm(Request::get()->get('sno'));

            //공급사 별 추가항목 가져오기
            if(Manager::isProvider()) {
                $scmNo = Session::get('manager.scmNo');
            } else {
                $scmNo = 1;
            }
            $addFields = $excelForm->getExcelAddFieldsByScm($scmNo);
            $this->setData('data', $data['data']);
            $this->setData('addFields', $addFields);
            $this->setData('menuList',$excelForm->menuList);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/order_draft_excel_register.php');

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
