
<input type="url" name="gdpr_custom_policy_page" value="<?php if($content!=""){?>
<?= _x($content, 'gdpr-framework');?>
<?php } ?>" />
<p class="description">
    <?= _x('Leave blank if privacy policy page already selected', '(Admin)', 'gdpr-framework'); ?>
</p>