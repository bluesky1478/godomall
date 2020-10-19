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

namespace Bundle\Controller\Admin\Board;

use Component\PlusShop\PlusReview\PlusReviewArticleAdmin;
use Component\PlusShop\PlusReview\PlusReviewConfig;
use Exception;
use Request;
use Globals;

/**
 * Class PlusReviewMilageController
 *
 * @package Bundle\Controller\Admin\Goods
 * @author  cjb3333 <cjb3333@godo.co.kr>
 */
class PlusReviewMilageController extends \Controller\Admin\Controller
{
    public function index()
    {

        $config = new PlusReviewConfig();
        $conf = $config->getConfig();
        $mileageBasicConfig = \Globals::get('gSite.member.mileageBasic');
        if ($conf['mileageFl'] != 'y' || $mileageBasicConfig['payUsableFl'] != 'y') {
            $message = __("마일리지 지급 기능을 사용하시려면, 플러스리뷰 게시판 설정과 마일리지 기본 설정 메뉴에서 마일리지 사용유무 설정을 '사용함'으로 설정해주세요.");
            $this->json(array('result' => 'fail', 'message' => $message));
        }

        $data = $config->getFormValue();

        if (is_array(Request::get()->get('sno'))) {
            $sno = implode(',',Request::get()->get('sno'));
        }else{
            $sno = Request::get()->get('sno');
        }

        // 선택된 게시글에 지급될 마일리지
        $plusReviewArticle = new PlusReviewArticleAdmin();
        $mileageAmount = $plusReviewArticle->checkMileageAmount($sno);
        // 계산된 마일리지 (실제 지급금액 아님)
        $data['data']['mileageAmount']['review'] = $mileageAmount['amount']['displayReview'];
        $this->setData('sno',$sno);
        $this->setData('data',$data['data']);
        $this->setData('mileageAmount', $mileageAmount);
        $this->getView()->setDefine('layout', 'layout_layer.php');

    }
}
