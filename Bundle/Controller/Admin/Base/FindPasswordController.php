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

namespace Bundle\Controller\Admin\Base;

use Component\Godo\MyGodoSmsServerApi;

/**
 * Class FindIdController
 *
 * @package Bundle\Controller\Admin\Base
 * @author haky <haky2@godo.co.kr>
 */
class FindPasswordController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        // 기존 인증번호 제거
        MyGodoSmsServerApi::deleteAuthKey();

        $this->addScript(
            [
                'jquery/jquery.countdownTimer.js',
            ]
        );
        $this->addCss(
            [
                'jquery.countdownTimer.css',
            ]
        );

        $this->getView()->setDefine('layout', 'layout_blank.php');
    }
}
