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
class OrderDraftExcelListController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
            Request::get()->set('displayFl','y');
            Request::get()->set('menu','orderDraft');
            $getData = $excelForm->getExcelFormListForAdmin('y');
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('page', $page);

            $this->setData('menuList', 'orderDraft');
            $this->setData('locationList', 'order_list_pay');

            $this->getView()->setDefine('layout', 'layout_blank_noiframe.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/order_draft_excel_list.php');

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
