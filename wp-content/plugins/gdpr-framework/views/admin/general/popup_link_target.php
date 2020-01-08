<select class="gdpr-select js-gdpr-conditional" name="gdpr_popup_link_target">
<option value="_blank" <?= selected($content, '_blank'); ?>>
    <?= _x('Next Tab', '(Admin)', 'gdpr-framework') ?>
</option>
<option value="_self" <?= selected($content, '_self'); ?>>
    <?= _x('Self', '(Admin)', 'gdpr-framework') ?>
</option>
</select>
