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

use Framework\Debug\Exception\LayerException;
use Component\Promotion\InsgoWidget;
use Request;
use App;

/**
 * 인스고위젯 미리보기
 * @author  <@godo.co.kr>
 */
class Insgov2WidgetPreviewController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        $widget = App::load('\\Component\\Promotion\\Insgov2Widget');
        $getValue = Request::get()->all();

        if(empty($getValue['widgetAccessToken']) === true){
            throw new LayerException(__('인스타그램 토큰이 확인되지 않습니다. 인스타그램 API연동을 다시 해주세요.'));
        }

        switch($getValue['mode']) {
            case 'regist':
            case 'modify':
                $insgoWidgetData = $widget->getInsgoWidgetData($getValue);
                if($getValue['widgetDisplayType'] == 'grid'){
                    $layoutCnt = $getValue['widgetWidthCount'] * $getValue['widgetHeightCount'];
                    $insgoWidgetData['thumbnails'] = array_slice($insgoWidgetData['thumbnails'], 0, $layoutCnt);
                }
                if($insgoWidgetData['error']){
                    if($insgoWidgetData['error']['code'] == 000 || $insgoWidgetData['error']['code'] == 10){
                        // errorCode_000: 연동된 인스타그램에 게시물이 없는 경우 000, errorCode_10: 미디어 엑세스 미허용 시 10
                        throw new LayerException(__('인스타그램 계정의 접근권한이 없거나 등록된 게시물이 확인되지 않습니다. 인스타그램 계정 상태를 확인 후 다시 연동해 주세요. [') . $insgoWidgetData['error']['code'] . ']', 0, null, null, 3000, false);
                    }else{
                        throw new LayerException(__('인스타그램 연동에 실패하였습니다. 다시 연결하시기 바랍니다. [') . $insgoWidgetData['error']['code'] . ']', 0, null, null, 3000, false);
                    }
                }
                break;
        }

        $this->setData('insgoData', json_encode($insgoWidgetData));
        $this->setData('widgetSideButtonColor', $insgoWidgetData['data']['widgetSideButtonColor'] == '' ? '#ffffff' : $insgoWidgetData['data']['widgetSideButtonColor']);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
