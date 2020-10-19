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

use Component\Excel\ExcelDepositConvert;
use Component\Mail\MailMimeAuto;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Framework\Utility\UrlUtils;
use Globals;
use Request;

/**
 * Class ExcelDepositPsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class ExcelDepositPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $logger = \App::getInstance('logger');
        $request = Request::request()->all();
        $convert = new ExcelDepositConvert();
        switch ($request['mode']) {
            case 'downloadSample':
                $this->streamedDownload('예치금샘플파일.xls');
                $convert->downloadSample();
                exit();
                break;
            case 'depositExcelUpload':
                $this->streamedDownload('예치금업로드결과.xls');
                $guideSend = Request::post()->get('guideSend', []);
                $logger->info('Upload deposit add/remove by excel.', $guideSend);
                $convert->setSendGuideMail(in_array('email', $guideSend));
                $convert->setSendGuideSms(in_array('sms', $guideSend));
                $convert->upload();
                $mailReceivers = $convert->getMailReceivers();
                $smsReceivers = $convert->getSmsReceivers();
                $mail = new MailMimeAuto();
                foreach ($mailReceivers as $index => $receiver) {
                    if ($receiver['deposit'] > 0) {
                        $mail->init(MailMimeAuto::ADD_DEPOSIT, $receiver);
                    } else {
                        $mail->init(MailMimeAuto::REMOVE_DEPOSIT, $receiver);
                    }
                    $mail->autoSend();
                }
                $aBasicInfo = gd_policy('basic.info');
                foreach ($smsReceivers as $index => $receiver) {
                    $groupInfo = \Component\Member\Group\Util::getGroupName('sno=' . $receiver['groupSno']);
                    $smsAuto = \App::load(\Component\Sms\SmsAuto::class);
                    $smsAuto->setSmsType(SmsAutoCode::MEMBER);
                    $smsAuto->setSmsAutoCodeType($receiver['rc_deposit'] > 0 ? Code::DEPOSIT_PLUS : Code::DEPOSIT_MINUS);
                    $smsAuto->setReceiver($receiver['cellPhone']);
                    $smsAuto->setReplaceArguments(
                        [
                            'name'       => $receiver['memNm'],
                            'rc_deposit' => $receiver['rc_deposit'],
                            'memNm'       => $receiver['memNm'],
                            'memId'       => $receiver['memId'],
                            'mileage'     => $receiver['mileage'],
                            'deposit'     => $receiver['deposit'],
                            'groupNm'     => $groupInfo[$receiver['groupSno']],
                            'rc_mallNm' => Globals::get('gMall.mallNm'),
                            'shopUrl' => $aBasicInfo['mallDomain'],
                        ]
                    );
                    $smsAuto->autoSend();
                }
                exit();
                break;
        }
    }
}
