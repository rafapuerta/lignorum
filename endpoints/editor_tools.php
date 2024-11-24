<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Hacer anchos de columnas de una tabla relativos
	if (isset($_POST['action']) && $_POST['action'] == 'relative_table_width' && isset($_POST['html'])) {

		require_once '../functions/adjust_table_column_width.php';

		$adjustedHtmlTable = adjustTableColumnWidths($_POST['html']);

		echo $adjustedHtmlTable;
	}
}