<div class="wrap">
    <h2>Login Control</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('ks_login_controller-group'); ?>
        <?php @do_settings_fields('ks_login_controller-group'); ?>
		<?php $content = get_option('ksLoginControl'); ?>
        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="login_msg">Custom Login Error Message</label></th>
                <td><input type="text" name="ksLoginControl[login_msg]" id="login_msg" value="<?php echo $content['login_msg']; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="login_limit">Login Try Limit</label></th>
                <td><input type="text" name="ksLoginControl[login_limit]" id="login_limit" value="<?php echo $content['login_limit']; ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="disable_time">Disable Login time (seconds)</label></th>
                <td><input type="text" name="ksLoginControl[disable_time]" id="disable_time" value="<?php echo $content['disable_time']; ?>" /></td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
</div>