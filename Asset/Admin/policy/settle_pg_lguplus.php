<script type="text/javascript">
    <!--
    $(document).ready(function(){
        <?php
        if ($data['noInterestFl'] != 'y') {
            echo 'display_toggle(\'noInterestToggle\',\'hide\');'.chr(10);
        }
        if ($data['installmentFl'] != 'y') {
            echo 'display_toggle(\'installmentToggle\',\'hide\');'.chr(10);
        }
        if ($data['eggDisplayFl'] == 'n') {
            echo 'display_toggle(\'eggDisplayBannerConf\',\'hide\');'.chr(10);
        }
        ?>
    });
    //-->
</script>

<div class="table-title gd-help-manual">
    <?php echo $pgNm;?> 일반결제 설정
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
    </colgroup>
    <tr>
        <th>PG사 모듈 버전</th>
        <td>XPay Crossplatform (V 1.0 - 20150826) / Smart XPay (V 1.2 - 20140730)</td>
    </tr>
    <tr>
        <th>결제수단 설정</th>
        <td>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[pc][useFl]" value="y" <?php echo gd_isset($checked['pc']['y']);?> <?php echo gd_isset($disabled['pc']['y']);?> /> 신용카드</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[pb][useFl]" value="y" <?php echo gd_isset($checked['pb']['y']);?> <?php echo gd_isset($disabled['pb']['y']);?> /> 계좌이체</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[pv][useFl]" value="y" <?php echo gd_isset($checked['pv']['y']);?> <?php echo gd_isset($disabled['pv']['y']);?> /> 가상계좌</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[ph][useFl]" value="y" <?php echo gd_isset($checked['ph']['y']);?> <?php echo gd_isset($disabled['ph']['y']);?> /> 휴대폰</label>
        </td>
    </tr>
    <tr>
        <th><?php echo $pgNm;?> ID</th>
        <td class="form-inline">
            <?php if ($data['pgAutoSetting'] === 'y') { ?>
                <span class="text-blue bold"><?php echo $data['pgId'];?></span> <span class="text-blue">(자동 설정 완료)</span>
                <div class="display-inline-block width-sm"><input type="button" onclick="settleKindUpdate();" value="<?php echo $pgNm;?> 정보 갱신" class="btn btn-gray btn-sm" /></div>
            <?php } else { ?>
                <?php if ($data['pgApprovalSetting'] === 'y') {?>
                    <span class="text-blue bold"><?php echo $data['pgId'];?></span> <span class="text-blue">(개별 승인 완료)</span>
                    <?php if (empty($pgApprovalId) === false) {?>
                        <input type="hidden" name="pgId" value="<?php echo $data['pgId'];?>" />
                        <div class="notice-info">개별 승인 요청이 승인 되었습니다. 반드시 정보를 확인 후 "PG 정보 저장"을 눌러 PG 설정을 완료 하셔야 합니다.</div>
                    <?php }?>
                <?php } else { ?>
                    <div class="notice-info notice-danger">전자결제(PG) 신청 전 또는 승인대기상태입니다.</div>
                    <div class="notice-info">
                        신청 전인 경우 먼저 서비스를 신청하세요.
                        <a href="https://www.godo.co.kr/echost/power/add/payment/pg-intro.gd" target="_blank" class="btn btn-gray btn-sm">전자결제(PG) 신청</a>
                    </div>
                    <div class="notice-info">
                        기존 고도 솔루션 이용 쇼핑몰 중 전자결제(PG) 설정이 되지 않는 경우 "개별 승인 신청" 바랍니다.
                        <input type="button" onclick="pg_prefix();" value="개별 승인 신청" class="btn btn-gray btn-sm" />
                    </div>
                <?php }?>
            <?php }?>
        </td>
    </tr>
    <tr>
        <th><?php echo $pgNm;?> mertkey</th>
        <td>
            <?php if ($data['pgAutoSetting'] === 'y') { ?>
                <span class="text-blue bold"><?php echo $data['pgKey'];?></span> <span class="text-blue">(자동 설정 완료)</span>
            <?php } else { ?>
                <?php if ($data['pgApprovalSetting'] === 'y') {?>
                    <input type="text" name="pgKey" value="<?php echo $data['pgKey'];?>" class="form-control width-xl" />
                    <?php if (empty($pgApprovalId) === false) {?><div class="notice-info">개별 승인 요청이 승인 되었습니다. 반드시 정보를 확인 후 "PG 정보 저장"을 눌러 PG 설정을 완료 하셔야 합니다.</div><?php }?>
                <?php } else { ?>
                    <div class="notice-info notice-danger">전자결제(PG) 신청 전 또는 승인대기상태입니다.</div>
                <?php }?>
            <?php }?>
        </td>
    </tr>
    <tr>
        <th>일반 할부 설정</th>
        <td>
            <label class="radio-inline"><input type="radio" name="installmentFl" value="n" <?php echo gd_isset($checked['installmentFl']['n']);?> onclick="display_toggle('installmentToggle','hide');" /> 일시불 결제</label>
            <label class="radio-inline"><input type="radio" name="installmentFl" value="y" <?php echo gd_isset($checked['installmentFl']['y']);?> onclick="display_toggle('installmentToggle','show');" /> 일반 할부 결제</label>
        </td>
    </tr>
    <tr id="installmentToggle">
        <th>일반 할부 기간 설정</th>
        <td class="space-checkbox">
            <?php
            for ($i = 2; $i <= $pgPeriod['general']; $i++) {
                $strCheck        = gd_isset($checked['installmentPeroid'][$i]);
                if (empty($strCheck)) {
                    $strClass    = 'nobold';
                } else {
                    $strClass    = 'bold';
                }
                echo '<label class="display-inline-block width-2xs hand"><input type="checkbox" name="installmentPeroid[]" value="'.$i.'" '.$strCheck.' onclick="checked_bold(this)" /><span class="'.$strClass.'">'.$i.'개월</span></label>';
                if (($i % 12) == 0) {
                    echo '<br/>';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <th>무이자 할부 사용 여부</th>
        <td>
            <label class="radio-inline"><input type="radio" name="noInterestFl" value="n" <?php echo gd_isset($checked['noInterestFl']['n']);?> onclick="display_toggle('noInterestToggle','hide');" /> 무이자 할부 사용안함</label>
            <label class="radio-inline"><input type="radio" name="noInterestFl" value="y" <?php echo gd_isset($checked['noInterestFl']['y']);?> onclick="display_toggle('noInterestToggle','show');" /> 무이자 할부 사용함</label>
        </td>
    </tr>
    <tr id="noInterestToggle">
        <th>무이자 할부 기간 설정</th>
        <td>
            <input type="button" onclick="noInterest_peroid_conf('<?php echo $data['pgCode'];?>');" value="기간 설정" class="btn btn-gray btn-sm" />
            <div id="noInterestPeroid">
                <?php
                $arrPeroid    = explode(',', $data['noInterestPeroid']);
                foreach ($arrPeroid as $key => $val) {
                    if (empty($val)) {
                        continue;
                    }
                    $arrCard    = explode('-',$val);
                    if ($arrCard[0] == 'ALL') {
                        $divId        = 'all';
                        $strCard    = '전체카드';
                    } else {
                        $divId        = $key;
                        $strCard    = $pgNointerest[$arrCard[0]];
                    }
                    echo '<div id="noInterest_Peroid_'.$divId.'" class="mgt3">'.chr(10);
                    echo '<input type="button" onclick="field_remove(\'noInterest_Peroid_'.$divId.'\');" value="삭제" class="btn btn-red-box btn-xs" />'.chr(10);
                    echo '<input type="hidden" name="noInterestPeroid[]" value="'.$val.'" />'.chr(10);
                    echo ' <span>'.$strCard.' - '.str_replace(':','개월, ',$arrCard[1]).'개월</span>'.chr(10);
                    echo '</div>'.chr(10);
                }
                ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>옵션 설정</th>
        <td class="form-inline">
            <?php echo gd_check_box('pgOption[]', ['LGD_CUSTOM_CARDPOINTUSEYN' . STR_DIVISION . 'Y' => '신용카드 포인트 사용'], $data['pgOption']);?>
        </td>
    </tr>
    <tr>
        <th>결제창 스킨</th>
        <td>
            <label class="radio-inline"><input type="radio" name="pgSkinGb" value="red" <?php echo gd_isset($checked['pgSkinGb']['red']);?> /> Red (기본)</label>
            <label class="radio-inline"><input type="radio" name="pgSkinGb" value="purple" <?php echo gd_isset($checked['pgSkinGb']['purple']);?> /> Purple</label>
            <label class="radio-inline"><input type="radio" name="pgSkinGb" value="yellow" <?php echo gd_isset($checked['pgSkinGb']['yellow']);?> /> Yellow</label>
        </td>
    </tr>
    <tr>
        <th>가상계좌 입금기한</th>
        <td class="form-inline">
            <input type="text" name="vBankDay" value="<?php echo $data['vBankDay'];?>" maxlength="2" class="form-control js-number width-3xs" data-number="2, 30, 3" /> 일
        </td>
    </tr>
    <tr>
        <th>가상계좌 입금내역<br />실시간 통보 URL</th>
        <td><div class="notice-info">결제시 자동으로 전송이 되며, 가상계좌에 입금시 자동으로 입금확인 처리가 됩니다.</div></td>
    </tr>
    <input type="hidden" name="testFl" value="n" />
    <tr>
        <th>앱 스키마 설정<br />app scheme</th>
        <td>
            <input type="text" name="appScheme" value="<?php echo $data['appScheme'];?>" class="form-control width-2xl" />
        </td>
    </tr>
    <!--<tr>
        <th>테스트 여부</th>
        <td>
            <label><input type="radio" name="testFl" value="n" <?php echo gd_isset($checked['testFl']['n']);?> /> 실결제</label>
            <label><input type="radio" name="testFl" value="y" <?php echo gd_isset($checked['testFl']['y']);?> /> 테스트 결제</label>
        </td>
    </tr>-->
    <tr>
        <th>복합과세안내</th>
        <td>
            <div class="notice-info">
                <div>필독 : PG사와의 과세 & 복합과세 계약 유의사항</div>
                <div class="text-danger">- 복합과세(면/과세 상품 동시 취급)로 쇼핑몰을 운영하신다면, 반드시 먼저 PG사와 복합과세 계약을 신청하시기 바랍니다.</div>
                <div>- 복합과세로 PG사와 계약이 되어 있지 않은 상태에서 복합과세로 설정 시 일부 면세 상품의 부분취소가 어려울 수 있습니다.</div>
            </div>
        </td>
    </tr>
    <tr>
        <th><?php echo $pgNm;?> 사이트</th>
        <td>
            <a href="http://pgweb.uplus.co.kr/pg/wmp/LoginMertAdmin.jsp" target="_blank" class="btn btn-gray btn-sm"><?php echo $pgNm;?> 상점 관리자모드 바로가기</a>
            <a href="http://ecredit.uplus.co.kr/" target="_blank" class="btn btn-gray btn-sm"><?php echo $pgNm;?> 사이트 바로가기</a>
        </td>
    </tr>
</table>

<div class="table-title gd-help-manual">
    에스크로 설정
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
    </colgroup>
    <tr>
        <th>PG사 모듈 버전</th>
        <td>XPay Crossplatform (V 1.0 - 20150826)</td>
    </tr>
    <tr>
        <th>사용상태</th>
        <td>
            <label class="radio-inline"><input type="radio" name="escrowFl" value="y" <?php echo gd_isset($checked['escrowFl']['y']);?> /> 사용</label>
            <label class="radio-inline"><input type="radio" name="escrowFl" value="n" <?php echo gd_isset($checked['escrowFl']['n']);?> /> 사용 안함</label>
        </td>
    </tr>
    <tr>
        <th>결제수단 설정</th>
        <td>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[ec][useFl]" value="y" <?php echo gd_isset($checked['ec']['y']);?> <?php echo gd_isset($disabled['ec']['y']);?> /> 신용카드</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[eb][useFl]" value="y" <?php echo gd_isset($checked['eb']['y']);?> <?php echo gd_isset($disabled['eb']['y']);?> /> 계좌이체</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[ev][useFl]" value="y" <?php echo gd_isset($checked['ev']['y']);?> <?php echo gd_isset($disabled['ev']['y']);?> /> 가상계좌</label>
        </td>
    </tr>
    <tr>
        <th>구매 안전 표시</th>
        <td>
            <label class="radio-inline">
                <input type="radio" name="eggDisplayFl" value="a" <?php echo gd_isset($checked['eggDisplayFl']['a']);?> onclick="display_toggle('eggDisplayBannerConf','show');" /> 표시함
            </label>
            <label class="radio-inline">
                <input type="radio" name="eggDisplayFl" value="n" <?php echo gd_isset($checked['eggDisplayFl']['n']);?> onclick="display_toggle('eggDisplayBannerConf','hide');" /> 표시안함
            </label>
        </td>
    </tr>
    <tr id="eggDisplayBannerConf">
        <th>구매 안전 표시 로고</th>
        <td class="form-inline">
            <div>
                <label>
                    <input type="radio" name="eggDisplayBannerFl" value="offer" <?php echo gd_isset($checked['eggDisplayBannerFl']['offer']);?> /> 솔루션 제공 로고 사용
                    <input class="btn btn-sm btn-gray" type="button" value="제공 로고 보기" onclick="image_viewer('<?php echo $eggBannerDefault[$data['pgCode']];?>');" />
                </label>
            </div>
            <div class="mgt5">
                <label>
                    <input type="radio" name="eggDisplayBannerFl" value="self" <?php echo gd_isset($checked['eggDisplayBannerFl']['self']);?> /> 자체 제작한 로고 사용
                    <?php if (empty($data['eggDisplayBanner']) === false) {?>
                        <input class="btn btn-sm btn-gray" type="button" value="제작 로고 보기" onclick="image_viewer('<?php echo UserFilePath::data('etc', $data['eggDisplayBanner'])->www();?>');" />
                    <?php }?>
                </label>
                <input type="file" name="eggDisplayBanner" class="form-control" />
                <input type="hidden" name="eggDisplayBannerTmp" value="<?php echo $data['eggDisplayBanner'];?>" />
            </div>
        </td>
    </tr>
</table>

<div class="table-title gd-help-manual">
    현금영수증 설정
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
    </colgroup>
    <tr>
        <th>PG사 모듈 버전</th>
        <td>XPay Crossplatform (V 1.0 - 20150826)</td>
    </tr>
    <tr>
        <th>현금영수증 사용상태</th>
        <td>
            <label class="radio-inline"><input type="radio" name="cashReceiptFl" value="y" <?php echo gd_isset($checked['cashReceiptFl']['y']);?> /> 사용함</label>
            <label class="radio-inline"><input type="radio" name="cashReceiptFl" value="n" <?php echo gd_isset($checked['cashReceiptFl']['n']);?> /> 사용안함</label>
        </td>
    </tr>
    <tr>
        <th>필수 신청</th>
        <td>
            <label class="radio-inline"><input type="radio" name="cashReceiptAboveFl" value="y" <?php echo gd_isset($checked['cashReceiptAboveFl']['y']);?> /> 사용함</label>
            <label class="radio-inline"><input type="radio" name="cashReceiptAboveFl" value="n" <?php echo gd_isset($checked['cashReceiptAboveFl']['n']);?> /> 사용안함</label>
            <div class="notice-info">현금성 거래 시, 고객이 주문서 작성 시점에 현금영수증을 필수로 신청하도록 설정하는 기능입니다.</div>
        </td>
    </tr>
    <tr>
        <th>신청 기간 제한</th>
        <td>
            <div class="form-inline">
                결제완료일로 부터
                <?php echo gd_select_box('cashReceiptPeriod','cashReceiptPeriod', gd_array_change_key_value($cashReceiptPeriod),'일',$data['cashReceiptPeriod']);?>
                이내 신청 가능
            </div>
        </td>
    </tr>
    <tr>
        <th>현금영수증 발급방법</th>
        <td>
            <label class="radio-inline"><input type="radio" name="cashReceiptAutoFl" value="y" <?php echo gd_isset($checked['cashReceiptAutoFl']['y']);?> /> 자동 발급</label>
            <label class="radio-inline"><input type="radio" name="cashReceiptAutoFl" value="n" <?php echo gd_isset($checked['cashReceiptAutoFl']['n']);?> /> 관리자 직접 발급</label>
        </td>
    </tr>
</table>
