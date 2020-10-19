<div class="notice-danger">
    HS코드란, 세계무역기구(WTO) 및 세계관세기구(WCO)가 무역통계 및 관세분류의 목적상 수출입 상품을 숫자 코드로 분류화 한 것으로,<br/>
    수입 시 세금부과와 수출품의 통제 및 통계를 위한 중요한 분류법입니다.
    <div class="text-gray">
        해외 배송 시, 통관에서 반드시 필요한 항목입니다. (원산지, 영문상품명, HS코드 필수 입력)<br/>
        정확한 관세 계산을 위하여 대한민국을 제외한 국가의 HS 코드는 해당국가의 세분류 번호까지 입력되어있는 코드(8자리 ~ 10자리)만 선택 가능합니다.
    </div>
</div>

<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup><col class="width-md" /><col class="width-md" /><col /></colgroup>
            <tr>
                <th>HS코드 검색</th>
                <td>
                    <select name="hscodeGroup">
                        <option value="">전체</option>
                        <?php foreach($hscodeGroup as $k => $v) {
                            $data = explode("\t",$v);
                            ?>
                        <option value="<?=$data[0]?>" <?=$selected['hscodeGroup'][$data[0]]?>><?=$data[2]?></option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <div class="form-inline">
                        <input type="text" name="hscodeName" value="<?php echo $hscodeName;?>" class="form-control" />
                        <input type="button" value="검색" class="btn btn-black btn-hf" onclick="layer_list_search();" />
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>


<table class="table table-cols no-title-line" style="margin-bottom:0px;">
    <tr>
        <th class="width7p">선택</th>
        <th class="width10p">HS코드</th>
        <th class="width50p">한글품명</th>
        <th>영문품명</th>
    </tr>
</table>
<div <?php if($hscodeList) { ?>style="overflow-y:auto;height:300px;"<?php } ?>>
<table class="table table-cols no-title-line">
    <colgroup><col class="width7p" /><col class="width10p" /><col class="width50p" /><col /></colgroup>
    <?php if($hscodeList) {
        foreach($hscodeList as $k => $v) { ?>
            <tr>
                <td><input type="radio" name="hscode" value="<?=$v[0]?>"></td>
                <td><?=$v[0]?></td>
                <td><?=$v[1]?></td>
                <td><?=$v[2]?></td>
            </tr>
            <?php
        }
    } else { ?>
        <td class="center" colspan="4">검색을 이용해 주세요.</td>
    <?php } ?>
</table>
</div>
<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black js-close" /></span></div>


<script type="text/javascript">
    <!--


    $(document).ready(function () {

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });

        $('.js-close').click(function(e){
            if ($('input[name=\'hscode\']:checked').length == 0) {
                alert('HS코드를 선택해주세요!');
                return false;
            }

            <?=$callFunc?>(<?php echo $hscodeIndex?>,$('input[name=\'hscode\']:checked').val());
            $('div.bootstrap-dialog-close-button').click();
        });

    });

    function layer_list_search() {
        var hscodeName = $('input[name=\'hscodeName\']').val();
        var hscodeGroup = $('select[name=\'hscodeGroup\']').val();
        if(hscodeGroup =='' && hscodeName =='') {
            alert('검색어를 입력해주세요');
            return false;
        }

        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'parentFormID': '<?php echo $parentFormID?>',
            'dataFormID': '<?php echo $dataFormID?>',
            'dataInputNm': '<?php echo $dataInputNm?>',
            'mode': '<?php echo $mode?>',
            'callFunc': '<?php echo $callFunc?>',
            'search': '<?php echo $search?>',
            'hscode': '<?php echo $hscode?>',
            'hscodeIndex': '<?php echo $hscodeIndex?>',
            'hscodeGroup': hscodeGroup,
            'hscodeName': hscodeName
        };
        $.get('../share/layer_hscode.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    //-->
</script>

