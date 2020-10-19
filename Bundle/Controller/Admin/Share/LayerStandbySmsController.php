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

namespace Bundle\Controller\Admin\Share;

use Component\Sms\Sms;
use App;
use Request;

/**
 * Class 레이어 발송대기 SMS 내용 수정
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class LayerStandbySmsController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var \Bundle\Component\Sms\SmsLog $smsLog */
        $smsLog = App::load('\\Component\\Sms\\SmsLog');
        $data = $smsLog->getSmsLog('sno, sendFl, contents, contentsMask', Request::get()->get('sno'));

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('data', gd_htmlspecialchars_stripslashes($data));
        $this->setData('smsStringLimit', Sms::SMS_STRING_LIMIT);
        $this->setData('lmsStringLimit', Sms::LMS_STRING_LIMIT);
        $this->setData('smsForbidTime', Sms::SMS_FORBID_TIME);
        $this->setData('lmsPoint', Sms::LMS_POINT);
    }
}
