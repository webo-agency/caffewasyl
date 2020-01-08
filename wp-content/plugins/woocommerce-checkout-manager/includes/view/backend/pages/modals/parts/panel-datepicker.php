<div id="tab_panel_datepicker" class="panel woocommerce_options_panel hidden" style="display: none;">
  <div class="options_group wooccm-premium">
    <p class="form-field">
      <label><?php esc_html_e('Date format', 'woocommerce-checkout-manager'); ?></label>
      <input class="short" type="text" placeholder="dd-mm-yy" name="date_format" value="{{data.date_format}}">
      <span class="description"><a href="https://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date and time formatting</a>.</span>
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>
  </div>
  <div class="options_group wooccm-premium">
    <p class="form-field">
      <label><?php esc_html_e('Date limit', 'woocommerce-checkout-manager'); ?></label>
      <select class="media-modal-change media-modal-render-datepicker-limit select short" name="date_limit">
        <option <# if ( data.date_limit == 'variable' ) { #>selected="selected"<# } #> value="variable"><?php esc_html_e('Since current date', 'woocommerce-checkout-manager'); ?></option>
        <option <# if ( data.date_limit == 'fixed' ) { #>selected="selected"<# } #> value="fixed"><?php esc_html_e('Between fixed dates', 'woocommerce-checkout-manager'); ?></option>
      </select>
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>
  </div>
  <div class="options_group wooccm-premium">
    <p class="form-field">
      <label><?php esc_html_e('Days disable', 'woocommerce-checkout-manager'); ?></label>
      <select class="wooccm-enhanced-select" name="date_limit_days[]" data-placeholder="<?php esc_attr_e('Disable week days', 'woocommerce-checkout-manager'); ?>" data-allow_clear="true" multiple="multiple">
        <?php for ($day_index = 0; $day_index <= 6; $day_index++) : ?>
          <option <# if ( _.contains(data.date_limit_days, '<?php echo esc_attr($day_index); ?>') ) { #>selected="selected"<# } #> value="<?php echo esc_attr($day_index); ?>"><?php echo $wp_locale->get_weekday($day_index); ?></option>
        <?php endfor; ?>
      </select>
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>
  </div>
  <div id="wooccm-modal-datepicker-limit">
  </div>
</div>