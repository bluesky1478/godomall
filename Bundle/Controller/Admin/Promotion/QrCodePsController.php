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

use App;
use Exception;
use Framework\Debug\Exception\LayerException;
use Message;
use OverflowException;
use Request;
use Endroid\QrCode\QrCode as EndroidQrCode;

/**
 * Class QrCodePsController
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 */
class QrCodePsController extends \Controller\Admin\Controller
{

    /**
     * QrCodePsController
     *
     * @author yjwee
     */
    public function index()
    {
        /** @var \Bundle\Component\Promotion\QrCode $qr */
        $qr = \App::load('\\Component\\Promotion\\QrCode');

        $requestParams = array_merge(Request::get()->toArray(), Request::post()->toArray());
        try {
            switch ($requestParams['mode']) {
                case 'save':
                    // 저장
                    $requestParams['sno'] = $qr->save($requestParams);
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'edit':
                    $qr->edit($requestParams);
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'delete':
                    $qr->delete($requestParams);
                    $this->json(__('삭제 되었습니다.'));
                    break;
                case 'preview':
                    $result['previewImage'] = $qr->preview($requestParams);
                    $result['resultMessage'] = __('QR코드 미리보기가 생성되었습니다. QR코드 내용에서 확인하세요.');
                    $this->json($result);
                    break;
                case 'config':
                    $qr->setConfig($requestParams);
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
            }
        } catch (OverflowException $e) {
            if (\Request::isAjax() === true) {
                $this->json($this->exceptionToArray(new Exception(__('QR코드 정밀도를 높여 주시기 바랍니다.'))));
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (Exception $e) {
            if (\Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new LayerException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }
}
