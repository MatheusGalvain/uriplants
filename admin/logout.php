<?php
include_once('includes/session.php');
start_session_if_none();
session_destroy();
?>

<script language="javascript">
document.location="index.php";
</script>
