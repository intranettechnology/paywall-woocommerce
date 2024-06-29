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
            <td><?php echo esc_html__('The name written on the card', 'pwo') ?></td>
            <td><input type="text" name="card_name" size="20" value="" />
        </tr>
        <tr>
            <td><?php _e('Credit Card Number', 'pwo'); ?></td>
            <td><input type="text" name="Pan" size="20" value="" />
        </tr>
        <tr>
            <td><?php _e('Expiration date', 'pwo'); ?></td>
            <td>
                <div style="display:flex;align-items:center;">
                    <div>
                        <label for="ay"><?= _e('Expire Month', 'pwo'); ?></label>
                        <input required type="text" placeholder="01" name="ay" id="ay" maxlength="2">
                    </div>
                    <div>
                        <label for="yil"><?= _e('Expire Year', 'pwo'); ?></label>
                        <input required type="text" placeholder="23" name="yil" id="yil" maxlength="2">
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td><?php _e('Security Code (CVV2)', 'pwo'); ?></td>
            <td><input type="text" name="Cvv2" size="4" value="" /></td>
        </tr>
        <tr>
            <td><?php _e('Number of Installments', 'pwo'); ?></td>
            <td><select name="InstallmentCount" id="">
                    <?php
                    for ($i = 1; $i < 13; $i++) {
                    ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php }  ?>
                </select>
                <p id="taksit1" style="display: inline; margin-left: 10px"><?php echo $order->get_total() . " " . get_woocommerce_currency_symbol(); ?></p>
            </td>
        </tr>
    </table>
    <input type="submit" id="gonder" value="<?= __('Pay', 'pwo'); ?>">
</form>