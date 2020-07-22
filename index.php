<?php
/**
 *
 * SAWPFORM
 * (c) Kamil Barański
 *
 */

$SAWPTXT2PDFwersja =   '3.00';
$SAWPTXT2PDFdata =     '12 września 2019 r.';

/**
 *
 * Narzędzie wspomaga wypełnianie formularzy SAWP.
 * Sposób użycia w pliku _opis.txt.
 * use at your own risk.
 *
 */

ob_start();

require_once('sawptxt2pdf.php');

if ($_POST['posttresc']) {		// mamy co konwertować
	echo '<!DOCTYPE html><html>';
	pokaz_head();
	echo '<body>';
	pokaz_naglowek();
	$tryb = ($_POST['download'] ? 'D' : 'I');	// F: save to local file; D: download; I: open in browser  (F nam się nie podoba np. ze względów bezpieczeństwa.)
	$tresc = explode(chr(13), $_POST['posttresc']);
	$utwory = parsuj_txt($tresc);
	wypisz_debugowo($utwory);
	$wszystkofile = $_POST['wszystkofile'];
	$wszystkofile = iconv('UTF-8', 'iso-8859-2', $wszystkofile);
	$pdf = inicjuj_pdf();
	for ($i = 0; $i <= count($utwory) - 1; $i++) {
		$utwory[$i]->wypelnij($pdf);
	}
	if ($tryb != 'F') {				// D lub I, czyli download lub open in browser
		echo '$wszystkofile=' . $wszystkofile;
		//exit;
		ob_end_clean();
		zapisz_lub_wyslij_pdf($wszystkofile, $pdf, $tryb);
	} else if ($tryb == 'F') {		// tego nie chcemy obsługiwać
		// zapisz_lub_wyslij_pdf('pliki/'.$wszystkofile,$pdf,$tryb);
		// echo '</body></html>';
		// ob_end_flush();
	};
} else {							// nie mamy czego konwertować

	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html>';
	pokaz_head();
	echo '<body>';
	pokaz_naglowek();
	pokaz_interfejs();
	echo '</body></html>';
};
