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

use App;
use Component\Sms\Sms;
use Request;

/**
 * Class 관리자-CRM SMS 내역
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class MemberCrmSmsController extends \Controller\Admin\Controller
{
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('member', 'member', 'crm');

        Request::get()->set('page', Request::get()->get('page', 0));
        Request::get()->set('pageNum', Request::get()->get('pageNum', 10));
        Request::get()->set('sort', Request::get()->get('sort', 'sno DESC'));

        /** @var \Bundle\Component\Sms\SmsUtil $smsUtil */
        $smsUtil = App::load('\\Component\\Sms\\SmsUtil');
        /** @var \Bundle\Component\Sms\SmsLog $smsLog */
        $smsLog = App::load('\\Component\\Sms\\SmsLog');

        $requestGetParams = Request::get()->all();
        if (!$requestGetParams['regDt'][0] && !$requestGetParams['regDt'][1]) {
            $requestGetParams['regDt'][0] = date('Y-m-d', strtotime('-6 day'));
            $requestGetParams['regDt'][1] = date('Y-m-d');
        }
        $list = $smsLog->getList($requestGetParams);
        $this->setData('logData', $list);
        $this->setData('checked', $smsUtil->setChecked());
        $this->setData('requestGetParams', $requestGetParams);
        $page = $smsLog->getPage();
        $this->setData('page', $page);
        $this->setData('sorts', $smsUtil->getCrmSorts());
        $this->setData('lmsPoint', Sms::LMS_POINT);
        $this->setData('smsSendType', Sms::SMS_SEND_TYPE);
        $this->setData('smsSendStatus', Sms::SMS_SEND_STATUS);
        $this->addScript(['member.js']);
    }
}
