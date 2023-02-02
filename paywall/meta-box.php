<?php
    if (!defined('ABSPATH')) {
        exit;
    }
    require_once "class/class-paywall.php";

    $order_id = $meta_id->ID;

    $MerchantUniqueCode = get_post_meta($order_id, "MerchantUniqueCode", true);
    $paywall = new paywall();
    $sonuc = $paywall->queryRequest($MerchantUniqueCode);

    $provider = $sonuc->Body->Provider->PaymentGatewayProviderResponse;

?>

<style>
    .tamEkran {
        background-color: #e7e7e7;
        z-index: 999999999!important;
        width: 100%;
        height: 100%;
        position: fixed;
        top: 0;
        left:0;
        text-align: center;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .loader {
        width: 48px;
        height: 48px;
        border: 5px solid #FFF;
        border-radius: 50%;
        display: inline-block;
        box-sizing: border-box;
        position: relative;
        animation: pulse 1s linear infinite;
    }
    .loader:after {
        content: '';
        position: absolute;
        width: 48px;
        height: 48px;
        border: 5px solid #FFF;
        border-radius: 50%;
        display: inline-block;
        box-sizing: border-box;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        animation: scaleUp 1s linear infinite;
    }

    @keyframes scaleUp {
        0% { transform: translate(-50%, -50%) scale(0) }
        60% , 100% { transform: translate(-50%, -50%)  scale(1)}
    }
    @keyframes pulse {
        0% , 60% , 100%{ transform:  scale(1) }
        80% { transform:  scale(1.2)}
    }

    .info-msg,
    .success-msg,
    .warning-msg,
    .error-msg {
        margin: 10px 0;
        padding: 10px;
        border-radius: 3px 3px 3px 3px;
        display: none;
    }
    .info-msg {
        color: #059;
        background-color: #BEF;
    }
    .success-msg {
        color: #270;
        background-color: #DFF2BF;
    }
    .warning-msg {
        color: #9F6000;
        background-color: #FEEFB3;
    }
    .error-msg {
        color: #D8000C;
        background-color: #FFBABA;
    }

</style>

<div class="tamEkran" id="loading">
    <span class="loader"></span>
</div>


<div class="success-msg" id="iptalBasarili">
    <?php _e('Ödeme iptal edilmiştir.', 'paywall'); ?>
</div>
<div class="error-msg" id="iptalBasarisiz">
    <?php _e('Ödeme iptal edilirken hata oluştu. Lütfen tekrar deneyin.', 'paywall'); ?>
</div>
<div class="success-msg" id="iadeBasarili">
    <?php _e('Ödeme iade edilmiştir.', 'paywall'); ?>
</div>
<div class="error-msg" id="iadeBasarisiz">
    <?php _e('Ödeme iade edilirken hata oluştu. Lütfen tekrar deneyin.', 'paywall'); ?>
</div>

<table class="form-table">

    <tr>
        <th scope="row"><label for="blogname">Ödeme Durumu</label></th>
        <td><?php echo $sonuc->Body->Paywall->TypeName ?></td>
    </tr>

    <!--<tr>
        <th scope="row"><label for="blogname">Banka</label></th>
        <td><?php echo $provider->CardFamily ?></td>
    </tr>
    <tr>
        <th scope="row"><label for="blogname">Kart Numarası</label></th>
        <td><?php echo $provider->BinNumber . "xxxxxx" . $provider->LastFourDigits ?></td>
    </tr>
    <tr>
        <th scope="row"><label for="blogname">Kart Tipi</label></th>
        <td><?php echo $provider->CardType ?></td>
    </tr>-->

    <tr>
        <th scope="row"><label for="blogname">Ödeme Saati</label></th>
        <td><?php echo wp_date("d F Y H:i:s", get_post_meta($order_id, "odeme_saati", true)) ?></td>
    </tr>

</table>
<?php
    if(get_post_meta($order_id, "iptal", true)!=1 && get_post_meta($order_id, "iade", true)!=1){ ?>
<button type="button" class="button save_order button-primary" id="iptal">İptal</button> <?php
    }
    if(get_post_meta($order_id, "iade", true)!=1 && get_post_meta($order_id, "iptal", true)!=1){ ?>
<button type="button" class="button save_order button-primary" id="iade">İade</button><?php
    }
?>

<script>
    jQuery(document).ready(function ($) {

        $("#iptal").click(function (e) {

            $("#loading").css("display", "flex");
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php") ?>',
                type: 'POST',
                data: {
                    "action": "cancelPayment",
                    "order_id": <?php echo $order_id ?>,
                    "MerchantUniqueCode": "<?php echo $MerchantUniqueCode ?>",

                },
                success: function (data) {

                    if(data==="1"){
                        $("#iptal").remove();
                        $("#iptalBasarili").show();
                    } else {
                        $("#iptalBasarisiz").show();
                    }
                    $("#loading").css("display", "none");

                },
            });
        })
        $("#iade").click(function (e) {

            $("#loading").css("display", "flex");
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php") ?>',
                type: 'POST',
                data: {
                    "action": "refundPayment",
                    "order_id": <?php echo $order_id ?>,
                    "MerchantUniqueCode": "<?php echo $MerchantUniqueCode ?>",

                },
                success: function (data) {

                    if(data==="1"){
                        $("#iade").remove();
                        $("#iadeBasarili").show();
                    } else {
                        $("#iadeBasarisiz").show();
                    }
                    $("#loading").css("display", "none");

                },
            });
        })
    });
</script>
