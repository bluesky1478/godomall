<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright �� 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Api\Myapp;

use Exception;
use Framework\Http\Request;
use Framework\Http\Response;

/**
 * ��ȸ�� �ֹ���ȸ ��ȿ�� üũ
 *
 * @author agni <agni@godo.co.kr>
 */
class OrderGuestPsController extends \Controller\Api\MyappController
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $myappApi = \App::load('Component\\Myapp\\MyappApi');
        try {
            switch ($this->method) {
                case Request::METHOD_POST:
                    if($myappApi->GuestOrderAvailable()) {
                        $this->httpCode = Response::HTTP_OK;

                    } else {
                        $this->httpCode = Response::HTTP_BAD_REQUEST;
                    }
                    break;
            }
        } catch (Exception $e) {
            $this->httpCode = empty($e->getCode()) === false ? $e->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->myappResult = $e->getMessage();
            $this->myappExceptionFl = true;
        }
        $this->setMyappResponse();
        exit;
    }
}
