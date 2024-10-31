<?php

function log_audit($con, $table, $action_id, $changed_by, $old_value = null, $new_value = null, $plant_id = null)
{

    $table = mysqli_real_escape_string($con, $table);
    $action_id = intval($action_id);
    $changed_by = intval($changed_by);
    $plant_id = isset($plant_id) ? intval($plant_id) : 'NULL';
    $old_value = isset($old_value) ? "'" . mysqli_real_escape_string($con, $old_value) . "'" : 'NULL';
    $new_value = isset($new_value) ? "'" . mysqli_real_escape_string($con, $new_value) . "'" : 'NULL';


    $sql = "INSERT INTO auditlogs (table_name, action_id, changed_by, change_time, old_value, new_value, plant_id) 
            VALUES ('$table', $action_id, $changed_by, NOW(), $old_value, $new_value, " . (is_numeric($plant_id) ? $plant_id : 'NULL') . ")";

    if (mysqli_query($con, $sql)) {
        return true;
    } else {
        return false;
    }
}
