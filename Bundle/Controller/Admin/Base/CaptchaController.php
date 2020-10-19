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

namespace Bundle\Controller\Admin\Base;

use Vendor\Captcha\Captcha as CaptchaModule;


class CaptchaController extends \Controller\Admin\Controller
{

    public function index()
    {
        try {
            $captcha = new CaptchaModule();
            $request = \App::getInstance('request');
            $getData = [];
            if ($request->get()->get('bgColor')) {
                $getData['bdCaptchaBgClr'] = $request->get()->get('bgColor');
            }
            if ($request->get()->get('color')) {
                $getData['bdCaptchaClr'] = $request->get()->get('color');
            }

            $captcha->output($getData['bdCaptchaBgClr'], $getData['bdCaptchaClr']);
            exit;
        } catch (\Exception $e) {
            debug($e->getMessage());
        }
    }
}
