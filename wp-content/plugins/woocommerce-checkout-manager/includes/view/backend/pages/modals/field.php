<script type="text/html" id='tmpl-wooccm-modal-main'>
<?php include_once( 'parts/main.php' ); ?>
</script>
<script type="text/html" id='tmpl-wooccm-modal-tabs'>
<?php include_once( 'parts/tabs.php' ); ?>
</script>
<script type="text/html" id='tmpl-wooccm-modal-panels'>
<?php include_once( 'parts/panel-general.php' ); ?>
  <# if ( _.contains(<?php echo json_encode(array('select', 'multiselect')); ?>, data.type)) { #>
<?php include_once( 'parts/panel-select2.php' ); ?>
  <# } #>
  <# if ( _.contains(<?php echo json_encode($option); ?>, data.type)) { #>
<?php include_once( 'parts/panel-options.php' ); ?>
  <# } #>
<?php include_once( 'parts/panel-display.php' ); ?>
  <# if ( !_.contains(<?php echo json_encode(array_merge($option, $template)); ?>, data.type)) { #>
<?php include_once( 'parts/panel-price.php' ); ?>
  <# } #>
  <# if (data.type == 'date') { #>
<?php include_once( 'parts/panel-datepicker.php' ); ?>
  <# } #>
  <# if (data.type == 'time') { #>
<?php include_once( 'parts/panel-timepicker.php' ); ?>
  <# } #>
<?php include_once( 'parts/panel-admin.php' ); ?>
</script>
<script type="text/html" id='tmpl-wooccm-modal-datepicker-limit'>
<?php include_once( 'parts/panel-datepicker-limit.php' ); ?>
</script>
<script type="text/html" id='tmpl-wooccm-modal-info'>
  <?php include_once( 'parts/info.php' ); ?>
</script>