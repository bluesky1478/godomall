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
namespace Bundle\Controller\Admin\Base;

use Globals;

class LayerStatisticsSetOrderController extends \Controller\Admin\Controller
{

    public function index()
    {
        /**
         * 매출통계설정
         *
         * @author sunny
         * @version 1.0
         * @since 1.0
         * @copyright ⓒ 2016, NHN godo: Corp.
         */

        // --- 데이터
        $getData = gd_policy('basic.statistics');
        $checked['term'][gd_isset($getData['order']['term'], 3)] = 'checked="checked"';
        $checked['graphFl'][gd_isset($getData['order']['graphFl'])] = 'checked="checked"';

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_blank.php');

        $this->setData('getData', $getData['order']);
        $this->setData('checked', $checked);
    }
}
