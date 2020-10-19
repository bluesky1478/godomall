<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup><col class="width-md" /><col /><col /></colgroup>
            <?php if (gd_use_provider()) { ?>
            <tr>
                <th>공급사 구분</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="layer_scmFl" value="all" <?php echo gd_isset($checked['scmFl']['all']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="layer_scmFl" value="0" <?php echo gd_isset($checked['scmFl']['0']); ?>/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="layer_scmFl" value="1" class="js-layer-register" <?php echo gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="radio"/> 공급사
                    </label>
                    <button type="button" name="scmFlButton" class="btn btn-sm btn-gray js-layer-register" data-id="layer_idscm" data-input-name="layer_scmNo" data-parent-id="layer_scmLayer" data-type="scm" data-mode="layer_radio">공급사 선택</button>

                    <div id="layer_scmLayer" class="selected-btn-group <?=!empty($search['scmNo']) && $search['scmFl'] != 'all' ? 'active' : ''?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == '1' && $search['scmNo']) { ?>
                            <div id="layer_idscm_<?=$search['scmNo']?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="layer_scmNo" value="<?=$search['scmNo']?>"/>
                                <input type="hidden" name="layer_scmNoNm" value="<?=$search['scmNoNm'] ?>"/>
                                <span class="btn"><?=$search['scmNoNm']?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#layer_idscm_<?=$search['scmNo']?>">삭제</button>
                            </div>
                        <?php } ?>
                    </div>
                </td>
                <td rowspan="2">
                    <div class="form-inline ">
                        <input type="button" value="검색" class="btn btn-black btn-hf" onclick="layer_da_list_search();" />
                    </div>
                </td>
            </tr>
            <tr>
                <th>지역별 추가배송비명</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <?php } else { ?>
            <tr>
                <th>지역별 추가배송비명</th>
                <td>
                    <div class="form-inline">
                        <input type="hidden" name="scmFl" value="all"/>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
                <td class="valign-mid">
                    <input type="button" value="검색" class="btn btn-black btn-hf" onclick="layer_da_list_search();" />
                </td>
            </tr>
            <?php } ?>

        </table>
    </div>
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th class="width10p">선택</th>
        <th class="width10p">번호</th>
        <th>지역별 추가배송비명</th>
        <th>공급사 구분</th>
        <th class="width20p">등록일</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (gd_isset($data) && is_array($data)) {
        $i = 0;
        foreach ($data as $key => $val) {
            if ($val['scmNo'] == '1') {
                $scmType = '본사';
                $addDisabled = '';
            } else if ($val['scmNo'] == '0') {
                $manage = $delivery->getDataSnoDelivery('1');
                $val['companyNm'] = $manage['manage']['companyNm'];
                $scmType = $val['companyNm'];
                $addDisabled = '';
            } else if ($val['scmNo'] == 'x') {
                $scmType = '탈퇴';
                $addDisabled = 'disabled="disabled"';
            } else {
                $scmType = $val['companyNm'];
                $addDisabled = '';
            }
            ?>
            <tr class="text-center">
                <td>
                    <input type="radio" id="layer_scm_<?php echo $val['sno'];?>" name="layer_scm" value="<?php echo $val['sno'];?>" <?=$addDisabled?> />
                </td>
                <td><?php echo number_format($page->idx--);?></td>
                <td>
                    <label for="layer_scm_<?php echo $val['sno'];?>" class="hand">
                        <?php echo $val['method'];?>
                        <input type="hidden" id="companyNm_<?php echo $val['sno'];?>" value="<?php echo gd_htmlspecialchars($val['companyNm']);?>" />
                        <input type="hidden" id="scmCommission_<?php echo $val['sno'];?>" value="<?php echo gd_htmlspecialchars($val['scmCommission']);?>" />
                        <input type="hidden" id="sno_<?php echo $val['sno'];?>" value="<?php echo $val['sno'];?>" />
                    </label>
                </td>
                <td><?php echo $scmType;?></td>
                <td><?php echo gd_date_format('Y-m-d', $val['regDt']);?></td>
            </tr>
            <?php
            $i++;
        }
    } else {
        ?>
        <tr>
            <td class="center" colspan="8">검색을 이용해 주세요.</td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
<div class="text-center"><?php echo $page->getPage('layer_da_list_search(\'PAGELINK\')');?></div>
<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black js-close" /></span></div>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $('.js-close').click(function(e){
            if ($('input[id*=\'layer_scm_\']:checked').length == 0) {
                alert('공급사를 선택해 주세요!');
                return false;
            }

            var scmNo = $('input[id*=\'layer_scm_\']:checked').val();
            var sno = $('#sno_' + scmNo).val();

            setAreaList(sno);

            $('div.bootstrap-dialog-close-button').click();
        });

        $('div.bootstrap-dialog-close-button').click(function() {
            // if ($(':button[name*=\'scmNoNm\']').length == 0) {
            if ($('[name=scmFl][value=n]', '#layer-wrap').length > 0 && $('input[name*="scmNo"]', '#layer-wrap').length == 0) {
                $('[name=scmFl][value=n]', '#layer-wrap').prop('checked',true);
            } else if ($('input[name*=\'scmNo\']').length == 0) {
                $('[name=scmFl][value=n]').prop('checked',true);
            }
        });

        $('input').keyup(function(e) {
            if (e.which == 13) layer_da_list_search();
        });

    });

    // 페이지 출력
    function layer_da_list_search(pagelink) {
        var companyNm = $('input[name=\'companyNm\']').val();
        var keyword = $('input[name=\'keyword\']').val();
        var layer_scmFl = $('input[name=\'layer_scmFl\']:checked').val();
        var layer_scmNo = $('input[name=\'layer_scmNo\']').val();
        var layer_scmNoNm = $('input[name=\'layer_scmNoNm\']').val();

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'parentFormID': '<?php echo $parentFormID?>',
            'dataFormID': '<?php echo $dataFormID?>',
            'dataInputNm': '<?php echo $dataInputNm?>',
            'mode': '<?php echo $mode?>',
            'layer_scmFl': layer_scmFl,
            'layer_scmNo': layer_scmNo,
            'layer_scmNoNm': layer_scmNoNm,
            'key': 'd.method',
            'keyword': keyword,
            'page': pagelink
        };

        $.get('<?php echo URI_ADMIN;?>policy/layer_delivery_area.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    // 화면출력
    function displayTemplate(data) {
        if (data.mode == 'input')   $("#" + data.parentFormID).html('');

        $.each(data.info, function (key, val) {
            var addHtml = '';
            var compiledData = {
                scmNoNm: val.scmNoNm,
                scmNo: val.scmNo,
                dataFormID: data.dataFormID,
                dataInputNm: data.dataInputNm,
                inputArr: (data.mode == 'radio' ? '' : '[]')
            };
            var complied = _.template($('#scmSimpleTemplate').html());
            if (data.mode == 'search') {
                complied = _.template($('#scmSearchTemplate').html());
            }
            addHtml += complied(compiledData);
            $('#' + data.parentFormID).html(addHtml);
        });

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5') && data.mode != 'check') {
            $('#' + data.parentFormID).prepend('<h5>선택된 공급사 : </h5>');
        }
    }
    //-->
</script>
