<form id="frmBase" method="post" action="../policy/mall_config_ps.php">
    <input type="hidden" name="sno" value="<?= $mall['sno']; ?>"/>
    <input type="hidden" name="domainFl" value="<?= $mall['domainFl']; ?>"/>
    <input type="hidden" name="mallName" value="<?= $mall['mallName']; ?>"/>
    <input type="hidden" name="mode" value="modify"/>
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red js-btn-save"/>
    </div>

    <!-- Nav tabs -->
    <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
        <?php foreach ($gGlobal['mallList'] as $val) { ?>
            <?php if ($val['domainFl'] == 'kr') continue; ?>
            <li role="presentation" class="js-popover <?= $mall['domainFl'] == $val['domainFl'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $val['mallName']; ?>" data-placement="top">
                <a href="#<?= $val['domainFl'] ?>" role="tab" data-toggle="tab" aria-controls="<?= $val['domainFl'] ?>">
                    <span class="flag flag-16 flag-<?php echo $val['domainFl'] ?>"></span>
                    <span class="mall-name"><?php echo $val['mallName']; ?></span>
                </a>
            </li>
        <?php } ?>
    </ul>

    <div class="table-title gd-help-manual mgt30">
        해외 상점 설정
    </div>
    <div class="tab-contents">
        <div class="tab-pane" role="tabpanel" id="<?= $mall['domainFl']; ?>">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md">
                    <col class="">
                </colgroup>
                <tr>
                    <th class="">도메인</th>
                    <td class="">
                        <div>
                            <span>임시 :</span>
                            <span><a><?=$tempDomain;?></a></span>
                            <span>
                                <?php
                                    if ($mall['domainFl'] == $globalMallDomains[2]) {
                                        $mallName = '중문몰'; 
                                    }
                                    else if ($mall['domainFl'] == $globalMallDomains[1]) { 
                                        $mallName = '일문몰'; 
                                    }
                                    else {
                                        $mallName = '영문몰';
                                    }
                                 ?>
                                <?php if (count($mall['connectDomain']['connect']) < 1) { ?>
                                    ( <?=$mallName;?> 대표도메인 )
                                <?php } ?>
                            </span>
                        </div>
                        <div id="connectDomainContainer">
                            <span>연결 :</span>
                            <button type="button" class="js-layer-connect-domain btn btn-gray btn-sm">+ 연결도메인 추가</button>
                            <div class="connectDomainTable display-none">
                                <table class="table table-rows width-3xl mgt20">
                                    <tr class="domain-head center">
                                        <td class="width-md"><?=$mallName;?> 대표도메인 설정</td>
                                        <td>도메인 주소</td>
                                        <td class="width-xs">삭제</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="notice-info">
                            <span class="text-darkred">해외상점의 연결도메인을 추가할 경우, 반드시 기준몰에서 사용하지 않는 도메인만 입력하시기 바랍니다.</span>
                            <br/>기준몰(국내몰)과 연결된 도메인을 추가하시면, 기준몰을 포함한 전체 쇼핑몰 이용에 문제가 발생할 수 있습니다.
                        </div>
                        <div class="notice-info">
                        <span>
                            추가한 도메인 중 해외상점의 대표 도메인을 설정할 수 있습니다.<br/>
                            해외 상점 이동 시 대표 도메인으로 연결되나, 보안서버가 연결 되어 있는 경우보안서버 도메인으로 자동 페이지 변경됩니다.
                        </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="require">사용설정</th>
                    <td class="">
                        <label for="useFlY" class="radio-inline">
                            <input type="radio" name="useFl" id="useFlY" value="y" <?= $checked['useFl']['y']; ?>>
                            사용
                        </label>
                        <label for="useFlN" class="radio-inline">
                            <input type="radio" name="useFl" id="useFlN" value="n" <?= $checked['useFl']['n']; ?>>
                            사용안함
                        </label>
                        <div class="notice-info">
                            <span class="text-darkred">해외상점을 사용하기 위해서는 "환율설정"을 반드시 입력하셔야만 정상적으로 사용이 가능합니다..</span>
                            <br/> 환율설정은 <a href="../policy/exchange_rate_config.php" target="_blank" class="btn-link">"기본설정 > 해외상점 > 환율설정"</a> 에서 설정하실 수 있습니다.
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="require">언어설정</th>
                    <td class="">
                        <?php if ($mall['domainFl'] == $globalMallDomains[2]) { ?>
                            <span class="flag flag-16 flag-cn"></span> Chinese(중국어)
                        <?php } elseif ($mall['domainFl'] == $globalMallDomains[1]) { ?>
                            <span class="flag flag-16 flag-jp"></span> Japanese(일본어)
                        <?php } else { ?>
                            <span class="flag flag-16 flag-us"></span> English(영어)
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th class="require" rowspan="3">사용화폐</th>
                    <td class="">
                        <span>결제화폐 :</span>
                        <span id="useCurrencyDisplay">
                            <?= $mall['globalCurrencyString'] . ' - ' . $mall['globalCurrencyName'] . '(' . $mall['globalCurrencySymbol'] . ')' ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="">
                        <span>금액 표시 방법 :</span>
                        <select class="width-md" name="currencyDisplayFl">
                            <option value="symbol" <?= $selected['currencyDisplayFl']['symbol']; ?>><?= $mall['globalCurrencySymbol'] ?></option>
                            <option value="string" <?= $selected['currencyDisplayFl']['string']; ?>><?= $mall['globalCurrencyString'] ?></option>
                            <option value="name" <?= $selected['currencyDisplayFl']['name']; ?>><?= $mall['globalCurrencyName'] ?></option>
                            <?php
                            if ($mall['globalCurrencyName2'] != '') {
                                ?>
                                <option value="name2" <?= $selected['currencyDisplayFl']['name2']; ?>><?= $mall['globalCurrencyName2'] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                        <span>
                            표시예제 :
                        </span>
                        <span id="currencyDisplaySample"></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>소수점 자리 노출 :</span>
                        <input type="hidden" name="globalCurrencyDecimalFormat" value="<?= $mall['globalCurrencyDecimalFormat']; ?>"/>
                        <label for="useDecimalN" class="radio-inline">
                            <input type="radio" name="globalCurrencyDecimal" id="useDecimalN" value="0" <?= $checked['globalCurrencyDecimal']['0']; ?>>
                            사용안함
                        </label>
                        <label for="useDecimalY" class="radio-inline">
                            <input type="radio" name="globalCurrencyDecimal" id="useDecimalY" value="2" <?= $checked['globalCurrencyDecimal']['2']; ?>>
                            사용함 (소수점 2자리)
                        </label>
                        <?php if($msgFl) { ?>
                            <div class="notice-info">
                                해외몰 금액은 원화대비 환율 값을 적용하여 나온 금액에 대하여 "버림"으로 절사 처리를 하고 있습니다.
                            </div>
                            <div class="notice-info">
                                원화금액이 작은 경우, 환율차이로 일부 할인금액 표시에서 오차가 있을 수 있습니다. (실제 결제금액은 정상)
                            </div>
                            <div class="notice-info">
                                <span class="text-darkred">소수점 노출을 "사용안함"으로 설정할 경우에는 반드시 <b style="color: #fa2828;"><u>관련 스킨패치</u></b>를 꼭 적용하셔야만 정상적인 PG 결제가 가능합니다.</span>
                            </div>
                        <?php } ?>
                    </td>
                </tr>

                <tr>
                    <th class="" id="addGlobalCurrencyRowspan">참조화폐</th>
                    <td class="">
                        <div>
                            <label for="addGlobalCurrencyNo0" class="radio-inline width-md">
                                <input type="radio" name="addGlobalCurrencyNo" value="0" id="addGlobalCurrencyNo0" <?= $checked['addGlobalCurrencyNo']['0']; ?>/>
                                사용안함
                            </label>
                            <label for="addGlobalCurrencyNo999" class="radio-inline width-md">
                                <input type="radio" name="addGlobalCurrencyNo" value="999" id="addGlobalCurrencyNo999" <?= $checked['addGlobalCurrencyNo']['999']; ?>/>
                                <?= $currencies[999]['globalCurrencyString'] . ' - ' . $currencies[999]['globalCurrencyName'] . '(' . $currencies[999]['globalCurrencySymbol'] . ')' ?>
                            </label>
                            <label for="addGlobalCurrencyNo1" class="radio-inline width-md">
                                <input type="radio" name="addGlobalCurrencyNo" value="1" id="addGlobalCurrencyNo1" <?= $checked['addGlobalCurrencyNo']['1']; ?>/>
                                <?= $currencies[1]['globalCurrencyString'] . ' - ' . $currencies[1]['globalCurrencyName'] . '(' . $currencies[1]['globalCurrencySymbol'] . ')' ?>
                            </label>
                        </div>
                        <div>
                            <label for="addGlobalCurrencyNo3" class="radio-inline width-md">
                                <input type="radio" name="addGlobalCurrencyNo" value="3" id="addGlobalCurrencyNo3" <?= $checked['addGlobalCurrencyNo']['3']; ?>/>
                                <?= $currencies[3]['globalCurrencyString'] . ' - ' . $currencies[3]['globalCurrencyName'] . '(' . $currencies[3]['globalCurrencySymbol'] . ')' ?>
                            </label>
                            <label for="addGlobalCurrencyNo2" class="radio-inline width-md">
                                <input type="radio" name="addGlobalCurrencyNo" value="2" id="addGlobalCurrencyNo2" <?= $checked['addGlobalCurrencyNo']['2']; ?>/>
                                <?= $currencies[2]['globalCurrencyString'] . ' - ' . $currencies[2]['globalCurrencyName'] . '(' . $currencies[2]['globalCurrencySymbol'] . ')' ?>
                            </label>
                            <label for="addGlobalCurrencyNo4" class="radio-inline width-md">
                                <input type="radio" name="addGlobalCurrencyNo" value="4" id="addGlobalCurrencyNo4" <?= $checked['addGlobalCurrencyNo']['4']; ?>/>
                                <?= $currencies[4]['globalCurrencyString'] . ' - ' . $currencies[4]['globalCurrencyName'] . '(' . $currencies[4]['globalCurrencySymbol'] . ')' ?>
                            </label>
                        </div>
                    </td>
                </tr>
                <tr id="addGlobalCurrencyDisplay">
                    <td>
                        <span>소수점 자리 노출 :</span>
                        <input type="hidden" name="addGlobalCurrencyDecimalFormat" value="<?= $mall['addGlobalCurrencyDecimalFormat']; ?>"/>
                        <label for="useAddGlobalDecimalN" class="radio-inline">
                            <input type="radio" name="addGlobalCurrencyDecimal" id="useAddGlobalDecimalN" value="0" <?= $checked['addGlobalCurrencyDecimal']['0']; ?>>
                            사용안함
                        </label>
                        <label for="useAddGlobalDecimalY" class="radio-inline">
                            <input type="radio" name="addGlobalCurrencyDecimal" id="useAddGlobalDecimalY" value="2" <?= $checked['addGlobalCurrencyDecimal']['2']; ?>>
                            사용함 (소수점 2자리)
                        </label>
                    </td>
                </tr>
                <tr>
                    <th class="">우선지역</th>
                    <td class="">
                        <?= gd_select_box('recommendCountryCode', 'recommendCountryCode', $recommendCountries, null, $mall['recommendCountryCode'], '- 선택 -', null, 'form-control width-xl'); ?>
                        <div class="notice-info">우선지역의 경우, 주문서 작성 페이지에서 주소정보 입력항목에서의 우선적으로 노출되는 국가를 설정합니다.</div>
                    </td>
                </tr>
                <tr>
                    <th class="require">디자인 스킨</th>
                    <td class="">
                        <table class="table table-rows width-3xl">
                            <colgroup>
                                <col class="width-md">
                                <col>
                            </colgroup>
                            <thead>
                            <tr>
                                <th>구분</th>
                                <th>디자인 스킨 선택</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>PC 쇼핑몰</td>
                                <td>
                                    <?php echo gd_select_box('skinPc', 'skinPc', $skinList, null, $mallSkin['frontLive'], null, null, 'width-2xl'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>모바일 쇼핑몰</td>
                                <td>
                                    <?php echo gd_select_box('skinMobile', 'skinMobile', $skinListMobile, null, $mallSkin['mobileLive'], null, null, 'width-2xl'); ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="notice-info">
                            <span class="text-darkred">해외몰 사용시에는 "해외몰 지원 스킨 (Story_g(스토리지), Moment(모먼트))"을 적용하셔야만 정상적인 이용이 가능합니다. </span>
                            <br/>그 외 스킨을 해외몰 디자인으로 사용하기 위해서는 <b>해외몰 기능 지원 함수나 치환코드 등을 추가 적용</b> 하셔야만 합니다.
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>
<script type="text/javascript">
    var use_shop_domain = '<?=$useShopDomain?>';
    var gd_mall = (function ($, _) {
        var connect_domain_list;
        var connect_domain_container = $('#connectDomainContainer');

        var options = $('select[name="addGlobalCurrencyDecimal"] option');

        function add_domain_list(domain) {
            console.log(connect_domain_list.connect);
            if (typeof connect_domain_list.connect === 'undefined') {
                connect_domain_list.connect = [];
            }
            connect_domain_list.connect.push(domain);
        }

        function check_duplicate_domain(domain) {
            var result = false;
            $(connect_domain_list.connect).each(function (idx, item) {
                result = (item == domain);
            });
            return result;
        }

        return function () {
            return {
                change_currency_display_sample: function () {
                    var $select = $('select[name="currencyDisplayFl"] option:selected');
                    var selectHtml = $select.text();
                    var selected = $select.val();
                    var display_container = $('#currencyDisplaySample');
                    var format = $('#useCurrencyDisplay').html();
                    format = format.trim();
                    switch (selected) {
                        case 'symbol':
                            var symbol = format.substring(format.indexOf('(') + 1, format.indexOf(')'));
                            logger.debug(format, symbol);
                            display_container.html(symbol + ' 1,000');
                            break;
                        case 'string':
                            var code = format.substring(0, format.indexOf('-') - 1);
                            logger.debug(format, code);
                            display_container.html(code + ' 1,000');
                            break;
                        case 'name':
                            var display = format.substring(format.indexOf('-') + 2, format.indexOf('('));
                            logger.debug(format, display);
                            display_container.html('1,000 ' + display);
                            break;
                        case 'name2':
                            var display2 = format.substring(format.indexOf('-') + 2, format.indexOf('('));
                            logger.debug(format, display2, selectHtml);
                            display_container.html('1,000 ' + selectHtml);
                            break;
                    }

                },
                change_unitType: function () {
                    if ($('input:radio[name="globalCurrencyDecimal"]:checked').val() == 2) {
                        $('input[name="globalCurrencyDecimalFormat"]').val('0.00');
                    } else if ($('input:radio[name="globalCurrencyDecimal"]:checked').val() == 0) {
                        $('input[name="globalCurrencyDecimalFormat"]').val('0');
                    }

                    if ($('input:radio[name="addGlobalCurrencyDecimal"]:checked').val() == 2) {
                        $('input[name="addGlobalCurrencyDecimalFormat"]').val('0.00');
                    } else if ($('input:radio[name="addGlobalCurrencyDecimal"]:checked').val() == 0) {
                        $('input[name="addGlobalCurrencyDecimalFormat"]').val('0');
                    }
                },
                add_currency: function() {
                    // 참조화폐 없음이나 한국원일때는 소수점노출 가림
                    if ($('input:radio[name="addGlobalCurrencyNo"]:checked').val() == 0 || $('input:radio[name="addGlobalCurrencyNo"]:checked').val() == 999) {
                        $('#addGlobalCurrencyRowspan').attr('rowspan', '1');
                        $('#addGlobalCurrencyDisplay').hide();
                    } else {
                        $('#addGlobalCurrencyRowspan').attr('rowspan', '2');
                        $('#addGlobalCurrencyDisplay').show();
                    }
                },
                move_mall: function (e) {
                    e.preventDefault();
                    var controls = $(e.target).attr('aria-controls');
                    if (typeof controls === 'undefined') {
                        controls = $(e.target).closest('a').attr('aria-controls');
                    }
                    var url = '../policy/mall_config.php?domainFl=' + controls;
                    logger.debug('tab click location: ' + url);
                    window.location.href = url;
                },
                layer_connect_domain: function () {
                    <?php if (empty($mallDomain) === true) { ?>
                    alert('해외몰 연결도메인 추가를 위해서는 기본설정 > 기본정보설정 의 "쇼핑몰 도메인"을 먼저 입력하시기 바랍니다.');
                    return false;
                    <?php } else { ?>
                    var connect = connect_domain_list.connect;
                    if (typeof  connect !== 'undefined' && connect.length >= 3) {
                        alert('연결도메인은 최대 3개까지만 추가 가능합니다.');
                        return false;
                    }
                    var loadChk = $('div#layerConnectDomain').length;
                    $.get('../policy/layer_connect_domain.php', {}, function (data) {
                        if (loadChk === 0) {
                            data = '<div id="#layerConnectDomain">' + data + '</div>';
                        }
                        BootstrapDialog.show({
                            name: "layer_connect_domain",
                            title: "연결 도메인 추가",
                            size: BootstrapDialog.SIZE_WIDE,
                            message: $(data),
                            closeable: false,
                            onshow: function () {
                                add_domain_function = function (domain) {
                                    add_domain_list(domain);
                                    mall.refresh_domain_container(domain);
                                };
                                check_domain_function = function (domain) {
                                    return check_duplicate_domain(domain);
                                };
                            }
                        });
                    });
                    <?php } ?>
                },
                refresh_domain_container: function (domain) {
                    if (typeof connect_domain_list.connect !== 'undefined') {
                        connect_domain_container.find('.connectDomainTable table tr:not(.domain-head)').remove();
                        connect_domain_container.find('.connectDomainTable').removeClass('display-none');
                        $.each(connect_domain_list.connect, function (idx, item) {
                            if (idx > 2) {
                                return false;
                            }
                            else {
                                var compiled = _.template($('#templateConnectDomain').html());
                                compiled = compiled({index: idx, domain: item});
                                $('.connectDomainTable table').append(compiled);
                            }
                        });
                        mall.useShopDomainSetting(use_shop_domain);
                    }
                },
                set_domain_list: function (list) {
                    connect_domain_list = list;
                },
                useShopDomainSetting: function (useShopDomain) {
                    if (useShopDomain == undefined) useShopDomain = 0;
                    if ($(':radio[name="useShopDomain"]').is(':checked') == false) {
                        use_shop_domain = useShopDomain;
                        $(':radio[name="useShopDomain"]').eq(use_shop_domain).attr('checked', true);
                    }
                },
                remove_domain_list: function (domain) {
                    logger.info('remove domain: ' + domain)
                    var list = [];
                    $.each(connect_domain_list.connect, function (idx, item) {
                        if (domain != item) {
                            list.push(item);
                        }
                    });
                    connect_domain_list.connect = list;
                },
                save: function () {
                    $('#frmBase').validate({
                        rules: {
                            useFl: {
                                required: true
                            },
                            skinPc: {
                                required: false
                            },
                            skinMobile: {
                                required: false
                            }
                        },
                        messages: {
                            useFl: {
                                required: "사용설정을 선택해주세요."
                            },
                            skinPc: {
                                required: "PC 쇼핑몰 스킨을 선택해주세요."
                            },
                            skinMobile: {
                                required: "모바일 쇼핑몰 스킨을 선택해주세요."
                            }
                        }, submitHandler: function (form) {
                            form.target = 'ifrmProcess';
                            form.submit();
                        }
                    });
                }
            };
        };
    })($, _);
    var mall = new gd_mall();
    $(document).ready(function () {
        $('.js-btn-save').click(mall.save);
        $('.js-layer-connect-domain').click(mall.layer_connect_domain);
        $('#connectDomainContainer').on('click', 'button[data-toggle="delete"]', function () {
            var $this = $(this);
            dialog_confirm('연결된 도메인을 삭제하시겠습니까?', function (result) {
                if (result == true) {
                    mall.remove_domain_list($this.data('domain'));
                    $this.closest('tr').remove();
                    mall.useShopDomainSetting(use_shop_domain);
                }
            });
        });
        $('#connectDomainContainer').on('click', 'input[name="useShopDomain"]', function(){
            use_shop_domain = $(this).index('input[name="useShopDomain"]');
        });
        $('li[role=presentation]').click(mall.move_mall);
        $('select[name="currencyDisplayFl"]').change(mall.change_currency_display_sample);
        $('select[name="currencyDisplayFl"]').trigger('change');
        $('input:radio[name=globalCurrencyDecimal]').change(mall.change_unitType);
        $('input:radio[name=addGlobalCurrencyDecimal]').change(mall.change_unitType);
        $('input[name="addGlobalCurrencyNo"]').click(mall.add_currency);
        mall.set_domain_list(<?= gd_isset($connectDomainList, '{connect:[]}'); ?>);
        mall.refresh_domain_container();
        mall.change_unitType();
        mall.add_currency();
    });
</script>
<script type="text/html" id="templateConnectDomain">
    <tr>
        <td class="center table-cols">
            <input type="radio" name="useShopDomain" id="useShopDomain" value="<%=index%>" />
        </td>
        <td class="pdl8"><%=domain%></td>
        <td class="center">
            <div>
                <input type="hidden" id="connectDomain<%=index%>"  value="<%=domain%>" />
                <input type="hidden" name="connectDomain[]" value="<%=domain%>"/>
                <button type="button" class="btn btn-sm btn-white btn-icon-minus js-delete-row" data-toggle="delete" data-target="connectDomain<%=index%>" data-domain="<%=domain%>" >삭제</button>
            </div>
        </td>
    </tr>
</script>