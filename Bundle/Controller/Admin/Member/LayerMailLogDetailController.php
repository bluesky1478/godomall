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
namespace Bundle\Controller\Admin\Member;

use Request;

/**
 * Class LayerMailLogDetailController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class LayerMailLogDetailController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */

        /** @var \Bundle\Component\Mail\MailLog $mailLog */
        $mailLog = \App::load('\\Component\\Mail\\MailLog');
        $contents = $mailLog->getMailLog(Request::get()->get('sno'));

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setDefine('layoutContent', \Request::getDirectoryUri() . '/' . \Request::getFileUri());

        $this->setData('contents', $contents);
    }
}
