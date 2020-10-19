<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-xs"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tr>
                <th>필수정보명</th>
                <td>
                    <input type="text" name="mustInfoNm" value="<?php echo $search['mustInfoNm']; ?>" class="form-control"/>
                </td>
                <td>
                    <input type="button" value="검색" class="btn btn-black btn-hfix"  onclick="layer_list_search(); ">
                </td>
            </tr>
        </table>
    </div>
</div>

<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width5p"> <?php if ($mode == 'radio') { ?>선택 <?php } else { ?>
                    <input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'layer_must_info_');"/><?php } ?>
            </th>
            <th class="width5p">번호</th>
            <th class="width50p">필수정보명</th>
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
                            <input type="checkbox" id="layer_must_info_<?php echo $val['sno']; ?>" name="layer_must_info_<?php echo $i; ?>" value="<?php echo $val['sno']; ?>"/>
                    </td>
                    <td class="center"><?php echo number_format($page->idx--); ?></td>
                    <td>
                        <?=$val['mustInfoNm']?>
                    </td>
                    <td class="center"><?php echo gd_date_format('Y-m-d', $val['regDt']); ?></td>
                </tr>
                <?php
                $i++;
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="4">검색을 이용해 주세요.</td>
            </tr>
            <?php
        }
        ?>

        </tbody>
    </table>

    <div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
</div>

<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="select_code();" /></div>


<script type="text/javascript">
    <!--


    $(document).ready(function () {

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });

    });

    function layer_list_search(pagelink) {
        var mustInfoNm = $('input[name=\'mustInfoNm\']').val();

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
            'mustInfoNm': mustInfoNm,
            'pagelink': pagelink
        };
        $.get('../share/layer_must_info.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function select_code() {
        if ($('input[id*=\'layer_must_info_\']:checked').length == 0) {
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
            info: []
        };

        $('input[id*=\'layer_must_info_\']:checked').each(function () {
            var mustInfoSno = $(this).val();

            resultJson.info.push({"mustInfoSno": mustInfoSno});
            applyGoodsCnt++;
        });

        if (applyGoodsCnt > 0) {

            <?=$callFunc?>(resultJson);

            // 선택된 버튼 div 토글
            if (chkGoodsCnt > 0) {
                $('#' + resultJson.parentFormID).addClass('active');
            } else {
                $('#' + resultJson.parentFormID).removeClass('active');
            }

            $('div.bootstrap-dialog-close-button').click();
        }
    }

    //-->
</script>
