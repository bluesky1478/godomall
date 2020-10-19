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

use Framework\Utility\SkinUtils;
use Request;

/**
 * Class 파워메일설정
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MailConfigPmailController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('member', 'mail', 'configPmail');

        /** @var  \Bundle\Component\Mail\Pmail $pMail */
        $pMail = \App::load('\\Component\\Mail\\Pmail');

        $pMailConfig = $pMail->getMailConfigPmailWithLicense();
        $mailDomainSelectBox = SkinUtils::makeSelectBoxByMailDomain('mail_site', 'mail_site', null, $pMailConfig['email'][1], '직접입력');

        $conf = $pMail->getMailConfigPmail();

        $this->setData('mailDomainSelectBox', $mailDomainSelectBox);
        $this->setData('pMailConfig', $pMailConfig);
        $this->addScript(['member.js']);
    }
}
