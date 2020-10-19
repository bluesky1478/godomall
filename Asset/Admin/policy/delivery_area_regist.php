<form id="frmDeliveryAreaGroupRegist" name="frmDeliveryAreaGroupRegist" enctype="multipart/form-data" action="delivery_ps.php" method="post">
    <input type="hidden" name="mode" value="area_regist"/>
    <input type="hidden" name="popupMode" value="<?=$popupMode?>"/>
    <input type="hidden" name="sno" value="<?= $data['sno'] ?>">

    <?php if(!$isAjax) { ?>
        <div class="page-header js-affix">
            <h3><?php echo end($naviMenu->location); ?></h3>
            <div class="btn-group">
                <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./delivery_area.php');" />
                <input type="submit" value="저장" class="btn btn-red">
            </div>
        </div>
    <?php } ?>

    <div class="table-title gd-help-manual">
        기본설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md">
            <col>
        </colgroup>
        <tbody>
        <?php if (gd_use_provider()) { ?>
            <?php if ($editFl == 'T' && $data['scmNo'] == '0') { ?>
                <tr>
                    <th>공급사 구분</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio"  <?php echo gd_isset($checked['scmFl'][0]); ?> disabled />본사
                        </label>
                        <label class="radio-inline">
                            <input type="radio" <?php echo gd_isset($checked['scmFl'][1]); ?> disabled /> 공급사
                        </label>
                    </td>
                </tr>
                <input type="hidden" name="scmFl" value="<?= $data['scmFl'] ?>">
                <input type="hidden" name="scmNo" value="<?= $data['manage']['scmNo'] ?>"/>
                <input type="hidden" name="scmNoNm" value="<?= $data['manage']['companyNm'] ?>"/>
            <?php } else { ?>
                <tr>
                    <th>공급사 구분</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="0" <?php echo gd_isset($checked['scmFl'][0]); ?> />본사
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="1" class="js-layer-register" <?php echo gd_isset($checked['scmFl'][1]); ?> data-type="scm" data-mode="radio"/> 공급사
                        </label>
                        <button type="button" name="scmFlButton" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="radio">공급사 선택</button>

                        <div id="scmLayer" class="selected-btn-group <?=$data['manage']['scmNo'] > 1 ? 'active' : ''?>">
                            <?php if (gd_isset($data['manage']) && $data['manage']['scmNo'] > 1) { ?>
                                <h5>선택된 공급사 : </h5>
                                <div id="idscm" class="btn-group btn-group-xs">
                                    <input type="hidden" name="scmNo" value="<?= $data['manage']['scmNo'] ?>"/>
                                    <input type="hidden" name="scmNoNm" value="<?= $data['manage']['companyNm'] ?>"/>
                                    <span class="btn"><?= $data['manage']['companyNm'] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#idscm">삭제</button>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="scmNo" value="1"/>
                                <input type="hidden" name="scmNoNm" value="본사"/>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
        <tr>
            <th class="require">지역별 추가배송비명</th>
            <td>
                <input type="text" name="method" value="<?= $data['method'] ?>" class="form-control width-lg js-maxlength" maxlength="20">
            </td>
        </tr>
        <tr>
            <th>지역별 추가배송비 설명</th>
            <td>
                <input type="text" name="description" value="<?= $data['description'] ?>" class="form-control width-2xl js-maxlength" maxlength="100">
            </td>
        </tr>
        <?php if (gd_use_provider()) { ?>
            <tr id="scmUseTr" <?php if ($editFl == 'T' && $data['scmFl'] == '1') { ?>style="display:none;"<?php } ?>>
                <th>공급사 사용 여부</th>
                <td>
                    <?php if ($useCount == 0) { ?>
                        <label class="radio-inline">
                            <input type="radio" name="scmUseFl" value="1" <?php echo gd_isset($checked['scmUseFl'][1]); ?>/>사용 가능
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmUseFl" value="0" <?php echo gd_isset($checked['scmUseFl'][0]); ?>/> 사용 불가
                        </label>
                    <?php } else { ?>
                        <label class="radio-inline">
                            <input type="radio" <?php echo gd_isset($checked['scmUseFl'][1]); ?> disabled/>사용 가능
                            <input type="radio" name="scmUseFl" value="1" <?php echo gd_isset($checked['scmUseFl'][1]); ?> style="display:none;" />
                        </label>
                        <label class="radio-inline">
                            <input type="radio" <?php echo gd_isset($checked['scmUseFl'][0]); ?> disabled/> 사용 불가
                            <input type="radio" name="scmUseFl" value="0" <?php echo gd_isset($checked['scmUseFl'][0]); ?> style="display:none;" />
                        </label>
                        <ul class="notice-info mgt5 mgl0 mgb5">
                            <li>해당 지역별 추가배송비가 적용된 공급사의 배송비조건이 존재하는 경우 수정이 불가합니다.</li>
                        </ul>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <p class="mgb30">
        <label class="checkbox-inline">
            <?php if ($data['count'] != 1 && $data['defaultFl'] != 'y') { ?>
                <input type="checkbox" name="defaultFl" value="y" class="js-default-fl"/>
            <?php } else { ?>
                <input type="checkbox" name="defaultFl" checked="checked" disabled="disabled"/>
                <input type="hidden" name="defaultFl" value="y"/>
            <?php } ?>
            배송비조건 등록 시 기본으로 노출되도록 설정합니다.
            <?php if (gd_use_provider()) { ?>
                <span id="scmUseBlock" style="display:<?php if (gd_isset($checked['scmUseFl'][0]) != '') {?>none;<?php } ?>">(공급사 동일 적용)</span>
            <?php } ?>
        </label>
    </p>

    <div class="table-title gd-help-manual">
        지역 및 배송비 추가
    </div>
    <div id="frmDeliveryAreaRegist">
        <table class="table table-cols mgb15">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>추가방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="addMethod" value="d" checked="checked" /> 주소 검색
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="addMethod" value="s" /> 주소 선택
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="addMethod" value="e" /> 엑셀파일등록
                    </label>
                    <!--                    <label class="radio-inline">-->
                    <!--                        <input type="radio" name="addMethod" value="b" /> 기본지역리스트 적용-->
                    <!--                    </label>-->
                </td>
            </tr>
            </tbody>
            <tbody id="addMethodDirect">
            <tr>
                <th class="require">지역 검색</th>
                <td>
                    <label><input type="text" class="form-control width-2xl js-maxlength" id="searchKeyword" name="searchKeyword" placeholder="시/군/구, 지번주소(동/읍/면), 도로명주소를 입력해주세요."/></label>
                    <button id="addrSearchBtn" type="button" class="btn btn-black btn-sm">검색</button>
                </td>
            </tr>
            <tr>
                <th class="require">지역 선택</th>
                <td>
                    <div class="width50p" style="max-height: 250px; overflow:auto">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width5p" />
                                <col/>
                            </colgroup>
                            <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="allAddress" value="y" onclick="check_toggle(this.id,'selectAddress');"/>
                                </th>
                                <th>지역명</th>
                            </tr>
                            </thead>
                            <tbody id="searchResult">
                            <tr>
                                <td colspan="2" class="no-data">검색된 지역이 없습니다.</td>
                                <td colspan="2" class="loading_td" style="display:none;">
                                    <div class="loading" style="width:100%;height:48px;opacity:0.5;background-position-x: 50%;"></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody id="addMethodExcel" style="display:none;">
            <tr>
                <th>엑셀파일등록<br /><a href="../policy/delivery_ps.php?mode=downloadDeliveryArea" class="btn btn-xs btn-black">샘플파일보기</a></th>
                <td class="form-inline">
                    <input type="file" name="excel" class="form-control input-group-item">
                </td>
            </tr>
            </tbody>
            <tbody id="addMethodSelect" style="display:none;">
            <tr>
                <th>지역 검색</th>
                <td>
                    <div class="form-inline">
                        <?php echo gd_select_box('newAreaSido', 'newAreaSido', $searchSidoArr, null, gd_isset($search['keyPost'])); ?>
                        <select id="newAreaGugun" name="newAreaGugun" class="form-control">
                            <option>시/군/구 선택</option>
                        </select>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody id="newPrice">
            <tr>
                <th class="require">추가배송비</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="newPrice" value="" class="form-control width-sm js-number" maxlength="8"/> 원
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="notice-info">
            추가배송비가 적용될 행정구역단위까지 입력해주세요. 예)전라남도 신안군 흑산면<br>
            시/구/군 이하 지역이 모두 적용되는 경우 ‘주소 선택’추가 방법으로 ‘시/도+시/군/구’ 단위의 주소를 입력할 수 있습니다. 예)경상북도 울릉군
        </div>
        <ul class="notice-danger">
            <li>지번주소와 도로명주소에 상관없이 주소는 최대 1,000개까지 입력 가능합니다.</li>
            <li>중복되는 지역의 지번주소와 도로명주소에 서로 다른 배송비 금액이 입력된 경우, 주문서에서 더 높은 금액의 배송비가 자동 부과됩니다.</li>
            <li>추가배송비가 정상적으로 부과되지 않는 경우 '시/도, 시/구/군'명이 정상적으로 등록되었는지 확인해주세요.</li>
        </ul>
        <div class="linepd30"></div>
        <div id="addMethodButton" class="table-btn">
            <button type="button" class="btn btn-black btn-lg js-add-delivery" data-sno="<?=$data[0]['basicKey']?>">추가</button>
        </div>
    </div>
</form>

<form id="frmDeliveryAreaRegistList">
    <div class="table-title">추가지역 리스트 <button type="button" id="deliveryAreaList" class="btn btn-sm btn-gray">추가지역 리스트 불러오기</button></div>
    <div class="table-header">
        <div class="pull-left">
            전체 <strong><?php echo number_format(count($areaData)); ?></strong>건
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?php echo gd_select_box('sort', 'sort', $sortList, null, 'addRegDt desc'); ?>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-rows">
            <colgroup>
                <col class="width5p" />
                <col class="width15p" />
                <col />
                <col class="width20p" />
            </colgroup>
            <thead>
            <tr>
                <th>
                    <input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'selectArea');"/>
                </th>
                <th>번호</th>
                <th>지역명</th>
                <th>추가배송비</th>
            </tr>
            </thead>
            <thead class="subhead">
            <tr>
                <th colspan="3" class="text-right">
                    <button type="button" class="btn btn-black btn-sm js-apply-addprice">추가배송비 일괄적용</button>
                </th>
                <th class="text-center">
                    <div class="form-inline text-default">
                        <?=gd_currency_symbol()?> <input type="text" class="form-control js-number" name="applyAddPrice" value="" /> <?=gd_currency_string()?>
                    </div>
                </th>
            </tr>
            </thead>
            <tbody id="areaDeliveryRow">
            <?php
            if (gd_isset($areaData) && is_array($areaData)) {
                $i = 0;
                $totalCount = count($areaData);
                foreach ($areaData as $key => $val) {
                    ?>
                    <tr class="text-center">
                        <td>
                            <input type="checkbox" name="selectArea[]" value="<?=$val['sno']?>"/>
                            <input type="hidden" name="addSno[]" value="<?=$val['sno']?>" />
                            <input type="hidden" name="addAreaCode[]" value="<?=$val['addAreaCode']?>" />
                            <input type="hidden" name="addArea[]" value="<?=$val['addArea']?>" />
                            <input type="hidden" name="addRegDt[]" value="<?=strtotime($val['regDt'])?>" />
                        </td>
                        <td><?=$totalCount - $key?></td>
                        <td><?=$val['addArea']?></td>
                        <td>
                            <div class="form-inline">
                                <?=gd_currency_symbol()?> <input type="text" class="form-control js-number" name="addPrice[]" value="<?=$val['addPrice']?>" /> <?=gd_currency_string()?>
                            </div>
                        </td>
                    </tr>
                    <?php
                    unset($tmp);
                    $i++;
                }
            }
            ?>
            </tbody>
            <tbody id="noList" style="display: <?=gd_isset($areaData) && is_array($areaData) ? '' : 'none'?>">
            <tr>
                <td colspan="4" class="no-data">지역별 추가배송비 내역이 없습니다.</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-selected-delete">선택 삭제</button>
        </div>
    </div>
</form>

<script id="areaDeliveryListTemplate" type="text/html">
    <tr class="text-center">
        <td>
            <input type="checkbox" name="selectArea[]" value="<%=addSno%>"/>
            <input type="hidden" name="addSno[]" value="<%=addSno%>" />
            <input type="hidden" name="addAreaCode[]" value="<%=addAreaCode%>" />
            <input type="hidden" name="addArea[]" value="<%=addArea%>" />
            <input type="hidden" name="addRegDt[]" value="<%=addRegDt%>" />
        </td>
        <td><%=addSno%></td>
        <td><%=addArea%></td>
        <td>
            <div class="form-inline">
                <input type="text" class="form-control js-number" name="addPrice[]" value="<%=addPrice%>" /> 원
            </div>
        </td>
    </tr>
</script>

<script type="text/javascript">
	<!--
	// 추가지역 리스트 건수 관련 처리
	var totalCount = getTotalCount();
	function getTotalCount() {
		return $('#frmDeliveryAreaRegistList .pull-left strong').text();
	}
	function setTotalCount() {
		totalCount = $('#areaDeliveryRow > tr').length;

		if (totalCount < 0) {
			totalCount = 0;
		}

		// 번호 자동 매김
		$.each($('#areaDeliveryRow tr'), function(key, val) {
			$(val).find('td:eq(1)').text(totalCount - key);
		});

		$('#frmDeliveryAreaRegistList .pull-left strong').text(totalCount);
		$('#noList').css({display: (totalCount == 0 ? '' : 'none')});
	}

	// 지역별 추가배송비 테이블 행 추가 (데이터 포함)
	function addAreaDeliveryRow(params) {
		if (!$.isEmptyObject(params) && params.hasOwnProperty('addSno') && params.hasOwnProperty('addAreaCode')) {
			var compiled = _.template($('#areaDeliveryListTemplate').html());
			$('#areaDeliveryRow').prepend(compiled(params));
			setTotalCount();
		}
	}

	// 수정시 기존 데이터를 사용하기 위해 clone 처리
	var clone = [];

	$(document).ready(function () {
		setTotalCount();

		// 추가배송비 일괄적용
		$(document).on('click', '.js-apply-addprice', function(e){
			if ($('input[name="applyAddPrice"]').val() == '') {
				alert('일괄 적용할 배송비를 입력해주세요.');
				return false;
			}
			$('input[name="addPrice[]"]').val($('input[name="applyAddPrice"]').val());
		});

		// 폼검증
		$("#frmDeliveryAreaGroupRegist").validate({
			debug: false,
			submitHandler: function (form) {
				// 엑셀파일 등록이 아닌 경우 처리
				if ($('input[name=addMethod]:checked').val() != 'e') {
					// 지역별 추가배송비
					if ($('#frmDeliveryAreaRegistList').valid() == false) {
						return false;
					}

					// 동적으로 폼 엘리먼트 생성
					var areaList = $('#frmDeliveryAreaRegistList').serializeObject();
					if (!$.isEmptyObject(areaList) && areaList.addArea) {
						$.each(areaList.addArea, function(idx){
							$('#frmDeliveryAreaGroupRegist')
								.append('<input type="hidden" name="add[sno][' + idx + ']" value="' + areaList.addSno[idx] + '" />')
								.append('<input type="hidden" name="add[addAreaCode][' + idx + ']" value="' + areaList.addAreaCode[idx] + '" />')
								.append('<input type="hidden" name="add[addArea][' + idx + ']" value="' + areaList.addArea[idx] + '" />')
								.append('<input type="hidden" name="add[addPrice][' + idx + ']" value="' + areaList.addPrice[idx] + '" />');
						});
					} else {
						alert('추가지역 리스트를 등록하세요.');
						return false;
					}
				}

				// 공용
                <?php if (gd_is_provider()) { ?>
				form.target = 'ifrmProcess';
				form.submit();
                <?php } else { ?>
				var checkSubmit = false;
				if ($('input[name=scmNo]').val() == '1') {
					if ($('input[name=defaultFl]').prop("checked") && $('input[name=scmUseFl]:checked').val() == '1') {
						var data = {
							'mode': 'checkDefaultDeliveryArea'
						};

						$.ajax({
							url: './delivery_ps.php',
							data: data,
							type: 'GET',
							dataType: 'JSON',
							async: false,
							success: function (data) {
								if (data.cnt > 0) {
									dialog_confirm('공급사에 이미 기본 설정으로 지정된 지역별 추가배송비가 있습니다.\n변경하면 모든 공급사의 기존 기본설정이 제외되고 해당 추가배송비가 기본으로 적용됩니다.', function (result) {
										if (result) {
											form.target = 'ifrmProcess';
											form.submit();
										}
									});
								} else {
									checkSubmit = true;
								}
							}
						});
					} else {
						checkSubmit = true;
					}
				} else {
					checkSubmit = true;
				}

				if (checkSubmit) {
					form.target = 'ifrmProcess';
					form.submit();
				} else {
					return false;
				}
                <?php } ?>
			},
			// onclick: false, // <-- add this option
			rules: {
				method: 'required',
				excel: {
					required: function() {
						return ($('input[name=addMethod]:checked').val() == 'e');
					},

				}
			},
			messages: {
				method: {
					required: "지역별 추가배송비명을 입력하세요.",
				},
				excel: {
					required: "지역별 추가배송비를 추가할 엑셀파일을 등록해주세요."
				}
			}
		});

		// 정렬하기
		$('#sort').change(function(e){
			var sort = [];
			$('#areaDeliveryRow tr').not('#noList').each(function(idx){
				sort.push({
					addSno: '',//$(this).find('[name="addSno[]"]').val() ? $(this).find('[name="addSno[]"]').val() : '',
					addAreaCode: $(this).find('[name="addAreaCode[]"]').val(),
					addArea: $(this).find('[name="addArea[]"]').val(),
					addPrice: $(this).find('[name="addPrice[]"]').val(),
					addRegDt: Date.now()
				});
			});

			var orderBy = $(this).val().split(' ');
			var tmpSort = _(sort).sortBy(function(obj) {
				return eval((orderBy[1] == 'asc' ? '+' : '-') + 'obj.' + orderBy[0]);
			});

			$('#areaDeliveryRow tr').not('#noList').remove();
			$.each(tmpSort, function(idx){
				addAreaDeliveryRow(tmpSort[idx]);
			});
		});

		// 테이블에 동적으로 지역 및 배송비추가
		$('.js-add-delivery').click(function(e){
		    var addMethod = $("input[name=addMethod]:checked").val();
		    if(addMethod == 'd') {
                var checkedAddress = [];
                $("input[name=selectAddress]:checked").each(function () {
                    checkedAddress.push($(this).data('address'));
                });

                if (checkedAddress.length < 1) {
                    alert('지역을 선택하세요.');
                    return false;
                }

                var params = [];
                $.each(checkedAddress, function (index, value) {
                    params[index] = {
                        addSno: '',
                        addAreaCode: '0',
                        addArea: value,
                        addPrice: $('input[name=newPrice]').val(),
                        addRegDt: Date.now()
                    };
                });
            } else {
		        var sido = $("#newAreaSido").val();
		        var gugun = $("#newAreaGugun").val();
		        var newPrice =  $('input[name=newPrice]').val();
		        if(!sido || !gugun) {
		            alert('지역 검색을 선택하세요.');
		            return false;
                }
		        if(!newPrice) {
                    alert('추가배송비를 선택하세요.');
                    return false;
                }
		        sido = sido.split('|');
		        var params = [{
                    addSno: '',
                    addAreaCode: '0',
                    addArea: sido[1] + ' ' + gugun,
                    addPrice: newPrice,
                    addRegDt: Date.now()
                }]
            }
			$.each(params, function (index, value) {
				$('#areaDeliveryRow tr').not('#noList').each(function(idx) {
					if ($.trim($(this).find('[name="addArea[]"]').val()) == $.trim(value.addArea)) {
						params[index] = '';
						return false;
					}
				});
			});

			params = params.filter(function(filter) {
				return filter != '';
			});

			if (params.length > 0) {
				$.each(params, function (index, value) {
					addAreaDeliveryRow(value);
				});
			}

			return false;
		});

		// 추가지역 리스트 폼 체크 (저장시 사용)
		$('#frmDeliveryAreaRegistList').validate({
			dialog: false,
			submitHandler: function (form) {
				return false;
			},
			rules: {
				'addPrice[]': {
					required: true,
					number: true,
					min: 0.001
				}
			},
			messages: {
				'addPrice[]': {
					required: "추가배송비를 입력하세요.",
					number: "숫자만 입력하실 수 있습니다.",
					min: "최소 0이상의 숫자를 기입해주세요"
				}
			}
		});

		// 수정페이지에 기 등록된 추가지역 리스트 복사
		clone[0] = $('#areaDeliveryRow').children().clone();

		// 지역 및 배송비추가에서 추가방법에 따른 처리
		$('[name=addMethod]').click(function(e){
			switch($('[name=addMethod]').index($(this))) {
				// 주소 검색
				default:
				case 0:
					$('#areaDeliveryRow tr').remove();
					$('#areaDeliveryRow').append(clone[0]);
					$('#addMethodDirect').css({display: ''});
                    $('#addMethodSelect').css({display: 'none'});
					$('#addMethodExcel').css({display: 'none'});
					$('#addMethodButton').css({display: ''});
					$('#frmDeliveryAreaRegistList').css({display: ''});
                    $('#newPrice').css({display: ''});
                    $('#newPrice input').val('');

                    $("#searchKeyword").val('');
                    $('#searchResult').find('.text-center').empty();
                    $('#searchResult').find('.no-data').show();
					$('input[name="addPrice[]"]').number_only();
					break;

                // 주소 선택
                case 1:
                    $('#newAreaSido').val('');
                    $('#addMethodDirect').css({display: 'none'});
                    $('#addMethodSelect').css({display: ''});
                    $('#addMethodExcel').css({display: 'none'});
                    $('#addMethodButton').css({display: ''});
                    $('#frmDeliveryAreaRegistList').css({display: ''});
                    $('#newPrice').css({display: ''});
                    $('#newPrice input').val('');
                    break;
				// 엑셀파일등록
				case 2:
					$('#addMethodDirect').css({display: 'none'});
                    $('#addMethodSelect').css({display: 'none'});
					$('#addMethodExcel').css({display: ''});
					$('#addMethodButton').css({display: 'none'});
					$('#frmDeliveryAreaRegistList').css({display: 'none'});
                    $('#newPrice').css({display: 'none'});
					break;

				// 기본지역리스트 적용
				case 3:
					$('#areaDeliveryRow tr').remove();
					$('#addMethodDirect').css({display: 'none'});
					$('#addMethodExcel').css({display: 'none'});
					$('#addMethodButton').css({display: 'none'});
					if (_.isUndefined(clone[2])) {
						$.ajax({
							method: "GET",
							cache: false,
							url: "./delivery_ps.php",
							data: {mode: 'basic_area_delivery'},
							success: function (data) {
								$.each(data, function(idx){
									data[idx].addSno = '';
									data[idx].addRegDt = Date.now();
									addAreaDeliveryRow(data[idx]);
								});
								$('input[name="addPrice[]"]').number_only();
								clone[2] = $('#areaDeliveryRow').children().clone();
							},
							error: function (data) {
								alert(data.message);
							}
						});
					} else {
						$('#areaDeliveryRow').append(clone[2]);
					}

					$('#frmDeliveryAreaRegistList').css({display: ''});

					var iframe = $('#ifrmLayer', window.parent.document)[0];
					if (iframe) {
						var iframewindow = iframe.contentWindow ? iframe.contentWindow : iframe.contentDocument.defaultView;
						setTimeout(function(){
							iframe.style.height = iframewindow.document.body.scrollHeight + 'px';
						}, 1000);
					}
					break;

			}

			setTotalCount();
		});

		// 공급사 구분
		$(document).on('click', '[name="scmFl"]', function () {
			if ($(this).val() == '0') {
				$('#scmUseTr').show();
				$("#scmLayer").html('');
			} else {
				$('#scmUseTr').hide();
				$('input:radio[name="scmUseFl"]')[1].click();
			}
		});

		// 공급사 선택 버튼 클릭시
		$(document).on('click', '[name="scmFlButton"]', function () {
			if ($('input:radio[name="scmFl"]:checked').val() == '0') {
				$('#scmUseTr').show();
			} else {
				$('#scmUseTr').hide();
				$('input:radio[name="scmUseFl"]')[1].click();
			}
		});

		// 공급사 사용 여부
		$(document).on('click', '[name="scmUseFl"]', function () {
			if ($(this).val() == '1') {
				$('#scmUseBlock').show();
			} else {
				$('#scmUseBlock').hide();
			}
		});

		// 추가지역 리스트 불러오기 클릭 이벤트
		$('#deliveryAreaList').click(function(e){
			var addParam = {
				mode: 'radio',
				layerFormID: 'SelecteDeliveryArea',
				parentFormID: 'frmDeliveryAreaGroupRegist',
				dataFormID: 'idscm',
				dataInputNm: 'scmNo',
			};
			$.get('../policy/layer_delivery_area.php', addParam, function (data) {
				var layerForm = '<div id="SelecteDeliveryArea">' + data + '</div>';
				BootstrapDialog.show({
					title: '지역별 추가배송비 선택',
					message: $(layerForm),
					size: BootstrapDialog.SIZE_NORMAL,
					closable: true,
					onhidden: function (dialog) {

					}
				});
			});
		});
	});

	// 테이블에서 동적으로 지역 및 배송비 삭제
	$('.js-selected-delete').click(function(e){
		var selectedCount = 0;
		$('[name="selectArea[]"]').each(function(idx){
			if ($(this).prop('checked') == true) {
				$(this).closest('tr').remove();
				selectedCount++;
			}
		});
		if (selectedCount == 0) {
			alert('삭제하실 지역을 선택해주세요.');
			return false;
		} else {
			setTotalCount();
		}
	});

	// 지역선택 이벤트
	$('#newAreaSido').change(function (e) {
		var html = '<option value="">시/군/구 선택</option>';
		var template = '<option value="<%=sigungu_name%>"><%=sigungu_name%></option>';
		var compiled = _.template(template);
		var sidoCode = $(this).val().split('|');
		if (_.isEmpty($(this).val())) {
			$('#newAreaGugun').html(html);
		} else {
			$.post('./delivery_ps.php', {mode: 'area_search', newAreaSidoCode: sidoCode[0]}, function (data) {
				var json = $.parseJSON(data);
				if (!_.isUndefined(json.godojuso.data)) {
					$.each(json.godojuso.data.item, function (idx, item) {
						html += compiled(item);
					});
				}
				$('#newAreaGugun').html(html);
			});
		}
	});

	// 추가 배송비 리스트 불러오기 레이어에서 호출
	function setAreaList(scmNo) {
		var data = {
			'mode' : 'getDeliveryAreaList',
			'sno'    : scmNo
		};

		$.ajax({
			url : './delivery_ps.php',
			data : data,
			type : 'GET',
			dataType: 'JSON',
			success : function(data) {
				if ($.isArray(data)) {
					$('#noList').hide();
					$('#areaDeliveryRow').show();
					$('#areaDeliveryRow').empty();
					$.each(data, function (key, val) {
						var params = {
							addSno: '',
							addAreaCode: val.addAreaCode,
							addArea: val.addArea,
							addPrice: val.addPrice,
							addRegDt: Date.now()
						};
						addAreaDeliveryRow(params);
					});
				} else {
					alert('선택한 추가지역의 추가배송비 내역이 없습니다. 다시 확인해 주세요.');
				}
			},
			error : function() {
				alert('선텍한 추가지역의 정보를 가져오지 못했습니다. 다시 시도해 주세요.');
			}
		});
	}
	//-->

	// 도로명 지역검색
	$('#addrSearchBtn').click(function(e) {
		var data = {
			'keyword' : $.trim($('input[name="searchKeyword"]').val()),
			'mode' : 'areaDelivery'
		};

		if (data['keyword'] == '') {
		    alert('검색어를 입력해주세요.');
			return;
		}
        if(data['keyword'].length == 1 || !checkSearchedWord(data['keyword'])) {
            alert('검색어를 다시 입력해주세요.');
            return;
        }

        $('#searchResult').find('.text-center').empty();
        $('#searchResult').find('.no-data').hide();
        $('#searchResult').find('.loading_td').show();
        $('#addrSearchBtn').attr('disabled', true);
        $('input[name=addMethod]').attr('disabled', true);

		$.ajax({
			url : '../share/postcode_search_ps.php',
			data : data,
			type : 'POST',
            timeout: 30000,
			success : function(data) {
                $('#addrSearchBtn').attr('disabled', false);
                $('input[name=addMethod]').attr('disabled', false);
                $('#searchResult').find('.loading_td').hide();
				var data = JSON.parse(data);
				if (data.length == 0) {
					$('#searchResult').find('.no-data').show();
				}
				else {
					$('#searchResult').find('.no-data').hide();
				}

				for (var i=0; i<data.length; i++) {
					var row = $('<tr class="text-center"></tr>');
					var td = $('<td><input type="checkbox" name="selectAddress" data-address="'+data[i]+'"></td>');
					row.append(td);
					td = $('<td style="text-align: left"></td>').text(data[i]);
					row.append(td);
					$('#searchResult').append(row);
				}
			},
			error : function(e) {
                $('#addrSearchBtn').attr('disabled', false);
                $('input[name=addMethod]').attr('disabled', false);
                $('#searchResult').find('.loading_td').hide();
                $('#searchResult').find('.no-data').show();
                alert('주소를 상세히 입력해주세요.');
			}
		});
	});

	// 지역 검색 엔터 입력
	$("#searchKeyword").keydown(function(key) {
		if (key.keyCode == 13) {
			$('#addrSearchBtn').trigger('click');
		}
	});

    function checkSearchedWord(str){
        if(str.length >0){
            //특수문자 제거
            var expText = /[%=><\[\]]/ ;
            if(expText.test(str) == true){
                return false;
            }

            //특정문자열(sql예약어의 앞뒤공백포함) 제거
            var sqlArray = [
                //sql 예약어
                "OR", "SELECT", "INSERT", "DELETE", "UPDATE", "CREATE", "DROP", "EXEC", "UNION", "FETCH", "DECLARE", "TRUNCATE"
            ];
            var regex ;
            str = str.toUpperCase();
            for(var i=0; i<sqlArray.length; i++){
                regex = new RegExp(sqlArray[i]) ;
                if (regex.test(str)) {
                    return false;
                }
            }
        }
        return true;
    }
</script>