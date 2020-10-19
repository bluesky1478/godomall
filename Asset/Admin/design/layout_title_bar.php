<!-- Title Bar -->
<h4 class="sr-only"><?php echo $skinWorkName; ?> (<?php echo $currentWorkSkin; ?>)</h4>
<?php
if (isset($designInfo) === true) {
    if ($designInfo['dir']['text'] !== 'default') {
        if (empty($designInfo['dir']['text']) === false) {
            echo $designInfo['dir']['text'];
        } else {
            echo $designInfo['dir']['name'];
        }
        ?>
        <span class="ui-icon ui-icon-carat-1-e"></span>
        <?php
        if (empty($designInfo['file']['text']) === false) {
            echo $designInfo['file']['text'];
        } else {
            echo $designInfo['file']['name'];
        }
        ?>
        <span class="text-muted" title="<?php echo gd_isset($getPageID); ?>">&nbsp; | &nbsp;<?php echo gd_isset($getPageID); ?></span>
        <span class="design-code-key-btn"><button type="button" class="btn btn-white bold">단축키 안내</button></span>
        <div class="layer-design-code-key-btn">
            <table width="280">
                <tr>
                    <td>1. 단어 or 정규식 검색</td>
                    <td>(Ctrl-F)</td>
                </tr>
                <tr>
                    <td style="padding-left:25px">(1)연속 다음검색</td>
                    <td>(ENTER or Ctrl-G)</td>
                </tr>
                <tr>
                    <td style="padding-left:25px">(2)연속 이전검색</td>
                    <td>(Shift-Ctrl-G)</td>
                </tr>
                <tr>
                    <td colspan="2" height="1">---------------------------------------------------------------</td>
                </tr>
                <tr>
                    <td>2. 단어 or 정규식 치환</td>
                    <td>((Shift-Ctrl-F)</td>
                </tr>
                <tr>
                    <td colspan="2" height="1">---------------------------------------------------------------</td>
                </tr>
                <tr>
                    <td>3. 일괄 단어 or 정규식 치환</td>
                    <td>(Shift-Ctrl-R)</td>
                </tr>
                <tr>
                    <td>4. 라인 이동</td>
                    <td>(Alt-G)</td>
                </tr>
            </table>
        </div>
        <?php
        if (isset($commonFuncCode) || isset($commonVarCode) || isset($designCode)) {
        ?>
            <span class="design-code-btn"><button type="button" class="btn btn-white js-layer-close bold">치환코드 열기</button></span>
        <?php
        }
    }
}
?>
<!-- //Title Bar -->
