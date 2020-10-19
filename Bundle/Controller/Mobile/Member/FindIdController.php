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

namespace Bundle\Controller\Mobile\Member;

class FindIdController extends \Controller\Mobile\Controller
{

    /**
     * index
     */
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\FindIdController $front */
        $front = \App::load('\\Controller\\Front\\Member\\FindIdController');
        $front->index();

        $this->setData($front->getData());

        $emailDomain = gd_array_change_key_value(gd_code('01004'));
        $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
        $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅
    }
}
