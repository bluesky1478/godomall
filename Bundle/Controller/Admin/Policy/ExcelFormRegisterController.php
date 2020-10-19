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
namespace Bundle\Controller\Admin\Policy;


use Globals;
use Request;
/**
 * 엑셀 다운로드 양식관리
 * @author atomyang
 */
class ExcelFormRegisterController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        try {
            $this->addScript([
                'bootstrap/bootstrap-table.js',
                'jquery/jquery.tablednd.js',
                'bootstrap/bootstrap-table-reorder-rows.js',
            ]);

            // --- 메뉴 설정
            if (Request::get()->get('sno') > 0) {
                $this->callMenu('policy', 'management', 'excelFormModify');
            } else {
                $this->callMenu('policy', 'management', 'excelFormRegister');
            }

            $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
            $data = $excelForm->getDataExcelForm(Request::get()->get('sno'));
            $menuList = $excelForm->menuList;
            if (empty($menuList['plusreview']) === false) {
                unset($menuList['plusreview']);
            }

            $this->setData('data', $data['data']);
            $this->setData('menuList', $menuList);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('policy/excel_form_register.php');

        } catch (\Exception $e) {
            throw $e;
        }

    }
}
