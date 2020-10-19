<div>
    <div class="mgt10"></div>
    <div>
        <form id="layreDisplayMainFrm">
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-xs"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tr>
                <th>분류명</th>
                <td>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                </td>
                <td>
                    <input type="button" value="검색" class="btn  btn-black btn-hfix"  onclick="layer_list_search();">
                </td>
            </tr>
            <tr>
                <th>쇼핑몰 유형</th>
                <td>
                    <label><input type="radio" name="mobileFl"
                                  value="all" <?php echo gd_isset($checked['mobileFl']['all']); ?> />전체</label>
                    <label><input type="radio" name="mobileFl"
                                  value="n" <?php echo gd_isset($checked['mobileFl']['n']); ?>  />PC쇼핑몰</label>
                    <label><input type="radio" name="mobileFl"
                                  value="y" <?php echo gd_isset($checked['mobileFl']['y']); ?> />모바일쇼핑몰</label>
                </td>
            </tr>
        </table>
            </form>
    </div>
</div>

<div style="max-height:370px;overflow-x:hidden;overflow-y:auto">
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width7p"> 선택</th>
            <th class="width7p">번호</th>
            <th class="width50p">분류명</th>
            <th class="width15p">진열방법</th>
            <th class="width15p">쇼핑몰 유형</th>
            <th class="width15p">등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data) && is_array($data)) {
            $i = 0;
            foreach ($data as $key => $val) {
                ?>
                <tr>
                    <td class="center">
                        <input type="checkbox" id="layer_display_main_<?php echo $val['sno']; ?>" name="layer_display_main[]" value="<?php echo $val['sno']; ?>"/>
                    </td>
                    <td class="center"><?php echo number_format($page->idx--); ?></td>
                    <td>
                        <?=$val['themeNm']?>
                    </td>
                    <td>
                        <?= $val['sortAutoFl'] == "y" ? "자동진열" : "수동진열"; ?>
                    </td>
                    <td>
                        <?= $val['mobileFl'] == "y" ? "모바일쇼핑몰" : "PC쇼핑몰"; ?>
                    </td>
                    <td class="center"><?php echo gd_date_format('Y-m-d', $val['regDt']); ?></td>
                </tr>
                <?php
                $i++;
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="5">검색을 이용해 주세요.</td>
            </tr>
            <?php
        }
        ?>

        </tbody>
    </table>
</div>

<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="select_code();" /></div>


<script type="text/javascript">
    <!--

    $(document).ready(function () {

        $('#layreDisplayMainFrm input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });

    });

    function layer_list_search(pagelink) {
        var keyword = $('#layreDisplayMainFrm input[name=\'keyword\']').val();
        var mobileFl = $('#layreDisplayMainFrm input[name=\'mobileFl\']:checked').val();


        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'parentFormID': '<?php echo $parentFormID?>',
            'dataFormID': '<?php echo $dataFormID?>',
            'dataInputNm': '<?php echo $dataInputNm?>',
            'scmFl'	: '<?php echo $scmFl?>',
            'scmNo'	: '<?php echo $scmNo?>',
            'mode': '<?php echo $mode?>',
            'callFunc': '<?php echo $callFunc?>',
            'keyword': keyword,
            'key': 'themeNm',
            'mobileFl' : mobileFl,
            'pagelink': pagelink
        };
        $.get('../share/layer_display_main.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function select_code() {
        if ($('input[id*=\'layer_display_main\']:checked').length == 0) {
            alert('필수정보를 선택해 주세요!');
            return false;
        }

        var applyGoodsCnt = 0;
        var chkGoodsCnt = 0;
        var resultJson = {
            mode: "<?php echo $mode?>",
            parentFormID: "<?php echo $parentFormID?>",
            dataFormID: "<?php echo $dataFormID?>",
            dataInputNm: "<?php echo $dataInputNm?>",
        };

        var displayMainSno = [];
        $('input[id*=\'layer_display_main\']:checked').each(function () {
            displayMainSno.push($(this).val());

        });


        resultJson.displayMainSno = displayMainSno.join("<?=INT_DIVISION?>");
        console.log(resultJson);

        <?=$callFunc?>(resultJson);

        // 선택된 버튼 div 토글
        if (chkGoodsCnt > 0) {
            $('#' + resultJson.parentFormID).addClass('active');
        } else {
            $('#' + resultJson.parentFormID).removeClass('active');
        }

        $('div.bootstrap-dialog-close-button').click();

    }

    //-->
</script>
