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
namespace Bundle\Controller\Front\Share\proc;

use Framework\Debug\Exception\LayerException;
use Framework\Utility\ArrayUtils;
use Logger;
use Request;
use UserFilePath;

/**
 * 파워메일 피드백 class
 *
 * 파워메일 발송시 메일업체에서 호출하는 페이지
 * @author cjb3333 <cjb3333@godo.co.kr>
 */

class AmailController extends \Controller\Front\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $pMail = \App::load('\\Component\\Mail\\Pmail');

        ### Amail 발송리스트 리턴
        switch (Request::get()->get('mode')) {
            case "grouplist" :
                $txt = "<tr><td>all</td><td>전체</td></tr>\n";
                echo iconv('UTF-8', 'EUC-KR', $txt);
                break;
            case "groupchoice" :
                $amailFile = UserFilePath::data()->getRealPath()."/mail/conf/amail.data.php";
                if (file_exists($amailFile)) {
                    $tmp = @file($amailFile);
                    $cnt = count($tmp);
                    echo $cnt;
                }
                break;
            case "groupftp" :
                /** @var \Bundle\Component\Mail\Pmail $pMail */
                $pMail->setPmail();
                $res = $pMail->setList(Request::get()->get('user_id'), Request::get()->get('post_id'));
                break;
            case "mailing" :

                if (gd_isset(Request::get()->get('id'))) {
                    $res = $pMail->set_mailing_refusal(Request::get()->get('id'));
                    if ($res === true) $msg = __("정상적으로 수신거부가 되었습니다.");
                    else $msg = __("올바른 회원아이디가 아닙니다.");
                } else {
                    $msg = __("회원아이디가 올바르지 않습니다.");
                }
                if (isset($msg)) echo $msg;
                break;
        }

        exit;
    }
}
