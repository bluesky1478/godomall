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
echo HTML_DOCTYPE;
?>
<head>
<title><?php echo __('게시판'); ?></title>
<meta http-equiv="Content-Type"
  content="text/html; charset=<?php echo SET_CHARSET;?>" />
<meta http-equiv="Cache-Control" content="No-Cache" />
<meta http-equiv="Pragma" content="No-Cache" />
<meta name="robots" content="noindex, nofollow" />
<meta name="robots" content="noarchive" />
</head>
<body>
  <table width="90%" cellpadding="0" cellspacing="0" class="list_form"
    border="1">
    <tr>
    <?php
    foreach ($bdList['columns'] as $data) { ?>
        <td>
            <?php echo $data['Field']?>
        </td>
    <?php
    }
    ?>
</tr>
<?php
foreach ($bdList['list'] as $data) { ?>
<tr>
    <?php
    foreach ($bdList['columns'] as $key) { ?>
        <td>
            <?php echo $data[$key['Field']]?>
        </td>
    <?php
    }
    ?>
</tr>
<?php
}
?>
</table>
</body>
</html>
