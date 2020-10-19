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
use Component\Validator\Validator;
use Exception;
use Request;
use Session;

/**
 * Class PopupCounselWriteController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class PopupCounselWriteController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     */
    public function index()
    {
        /** @var \Bundle\Component\Member\Member $member */
        $member = App::load('\\Component\\Member\\Member');
        $memberNo = Request::get()->get('memNo');
        if (Validator::number($memberNo, null, null, true) === false) {
            throw new Exception(__('유효하지 않은 회원번호 입니다.'));
        }
        $memberData = $member->getMember($memberNo, 'memNo');

        $counselSno = Request::get()->get('sno');
        if($counselSno) {
            $counsel = App::load('\\Component\\Member\\Counsel');
            // 수정 시 해당 글정보 호출
            $counselData = $counsel->getViewOnce($counselSno)[0];
            $this->setData('counselData', $counselData);
            // select 박스 배열 선언
            $selectCodeData = $counsel->counselCodeData();
            $this->setData('selectCodeData', $selectCodeData);
        }
        // 관리자 정보
        $managerData = Session::get('manager');
        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->setData('memberData', $memberData);
        $this->setData('managerData', $managerData);

    }
}
