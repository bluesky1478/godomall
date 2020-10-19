<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Service;

use Exception;
use Framework\Debug\Exception\LayerException;

/**
 * 카카오 상담톡 설정
 *
 * @author dlwoen9 <dlwoen9@godo.co.kr>
 */
class HappytalkPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            $request = \App::getInstance('request');
            $postValue = $request->post()->all();
            $happytalk = \App::load('Component\\Service\\Happytalk');
            $happytalk->validate($postValue);

            switch($postValue['mode']) {
                case 'config':
                    $happytalk->setHappytalkConfig($postValue);
                    $this->layer('저장이 완료되었습니다.');
                    break;
            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
