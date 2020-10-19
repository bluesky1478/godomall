<form id="frmOverseasDeliveryGroup" method="post" target="ifrmProcess" action="../policy/mall_delivery_ps.php">
    <input type="hidden" name="mode" value="groupModify"/>
    <input type="hidden" name="countryGroup[sno]" value="<?= $data['sno']; ?>"/>
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col />
        </colgroup>
        <tbody>
        <tr>
            <th>배송국가 그룹명</th>
            <td>
                <input type="text" name="countryGroup[groupName]" value="<?=$data['groupName']?>" class="width-xl" />
            </td>
        </tr>
        </tbody>
    </table>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col />
        </colgroup>
        <tbody>
        <tr>
            <th>국가 검색</th>
            <td class="form-inline">
                <?= gd_select_box('SearchCountrycode', null, $countryAddress, null, null, null, null, 'form-control'); ?>
                <button type="button" class="btn btn-sm btn-white btn-icon-plus js-add-country">추가</button>
            </td>
        </tr>
        <tr>
            <th>선택된 국가</th>
            <td>
                <table class="table table-rows" id="selectedCountries">
                    <colgroup>
                        <col class="width-xs" />
                        <col class="width-md" />
                        <col />
                        <col class="width-xs" />
                    </colgroup>
                    <thead>
                    <tr>
                        <th>NO</th>
                        <th>국제전화 국가코드</th>
                        <th>국가명</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<!-- 선택된 국가 템플릿 시작 -->
<script type="text/template" id="selectedCountryTemplate">
    <tr class="text-center" data-country-code="<%=code%>">
        <td><%=idx%></td>
        <td><%=callPrefix%></td>
        <td><%=countryNameKor%></td>
        <td>
            <input type="hidden" name="countries[sno][]" value="<%=sno%>">
            <input type="hidden" name="countries[code][]" value="<%=code%>">
            <input type="hidden" name="countries[countryIsoNo][]" value="<%=isoNo%>">
            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-delete-row">삭제</button>
        </td>
    </tr>
</script>
<!--// 선택된 국가 템플릿 시작 -->

<script type="text/javascript">
    var selectedCountryIndex = 1;

    /**
     * 선택된 국가 ROW 추가
     * @param data
     */
    function addSelectedCountryGroup(data) {
        data.idx = selectedCountryIndex;
        data.sno = (!_.isUndefined(data.sno) ? data.sno : '');
        var compiled = _.template($('#selectedCountryTemplate').html());
        $('#selectedCountries tbody').append(compiled(data));
        selectedCountryIndex++;
        $('.js-add-country').removeAttr('disabled');
    }

    $(function() {
        // 배송가능 국가 선택 영역 템플릿
        var countryGroupData = <?=json_encode($data['countries'])?>;
        if (countryGroupData !== null) {
            $.each(countryGroupData, function(key, val){
                addSelectedCountryGroup(val);
            });
        }

        // 클릭시 국가 추가
        $('.js-add-country').click(function(e) {
            // 중복 클릭 방지
            $(this).attr('disabled', 'disabled');

            // 값 초기화
            var code = $('#SearchCountrycode').val(),
                message = null;

            // 국가 선택 필수
            if (_.isEmpty(code)) {
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
                $(this).removeAttr('disabled');
                return false;
            }

            // 데이터 가져오기
            $.get('../policy/mall_delivery_ps.php?mode=getCountriesView&code=' + code, function(data) {
                if (data.result === true) {
                    addSelectedCountryGroup(data.data);
                } else {
                    alert('국가정보를 불러올 수 없습니다. 잠시 후 다시 시도해주세요.');
                    $(this).removeAttr('disabled');
                    return false;
                }
            });
        });

        // 테이블 ROW 삭제
        $(document).on('click', '.js-delete-row', function(e){
            $(this).closest('tr').remove();
            selectedCountryIndex--;
        });

        // 폼 체크
        $('#frmOverseasDeliveryGroup').validate({
            dialog: false,
            rules: {
                'countryGroup[groupName]': {
                    required: true
                }
            },
            messages: {
                'countryGroup[groupName]': {
                    required: "배송국가 그룹명을 입력해주세요."
                }
            },
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            }
        });
    });
</script>
