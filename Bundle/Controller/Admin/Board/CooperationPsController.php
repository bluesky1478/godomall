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
namespace Bundle\Controller\Admin\Board;

use Framework\Debug\Exception\Except;
use Message;
use Request;
use Framework\Debug\Exception\LayerException;

class CooperationPsController extends \Controller\Admin\Controller
{

    /**
     * Description
     * @throws Except
     */
    public function index()
    {

        /**
         * 광고제휴문의 처리
         *
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 모듈 호출

        // --- 광고제휴문의 설정
        $coop = \App::load('\\Component\\Board\\Cooperation');

        switch (Request::post()->get('mode')) {
            case 'modify':
                try {
                    ob_start();
                    $coop->modifyCooperationData(Request::post()->toArray());
                    if ($out = ob_get_clean()) {
                        throw new Except(__('처리중에 오류가 발생하여 실패되었습니다.'), $out);
                    }
                    throw new LayerException();
                } catch (Except $e) {
                    $e->actLog();
                    $item = ($e->ectMessage ? ' - ' . str_replace(array("\n", "\r"), ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;
            case 'delete':
                try {
                    ob_start();
                    foreach (Request::post()->get('chk') as $sno) {
                        $coop->deleteCooperationData($sno);
                    }
                    if ($out = ob_get_clean()) {
                        throw new Except(__('처리중에 오류가 발생하여 실패되었습니다.'), $out);
                    }
                    throw new LayerException(__('삭제 되었습니다.'));
                } catch (Except $e) {
                    $e->actLog();
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;
            case 'mailsend':
                try {
                    ob_start();
                    $coop->sendCooperationMail(Request::get()->get('sno'));
                    if ($out = ob_get_clean()) {
                        throw new Except(__('처리중에 오류가 발생하여 실패되었습니다.'), $out);
                    }
                    throw new LayerException(__('메일이 전송되었습니다.'));
                } catch (Except $e) {
                    $e->actLog();
                    $item = ($e->ectMessage ? ' - ' . str_replace("\n", ' - ', $e->ectMessage) : '');
                    throw new LayerException(__('메일 전송이 실패되었습니다.') . $item, null, null, null, 0);
                }
                break;
        }
    }
}
