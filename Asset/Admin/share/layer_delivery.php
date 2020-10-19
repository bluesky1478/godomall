<div>
    <div class="mgt10"></div>
    <div>
        <form id="layreDeliveryFrm">
        <table class="table table-cols no-title-line">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>배송비 조건명</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="keyword" value="<?php echo $search['keyword'];?>" class="form-control" />
                    </div>
                </td>
                <td rowspan="2"><input type="button" value="검색" class="btn btn-black btn-hf" onclick="layer_list_search();" /></td>
            </tr>
            <tr>
                <th>배송 정책</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="goodsDeliveryFl" value="all" <?php echo gd_isset($checked['goodsDeliveryFl']['all']); ?>/> 전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="goodsDeliveryFl" value="y" <?php echo gd_isset($checked['goodsDeliveryFl']['y']); ?>/> 배송비조건별
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="goodsDeliveryFl" value="n" <?php echo gd_isset($checked['goodsDeliveryFl']['n']); ?>/> 상품별
                    </label>
                    <div>
                    <?php foreach($searchData['fix'] as $k => $v) { ?>
                        <label class="checkbox-inline"><input type="checkbox" name="fixFl[]" value="<?=$k?>"  <?php echo gd_isset($checked['fixFl'][$k]); ?> <?php if($k =='all') { echo 'class="js-not-checkall" data-target-name="fixFl[]" checked="checked"'; } ?>><?=$v?></label>
                    <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-rows">
        <thead>
        <tr>
            <th>
                <?php if($mode == 'radio') { ?>선택 <?php } else { ?><input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'deliverChk[]');" /><?php } ?>
</th>
            <th>번호</th>
            <th>배송비조건명</th>
            <th>공급사구분</th>
            <th>배송방식</th>
            <th>배송비유형</th>
            <th>배송비설정</th>
            <th>배송비부과방법</th>
            <th>결제방법</th>
            <th>지역별추가배송비</th>
            <th>등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            foreach ($data as $row) {
                $deliveryMethodName = gd_get_delivery_method_display($row['deliveryMethodFl']);

                //배송명에 배송방식 포함하여 노출
                $deliveryNm = $row['method'];
                if(trim($deliveryMethodName) !== ''){
                    $deliveryNm .= ' ['.$deliveryMethodName.']';
                }
                ?>
                <tr class="text-center">
                    <td>
                        <?php if($mode == 'radio') { ?>
                            <input type="radio" id="layer_delivery_<?php echo $row['basicKey'];?>" name="deliverChk" value="<?php echo $row['basicKey'];?>" />
                        <?php } else { ?>
                            <input type="checkbox" id="layer_delivery_<?php echo $row['basicKey'];?>" name="deliverChk[]" value="<?php echo $row['basicKey'];?>" />
                        <?php } ?>
                        </td>
                    <td><?php echo number_format($page->idx--); ?></td>
                    <td>
                        <input type="hidden" id="deliveryNm_<?php echo $row['basicKey'];?>" value="<?php echo gd_htmlspecialchars($deliveryNm);?>" />
                        <button type="button" class="btn btn-sm js-tooltip" data-placement="right" title="<?=$row['description']?>"><?=$row['method'] . ($row['defaultFl'] != 'y' ? '' : '<br />(기본설정)')?></span></button>
                    </td>
                    <td><?=$row['companyNm']?></td>
                    <td><?=$deliveryMethodName?></td>
                    <td><?=$row['fixFlText']?></td>
                    <td>
                        <?php
                        if (!in_array($row['fixFl'], ['price','count','weight'])) {
                            if ($row['multipleDeliveryFl']) {
                                ?>
                                <button type="button" class="btn btn-xs btn-black js-layer-charge" data-sno="<?=$row['basicKey']?>">상세보기</button>
                                <?php
                            } else {
                                echo gd_money_format($row['price']) . '원';
                            }
                        } else {
                            ?>
                            <button type="button" class="btn btn-xs btn-black js-layer-charge" data-sno="<?=$row['basicKey']?>">상세보기</button>
                        <?php } ?>
                    </td>
                    <td><?=$row['goodsDeliveryFlText']?></td>
                    <td><?=$row['collectFlText']?></td>
                    <td><?=$row['areaFlText']?></td>
                    <td><?=gd_date_format('Y-m-d', $row['regDt'])?></td>
                </tr>
                <?php } } else { ?>
            <tr>
                <td colspan="12" class="no-data">
                    검색된 배송비 조건이 없습니다.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<div class="center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
<div class="center"><input type="button" value="확인" class="btn btn-black js-close" /></span></div>

<script type="text/javascript">
    <!--
    $(document).ready(function(){

        $('#layreDeliveryFrm input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });


        // 지역 및 배송비 추가 iframe 다이얼로그 호출
        $('.js-layer-charge').click(function(e){
            var addParam = {
                sno: $(this).data('sno')
            };
            $.get('../policy/layer_delivery_charge.php', addParam, function (data) {
                var layerForm = '<div id="chargeDelivery">' + data + '</div>';
                BootstrapDialog.show({
                    title: '배송비 설정 상세보기',
                    message: $(layerForm),
                    closable: true,
                    onhidden: function (dialog) {

                    }
                });
            });
        });

        $('.js-close').click(function(e){
            if ($('input[id*=\'layer_delivery_\']:checked').length == 0) {
                alert('배송비를 선택해 주세요!');
                return false;
            }

            var applyGoodsCnt	= 0;
            var chkGoodsCnt		= 0;
            var resultJson = {
                "mode": "<?php echo $mode?>",
                "parentFormID": "<?php echo $parentFormID?>",
                "dataFormID": "<?php echo $dataFormID?>",
                "dataInputNm": "<?php echo $dataInputNm?>",
                "scmFl": "<?php echo $scmFl?>",
                "scmNo": "<?php echo $scmNo?>",
                "info": []
            };

            $('input[id*=\'layer_delivery_\']:checked').each(function() {
                var deliveryNo		=$(this).val();
                var deliveryNm		= $('#deliveryNm_'+deliveryNo).val();
                if ($('#' + resultJson.dataFormID + '_' + deliveryNo).length == 0) {
                    resultJson.info.push({"deliveryNo": deliveryNo, "deliveryNoNm": deliveryNm});
                    applyGoodsCnt++;
                }
                chkGoodsCnt++;
            });

            if (applyGoodsCnt > 0) {
                <?php if($callFunc) { ?>
                <?=$callFunc?>(resultJson);
                <?php } else { ?>
                displayTemplate(resultJson);
                <?php } ?>

                if (applyGoodsCnt != chkGoodsCnt) {
                    alert('선택한 '+chkGoodsCnt+'개의 배송비 중 '+applyGoodsCnt+'개의 배송비가 추가 되었습니다.');
                }

                // 선택된 버튼 div 토글
                if (chkGoodsCnt > 0) {
                    $('#' + resultJson.parentFormID).addClass('active');
                } else {
                    $('#' + resultJson.parentFormID).removeClass('active');
                }

                $('div.bootstrap-dialog-close-button').click();
            } else {
                alert('동일한 배송비가 이미 존재합니다.');
            }
        });


        $('div.bootstrap-dialog-close-button').click(function() {
            if ($(':button[name*=\'deliveryNoNm\']').length == 0) {
                $('[name=deliveryFl][value=n]').prop('checked',true);
            }
        });


    });

    function layer_list_search(pagelink)
    {
        var keyword		= $('#layreDeliveryFrm input[name=\'keyword\']').val();
        var goodsDeliveryFl		=  $('#layreDeliveryFrm input[name="goodsDeliveryFl"]:checked').val();

        if (typeof pagelink == 'undefined') {
            pagelink		= '';
        }

        var parameters		= {
            'layerFormID'	: '<?php echo $layerFormID?>',
            'parentFormID'	: '<?php echo $parentFormID?>',
            'dataFormID'	: '<?php echo $dataFormID?>',
            'dataInputNm'	: '<?php echo $dataInputNm?>',
            'callFunc'	: '<?php echo $callFunc?>',
            'mode'      	: '<?php echo $mode?>',
            "scmFl": "<?php echo $scmFl?>",
            "scmNo": "<?php echo $scmNo?>",
            'key'      	: 'd.method',
            'goodsDeliveryFl'		: goodsDeliveryFl,
            'keyword'		: keyword,
            'pagelink'		: pagelink
        };


        $('#layreDeliveryFrm input[name="fixFl[]"]:checked').each(function (key, val) {
            parameters['fixFl['+key+']'] = $(this).val();
        });

        $.get('../share/layer_delivery.php', parameters, function(data){
            $('#<?php echo $layerFormID?>').html(data);
        });

    }

    // 화면출력
    function displayTemplate(data) {
        if(data.mode == 'input')   $("#"+data.parentFormID).html('');

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
            $('#' + data.parentFormID).prepend('<h5>선택된 배송비</h5>');
        }

        $.each(data.info, function (key,val) {
            var addHtml = '';
            var complied = _.template($('#deliverySearchTemplate').html());
            addHtml += complied({
                deliveryNoNm: val.deliveryNoNm,
                deliveryNo: val.deliveryNo,
                dataFormID: data.dataFormID,
                dataInputNm: data.dataInputNm,
                inputArr: (data.mode == 'search' ? '[]': '')
            });

            if(data.mode =='radio') $('#' + data.parentFormID).html(addHtml);
            else $('#' + data.parentFormID).append(addHtml);
        });


        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
            $('#' + data.parentFormID).prepend('<b>선택된 배송비 : </b>');
        }

    }
    //-->
</script>
<script type="text/html" id="deliverySearchTemplate">
    <div id="<%=dataFormID%>_<%=deliveryNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%><%=inputArr%>" value="<%=deliveryNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm<%=inputArr%>" value="<%=deliveryNoNm%>">
        <span class="btn"><%=deliveryNoNm%></span>
    </div>
</script>
