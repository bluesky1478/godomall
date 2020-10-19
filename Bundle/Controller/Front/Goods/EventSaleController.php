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

namespace Bundle\Controller\Front\Goods;


use Component\Member\Manager;
use Framework\Debug\Exception\AlertBackException;
use Framework\StaticProxy\Proxy\Request;

class EventSaleController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $goods = \App::load('\\Component\\Goods\\Goods');
            $data = $goods->getDisplayThemeInfo(Request::get()->get('sno'));
            $nowDate = strtotime(date("Y-m-d H:i:s"));
            $_displayStartDate = strtotime($data['displayStartDate']);
            $_displayEndDate = strtotime($data['displayEndDate']);
            if ($nowDate < $_displayStartDate) {
                $status = 'wait';
            } else if ($nowDate > $_displayStartDate && $nowDate < $_displayEndDate) {
                $status = 'active';
            } else if ($nowDate > $_displayEndDate) {
                $status = 'end';
            } else {
                $status = 'error';
            }

            if (!Manager::isAdmin()) {
                switch ($status) {
                    case 'wait':
                    case 'end':
                        throw new AlertBackException(__('진행중인 기획전이 아닙니다.'));
                        break;
                    case 'active':
                        break;
                    default:
                        throw new AlertBackException('Illegal arg (' . $data['data']['status'] . ')');
                }

                if ($data['pcFl'] != 'y') {
                    throw new AlertBackException(__('진행중인 기획전이 아닙니다.'));
                }
            }
        } catch (Exception $e) {
            throw new AlertBackException(__('진행중인 기획전이 아닙니다.'));
        }
        $this->setData('displayCategoryType', $data['displayCategory']);
        $this->setData('eventData', ['eventNm' => $data['themeNm'],'eventDescription' => gd_remove_tag($data['pcContents'])]);
        $this->setData('sno', Request::get()->get('sno'));
    }
}
