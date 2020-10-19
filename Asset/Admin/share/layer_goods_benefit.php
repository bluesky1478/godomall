<div>
    <?php
    if($mode == 'layer_benefit') {
        ?>
        <p class="notice-danger">
            종료된 혜택은 노출되지 않으며 특정기간 할인 혜택만 노출됩니다.
        </p>
        <?php
    }
    if($mode == 'layer' || $mode == 'layer_search_page') {
        ?>
        <p class="notice-danger">
            종료된 혜택은 노출되지 않습니다.
        </p>
        <?php
    }
    ?>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-xs"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tr>
                <th>혜택명</th>
                <td>
                    <input type="text" name="benefitNmSearch" value="<?php echo $search['benefitNm']; ?>" class="form-control width-xl"/>
                </td>
                <td rowspan="2">
                    <input type="button" value="검색" class="btn btn-black btn-hf"  onclick="layer_list_search(); ">
                </td>
            </tr>
            <?php
            if($mode != 'layer_benefit') {
                ?>
            <tr>
                <th>진행유형</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="benefitUseTypeSearch"
                                                       value="" <?= gd_isset($checked['benefitUseType']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="benefitUseTypeSearch"
                                                       value="nonLimit" <?= gd_isset($checked['benefitUseType']['nonLimit']); ?> />제한
                        없음</label>
                    <label class="radio-inline"><input type="radio" name="benefitUseTypeSearch"
                                                       value="newGoodsDiscount" <?= gd_isset($checked['benefitUseType']['newGoodsDiscount']); ?> />신상품
                        할인</label>
                    <label class="radio-inline"><input type="radio" name="benefitUseTypeSearch"
                                                       value="periodDiscount" <?= gd_isset($checked['benefitUseType']['periodDiscount']); ?> />특정기간
                        할인</label>
                </td>
            </tr>
                <?php
            }
            ?>
            <tr>
                <th>헤택 대상</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsDiscountGroupSearch" value="" <?=gd_isset($checked['goodsDiscountGroup']['']);?>>전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsDiscountGroupSearch" value="all" <?=gd_isset($checked['goodsDiscountGroup']['all']);?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="goodsDiscountGroupSearch" value="member" <?=gd_isset($checked['goodsDiscountGroup']['member']);?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="goodsDiscountGroupSearch" value="group" <?=gd_isset($checked['goodsDiscountGroup']['group']);?>>특정회원등급</label>
                </td>
            </tr>
        </table>
    </div>
</div>

<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width7p center">선택</th>
            <th class="width7p center">번호</th>
            <th class="width40p center">혜택명</th>
            <th class="width40p center">진행기간</th>
            <th class="width20p center">할인혜택 내용</th>
            <th class="width20p center">혜택 대상</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $arrUseType    = array('nonLimit' => '제한없음', 'newGoodsDiscount' => '신상품 할인', 'periodDiscount' => '특정기간 할인');
        $arrDiscountGroup        = array('all' => '전체', 'member' => '회원전용', 'group' => '특정회원등급');
        $arrNewGoodsReg        = array('regDt' => '등록일', 'modDt' => '수정일');
        $arrNewGoodsDate      = array('day' => '일', 'hour' => '시간');

        if (gd_isset($data) && is_array($data)) {
            $i = 0;
            foreach ($data as $key => $val) {
                $stateText = '';
                if($val['benefitUseType'] == 'periodDiscount') {

                    if ($val['periodDiscountStart'] < date('Y-m-d H:i:s') && $val['periodDiscountEnd'] > date('Y-m-d H:i:s')) {
                        $stateText = "<span class='text-blue'>진행중</span>";
                    } else if ($val['periodDiscountEnd'] < date('Y-m-d H:i:s')) {
                        $stateText = "<span class='text-red'>종료</span>";
                    } else if ($val['periodDiscountStart'] > date('Y-m-d H:i:s')) {
                        $stateText = "<span>대기중</span>";
                    }
                }
                if($val['benefitUseType'] == 'nonLimit'){
                    $benefitPeriod = '<span class="text-blue">'.$arrUseType[$val['benefitUseType']].'</span>';
                }else if($val['benefitUseType'] == 'newGoodsDiscount'){
                    $benefitPeriod = '<span class="text-blue">상품'.$arrNewGoodsReg[$val['newGoodsRegFl']].'부터 '.$val['newGoodsDate'].$arrNewGoodsDate[$val['newGoodsDateFl']].'까지</span>';
                }else{
                    $benefitPeriod ='<span>'.gd_date_format("Y-m-d H:i",$val['periodDiscountStart']).' ~ '.gd_date_format("Y-m-d H:i",$val['periodDiscountEnd']).'</span>';
                }
                ?>
                <tr>
                    <td class="center">
                    <?php
                    if($mode == 'layer_search_page') {
                    ?>
                        <input type="checkbox" id="layer_goods_benefit" name="layer_goods_benefit" value="<?php echo $val['sno']; ?>"/>
                    <?php
                    } else {
                    ?>
                        <input type="radio" id="layer_goods_benefit" name="layer_goods_benefit" value="<?php echo $val['sno']; ?>"/>
                    <?php
                    }
                    ?>
                    </td>
                    <td class="center"><?php echo number_format($page->idx--); ?></td>
                    <td><?php echo $val['benefitNm']; ?></td>
                    <td><?=$stateText?><div id="benefitPeriod<?=$val['sno']?>"><?=$benefitPeriod?></div></td>
                    <td class="center" id="goodsDiscount<?=$val['sno']?>">
                        <?php
                        $goodsDiscountGroup = gd_isset($val['goodsDiscountGroup'], 'all');
                        if (in_array($goodsDiscountGroup, ['all', 'member']) === true) {
                            if ($val['goodsDiscountUnit'] == 'percent') {
                                echo  $val['goodsDiscount'] . '%';
                            } else {
                                echo  gd_money_format($val['goodsDiscount']) . gd_currency_default();
                            }
                        } else {
                            ?>

                            <button type="button" class="btn btn-sm btn-white js-member-group-benefit" data-sno="<?=$val['sno'];?>">보기</button>
                            <?php
                        }
                        ?>
                    </td>
                    <td class="center" id="goodsDiscountGroup<?=$val['sno']?>"><?=$arrDiscountGroup[$val['goodsDiscountGroup']];?></td>

                    <input type="hidden" id="benefitNm<?=$val['sno']?>" value="<?=$val['benefitNm']?>">


                </tr>
                <?php
                $i++;
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="6">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>

        </tbody>
    </table>


    <div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
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

        $('.js-member-group-benefit').click(function(){
            var sno = $(this).data('sno');

            var title = "할인 혜택 상세보기";
            $.get('../goods/layer_benefit_detail.php',{ sno : sno }, function(data){

                data = '<div id="viewInfoForm">'+data+'</div>';

                var layerForm = data;

                BootstrapDialog.show({
                    title:title,
                    size: get_layer_size('normal'),
                    message: $(layerForm),
                    closable: true
                });
            });
        });

    });

    function layer_list_search(pagelink) {
        var benefitNm = $('input[name=\'benefitNmSearch\']').val();
        var benefitUseType = $('input[name=\'benefitUseTypeSearch\']:checked').val();
        var goodsDiscountGroup = $('input[name=\'goodsDiscountGroupSearch\']:checked').val();


        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'parentFormID': '<?php echo $parentFormID?>',
            'dataFormID': '<?php echo $dataFormID?>',
            'dataInputNm': '<?php echo $dataInputNm?>',
            'goodsBenefitSno': "<?php echo $goodsBenefitSno?>",
            'mode': '<?php echo $mode?>',
            'benefitNm': benefitNm,
            'benefitUseType': benefitUseType,
            'goodsDiscountGroup': goodsDiscountGroup,
            'pagelink': pagelink
        };

        $.get('../share/layer_goods_benefit.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function select_code() {

        if ($('input[id*=\'layer_goods_benefit\']:checked').length == 0) {
            alert('상품혜택을 선택해 주세요!');
            return false;
        }

        var applyGoodsCnt = 0;
        var chkGoodsCnt = 0;
        var resultJson = {
            mode: "<?php echo $mode?>",
            goodsBenefitSno: "<?php echo $goodsBenefitSno?>",
            parentFormID: "<?php echo $parentFormID?>",
            dataFormID: "<?php echo $dataFormID?>",
            dataInputNm: "<?php echo $dataInputNm?>",
            info: []
        };

        $('input[id*=\'layer_goods_benefit\']:checked').each(function () {
            var benefitSno = $(this).val();
            var benefitNm = $('#benefitNm'+benefitSno).val();
            var benefitPeriod = $('#benefitPeriod'+benefitSno).text();
            var goodsDiscountGroup = $('#goodsDiscountGroup'+benefitSno).text();
            var goodsDiscount = $('#goodsDiscount'+benefitSno).text();
            if($.trim(goodsDiscount) == '보기') goodsDiscount = '';

            resultJson.info.push({"benefitSno": benefitSno,"benefitNm": benefitNm,"benefitPeriod": benefitPeriod,"goodsDiscountGroup": goodsDiscountGroup,"goodsDiscount": goodsDiscount});
            applyGoodsCnt++;
        });

        displayTemplate(resultJson);

    }

    function displayTemplate(data) {
        if (data.dataInputNm == '') {
            data.dataInputNm = 'goodsBenefitSno[]';
        }

        var addHtml = '';
        var complied = _.template($('#goodsBenefitTemplate').html());

        <?php
        if($mode == 'layer_search_page') { // 상품리스트 검색 일 경우
        ?>
            data.dataInputNm = 'goodsBenefitSno[]';
            data.parentFormID = 'goodsSearchBenefitLayer';

            // 선택된 버튼 div 토글
            if (data.info.length > 0) {
                $('#frmSearchGoods #' + data.parentFormID).addClass('active');
                $('#frmSearchGoods #goodsBenefitNoneFlText').html('선택한 혜택 미적용 상품');
            } else {
                $('#frmSearchGoods #' + data.parentFormID).removeClass('active');
            }
            for (var dataKey in data.info) {
                var val = data.info[dataKey];
                addHtml += complied({
                    groupNm: val.benefitNm,
                    benefitPeriod: val.benefitPeriod,
                    goodsDiscountGroup: val.goodsDiscountGroup,
                    goodsDiscount: val.goodsDiscount,
                    groupSno: val.benefitSno,
                    dataFormID: data.dataFormID,
                    dataInputNm: data.dataInputNm,
                    inputArr: (data.mode == 'search' ? '[]' : '')
                });
            }
        $('#frmSearchGoods #' + data.parentFormID).html(addHtml);
        <?php
        } else {
        ?>
            var val = data.info[0];
            addHtml += complied({
                groupNm: val.benefitNm,
                benefitPeriod: val.benefitPeriod,
                goodsDiscountGroup: val.goodsDiscountGroup,
                goodsDiscount: val.goodsDiscount,
                groupSno: val.benefitSno,
                dataFormID: data.dataFormID,
                dataInputNm: data.dataInputNm,
                inputArr: (data.mode == 'search' ? '[]' : '')
            });
        $('#' + data.parentFormID).html(addHtml);
        <?php
        }
        ?>


        layer_close();
    }

    //-->
</script>
<script type="text/html" id="goodsBenefitTemplate">
    <?php
    if($mode == 'layer_search_page') {
        ?>
        <div id="<%=dataFormID%>_<%=groupSno%>" class="btn-group btn-group-xs" style="display:block;">
            <input type="hidden" name="<%=dataInputNm%><%=inputArr%>" value="<%=groupSno%>">
            <input type="hidden" name="goodsBenefitNm[]" value="<%=groupNm%>">
            <input type="hidden" name="goodsBenefitDiscount[]" value="<%=goodsDiscount%>">
            <input type="hidden" name="goodsBenefitDiscountGroup[]" value="<%=goodsDiscountGroup%>">
            <input type="hidden" name="goodsBenefitPeriod[]" value="<%=benefitPeriod%>">
            <span><%=groupNm%> (<%=goodsDiscount%><% if (goodsDiscountGroup != '특정회원등급') { %> - <%}%><%=goodsDiscountGroup%>) (<%=benefitPeriod%>)</span>
            <span><button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=groupSno%>" data-none="#goodsBenefitNoneFlText">삭제</button></span>
        </div>
    <?php
    } else {
        ?>
        <h5>선택된 혜택:</h5>
        <div id="<%=dataFormID%>_<%=groupSno%>" class="btn-group btn-group-xs">
            <input type="hidden" name="<%=dataInputNm%><%=inputArr%>" value="<%=groupSno%>">
            <span><%=groupNm%>(<%=goodsDiscount%><% if (goodsDiscountGroup != '특정회원등급') { %> - <%}%><%=goodsDiscountGroup%>) (<%=benefitPeriod%>)</span>
        <span>
            <button type="button" class="btn btn-sm btn-white" data-benefitSno="<%=groupSno%>" onclick="goods_benefit_popup('<%=groupSno%>')">수정</button>
            <button type="button" class="btn btn-sm btn-white" data-toggle="delete" data-target="#<%=dataFormID%>_<%=groupSno%>">삭제</button>
        </span>
        </div>
        <div><span class="notice-info">신상품 할인, 특정기간 할인 혜택을 상품에 적용할 경우, 혜택 종료 시 혜택 제외 설정도 함께 종료됩니다.</span></div>
        <?php
    }
    ?>

</script>