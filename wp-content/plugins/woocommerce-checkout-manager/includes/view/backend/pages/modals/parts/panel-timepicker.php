<div id="tab_panel_timepicker" class="panel woocommerce_options_panel hidden" style="display: none;">
  <div class="options_group wooccm-premium">
    <p class="form-field">
      <label><?php esc_html_e('Hour start', 'woocommerce-checkout-manager'); ?></label>
      <input class="short" type="number" min="0" max="24" placeholder="6" name="time_limit_start" value="{{data.time_limit_start}}">
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>
    <p class="form-field">
      <label><?php esc_html_e('Hour end', 'woocommerce-checkout-manager'); ?></label>
      <input class="short" type="number" min="0" max="24" placeholder="9" name="time_limit_end" value="{{data.time_limit_end}}">
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>
    <p class="form-field">
      <label><?php esc_html_e('Minutes interval', 'woocommerce-checkout-manager'); ?></label>
      <input class="short" type="number" min="0" max="60" step="5" placeholder="15" name="time_limit_interval" value="{{data.time_limit_interval}}">
      <span class="description premium">(<?php esc_html_e('This is a premium feature', 'woocommerce-checkout-manager'); ?>)</span>
    </p>
  </div>
</div>