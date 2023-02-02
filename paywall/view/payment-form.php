<?php
    if (!defined('ABSPATH')) {
        exit;
    }
?>
<style>
    .gizle {
        display: none !important;
    }
</style>
<form method="post" action="" id="paymentForm">
    <table>
        <tr>
            <td><?php echo esc_html__( 'Kartın üzerinde yazan isim', 'paywall') ?></td>
            <td><input type="text" name="card_name" size="20" value=""/>
        </tr>
        <tr>
            <td><?php _e('Kredi Kart Numarasi', 'paywall'); ?></td>
            <td><input type="text" name="Pan" size="20" value=""/>
        </tr>
        <tr>
            <td><?php _e('Son Kullanma Tarihi', 'paywall'); ?></td>
            <td>
                <select name="ay" id="ay" class="form-control">
                    <option value="01"><?php _e('Ocak', 'paywall'); ?></option>
                    <option value="02"><?php _e('Şubat', 'paywall'); ?></option>
                    <option value="03"><?php _e('Mart', 'paywall'); ?></option>
                    <option value="04"><?php _e('Nisan', 'paywall'); ?></option>
                    <option value="05"><?php _e('Mayıs', 'paywall'); ?></option>
                    <option value="06"><?php _e('Haziran', 'paywall'); ?></option>
                    <option value="07"><?php _e('Temmuz', 'paywall'); ?></option>
                    <option value="08"><?php _e('Ağustos', 'paywall'); ?></option>
                    <option value="09"><?php _e('Eylül', 'paywall'); ?></option>
                    <option value="10"><?php _e('Ekim', 'paywall'); ?></option>
                    <option value="11"><?php _e('Kasım', 'paywall'); ?></option>
                    <option value="12"><?php _e('Aralık', 'paywall'); ?></option>
                </select>
                <select name="yil" id="yil" class="form-control"><?php
                        for ($i = wp_date("Y"); $i < wp_date("Y") + 15; $i++) {
                            echo '<option value="' . str_replace("20", "", $i) . '">' . $i . '</option>';
                        }
                    ?>
                    <option value="2023"></option>
                </select>
            </td>
        </tr>

        <tr>
            <td><?php _e('Güvenlik Kodu', 'paywall'); ?></td>
            <td><input type="text" name="Cvv2" size="4" value=""/></td>
        </tr>
        <tr>
            <td><?php _e('Taksit Sayısı', 'paywall'); ?></td>
            <td><select name="InstallmentCount" id=""><?php
                        for ($i = 1; $i < 13; $i++) {
                            ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php
                        }
                    ?>
                </select>

                <p id="taksit1" style="display: inline; margin-left: 10px"><?php echo $order->get_total() . " " . get_woocommerce_currency_symbol(); ?></p>

            </td>
        </tr>
    </table>
    <input type="submit" id="gonder" value="ÖDEME">
</form>
