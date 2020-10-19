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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Component\Excel\ExcelForm;
use Framework\Utility\ArrayUtils;

/**
 * 레이어 엑셀 다운로드
 * @package Bundle\Controller\Admin\Share
 * @author  atomyang
 */
class LayerExcelController extends \Controller\Admin\Controller
{
    private $excelDownloadCode = [
        'order' => ExcelForm::EXCEL_DOWNLOAD_REASON_CODE_ORDER,
        'member' => ExcelForm::EXCEL_DOWNLOAD_REASON_CODE_MEMBER,
        'board' => ExcelForm::EXCEL_DOWNLOAD_REASON_CODE_BOARD,
        'plusreview' => ExcelForm::EXCEL_DOWNLOAD_REASON_CODE_BOARD
    ];

    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $getValue = $request->get()->toArray();

        try {
            $excelForm = \App::load('Component\\Excel\\ExcelForm');
            $_tmp = pathinfo($request->getParserReferer()->path);
            $menu = basename($_tmp['dirname']);
            if ($_tmp['filename'] === 'plus_review_list') {
                $menu = 'plusreview';
            }
            //게시판은 상세항목 선택할수 있음
            if ($menu != 'board' && $menu != 'plusreview') {
                $location = $_tmp['filename'];
            }

            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 고객 교환/반품/환불신청 관리 탭 페이지 변수
            if ($getValue['currentTabView']) {
                $this->setData('currentTabView', $getValue['currentTabView']);
                $location = $getValue['currentTabView'];
            }

            // 엑셀 다운로드 사유
            $downloadReasonList = gd_code($this->excelDownloadCode[$menu]);
            if ($downloadReasonList !== false) {
                $this->setData('reasonList', ArrayUtils::changeKeyValue($downloadReasonList));
                $this->setData('reasonUseFl', 'y');
            } else {
                $this->setData('reasonUseFl', 'n');
            }

            $this->setData('menu', $menu);
            $this->setData('location', $location);
            $this->setData('targetListForm', $getValue['targetListForm']);
            $this->setData('targetListSno', $getValue['targetListSno']);
            $this->setData('targetForm', $getValue['targetForm']);
            $this->setData('searchCount', $getValue['searchCount']);
            $this->setData('totalCount', $getValue['totalCount']);

            $this->setData('orderStateMode', $getValue['orderStateMode']);

            $this->setData('menuList', $excelForm->menuList);
            $this->setData('locationList', $excelForm->locationList[$menu]);

            $request->get()->set('menu', $menu);
            $request->get()->set('location', $location);

            $this->setData('formList', $excelForm->getExcelFormList());

            $this->setData('excelFileName', urlencode($excelForm->locationList[$menu][$location]));
            $this->setData('managerId', $session->get(\Component\Member\Manager::SESSION_MANAGER_LOGIN . '.managerId'));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/layer_excel.php');
        } catch (\Exception $e) {
            throw $e;
        }

    }
}
