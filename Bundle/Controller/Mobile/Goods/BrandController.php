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

namespace Bundle\Controller\Mobile\Goods;
use Exception;
use Session;

class BrandController extends \Controller\Mobile\Controller
{
    public function index()
    {
        try {
            $brand = \App::load('\\Component\\Category\\Brand');
            $getData = $brand->getBrandCodeInfo(null, 4, '', false, 'cateNm ASC', false);

            $english_alphabet = range('A', 'Z');
            $korea_alphabet = array('ㄱ', 'ㄴ', 'ㄷ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅅ', 'ㅇ', 'ㅈ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ');

            $this->setData('english_alphabet', $english_alphabet);
            $this->setData('korea_alphabet', $korea_alphabet);

            $this->setData('gPageName', __('브랜드'));
            $this->setData('list', $getData);
            $this->setData('alphabetFl', !Session::has(SESSION_GLOBAL_MALL));
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}