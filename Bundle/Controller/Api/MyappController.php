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

namespace Bundle\Controller\Api;

use Framework\Http\Request;
use Framework\Http\Response;

/**
 * 마이앱 API
 *
 * @author Hakyoung Lee <haky2@godo.co.kr>
 */
class MyappController extends \Controller\Api\Controller
{
    // 호출된 method
    protected $method;

    // response code
    protected $httpCode;

    // 결과 출력 데이터
    protected $myappResult;

    // 결과 예외 여부
    protected $myappExceptionFl = false;

    // 모든 method 허용 디렉토리
    protected $myappAllowAllMethodsDirectory = 'configs';

    // 기본 허용 메소드
    protected $myappAllowDefaultMethods = [
        Request::METHOD_GET,
        Request::METHOD_OPTIONS,
    ];

    // 모든 메소드 허용
    protected $myappAllowAllMethods = [
        Request::METHOD_GET,
        Request::METHOD_POST,
        Request::METHOD_PUT,
        Request::METHOD_DELETE,
        Request::METHOD_OPTIONS,
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->method = \Request::getMethod();
        $this->httpCode = Response::HTTP_NOT_FOUND;

        // api request data for log
        $requestInfo['method'] = $this->method;
        $requestInfo['url'] = \Request::getDomainUrl() . \Request::getRequestUri();
        $requestInfo['params'] = \Request::request()->all();
        if (isset($requestInfo["params"]["loginPwd"])) {
            unset($requestInfo["params"]["loginPwd"]);
        }
        $requestInfo['ip'] = \Request::getRemoteAddress();
        \Logger::channel('myapp')->info('api request', $requestInfo);
    }

    /**
     * myapp API response
     */
    protected function setMyappResponse()
    {
        // header 설정
        $allowMethods = $this->myappAllowDefaultMethods;
        if (\Request::getDirectoryUri() == $this->myappAllowAllMethodsDirectory) {
            $allowMethods = array_unique(array_merge($allowMethods, $this->myappAllowAllMethods));
        }
        $header[] = 'Access-Control-Allow-Origin: *';
        $header[] = 'Access-Control-Allow-Credentials: true';
        $header[] = 'Access-Control-Allow-Headers: X-Requested-With, access-control-allow-methods, access-control-allow-headers, access-control-allow-origin';
        $header[] = 'Content-Type: application/json; charset=utf-8';
        $header[] = 'Access-Control-Allow-Methods: ' . implode(', ', $allowMethods);
        $this->setHeader($header);

        if ($this->myappExceptionFl) {
            \Logger::channel('myapp')->error($this->myappResult);
        }

        // response code 설정
        if ($this->httpCode === Response::HTTP_NOT_FOUND) {
            if ($this->method === Request::METHOD_OPTIONS) {
                $this->httpCode = Response::HTTP_OK;
            }
        }
        http_response_code($this->httpCode);

        // data 출력
        if (empty($this->myappResult) === false) {
            echo $this->myappResult;
        }
    }
}
