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

use Component\Validator\Validator;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Framework\Debug\Exception\AlertBackException;
use Request;

/**
 * Class QrCodeDownloadController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class QrCodeDownloadController extends \Core\Base\Controller\StreamedController
{
    public function index()
    {
        $requestParams = Request::get()->all();
        if (Validator::required($requestParams['qrName']) === false) {
            throw new AlertBackException(__('QR코드 이름이 없습니다.'));
        }
        if (Validator::required($requestParams['qrString']) === false) {
            throw new AlertBackException(__('QR코드 내용이 없습니다.'));
        }
        if (Validator::required($requestParams['qrSize']) === false) {
            throw new AlertBackException(__('QR코드 크기를 설정해 주세요.'));
        }
        if (Validator::required($requestParams['qrVersion']) === false) {
            throw new AlertBackException(__('QR코드 정밀도를 설정해 주세요.'));
        }

        $endroidQrCode = new EndroidQrCode();
        $endroidQrCode->setSize(intval($requestParams['qrSize']) * 45); // S4의 1레벨 당 45px 증가를 참고함
        $endroidQrCode->setVersion(intval($requestParams['qrVersion']));
        $endroidQrCode->setText($requestParams['qrString']);
        $endroidQrCode->render();
        echo $endroidQrCode->get();

        $this->streamedDownload('QrCode_' . gd_isset($requestParams['qrName'], 'tmp') . '.png');
    }
}
