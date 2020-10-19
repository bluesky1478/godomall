<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ¨Ï 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Api\Myapp\Statistics;

use Exception;
use Framework\Http\Request;
use Framework\Http\Response;

/**
 * ¾Û Çª½Ã ÁÖ¹® Åë°è Á¤º¸
 *
 * @author agni <agni@godo.co.kr>
 */
class PushOrderController extends \Controller\Api\MyappController
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
                    $orderStatistics = $myappApi->getAppPushOrderStatistics();
                    $this->httpCode = Response::HTTP_OK;
                    if (empty($orderStatistics)) {
                        $this->httpCode = Response::HTTP_NO_CONTENT;
                    } else {
                        $this->myappResult = json_encode($orderStatistics, JSON_UNESCAPED_UNICODE);
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
