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

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\Framework\Debug\Exception;
use Framework\Utility\DateTimeUtils;
use Message;
use Globals;
use Request;
use Session;

class GoodsMainController extends \Controller\Mobile\Controller
{

    /**
     * 메인리스트
     *
     * @author artherot, sunny
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {

        $getValue = Request::get()->toArray();

        $goods = \App::load('\\Component\\Goods\\Goods');
        $getData = $goods->getDisplayThemeInfo($getValue['sno']);
        $mainLinkData = [
            'mainThemeSno' => $getData['sno'],
            'mainThemeNm' => $getData['themeNm'],
            'mainThemeDevice' => $getData['mobileFl'],
        ];
        Request::get()->set('mainLinkData',$mainLinkData);
        //기획전 그룹형 그룹정보 로드
        if((int)$getValue['groupSno'] > 0) {
            $eventGroup = \App::load('\\Component\\Promotion\\EventGroupTheme');
            $getData = $eventGroup->replaceEventData($getValue['groupSno'], $getData);
        }
        if (in_array($getData['displayType'], ['04', '06', '07', '11']) === true) {
            $getData['displayType'] = '01';
        }

        $this->getView()->setDefine('goodsTemplate', 'goods/list/list_01.html');
        $this->setData('gPageName',$getData['themeNm']);
        $this->setData('mainData', $getData);
        $this->setData('sno', $getValue['sno']);


    }
}
