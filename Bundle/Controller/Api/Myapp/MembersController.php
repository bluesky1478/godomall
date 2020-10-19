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

namespace Bundle\Controller\Api\Myapp;

use Exception;
use Framework\Http\Request;
use Framework\Http\Response;

/**
 * 회원 정보
 *
 * @author Hakyoung Lee <haky2@godo.co.kr>
 */
class MembersController extends \Controller\Api\MyappController
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
                    $members = $myappApi->getMemberInfo();
                    $this->httpCode = Response::HTTP_OK;
                    if (empty($members)) {
                        $this->httpCode = Response::HTTP_NO_CONTENT;
                    } else {
                        $this->myappResult = json_encode($members, JSON_UNESCAPED_UNICODE);
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
