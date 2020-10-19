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

use Component\Mall\Mall;
use Component\OutSideScript\OutSideScriptAdmin;
use Framework\Debug\Exception\LayerException;

/**
 * Class OutSideScriptListController
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class OutSideScriptListController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('policy', 'basic', 'outScriptList');

        // --- 관리자 데이터
        try {
            $outSideScriptAdmin = new OutSideScriptAdmin();
            $getData = $outSideScriptAdmin->getOutSideScript();
            $this->setData('data', $getData);

            $mall = new Mall();
            $mallList = $mall->getListByUseMall();
            if (count($mallList) > 1) {
                $this->setData('mallCnt', count($mallList));
                $this->setData('mallList', $mallList);
            }
        }
        catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
