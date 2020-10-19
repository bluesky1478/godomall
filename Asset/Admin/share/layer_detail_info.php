<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tr>
                <th>이용안내 제목</th>
                <td>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                </td>
                <td>
                    <input type="button" value="검색" class="btn btn-hfix btn-black" onclick="layer_list_search();">
                </td>
            </tr>
        </table>
    </div>
</div>

<div>
    <table class="table table-rows table-fixed" id="tbl_list">
        <thead>
        <tr>
            <th class="width10p">선택</th>
            <th class="width10p">번호</th>
            <th class="width50p">이용안내 제목</th>
            <th class="width20p">구분</th>
            <th class="width10p">등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data) && is_array($data)) {
            $i = 0;
            foreach ($data as $key => $val) {
                ?>
                <tr id='tr_'<?=$val['sno']?>>
                    <td class="center">
                        <input type="radio" id="layer_detail_info" name="layer_detail_info" value="<?php echo $val['informCd']; ?>"/>
                    </td>
                    <td class="center"><?php echo number_format($page->idx--); ?></td>
                    <td><?=$val['informNm']?></td>
                    <td><?=$val['scmNm']?></td>
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

    /*
    $('#tbl_list tr:gt(0)').mouseover(function () {
        $(this).css('background-color','#BCBCBC');
    });

    $('#tbl_list tr:gt(0)').mouseout(function () {
        if($(this).children().children().prop('checked') == false){
            $(this).css('background-color','');
        }
    });
    */

    $('#tbl_list tr:gt(0)').click(function () {
       $(this).children().children().prop("checked",true);
       /*
       $(this).css('background-color','#BCBCBC');
       $('#tbl_list tr:gt(0)').each(function () {
           if($(this).children().children().prop('checked') == false){
               $(this).css('background-color','');
           }
       });
       */
    });

    function layer_list_search(pagelink) {

        var keyword = $('input[name=\'keyword\']').val();

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
            'key': 'informNm',
            'keyword': keyword,
            'groupCd': '<?php echo $groupCd?>',
            'pagelink': pagelink
        };
        $.get('../share/layer_detail_info.php', parameters, function (data) {
            console.log(data);
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function select_code() {
        if ($('input[id*=\'layer_detail_info\']:checked').length == 0) {
            alert('이용안내를 선택해 주세요!');
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

        $('input[id*=\'layer_detail_info\']:checked').each(function () {
            var detailInfoInformCd = $(this).val();

            resultJson.info.push({"detailInfoInformCd": detailInfoInformCd});
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
