<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Provider\Share;

class LayerOrderGridConfigController extends \Controller\Admin\Share\LayerOrderGridConfigController
{

    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            parent::index();

        } catch (Exception $e) {
            throw $e;
        }
    }
}
