<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// When certain tokens are set in the URL, update the option.
if (isset($_GET['access_token'])) {
    update_site_option('webarx_googledrive_access_token', $_GET['access_token']);
}
if (isset($_GET['refresh_token'])) {
    update_site_option('webarx_googledrive_refresh_token', $_GET['refresh_token']);
}
if (isset($_GET['folder_id'])) {
    update_site_option('webarx_googledrive_root_folder', $_GET['folder_id']);
}
if (isset($_GET['folder_site'])) {
    update_site_option('webarx_googledrive_site_folder', $_GET['folder_site']);
}

if (isset($_GET['disable_backups']) && current_user_can('manage_options') && wp_verify_nonce($_GET['WebarxNonce'], 'webarx-nonce')) {
    update_site_option('webarx_googledrive_access_token', '');
}

$accessToken = get_site_option('webarx_googledrive_access_token');
if (empty($accessToken)) {
?>
    <h3><?php echo __('Backup site files and database', 'webarx'); ?></h3>
    <p><?php echo __('Backups are stored in your Google Drive account. In order to save backup, please link your Google account first.', 'webarx'); ?></p>
    <p><input type="submit" id="webarx_backup_connect_gdrive" name="webarx_backup_connect_gdrive" value="<?php echo __('Connect to Google Drive', 'webarx'); ?>" class="button-primary" /></p>
    <br />
<?php
} else {
?>
<h3><?php echo __('Backup Settings', 'webarx'); ?></h3>

<?php if (is_multisite()) { ?>
    <p style="color: red;">We do not recommend using the backup feature on multisites that have many sites or many large files on the server. It is recommended to use a backup solution provided by your hosting provider.</p>
<?php } ?>

<p>If the site is in process of creating or reverting a backup archive, all other actions will not be available.</p>
<table class="form-table webarx-form-table">
    <tr>
        <th>
            <label><?php echo __('Backup frequency', 'webarx'); ?></label>
        </th>
        <td>
            <table>
                <tr>
                    <td>
                        <select name="webarx-backup-frequency" id="webarx-backup-frequency">
                            <option value="12hours" <?php echo get_site_option('webarx_backup_frequency') == '12hours' ? 'selected' : '' ?> >Every 12 hours</option>
                            <option value="24hours" <?php echo get_site_option('webarx_backup_frequency') == '24hours' ? 'selected' : '' ?> >Every 24 hours</option>
                            <option value="48hours" <?php echo get_site_option('webarx_backup_frequency') == '48hours' ? 'selected' : '' ?> >Every 48 hours</option>
                            <option value="week" <?php echo get_site_option('webarx_backup_frequency') == 'week' ? 'selected' : '' ?>>Every week</option>
                        </select>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <th>
            <label><?php echo __('Max number of backups to keep', 'webarx'); ?></label>
        </th>
        <td>
            <table>
                <tr>
                    <td>
                        <input type="text" name="webarx_backups_limit" value="<?php echo get_site_option('webarx_backups_limit', 7); ?>"/>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <p class="submit">
                <input type="submit" id="webarx-save-backup-settings" name="webarx-save-backup-settings" value="<?php echo __('Save Settings', 'webarx'); ?>" class="button" <?php echo get_site_option('webarx_googledrive_backup_is_running', false) == true ? 'disabled' : '' ?> />
                <a href="?page=<?php echo $page; ?>&tab=sitebackup&disable_backups=true&WebarxNonce=<?php echo wp_create_nonce('webarx-nonce') ?>">
                    <input type="submit" id="webarx-disable-backups" name="webarx-disable-backups" value="<?php echo __('Disable Backups', 'webarx'); ?>" class="button" <?php echo get_site_option('webarx_googledrive_backup_is_running', false) == true ? 'disabled' : '' ?> />
                </a>
            </p>
        </td>
    </tr>
</table>

<h3><?php echo __('Revert to backup', 'webarx'); ?></h3>
<table class="form-table webarx-form-table">
    <tr>
        <th><label><?php echo __('Select date to revert ', 'webarx'); ?></label></th>
        <td>
            <select name="webarx-select-backup" id="webarx-select-backup">
                <?php
                $backups = $this->plugin->backup->getAvailableBackups();
                foreach ($backups as $backup) {
                    echo "<option value='" . $backup->id . "'>" . $backup->name . "</option>";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th><label><?php echo __('Revert method ', 'webarx'); ?></label></th>
        <td>
            <select name="" id="">
                <option value="">Files and database</option>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <p class="submit">
                <input type="submit" id="webarx-revert-backup" name="webarx-revert-backup" value="<?php echo __('Revert', 'webarx'); ?>" class="button" <?php echo get_site_option('webarx_googledrive_backup_is_running', false) == true ? 'disabled' : '' ?> />&nbsp;
                <input type="submit" id="webarx-backup-create" name="webarx-test-api" value="<?php echo __('Create Backup', 'webarx'); ?>" class="button" <?php echo get_site_option('webarx_googledrive_backup_is_running', false) == true ? 'disabled' : '' ?> />&nbsp;
            </p>
        </td>
    </tr>
</table>
Current status: <pre id="webarx-backup-status"><?php echo get_site_option('webarx_googledrive_upload_state') ?></pre>
<script>
    setInterval(get_status, 10000);
    function get_status(){
        var feedback = jQuery.ajax({
            type: "POST",
            data: {'WebarxNonce': WebarxVars.nonce},
            url: "/wp-admin/admin-ajax.php?action=webarx_get_backup_state",
            async: false
        }).success(function(){}).responseText;
        jQuery('pre#webarx-backup-status').html(JSON.parse(feedback).status);
    }
</script>
<?php
}
?>