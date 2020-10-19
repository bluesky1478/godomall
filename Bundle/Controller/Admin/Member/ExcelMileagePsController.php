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

namespace Bundle\Controller\Admin\Member;

use Component\Excel\ExcelMileageConvert;
use Component\Mail\MailMimeAuto;
use Component\Sms\Code;
use Component\Sms\Sms;
use Component\Sms\SmsAutoCode;
use Request;

/**
 * Class ExcelMileagePsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class ExcelMileagePsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = Request::request()->all();
        $mileageConvert = new ExcelMileageConvert();
        switch ($request['mode']) {
            case 'downloadSample':
                $this->streamedDownload('마일리지샘플파일.xls');
                $mileageConvert->downloadSample();
                exit();
                break;
            case 'mileageExcelUpload':
                $this->streamedDownload('마일리지업로드결과.xls');
                $guideSend = Request::post()->get('guideSend', []);
                $mileageConvert->setSendGuideMail(in_array('email', $guideSend));
                $mileageConvert->setSendGuideSms(in_array('sms', $guideSend));
                $mileageConvert->upload();
                $mailReceivers = $mileageConvert->getMailReceivers();
                $smsReceivers = $mileageConvert->getSmsReceivers();
                $sms = new Sms();
                $mail = new MailMimeAuto();
                foreach ($mailReceivers as $index => $receiver) {
                    if ($receiver['mileage'] > 0) {
                        $mail->init(MailMimeAuto::ADD_MILEAGE, $receiver);
                    } else {
                        $mail->init(MailMimeAuto::REMOVE_MILEAGE, $receiver);
                    }
                    $mail->autoSend();
                }
                foreach ($smsReceivers as $index => $receiver) {
                    if ($receiver['rc_mileage'] > 0) {
                        $sms->smsAutoSend(SmsAutoCode::MEMBER, Code::MILEAGE_PLUS, $receiver['cellPhone'], $receiver);
                    } elseif ($receiver['rc_mileage'] < 0) {
                        $sms->smsAutoSend(SmsAutoCode::MEMBER, Code::MILEAGE_MINUS, $receiver['cellPhone'], $receiver);
                    } else {
                        $logger = \App::getInstance('logger');
                        $logger->info(sprintf('%s, receiver rc_mileage is zero.', __METHOD__), $receiver);
                    }
                }
                exit();
                break;
        }
    }
}
