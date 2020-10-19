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

use Framework\Debug\Exception\Except;
use Component\Godo\GodoGongjiServerApi;

/**
 * 자동입금확인 안내페이지
 *
 * @author    cjb333
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class BankdaIntroController extends \Controller\Admin\Controller
{
    public function index()
    {
        try{
            // 고도 공지서버 모듈 호출
            $godoApi = new GodoGongjiServerApi();
            $contents = $godoApi->setGodoRemotePage('bankda_intro');
            echo $contents;

        } catch (Except $e) {
            echo($e->ectMessage);
        }
        exit;
    }
}
