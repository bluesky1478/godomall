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
use Component\Promotion\SocialShare;
use Exception;
use Framework\Debug\Exception\LayerException;
use Message;
use OverflowException;
use Request;

/**
 * Class SnsSharePsController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class SnsSharePsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $requestParams = Request::request()->toArray();
        switch ($requestParams['mode']) {
            // 소셩 공유하기 환경설정
            case 'config':
                try {
                    unset($requestParams['mode']);

                    $socialShare = new SocialShare();
                    $socialShare->setConfig($requestParams);

                    throw new LayerException(__('저장이 완료되었습니다.'));

                } catch (Exception $e) {
                    throw new LayerException($e->getMessage(), $e->getCode(), $e);
                }
                break;
        }
    }
}
