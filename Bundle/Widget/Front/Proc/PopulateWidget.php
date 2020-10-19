<?php

namespace Bundle\Widget\Front\Proc;

use Component\Goods\Populate;
use Request;
use Exception;
use App;
use Framework\Debug\Exception\AlertOnlyException;

class PopulateWidget extends \Widget\Front\Widget
{
    public function index()
    {
        $sno = gd_isset($this->getData('sno'), 1);
        $populate = new Populate($sno);
        $populateConfig = $populate->cfg;

        $getData = $populate->getGoodsInfo('rank');

        $this->setData('data', $getData);
        $this->setData('config', $populateConfig);
    }
}
