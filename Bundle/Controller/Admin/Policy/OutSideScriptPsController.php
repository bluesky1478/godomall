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

use Component\OutSideScript\OutSideScriptAdmin;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;
use Session;

/**
 * Class OutSideScriptPsController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class OutSideScriptPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();
        $postValue['managerNo'] = Session::get('manager.sno');
        $postValue['managerNm'] = Session::get('manager.managerNm');
        $postValue['managerId'] = Session::get('manager.managerId');
        $mallSno = gd_isset($postValue['mallSno'], 1);

        $outSideScriptAdmin = new OutSideScriptAdmin();
        // 각 모드에 따른 처리
        switch ($postValue['mode']) {
            case 'insert':
            case 'modify':
                try {
                    $returnNo = $outSideScriptAdmin->setOutSideScript($postValue);
                    $this->layer(__('저장 되었습니다.'), 'parent.location.replace("out_side_script_register.php?' . ($mallSno > 1 ? 'mallSno=' . $mallSno . '&' : '') . 'outSideScriptNo=' . $returnNo . '")');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
            case 'delete':
                try {
                    $outSideScriptAdmin->setOutSideScriptDelete($postValue['chk'], $mallSno);
                    $this->layer(__('삭제 되었습니다.'), 'parent.location.replace("out_side_script_list.php' . ($mallSno > 1 ? '?mallSno=' . $mallSno : '') . '")');
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;
        }
        exit;
    }
}
