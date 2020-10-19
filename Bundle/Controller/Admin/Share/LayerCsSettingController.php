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

namespace Bundle\Controller\Admin\Share;

use Component\Board\BoardAdmin;
use Component\Member\Manager;
use Component\Policy\MainSettingPolicy;
use Request;


/**
 * Class LayerCsSettingController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class LayerCsSettingController extends \Controller\Admin\Controller
{
    public function index()
    {
        $sessionByManager = \Session::get(Manager::SESSION_MANAGER_LOGIN);
        $boardAdmin = new BoardAdmin();
        $getData = $boardAdmin->getBoardList(Request::get()->all(), true, 'desc', true, 100);
        //플러스리뷰 사용중인경우 출력
        if (gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === true) {
            $getData['data'][] = [
                'sno' => 10000,
                'bdId' => 'plusReview',
                'bdNm' => __('플러스리뷰'),
                'bdKind' => 'default',
            ];
        }
        $getData['data'][] = [
            'sno'    => 9999,
            'bdId'   => 'scm',
            'bdNm'   => __('공급사문의'),
            'bdKind' => 'default',
        ];
        $mainPolicy = new MainSettingPolicy();
        $data = $mainPolicy->getBoard($sessionByManager['sno']);
        $checked['period'][$data['period']] = 'checked';
        foreach ($data['sno'] as $index => $item) {
            $checked['sno'][$item] = 'checked';
        }
        $this->setData('checked', $checked);
        $this->setData('lists', $getData['data']);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
