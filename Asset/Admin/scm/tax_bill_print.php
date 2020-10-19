<style type="text/css">
    .panel {margin-top:15px;margin-bottom:30px;}
    td, th { font-family:돋움; font-size:9pt; color:333333; }

    .order_title {margin:10px 40px 10px 40px; padding:10px 30px 10px 30px; border:3px solid #627DCE; background-color:#F7F7F7; font-size:11px; line-height:16px;}
    td, th { font-family: 돋움; font-size: 9pt; color: 333333;}

    #cssblue { margin:0px auto 0px auto; width: 604px; border: solid 2px #364F9E;  }
    #cssblue strong { font:18pt 굴림; color:#364F9E; font-weight:bold; }
    #cssblue table { border-collapse: collapse; }
    #cssblue td, #cssblue table { border-color: #364F9E; border-style: solid; border-width: 0; }

    #cssblue #head { border-width: 1px 1px 0 1px; }
    #cssblue #box td { border-width: 1px; padding-top: 3px; }

    #cssred { margin:0px auto 0px auto; width: 604px; border: solid 2px #FF4633;  }
    #cssred strong { font:18pt 굴림; color:#FF4633; font-weight:bold; }
    #cssred table { border-collapse: collapse; }
    #cssred td, #cssred table { border-color: #FF4633; border-style: solid; border-width: 0; }

    #cssred #head { border-width: 1px 1px 0 1px; }
    #cssred #box td { border-width: 1px; padding-top: 3px; }
</style>

<div class="page-header js-affix">
    <h3>세금계산서</h3>
    <div class="btn-group">
        <button class="btn btn-white js-print hidden-print">인쇄하기</button>
    </div>
</div>

<div class="hidden-print">
    <div class="panel panel-default">
        <div class="panel-heading">
            인쇄가이드
        </div>
        <div class="panel-body">
            <p>※ 인쇄시 직인(도장이미지)도 인쇄되려면 아래와 같이 설정되어 있어야 가능합니다.</p>
            <ol>
                <li>인터넷 익스플로러 : 브라우저의 [도구 메뉴]-[인터넷옵션]-[고급]-[인쇄] 에서 [배경색 및 이미지 인쇄] 체크</li>
                <li>파이어폭스 : 브라우저의 [파일]-[인쇄화면설정]-[용지 및 설정]-[옵션]에서 [배경 인쇄(색상 및 그림)] 체크</li>
            </ol>
            <?php if ($orderPrintMode == 'report') { ?>
                <p>※ 인쇄시 상품이 많아 내용이 짤린다면 아래와 같이 설정해 주세요.</p>
                <ol>
                    <li>인터넷 익스플로러 : 브라우저의 [파일]-[페이지설정] 에서 [여백]부분을 전부 0으로 설정</li>
                </ol>

            <?php } ?>
            <div class="center">

            </div>
        </div>
    </div>
</div>

<!-- 영수증 출력 -->
<?php
foreach($taxInfo['classids'] as $k => $v) {
?>
    <hr style="border:1px dashed #d9d9d9; width:500px;" />
    <div id="<?=$v?>">
        <table id="head" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="50%" align="right"><strong>세 금 계 산 서</strong></td>
                            <td width="50%" style="padding-left:6px">(<?=$taxInfo['headuser'][$v]?>)</td>
                        </tr>
                    </table>
                </td>
                <td width="60" style="border-right-width: 3px;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td height="28" align="center">책&nbsp;번&nbsp;호</td>
                        </tr>
                        <tr>
                            <td height="24" align="center">일련번호</td>
                        </tr>
                    </table>
                </td>
                <td width="150">
                    <table width="100%" border="0" cellspacing="0" cellpadding="4">
                        <tr height="26">
                            <td width="50%" align="right" style="border-right-width: 1px; border-bottom-width: 1px;"> 권</td>
                            <td width="50%" align="right" style="border-bottom-width: 1px;"> 호</td>
                        </tr>
                        <tr height="26">
                            <td align="center" style="border-right-width: 1px;">&nbsp;</td>
                            <td align="center">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <col width="3%"><col width="47%"><col width="3%">
            <tr>
                <!-- 공급자 -->
                <td align="center" style="border-width: 3px 1px 0px 1px; padding-left: 2px; line-height: 36px;">공<br>급<br>자</td>
                <td valign="top" height="100%" style="border-width: 3px 1px 0 0; background: url(<?=$sealPath?>) no-repeat; background-position: 210px 18px;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <col width="53"><col width="100"><col width="26">
                        <tr height="38" align="center">
                            <td style="border-width: 0 1px 1px 2px;">등록번호</td>
                            <td colspan="3" style="border-width: 0 2px 1px 0; padding-left:6px;"><?=$gMall['businessNo']?></td>
                        </tr>
                        <tr height="38" align="center">
                            <td style="border-width: 0 1px 3px 2px;">상&nbsp;&nbsp;&nbsp;&nbsp;호<br>(법인명) </td>
                            <td style="padding:0 6px; border-bottom-width:3px;"><?=$gMall['companyNm']?></td>
                            <td style="border-width: 0 1px 3px 1px;">성명</td>
                            <td style="border-width: 0 2px 3px 0; padding-right:10px;" align="left">&nbsp;<?=$gMall['ceoNm']?></td>
                        </tr>
                        <tr height="48" align="center">
                            <td style="border-width: 0 1px 1px 0px;">사 업 장<br>소 재 지 </td>
                            <td colspan="3" style="padding: 0 6px; border-bottom-width: 1px;" align="left"><?=$gMall['address']?> <?=$gMall['addressSub']?></td>
                        </tr>
                        <tr height="38" align="center">
                            <td style="border-right-width: 1px;">업&nbsp;&nbsp;&nbsp;&nbsp;태</td>
                            <td style="padding:0 6px;"><?=$gMall['service']?></td>
                            <td style="border-width: 0 1px;">종<br>목 </td>
                            <td style="padding: 0 6px;"><?=$gMall['item']?></td>
                        </tr>
                    </table>
                </td>
                <!-- 공급받는자 -->
                <td align="center" style="border-top-width: 3px; border-right-width: 1px; line-height: 22px; padding-left: 2px">공<br>급<br>받<br>는<br>자</td>
                <td valign="top" height="100%" style="border-top-width: 3px;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <col width="53"><col width="100"><col width="26">
                        <tr height="38" align="center">
                            <td style="border-width: 0 1px 1px 2px;">등록번호</td>
                            <td colspan="3" style="border-bottom-width: 1px; padding-left:6px;"><?=$data['scmBusinessNo']?></td>
                        </tr>
                        <tr height="38" align="center">
                            <td style="border-width: 0 1px 3px 2px;">상&nbsp;&nbsp;&nbsp;&nbsp;호<br>(법인명) </td>
                            <td style="padding:0 6px; border-bottom-width:3px;"><?=$data['scmCompanyNm']?></td>
                            <td style="border-width: 0 1px 3px 1px;">성명</td>
                            <td style="border-bottom-width: 3px; padding-right:10px;"><?=$data['scmCeoNm']?></td>
                        </tr>
                        <tr height="48" align="center">
                            <td style="border-width: 0 1px 1px 0px;">사 업 장<br>소 재 지 </td>
                            <td colspan="3" style="padding: 0 6px; border-bottom-width: 1px;" align="left"><?=$data['scmAddress']?> <?=$data['scmAddressSub']?></td>
                        </tr>
                        <tr height="38" align="center">
                            <td style="border-right-width: 1px;">업&nbsp;&nbsp;&nbsp;&nbsp;태</td>
                            <td style="padding:0 6px;"><?=$data['scmService']?></td>
                            <td style="border-width: 0 1px;">종<br>목 </td>
                            <td style="padding: 0 6px;"><?=$data['scmItem']?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table id="box" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-top-width:3px; border-bottom-width:2px;">
            <col width="34"><colgroup span="2" width="20"></colgroup><col width="34"><colgroup span="11"></colgroup><col width="1"><colgroup span="10"></colgroup><col width="64">
            <tr align="center">
                <td colspan="3">작&nbsp;&nbsp;&nbsp;성</td>
                <td colspan="12">공&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;급&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;액</td>
                <td><img src="" width="1" height="1" /></td>
                <td colspan="10" style="border-right-width:3px;">세&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;액</td>
                <td >비&nbsp;&nbsp;&nbsp;&nbsp;고</td>
            </tr>
            <tr align="center">
                <td>년</td>
                <td>월</td>
                <td>일</td>
                <td style="letter-spacing:-2px">공란수</td>
                <td>백</td>
                <td>십</td>
                <td>억</td>
                <td>천</td>
                <td>백</td>
                <td>십</td>
                <td>만</td>
                <td>천</td>
                <td>백</td>
                <td>십</td>
                <td>일</td>
                <td><img src="" width="1" height="1" /></td>
                <td>십</td>
                <td>억</td>
                <td>천</td>
                <td>백</td>
                <td>십</td>
                <td>만</td>
                <td>천</td>
                <td>백</td>
                <td>십</td>
                <td>일</td>
                <td style="border-left-width:3px;">&nbsp;</td>
            </tr>
            <tr align="center" height="34">
                <td><?=gd_date_format('Y',$data['scmAdjustTaxBillDt'])?></td>
                <td><?=gd_date_format('m',$data['scmAdjustTaxBillDt'])?></td>
                <td><?=gd_date_format('d',$data['scmAdjustTaxBillDt'])?></td>
                <td>&nbsp;<?=(11-strlen($data['scmAdjustTaxPrice']))?></td>
                <?php for ($ixx = (strlen(number_format($data['scmAdjustTaxPrice'], 0, '', '')) - 11); $ixx < strlen(number_format($data['scmAdjustTaxPrice'], 0, '', '')); ++$ixx){?>
                    <td><?php if ($ixx >= 0) { echo number_format($data['scmAdjustTaxPrice'], 0, '', '')[$ixx]; } else { echo '&nbsp;'; }?></td>
                <?php }?>
                <td><img src="" width="1" height="1"></td>
                <?php for ($ixx = (strlen(number_format($data['scmAdjustVatPrice'], 0, '', '')) - 10); $ixx < strlen(number_format($data['scmAdjustVatPrice'], 0, '', '')); ++$ixx){?>
                    <td><?php if ($ixx >= 0) { echo number_format($data['scmAdjustVatPrice'], 0, '', '')[$ixx]; } else { echo '&nbsp;'; }?></td>
                <?php }?>
                <td style="border-left-width:3px; line-height:11px;"><?php echo $data['scmAdjustTaxBillNo'];?></td>
            </tr>
        </table>

        <table id="box" width="100%" border="0" cellspacing="0" cellpadding="0">
            <colgroup span="2" width="3%"></colgroup><col><colgroup span="2" width="9%"></colgroup><col width="12%"><col width="19%"><col width="13%"><col width="8%">
            <tr height="24" align="center">
                <td>월</td>
                <td>일</td>
                <td>품&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;목</td>
                <td>규&nbsp;&nbsp;격</td>
                <td>수&nbsp;&nbsp;량</td>
                <td>단&nbsp;&nbsp;가</td>
                <td>공&nbsp;&nbsp;급&nbsp;&nbsp;가&nbsp;&nbsp;액</td>
                <td>세&nbsp;&nbsp;액</td>
                <td>비&nbsp;&nbsp;고</td>
            </tr>
            <tr height="25" align="center">
                <td><?=gd_date_format('m',$data['scmAdjustTaxBillDt'])?></td>
                <td><?=gd_date_format('d',$data['scmAdjustTaxBillDt'])?></td>
                <td style="padding: 0 6px; word-break:break-all">수수료</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td align="right" style="padding-right:6px">&nbsp;</td>
                <td align="right" style="padding-right:6px"><?=number_format($data['scmAdjustTaxPrice'])?>원</td>
                <td align="right" style="padding-right:6px"><?=number_format($data['scmAdjustVatPrice'])?>원</td>
                <td>&nbsp;</td>
            </tr>

            <? for($jj = 1; $jj < 4; $jj++){ ?>
                <tr height="25">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            <? } ?>
        </table>

        <table id="box" width="100%" border="0" cellspacing="0" cellpadding="0">
            <col width="100"><colgroup span="4" width="88"></colgroup>
            <tr align="center">
                <td style="border-top-width: 0;">합 계 금 액</td>
                <td style="border-top-width: 0;">현&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;금</td>
                <td style="border-top-width: 0;">수&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;표</td>
                <td style="border-top-width: 0;">어&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;음</td>
                <td style="border-top-width: 0;">외상미수금</td>
                <td rowspan="3" style="border-top-width: 0;">위 금액을 영수 함</td>
            </tr>
            <tr height="25" align="center">
                <td><?=number_format($data['scmAdjustTaxPrice'] + $data['scmAdjustVatPrice'])?>원</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </div>
    <br/>
<?php } ?>
<!-- // 영수증 출력 -->

