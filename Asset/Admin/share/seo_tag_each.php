<input type="hidden" id="depth-toggle-hidden-seoTag" value="<?=$toggle['seoTag_'.$SessScmNo]?>">
<div id="depth-toggle-line-seoTag" class="depth-toggle-line display-none"></div>
<div id="depth-toggle-layer-seoTag">
    <?php if(empty($data['seoTag']['data']['sno']) === false) { ?><input type="hidden" name="seoTagSno" value="<?=$data['seoTag']['data']['sno']; ?>" /> <?php } ?>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg">
            <col>
        </colgroup>
        <tr>
            <th>개별 설정 사용여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="seoTagFl" value="y" <?=gd_isset($checked['seoTagFl']['y']); ?>>사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="seoTagFl" value="n" <?=gd_isset($checked['seoTagFl']['n']); ?>>사용안함
                </label>

                <div class="notice-info"> ‘사용함’ 선택 시 기본설정 > 검색엔진 최적화(SEO) 설정보다 개별 설정이 우선적으로 적용됩니다.<br/>
                    설정 결과는 검색 엔진에 따라 평균 2주 ~ 3주 후에 반영될 수 있습니다.</div>

            </td>
        </tr>
        <?php
        foreach($data['seoTag']['config'] as $k => $v) {
            $maxLength = 50;
            if (in_array($k, ['description', 'keyword']) === true) {
                $maxLength = 300;
            }
            ?>
            <tr>
                <th><?=$v?></th>
                <td>
                    <input type="text" name="seoTag[<?=$k?>]" value="<?=$data['seoTag']['data'][$k]; ?>" class="form-control width100p" maxlength="<?=$maxLength;?>" />
                </td>
            </tr>
        <?php } ?>
    </table>

    <div class="js-replace-code-<?=$data['seoTag']['target']?> display-none">
        <table class="table table-rows">
            <thead>
            <tr>
                <th>번호</th>
                <th>치환코드</th>
                <th>설명</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $num = 1;
            foreach($data['seoTag']['replaceCode'] as $k1 =>$v1) { ?>
                <tr class="text-center">
                    <td><?=$num++?></td>
                    <td><?=$k1?></td>
                    <td><?=$v1?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('.js-code-view').click(function(e){
            BootstrapDialog.show({
                nl2br: false,
                type: BootstrapDialog.TYPE_PRIMARY,
                title: '치환코드 보기',
                message: $(".js-replace-code-"+$(this).data('target')).html()
            });
        });

    });

    //-->
</script>

