<form id="frmConfig" action="dburl_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="type" value="config"/>
    <input type="hidden" name="company" value="payco"/>
    <input type="hidden" name="mode" value="config"/>
    <input type="hidden" name="shopKey" value="<?php echo $data['shopKey']; ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red">
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>사용 여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="paycoFl" value="y" <?php echo gd_isset($checked['paycoFl']['y']); ?> />사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="paycoFl" value="n" <?php echo gd_isset($checked['paycoFl']['n']); ?> />사용안함
                </label>
            </td>
        </tr>
        <tr>
            <th>페이코 쇼핑<br/>상품 노출 설정</th>
            <td>
                <a href="/marketing/payco_goods_config.php" class="btn btn-gray btn-sm" target="_blank">페이코 쇼핑 상품 설정</a>
            </td>
        </tr>
    </table>
</form>

<?php if ($data['paycoFl'] == 'y') { ?>
    <form id="frmGen" action="dburl_ps.php" method="post">
        <input type="hidden" name="type" value="gen"/> <input type="hidden" name="company" value="payco"/>
        <table class="table table-rows">
            <thead>
            <tr>
                <th>업체</th>
                <th>상품 DB URL [페이지 미리보기]</th>
            </tr>
            </thead>
            <tbody>
            <tr class="center">
                <td class="width-md">페이코 쇼핑<br/>상품 DB URL</td>
                <td class="left">
                    <?php
                    $dbUrlFile = UserFilePath::data('dburl', 'payco', 'payco_all');

                    echo '<div>[전체상품] <a href="' . $mallDomain . 'partner/payco_all.php" target="_blank">' . $mallDomain . 'partner/payco_all.php</a> <a href="' . $mallDomain . 'partner/payco_all.php" target="_blank" class="btn btn-gray btn-sm">미리보기</a></div>';

                    ?>
                    <?php

                    echo '<div class="mgt5 js-payco-summary-url">[요약상품] <a href="' . $mallDomain . 'partner/payco_summary.php" target="_blank">' . $mallDomain . 'partner/payco_summary.php</a>  <a href="' . $mallDomain . 'partner/payco_summary.php" target="_blank" class="btn btn-gray btn-sm">미리보기</a></div>';

                    ?>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
<?php } ?>
