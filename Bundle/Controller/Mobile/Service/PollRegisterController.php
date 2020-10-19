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

namespace Bundle\Controller\Mobile\Service;

use App;
use Request;

/**
 * Class AgreementController
 * @package Bundle\Controller\Mobile\Service
 * @author  Bagyj
 */
class PollRegisterController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $postValue = Request::post()->toArray();

        /** @var \Bundle\Controller\Front\Controller $front */
        $front = App::load('\\Controller\\Front\\Service\\PollRegisterController');
        $front->index();

        $itemSno = $postValue['itemSno'] ?? 0;

        $resultData = json_decode(base64_decode($postValue['resultData']),true);
        $resultEtcData = json_decode(base64_decode($postValue['resultEtcData']),true);
        if ($itemSno > 0 && $postValue['save'] == 'Y') {
            $resultData[$itemSno - 1] = $postValue['result'][$itemSno - 1];
            $resultEtcData[$itemSno - 1] = $postValue['resultEtc'][$itemSno - 1];
        }

        $data = $front->getData('data');

        if ($data['itemResponseType'][$itemSno] == 'radio') {
            if (isset($resultData[$itemSno])) {
                $checked[$resultData[$itemSno]] = 'checked';
            }
        } elseif ($data['itemResponseType'][$itemSno] == 'checkbox') {
            if (isset($resultData[$itemSno]) && is_array($resultData[$itemSno])) {
                foreach ($resultData[$itemSno] as $v){
                    $checked[$v] = 'checked';
                }
            }
        }

        $this->setData('gPageName', $front->getData('title'));
        $this->setData('itemSno', $itemSno);
        $this->setData('resultData', base64_encode(json_encode($resultData)));
        $this->setData('result', $resultData);
        $this->setData('resultEtcData', base64_encode(json_encode($resultEtcData)));
        $this->setData('resultEtc', $resultEtcData);
        $this->setData('checked', $checked);
        $this->setData($front->getData());
    }
}
