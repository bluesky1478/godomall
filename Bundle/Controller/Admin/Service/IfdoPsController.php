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
namespace Bundle\Controller\Admin\Service;

use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

/**
 * Class IfdoPsController
 * @package Bundle\Controller\Admin\Service
 * @author  choisueun <cseun555@godo.co.kr>
 */
class IfdoPsController extends \Controller\Admin\Controller
{

    public function index()
    {
        try {
            switch (Request::post()->get('mode')) {
                case 'config':
                    $ifdoConfigArrData = [
                        'ifdoUseType'    => Request::post()->get('ifdoUseType'),
                        'ifdoServiceCode' => Request::post()->get('ifdoServiceCode'),
                    ];
                    gd_set_policy('service.ifdo', $ifdoConfigArrData);
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="ifdo_config.php";');
                    break;
            }
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
        }
    }
}
