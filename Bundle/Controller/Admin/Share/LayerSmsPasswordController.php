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


class LayerSmsPasswordController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $displayInfo = [
            'title'      => 'SMS 인증번호',
            'useCaptcha' => false,
            'retry'      => $session->get('captchaRetry', 1),
        ];
        if ($request->get()->get('mode', 'input') === 'change') {
            $displayInfo['useCaptcha'] = true;
        }
        $this->setData('displayInfo', $displayInfo);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
