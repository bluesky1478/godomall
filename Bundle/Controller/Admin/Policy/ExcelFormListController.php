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
class ExcelFormListController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'management', 'excelFormList');

        try {
            $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
            Request::get()->set('displayFl','y');
            $getData = $excelForm->getExcelFormListForAdmin();
            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            $menuList = $excelForm->menuList;
            $locationList = $excelForm->locationList;
            if (empty($menuList['plusreview']) === false) {
                $locationList['board'] = array_merge($locationList['board'], $locationList['plusreview']);
                unset($menuList['plusreview'], $locationList['plusreview']);
            }
            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('page', $page);
            $this->setData('menuList', $menuList);
            $this->setData('locationList', $locationList);

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('policy/excel_form_list.php');

        } catch (\Exception $e) {
            throw $e;
        }

    }
}
