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
namespace Bundle\Controller\Admin\Promotion;

use Exception;
use Session;
use App;
use Request;
use Framework\Debug\Exception\AlertCloseException;

/**
 * Class PopupEventSaleGroupRegisterController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  <bumyul2000@godo.co.kr>
 */
class PopupEventSaleGroupRegisterController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertCloseException
     */
    public function index()
    {
        try {
            $eventGroupNo = Request::get()->get('eventGroupNo');
            $eventGroupTempNo = Request::get()->get('eventGroupTempNo');
            if((int)$eventGroupNo > 0){
                $eventGroupGetInfo = array(
                    'loadType' => 'real',
                    'eventGroupSno' => $eventGroupNo,
                );
            }
            else if((int)$eventGroupTempNo > 0){
                $eventGroupGetInfo = array(
                    'loadType' => 'temp',
                    'eventGroupSno' => $eventGroupTempNo,
                );
            }
            else {
                $eventGroupGetInfo = array();
            }

            $eventGroupTheme = App::load('\\Component\\Promotion\\EventGroupTheme');
            $displayConfig = App::load('\\Component\\Display\\DisplayConfigAdmin');

            $data = $eventGroupTheme->getDataEventGroupTheme($eventGroupGetInfo);
            $data['data']['sortList'] = array_merge(array('' => __('운영자 진열 순서')) + $displayConfig->goodsSortList);

            $toggle = gd_policy('display.toggle');
            $SessScmNo = Session::get('manager.scmNo');
            if($data['data']['mode'] === 'event_group_modify'){
                $popupTitle = '수정';
                $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
                $eventThemeData = $goods->getDisplayThemeInfo($data['data']['groupThemeSno']);
                $this->setData('eventThemeData', $eventThemeData);
            }
            else {
                $popupTitle = '등록';
                $data['data']['groupThemeCd'] = $data['data']['groupThemeCd'] ?? 'F0000001';
                $data['data']['groupMobileThemeCd'] = $data['data']['groupMobileThemeCd'] ?? 'F0000002';
            }

            $this->setData('toggle', $toggle);
            $this->setData('SessScmNo', $SessScmNo);
            $this->setData('eventGroupGetInfo', $eventGroupGetInfo);
            $this->setData('popupTitle', $popupTitle);
            $this->setData('data', $data['data']);
            $this->setData('checked', $data['checked']);
            $this->setData('selected', $data['selected']);

            $this->getView()->setDefine('layout', 'layout_blank.php');
        }
        catch (Exception $e) {
            throw new AlertCloseException($e->ectMessage);
        }
    }
}
