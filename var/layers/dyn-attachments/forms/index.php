<?php

global $_BASE_PATH;

$_BASE_PATH = "../../../../";

switch($_REQUEST['formtype'])
{
 case 'editatt' : include($_BASE_PATH.'var/layers/dyn-attachments/forms/edit_attachment.php'); break;
}

