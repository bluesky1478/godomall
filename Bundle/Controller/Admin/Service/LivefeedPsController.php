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
use Message;
use Request;

/**
 * Class LivefeedPsController
 * @package Bundle\Controller\Admin\Service
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class LivefeedPsController extends \Controller\Admin\Controller
{

    public function index()
    {
        try {
            switch (Request::post()->get('mode')) {
                case 'config':
                    $livefeedConfigArrData = [
                        'livefeedUseType'    => Request::post()->get('livefeedUseType'),
                        'livefeedDeviceType' => Request::post()->get('livefeedDeviceType'),
                        'livefeedServiceID'  => Request::post()->get('livefeedServiceID'),
                        'livefeedAuthKey'    => Request::post()->get('livefeedAuthKey'),
                    ];
                    gd_set_policy('service.livefeed', $livefeedConfigArrData, true, Request::post()->get('mallSno'));
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.href="livefeed_config.php";');
                    break;
            }
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
        }
    }
}
