<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location);?> <small></small></h3>
</div>

<div class="power-sub" style="margin:0px; width:800px;">
    <div class="right">
        <div class="payco_stat">
            <div>
                <img src="//img.godo.co.kr/enamoo/payco/payco_stat01.png" alt="" usemap="#payco_stat01"/>
                <map name="payco_stat01" id="payco_stat01">
                    <area shape="rect" coords="424,0,654,60" href="https://partner.payco.com/statistics/search.nhn?mcd=<?=$paycoSellerKey?>" alt="통계바로가기" target="_blank" onfocus="this.blur()" id="btnPaycoStat">
                    <area shape="rect" coords="660,0,800,60" href="http://guide.godo.co.kr/guide/doc/Payco_Statistics_Report_Manual.pdf" target="_blank" alt="통계리포트 매뉴얼" onfocus="this.blur()">
                </map>
            </div>
            <div><img src="//img.godo.co.kr/enamoo/payco/payco_stat02.png" alt=""></div>
            <div><img src="//img.godo.co.kr/enamoo/payco/payco_stat03.png" alt=""></div>
            <div><img src="//img.godo.co.kr/enamoo/payco/payco_stat04.png" alt=""></div>
            <div><img src="//img.godo.co.kr/enamoo/payco/payco_stat05.png" alt=""></div>
            <div>
                <img src="//img.godo.co.kr/enamoo/payco/payco_stat06.png" alt="" usemap="#payco_stat06"/>
                <map name="payco_stat06" id="payco_stat06">
                    <area shape="rect" coords="31,258,232,300" href="../marketing/marketing_info.php?menu=payco_pg" alt="페이코 서비스 신청" onfocus="this.blur()">
                </map>
            </div>
        </div>
    </div>
</div>

<?php if (empty($paycoSellerKey) === true) { ?>
<script type="text/javascript">
    $(function(){
        $('#btnPaycoStat').click(function(e){
            e.preventDefault();
            $(this).prop('href', '');
            alert('페이코 결제 서비스를 신청하셔야 이용하실 수 있습니다.');

            return false;
        });
    });
</script>
<?php } ?>
