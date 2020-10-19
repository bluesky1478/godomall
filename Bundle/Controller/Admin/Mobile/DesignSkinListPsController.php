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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Mobile;

use Framework\Debug\Exception\AlertBackException;

/**
 * 디자인 스킨 설정 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignSkinListPsController extends \Controller\Admin\Design\DesignSkinListPsController
{
    public function index()
    {
        $request = \App::getInstance('request');
        if ($request->get()->get('mode', '') == 'clearCache') {
            if ($request->get()->get('mallSno', 0) < 1) {
                throw new AlertBackException('상점번호를 찾을 수 없습니다.');
            }
            try {
                $cache = \App::getInstance('cache');
                $adaptor = $cache->getAdaptor();
                $config = $adaptor->getConfig();
                $config['file']['mallSno'] = $request->get()->get('mallSno');
                $config['file']['subDomain'] = 'mobile';
                $adaptor->setConfig($config);
                $cache->setAdaptor($adaptor);
                $cache->flush();
            } catch (\Throwable $e) {
                throw new AlertBackException('초기화 중 오류가 발생하였습니다.');
            }
            throw new AlertBackException('초기화 되었습니다.');
        } else {
            parent::index();
        }
    }
}
