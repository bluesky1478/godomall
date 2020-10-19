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
namespace Bundle\Controller\Admin\Order;

use App;
use Exception;

/**
 * 미확인 입금자 리스트 관리 컨트롤러
 *
 * @author  cjb3333
 * @copyright ⓒ 2016, NHN godo: Corp.

 */

class BankdaNoMatchController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('order', 'bankda', 'nomatch');

        // --- 페이지 데이터
        try {

            $ghostDepositor = \App::load('\\Component\\Bankda\\BankdaGhostDepositor');
            $cfgGhostDepositor = $ghostDepositor -> getGhostDepositorPolicy();
            $bankda = App::load('\\Component\\Bankda\\Bankda');
            $rBank = $bankda -> getUseBank();

        } catch (Exception $e) {
            echo($e->ectMessage);
        }

        // --- 관리자 디자인 템플릿
        $this->setData('search', gd_isset($search));
        $this->setData('rBank', gd_isset($rBank));
        $this->setData('cfgGhostDepositor', gd_isset($cfgGhostDepositor));
        $this->setData('selected', $selected);
    }
}
