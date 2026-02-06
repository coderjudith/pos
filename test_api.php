<?php
session_start();
echo json_encode(['test' => 'success', 'session_id' => session_id()]);
?>