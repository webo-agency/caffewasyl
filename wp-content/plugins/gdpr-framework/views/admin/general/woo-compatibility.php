<input
    type="checkbox"
    id="gdpr_enable_woo_compatibility"
    name="gdpr_enable_woo_compatibility"
    value="1"
    <?= checked($enabled, true); ?>
/>
<label for="gdpr_enable_woo_compatibility">
    <?= _x('Enable WooCommerce data on GDPR tool.', '(Admin)', 'gdpr-framework'); ?>
</label>
<p class="description">
    <?= _x('Will work for WooCommerce Version 3.4.0 or later.', '(Admin)', 'gdpr-framework'); ?>
</p>