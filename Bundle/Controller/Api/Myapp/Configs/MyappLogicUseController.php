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

namespace Bundle\Controller\Api\Myapp\Configs;

use Exception;
use Framework\Http\Request;
use Framework\Http\Response;

/**
 * 솔루션 마이앱 소스 사용여부 설정 API
 *
 * @author agni <agni@godo.co.kr>
 */
class MyappLogicUseController extends \Controller\Api\MyappController
{
    /**
     * {@inheritDoc}
     */
    public function index()
    {
        $myappApi = \App::load('Component\\Myapp\\MyappApi');
        try {
            switch ($this->method) {
                case Request::METHOD_GET:
                    break;
                case Request::METHOD_POST:
                    $myappApi->setAppConfig($myappApi::APP_CONFIG_MYAPP_LOGIC_USE);
                    $this->httpCode = Response::HTTP_OK;
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
