<?php
if ( !defined( 'ABSPATH' ) ) exit;
$sevices_ids = $data[0];
$times = $data[1];
$category_id = $data[2];
?>
<?php
     echo WBK_Placeholder_Processor::process_not_booked_item_placeholders( $sevices_ids, $times, $category_id );

?>
