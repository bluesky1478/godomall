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
namespace Bundle\Controller\Admin\Share;

use Globals;

class IframeResizeController extends \Controller\Admin\Controller
{
    public function index()
    {
        echo '
            IFRAME 높이 리사이징 스크립트
<script language="javascript">
<!--
var name = "<?=$_GET[name]?>";
var height = "<?=$_GET[height]?>";
if (name !=\'\' && height !=\'\' && parent.parent.document.getElementsByName(name)[0])
{
    parent.parent.document.getElementsByName(name)[0].height = height;
}
//-->
</script>
            ';
    }
}
