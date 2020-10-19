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
use App;

/**
 * 무이자 할부 설정 레이어창
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerPgNointerestPeroidController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 모듈 호출
        $pgCodeConfig = App::getConfig('payment.pg');
        $periodCodeConfig = App::getConfig('payment.installment');

        // --- 상품 설정
        $data = $pgCodeConfig->getPgNointerest()[Request::post()->get('pgName')];

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData('pgName', Request::post()->get('pgName'));
        $this->setData('data', $data);
        $this->setData('pgPeriod', $periodCodeConfig->getPeriod());
    }
}
