<?php
function adjustTableColumnWidths($htmlTable)
{
	//Envolvemos la tabla en un documento html para poder manipularla con DOMDocument
	$wrappedHtml = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $htmlTable . '</body></html>';

	$dom = new DOMDocument();
	$dom->loadHTML($wrappedHtml);
	libxml_clear_errors();

	//Obtenemos la tabla

	$tables = $dom->getElementsByTagName('table');
	if ($tables->length > 0) {
		$table = $tables->item(0);  //TODO: Procesar más de una tabla	
	} else {
		return $htmlTable;
	}

	//Obtener el ancho total de la tabla

	$tableWidth = 0;
	if ($table->hasAttribute('width')) {
		$tableWidth = intval($table->getAttribute('width'));
	} elseif ($table->hasAttribute('style')) {
		preg_match('/width:\s*(\d+)(px|pt)/', $table->getAttribute('style'), $matches);
		if (!empty($matches[1])) {
			$tableWidth = intval($matches[1]);
		}
	} else { // Si no hay ancho definido en la tabla, obtenerlo del ancho de las columnas
		$colgroup = $dom->getElementsByTagName('colgroup')->item(0);
		$elements = $colgroup ? $colgroup->getElementsByTagName('col') : $table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td');
		foreach ($elements as $element) {
			if ($element->hasAttribute('width')) {
				$tableWidth += intval($element->getAttribute('width'));
			} elseif ($element->hasAttribute('style')) {
				preg_match('/width:\s*(\d+)(px|pt)/', $element->getAttribute('style'), $matches);
				if (!empty($matches[1])) {
					$tableWidth += intval($matches[1]);
				}
			}
		}
	}
		// Ajustar el ancho de la tabla al 100%
		$table->setAttribute('style', 'width: 100%;');
		$table->removeAttribute('width');

	if ($tableWidth == 0) {
		return $htmlTable;
	}

	// Calculamos el ancho de las columnas
	$colgroup = $dom->getElementsByTagName('colgroup')->item(0);
	$elements = $colgroup ? $colgroup->getElementsByTagName('col') : $table->getElementsByTagName('tr')->item(0)->getElementsByTagName('td');
	foreach ($elements as $element) {
		if ($element->hasAttribute('width')) {
			$colWidth = intval($element->getAttribute('width'));
		} elseif ($element->hasAttribute('style')) {
			preg_match('/width:\s*(\d+)(px|pt)/', $element->getAttribute('style'), $matches);
			if (!empty($matches[1])) {
				$colWidth = intval($matches[1]);
			}
		}
		if ($colWidth > 0) {
			$percentWidth = ($colWidth / $tableWidth) * 100;
			$element->setAttribute('style', 'width: ' . round($percentWidth, 2) . '%');
			$element->removeAttribute('width');
		}
	}

	// Eliminar el atributo width o style: width de todas las demás celdas
	foreach ($table->getElementsByTagName('tr') as $row) {
		$firstRow = true; // No eliminamos los anchos de la primera fila
		if ($firstRow) {
			$firstRow = false;
			continue;
		}
		foreach ($row->getElementsByTagName('td') as $cell) {
			if (!$cell->hasAttribute('style') || !preg_match('/width:\s*\d+(\.\d+)?%/', $cell->getAttribute('style'))) {
				$cell->removeAttribute('width');
				$style = $cell->getAttribute('style');
				$style = preg_replace('/width:\s*\d+(px|pt|%);?\s*/', '', $style);
				if (empty($style)) {
					$cell->removeAttribute('style');
				} else {
					$cell->setAttribute('style', $style);
				}
			}
		}
	}

	// Extraer solo el cuerpo de la tabla
	$tableHtml = $dom->saveHTML($table);

	return $tableHtml;
}
