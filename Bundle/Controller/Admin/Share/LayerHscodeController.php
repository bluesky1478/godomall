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

use App;
use Request;

/**
 * Class LayerHscodeController
 * @package Bundle\GlobalController\Admin\Share
 * @author  Young Eun Jung <atomyang@godo.co.kr>
 */
class LayerHscodeController extends \Controller\Admin\Controller
{
    public function index()
    {
        $getValue = Request::get()->toArray();
        $hscodeGroup = file( App::getBasePath() . '/data/goods/hs_code_group.txt');

        unset($hscodeGroup[0]);

        $this->getView()->setDefine('layout', 'layout_layer.php');

        $selected['hscodeGroup'][$getValue['hscodeGroup']] = "selected";

        $hscodeList = [];
        if($getValue['hscodeGroup'] || $getValue['hscodeName']) {
            $tmpHscodeList = file( App::getBasePath() . '/data/goods/hs_code_'.$getValue['hscode'].'.txt');
            foreach($tmpHscodeList as $k => $v) {
                $tmp = explode("\t",$v);
                if(substr($tmp[0],0,strlen($getValue['hscodeGroup'])) == $getValue['hscodeGroup'] && ($getValue['hscodeName']=='' || ($getValue['hscodeName'] && (stripos($tmp[1], $getValue['hscodeName']) !== false || stripos($tmp[2], $getValue['hscodeName']) !== false)))) {
                    $hscodeList[] = $tmp;
                }
            }
        }

        $this->setData('layerFormID', $getValue['layerFormID']);
        $this->setData('parentFormID', $getValue['parentFormID']);
        $this->setData('dataFormID', $getValue['dataFormID']);
        $this->setData('dataInputNm', $getValue['dataInputNm']);
        $this->setData('mode', gd_isset($getValue['mode'],'search'));
        $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
        $this->setData('hscodeName', gd_isset($getValue['hscodeName'],''));
        $this->setData('hscodeGroup', gd_isset($getValue['hscodeGroup'],''));
        $this->setData('callFunc', gd_isset($getValue['callFunc'],''));
        $this->setData('hscodeIndex', gd_isset($getValue['hscodeIndex'],'0'));
        $this->setData('hscode', $getValue['hscode']);
        $this->setData('hscodeGroup', $hscodeGroup);
        $this->setData('hscodeList', $hscodeList);
        $this->setData('selected', $selected);

        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_hscode.php');
    }
}
