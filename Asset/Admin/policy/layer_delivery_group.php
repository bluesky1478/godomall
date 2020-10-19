<table class="table table-rows table-condensed" id="selectedCountries">
    <colgroup>
        <col class="width-lg" />
        <col />
    </colgroup>
    <thead>
    <tr>
        <th>배송 국가 그룹</th>
        <th>배송비 조건명</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($deliveryGroup as $val) { ?>
        <tr class="text-center">
            <td><?=$val['countryGroup']['groupName']?></td>
            <td><?=$val['scmDeliveryBasic']['method']?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="table-btn mgb0">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
    </div>
</form>

<!-- 선택된 국가 템플릿 시작 -->
<script type="text/template" id="selectedCountryTemplate">
    <tr class="text-center" data-country-code="<%=code%>">
        <td>
            <%=index%>
            <input type="hidden" name="countries[][code]" value="<%=code%>">
            <input type="hidden" name="countries[][countryIsoNo]" value="<%=isoNo%>">
            <input type="hidden" name="countries[][countryNameKor]" value="<%=countryNameKor%>">
        </td>
        <td><%=callPrefix%></td>
        <td><%=countryNameKor%></td>
        <td><button type="button" class="btn btn-sm btn-white btn-icon-minus js-delete-row">삭제</button></td>
    </tr>
</script>
<!--// 선택된 국가 템플릿 시작 -->

<script type="text/javascript">
    var selectedCountryIndex = 1;
    $(function() {
        // 클릭시 국가 추가
        $('.js-add-country').click(function(e) {
            // 값 초기화
            var code = $('#receiverCountrycode').val(),
                message = null;

            // 국가 선택 필수
            if (_.isUndefined(code)) {
                message = '국가 검색에서 국가를 선택해주세요.';
            }

            // 중복선택 방지
            if ($('#selectedCountries tbody tr').length) {
                $.each($('#selectedCountries tbody tr'), function(idx, obj) {
                    if ($(obj).data('country-code') == code) {
                        message = '이미 선택된 국가가 있습니다.';
                        return false;
                    }
                });
            }

            // 메시지 출력 후 종료
            if (message != null) {
                alert(message);
                return false;
            }

            // 데이터 가져오기
            $.get('../policy/mall_delivery_ps.php?mode=getCountriesView&code=' + code, function(data) {
                if (data.result === true) {
                    data.data.index = selectedCountryIndex++;
                    var compiled = _.template($('#selectedCountryTemplate').html());
                    $('#selectedCountries tbody').append(compiled(data.data));
                } else {
                    alert('국가정보를 불러올 수 없습니다. 잠시 후 다시 시도해주세요.');
                    return false;
                }
            });
        });

        // 배송그룹 삭제 mall_delivery_register.php의 이벤트 바인드
        $(document).bind('click', function(e){
            if ($(e.target).is('.js-delete-row')) {
                selectedCountryIndex--;
            }
        });

        // 폼 체크
        $('#frmLayer').validate({
            rules: {
                groupName: {
                    required: true
                }
            },
            messages: {
                groupName: {
                    required: "배송국가 그룹명을 입력해주세요."
                }
            },
            submitHandler: function (form) {
                var params = $(form).serializeObject();
                console.log(params);
                addSelectedCountryGroup(params);
                layer_close();
                return false;
            }
        });
        $('#btnConnectDomain').click(function () {
            $('#frmLayer').submit();
        });
    });
</script>
