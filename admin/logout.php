<?php
include_once('functions/session.php');
start_session_if_none();
session_destroy();
?>

<script language="javascript">
    document.location = "admin.php";
</script>