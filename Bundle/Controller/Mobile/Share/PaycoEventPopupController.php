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

namespace Bundle\Controller\Mobile\Share;

/**
 * Class PaycoEventPopupController
 * @package Bundle\Controller\Mobile\Share
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class PaycoEventPopupController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Share\PaycoEventPopupController $front */
        $front = \App::load('Controller\\Front\\Share\\PaycoEventPopupController');
        $front->index();
        $userFilePathResolver = \App::getInstance('user.path');
        $templateDir = $userFilePathResolver->data('common', 'payco', 'event', 'mobile');
        $this->setData($front->getData());
        $this->getView()->setTemplateDir($templateDir);
        $this->getView()->setCompileDir($templateDir);
        $this->getView()->setPageName('00_payco_benefit');
    }

}
