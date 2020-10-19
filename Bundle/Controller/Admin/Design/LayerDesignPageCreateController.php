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

namespace Bundle\Controller\Admin\Design;

use Component\Design\SkinDesign;
use Globals;
use Request;

/**
 * 디자인 페이지 추가하기
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerDesignPageCreateController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        // skinType 설정
        if (Request::get()->has('skinType') === false) {
            $skinType = 'front';
        } else {
            $skinType = Request::get()->get('skinType');
        }

        // GET 파라메터
        $getValue = Request::get()->toArray();

        try {
            //--- SkinDesign 정의
            $skinDesign = new SkinDesign($skinType);
            $skinDesign->setSkin(Globals::get('gSkin.' . $skinDesign->skinType . 'SkinWork'));
        } catch (\Exception $e) {
            echo($e->getMessage());
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', 'design/' . Request::getFileUri());

        $this->setData('skinType', $skinDesign->skinType);
        $this->setData('dirPath', $getValue['dirPath']);
        $this->setData('dirText', $getValue['dirText']);
        $this->setData('saveMode', $getValue['saveMode']);
    }
}
