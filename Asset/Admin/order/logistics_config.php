<form id="frmBase" action="logistics_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="config">
    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        CJ 대한통운 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">CJ대한통운 고객 아이디</th>
            <td class="form-inline">
                <input type="text" name="CUST_ID" value="<?=$data['CUST_ID']?>" class="form-control width-lg"/>
                <input type="button" value="대한통운 아이디 인증" class="form-control btn-gray js-logistics-check"/>
                <input type="hidden" id="checkId" value="<?=$data['CUST_ID']?>"/>
            </td>
        </tr>
        <tr>
            <th>배송비 타입</th>
            <td class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="FRT_DV_CD" value="01" <?=$checked['FRT_DV_CD']['01']?>/>선불
                </label>
                <label class="radio-inline">
                    <input type="radio" name="FRT_DV_CD" value="02" <?=$checked['FRT_DV_CD']['02']?>/>착불
                </label>
                <label class="radio-inline">
                    <input type="radio" name="FRT_DV_CD" value="03" <?=$checked['FRT_DV_CD']['03']?>/>신용
                </label>
        </tr>
        <tr>
            <th class="require">보내는 사람 이름</th>
            <td class="form-inline">
                <input type="text" name="SENDR_NM" value="<?=$data['SENDR_NM']; ?>" class="form-control width-lg"/>
            </td>
        </tr>
        <tr>
            <th class="require">보내는 사람 전화번호</th>
            <td class="form-inline">
                <input type="text" name="SENDR_TEL_NO1" maxlength="4" value="<?=$data['SENDR_TEL_NO1']; ?>" class="js-number form-control width-3xs"/> -
                <input type="text" name="SENDR_TEL_NO2" maxlength="4" value="<?=$data['SENDR_TEL_NO2']; ?>" class="js-number form-control width-3xs"/> -
                <input type="text" name="SENDR_TEL_NO3" maxlength="4" value="<?=$data['SENDR_TEL_NO3']; ?>" class="js-number form-control width-3xs"/>
            </td>
        </tr>
        <tr>
            <th>보내는 사람 휴대폰번호</th>
            <td class="form-inline">
                <input type="text" name="SENDR_CELL_NO1" maxlength="4" value="<?=$data['SENDR_CELL_NO1']; ?>" class="js-number form-control width-3xs"/> -
                <input type="text" name="SENDR_CELL_NO2" maxlength="4" value="<?=$data['SENDR_CELL_NO2']; ?>" class="js-number form-control width-3xs"/> -
                <input type="text" name="SENDR_CELL_NO3" maxlength="4" value="<?=$data['SENDR_CELL_NO3']; ?>" class="js-number form-control width-3xs"/>
            </td>
        </tr>
        <tr>
            <th class="require">보내는 사람 주소</th>
            <td class="form-inline">
                <div class="form-inline mgb5">
                    <input type="text" name="zonecode" value="<?php echo $data['zonecode']; ?>" maxlength="5" class="form-control width-2xs"/>
                    <input type="hidden" name="zipcode" value="<?php echo $data['SENDR_ZIP_NO']; ?>"/>
                    <span id="zipcodeText" class="number <?php if (strlen($data['SENDR_ZIP_NO']) != 7) { echo 'display-none'; } ?>">(<?php echo $data['SENDR_ZIP_NO']; ?>)</span>
                    <input type="button" onclick="postcode_search('zonecode', 'address', 'zipcode');" value="우편번호찾기" class="btn btn-gray btn-sm"/>
                </div>
                <div class="form-inline">
                    <input type="text" name="address" value="<?php echo $data['SENDR_ADDR']; ?>" class="form-control width-2xl"/>
                    <input type="text" name="addressSub" value="<?php echo $data['SENDR_DETAIL_ADDR']; ?>" class="form-control width-2xl"/>
                </div>
            </td>
        </tr>
    </table>
</form>
<div class="notice-danger">계약사항과 동일하게 기재부탁드립니다. 실제 계약된 사항과 다르게 기입하게 될 경우, 부정한 용도로 판단하여 송장출력에 제한이 발생할 수 있습니다.</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.js-logistics-check').bind('click', function () {
            var custId = $('[name="CUST_ID"]').val();
            if(_.isEmpty(custId)) {
                alert('대한통운 고객 아이디를 입력해주세요.');
                return;
            }
            $.ajax({
                method: "POST",
                url: "logistics_ps.php",
                data: {
                    'mode': 'check',
                    'custId' : custId
                },
                dataType: 'text'
            }).success(function (data) {
                if(data == 'Y') {
                    $('#checkId').val(custId);
                    alert('대한통운 아이디가 인증되었습니다.');
                }
                else {
                    $('#checkId').val('');
                    alert('대한통운 아이디 인증에 실패하였습니다. 아이디를 다시 확인해주세요.');
                }
            }).error(function (e) {
                alert(e.responseText);
            });
        })

        $("#frmBase").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'CUST_ID' : {
                    required:true,
                    minlength: 2,
                    equalTo: '#checkId'
                },
                'SENDR_NM': "required",
                'SENDR_TEL_NO1': "required",
                'SENDR_TEL_NO2': "required",
                'SENDR_TEL_NO3': "required",
                'zonecode': "required",
                'address': "required",
                'addressSub': "required",
            },
            messages: {
                'CUST_ID': {
                    required: '대한통운 고객 아이디를 입력해주세요.',
                    minlength: '대한통운 고객 아이디를 3자이상 입력해주시기 바랍니다.',
                    equalTo: '대한통운 아이디 인증 후 저장이 가능합니다.',
                },
                'SENDR_NM': {
                    required: '보내는 사람 이름을 입력해주세요.'
                },
                'SENDR_TEL_NO1': {
                    required: '보내는 사람 전화번호를 입력해주세요.'
                },
                'SENDR_TEL_NO2': {
                    required: '보내는 사람 전화번호를 입력해주세요.'
                },
                'SENDR_TEL_NO3': {
                    required: '보내는 사람 전화번호를 입력해주세요.'
                },
                'zonecode': {
                    required: '우편번호를 입력해주세요.'
                },
                'address': {
                    required: '보내는 사람 주소를 입력해주세요.'
                },
                'addressSub': {
                    required: '보내는 사람 상세주소를 입력해주세요.'
                },
            }
        });
    });
</script>
