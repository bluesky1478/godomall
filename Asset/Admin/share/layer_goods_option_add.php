<div id="optionList">
</div>
<div id="optionBtn" class="center">
    <input type="button" value="적용" class="btn btn-lg btn-black" onclick="addOptionsToMain()">
    <input type="button" value="취소" class="btn btn-lg btn-white" onclick="$('.close').click()">
</div>
<script type="text/javascript">
    function addOptionsToMain(){
        var sno = parseInt($('#optionGridTable:first > tbody > tr:last > td')[1].innerText);
        var optCnt = parseInt($('#optionY_optionCnt').val());

        for (addOptions=0;addOptions<$('[name*="optionLayerAdd[optionValue]"]').length;addOptions++){
            if($('[name*="optionLayerAdd[optionValue]"]')[addOptions].value.trim() == ""){
                alert('옵션값을 입력해주세요!');
                return;
            }
        }

        for(addOptions=0;addOptions<$('[name="option[costPrice][]"]').length;addOptions++){
            var optionNm = Array();
            for(i=2;i<optCnt+2;i++){
                optionNm.push($('[name="optionLayerAdd[optionValue]['+(i-2)+'][]"]')[addOptions].value);
            }
            var optNmStr = optionNm.join("<?=STR_DIVISION?>");
            if($("[value='"+optNmStr+"']").length > 0){
                continue;
            }

            var template = $('#optionGridTable:first > tbody > tr:last')[0].outerHTML;
            template = template.replace('tbl_option_info_'+sno, 'tbl_option_info_'+(sno+1));
            $('#optionGridTable:first > tbody').append(template);
            $('#optionGridTable:first > tbody > tr:last > td')[1].innerText = (sno + 1);
            $("[name='optionY[sno][]']:last").val("");
            $("[name='optionY[optionValueText][]']:last").val(optNmStr);


            for(i=2;i<optCnt+2;i++){
                $('[name="optionY[optionNo][]"]:last').val(sno+1);
                $('#optionGridTable:first > tbody > tr:last > td')[i].innerText = $('[name="optionLayerAdd[optionValue]['+(i-2)+'][]"]')[addOptions].value;
            }

            $('[name="optionY[optionCostPrice][]"]:last').val($('[name="option[costPrice][]"]:eq('+addOptions+')').val());
            $('[name="optionY[optionPrice][]"]:last').val($('[name="option[price][]"]:eq('+addOptions+')').val());
            $('[name="optionY[stockCnt][]"]:last').val($('[name="option[stock][]"]:eq('+addOptions+')').val());
            //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
            //$('[name="optionY[optionStopFl][]"]:last').val($('[name="option[stopUse][]"]:eq('+addOptions+')').val());
            //$('[name="optionY[optionStopCnt][]"]:last').val($('[name="option[stopCnt][]"]:eq('+addOptions+')').val());
            //$('[name="optionY[optionRequestFl][]"]:last').val($('[name="option[requestUse][]"]:eq('+addOptions+')').val());
            $('[name="optionY[optionViewFl][]"]:last').val($('[name="option[display][]"]:eq('+addOptions+')').val());
            $('[name="optionY[optionSellFl][]"]:last').val($('[name="option[soldout][]"]:eq('+addOptions+')').val());
            $('[name="optionY[optionDeliveryFl][]"]:last').val($('[name="option[delivery][]"]:eq('+addOptions+')').val());
            //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
            //$('[name="optionY[optionRequestCnt][]"]:last').val($('[name="option[requestCnt][]"]:eq('+addOptions+')').val());
            $('[name="optionY[optionCode][]"]:last').val($('[name="option[optionCode][]"]:eq('+addOptions+')').val());
            $('[name="optionY[optionMemo][]"]:last').val($('[name="option[optionMemo][]"]:eq('+addOptions+')').val());
            sno += 1;
        }

        $('.close').click();
    }

    function removeOption(cnt){
        $('#addOption1_'+cnt).remove();
        $('#addOption2_'+cnt).remove();
    }

    function addNewOption(){
        var optionCnt = $('#optionY_optionCnt').val();
        var addOptionListCnt = $('#optionList > div').length / 2;

        if(addOptionListCnt >= 10){
            alert('옵션 추가는 한번에 10개까지 가능합니다.');
            return;
        }

        var addHtml = '';
        var addOptionCnt = '<span id="addOptionTitleCnt"></span>';
        var utilBtn = '<input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus" onclick="addNewOption()">';
        if (addOptionListCnt > 0){
            addOptionCnt = addOptionListCnt + 1;
            $('#addOptionTitleCnt').html('1');
            utilBtn += '<input type="button" class="btn btn-sm btn-white btn-icon-minus btn-remove" onclick="removeOption('+addOptionListCnt+')" value="삭제">';
        }


        addHtml += '<div id="addOption1_'+addOptionListCnt+'">'
        addHtml += '<table width="100%"><tr><td><strong>옵션정보'+addOptionCnt+'</strong></td><td align="right">'+utilBtn+'</td></tr></table>';
        addHtml += '<table id="optionGridTable" class="table table-cols">';
        addHtml += '<thead>';
        addHtml += '<tr id="optionListTitle_'+addOptionListCnt+'"></tr>';
        addHtml += '</thead>';
        addHtml += '<tbody>';
        addHtml += '<tr id="optionListValue_'+addOptionListCnt+'"></tr>';
        addHtml += '</tbody>';
        addHtml += '</table>';
        addHtml += '</div>';

        $('#optionList').append(addHtml);
        addHtml = ''

        for(i=0; i< optionCnt; i++) {
            addHtml += '<th class="width10p">'+$('#option_optionName_layer_' + i).val()+'</th>';
        }
        $('#optionListTitle_'+addOptionListCnt).append(addHtml);

        addHtml = ''
        for(i=0; i< optionCnt; i++) {
            addHtml += '<td><input type="text" name="optionLayerAdd[optionValue]['+i+'][]" value="" class="form-control" style="width:100%;" placeholder="옵션값을 입력하세요." maxlength="100" ></td>';
        }

        $('#optionListValue_'+addOptionListCnt).append(addHtml);
        addHtml = '';

        addHtml += '<div id="addOption2_'+addOptionListCnt+'">'
        addHtml += '<table id="optionGridTable" class="table table-cols">';
        addHtml += '<thead>';
        addHtml += '<tr>';
        addHtml += '<th>옵션매입가</th>';
        addHtml += '<th>옵션가</th>';
        addHtml += '<th>재고량</th>';
        //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
        //addHtml += '<th>판매중지수량</th>';
        //addHtml += '<th>확인요청수량</th>';
        addHtml += '</tr>';
        addHtml += '</thead>';
        addHtml += '<tbody>';
        addHtml += '<tr>';
        addHtml += '<td><input type="text" name="option[costPrice][]" class="form-control" style="width:100%"></td>';
        addHtml += '<td><input type="text" name="option[price][]" class="form-control" style="width:100%"></td>';
        addHtml += '<td><input type="text" name="option[stock][]" class="form-control" style="width:100%"></td>';
        //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
        //addHtml += '<td><select class="form-control" name="option[stopUse][]"><option value="y">사용함</option><option value="n">사용안함</option></select><input type="text" name="option[stopCnt][]" class="form-control" style="width:100%"></td>';
        //addHtml += '<td><select class="form-control" name="option[requestUse][]"><option value="y">사용함</option><option value="n">사용안함</option></select><input type="text" name="option[requestCnt][]" class="form-control" style="width:100%"></td>';
        addHtml += '</tr>';
        addHtml += '</tbody>';
        addHtml += '</table>';
        addHtml += '<table id="optionGridTable" class="table table-cols">';
        addHtml += '<thead>';
        addHtml += '<tr>';
        addHtml += '<th>옵션노출상태</th>';
        addHtml += '<th>옵션품절상태</th>';
        addHtml += '<th>옵션배송상태</th>';
        addHtml += '<th>자체옵션코드</th>';
        addHtml += '<th>메모</th>';
        addHtml += '</tr>';
        addHtml += '</thead>';
        addHtml += '<tbody>';
        addHtml += '<tr>';
        addHtml += '<td><select class="form-control" name="option[display][]"><option value="y">노출함</option><option value="n">노출안함</option></select></td>';
        addHtml += '<td><select class="form-control" name="option[soldout][]"><?php foreach($stockReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?></select></td>';
        addHtml += '<td><select class="form-control" name="option[delivery][]"><?php foreach($deliveryReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?></select></td>';
        addHtml += '<td><input type="text" name="option[optionCode][]" class="form-control" style="width:100%"></td>';
        addHtml += '<td><input type="text" name="option[optionMemo][]" class="form-control" style="width:100%"></td>';
        addHtml += '</tr>';
        addHtml += '</tbody>';
        addHtml += '</table>';
        addHtml += '</div>';

        $('#optionList').append(addHtml);
    }

    addNewOption();
</script>