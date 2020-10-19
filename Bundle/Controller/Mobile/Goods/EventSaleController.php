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

namespace Bundle\Controller\Mobile\Goods;

use Component\Member\Manager;
use Framework\Debug\Exception\AlertBackException;
use Framework\StaticProxy\Proxy\Request;

class EventSaleController extends \Controller\Mobile\Controller
{
    public function index()
    {
        try {
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
            $data = $goods->getDataDisplayTheme(Request::get()->get('sno'));
            $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
            $data['data']['sortList'] = array_merge(array('' => __('운영자 진열 순서')) + $displayConfig->goodsSortList);

            if (!Manager::isAdmin()) {
                switch ($data['data']['status']) {
                    case 'wait' :
                    case 'end' :
                        throw new AlertBackException(__('진행중인 기획전이 아닙니다.'));
                        break;
                    case 'active' :
                        break;
                    default :
                        throw new AlertBackException('illgal arg (' . $data['data']['status'] . ')');
                }

                if ($data['data']['mobileFl'] != 'y') {
                    throw new AlertBackException(__('진행중인 기획전이 아닙니다.'));
                }
            }



        } catch (Exception $e) {
            throw $e;
        }

        $this->setData('data', $data['data']);
        $this->setData('sno', Request::get()->get('sno'));
        $this->setData('isMobile', 'y');
        $this->setData('eventData', ['eventNm' => $data['data']['themeNm'],'eventDescription' => gd_remove_tag($data['data']['mobileContents'])]);
    }
}
