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

namespace Bundle\Controller\Admin\Goods;

use Exception;
use Globals;
use App;
use Request;
/**
 * [관리자] 상품 이동/복사/삭제 관리 상품리스트
 * @author Lee Hakyoung <haky2@godo.co.kr>
 */
class BatchLinkGoodsListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Exception
     */
    public function index()
    {
        try {
            if (!Request::isAjax()) {
                throw new Exception(__('Ajax 전용 페이지 입니다.'));
            }

            $goods = App::load('\\Component\\Goods\\GoodsAdmin');
            $getData = $goods->getAdminListBatch('image');

            $conf['mileage'] = Globals::get('gSite.member.mileageGive'); // 마일리지 지급 여부
            $conf['mileageBasic'] = Globals::get('gSite.member.mileageBasic'); // 마일리지 기본설정

            $page = App::load('\\Component\\Page\\Page'); // 페이지 재설정

            $this->setData('conf', $conf);
            $this->setData('data', $getData['data']);
            $this->setData('page', $page);

            $this->getView()->setDefine('layout', 'goods/_batch_link_goods_list.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}