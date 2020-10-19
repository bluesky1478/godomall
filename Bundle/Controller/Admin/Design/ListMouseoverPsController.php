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

namespace Bundle\Controller\Admin\Design;

use Component\Design\ListMouseover;

/**
 * 리스트 마우스오버 효과 처리
 * @author <kookoo135@godo.co.kr>
 */
class ListMouseoverPSController extends \Controller\Admin\Controller
{
    public function index()
    {
        try{
            $postValue = \Request::post()->all();
            $listMouseover = new ListMouseover();

            $listMouseover->save($postValue);

            $this->layer(__('저장되었습니다.'), 'top.location.reload();');
        } catch (\Exception $e) {

        }
        exit;
    }
}