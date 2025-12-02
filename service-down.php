<?php $page = "Service Down";
ob_start(); ?>



<?php $slot = ob_get_clean();
include 'app/components/layouts/base.php';
?>