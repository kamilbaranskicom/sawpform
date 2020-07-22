<?php

function pokaz_head() {
	echo '<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="css/sawpform.css">
	<script src="js/sawpform_dragndrop.js" defer></script>
</head>';
};

function pokaz_naglowek() {
	global $SAWPTXT2PDFwersja, $SAWPTXT2PDFdata;
	echo '
		<h1>SAWPFORM - beta ' . $SAWPTXT2PDFwersja . '</h1>
		<div class="wersja">[' . $SAWPTXT2PDFdata . '] &copy; <a href="http://kamilbaranski.com/">kamilbaranski</a></div>
		<a name="top"></a>';
};

function pokaz_interfejs() {
	echo '
		<form method="post" id="formularz">
		<textarea id="posttresc" name="posttresc">' .
		file_get_contents('_opis.txt') .
		'</textarea><br />
		<div id="stopka">
			<input name="wszystkofile" id="wszystkofile" value="sawp_.pdf" /><br />
			<div id="status" name"status"><span style="color:darkred">(Drag&drop chyba nie da rady.)</span></div>
			<label><input name="download" id="download" type="checkbox" value=false> Ściągnąć? <span style="color:#c0c0c0;">(inaczej spróbuje otworzyć PDF w przeglądarce)</span>.</label><br />
			<label><input name="generujodrazu" id="generujodrazu" type="checkbox" value=false> Generuj od razu po upuszczeniu?</label><br />
			<input type="submit" value="Generuj" /></form>
		</div>';
};

function implode2($co, $ile) {	// łączy nową linią, chyba że więcej niż $ile - wtedy łączy przecinkiem.
	if (count($co) > $ile) {
		return implode(', ', $co);
	} else {
		return implode("\n", $co);
	};
};

class piosenka {		// p jak piosenka
	var $tytul;
	var $artysta = array();
	var $artystamode;
	var $kompozytor;
	var $autortekstu;
	var $czastrwania;
	var $datanagrania;
	var $rodzajutworu;
	var $rodzajwykonania;
	var $nazwakoncertu;
	var $fonogram;
	var $video;
	var $producent;
	var $nrkat;
	var $wydany;
	var $lead = array();
	var $dyrygent = array();
	var $solowki = array();
	var $akompaniatorzy = array();
	var $chorek = array();
	function dodajlinie($ludzie, $funkcje) {
		$ludzie = explode(', ', $ludzie);
		foreach ($ludzie as $czlowiek) {
			if ($czlowiek == 'kb') {
				$czlowiek = 'Kamil Barański';
			};
			if ($czlowiek == 'kb!') {
				$czlowiek = 'Kamil Barański!';
			};
			if ($czlowiek[strlen($czlowiek) - 1] == '!') {
				$czlowiek = substr($czlowiek, 0, -1) . ' x5';
			};

			$wielefunkcji = explode(', ', $funkcje);
			$md = array_search('md', $wielefunkcji);
			if ($md !== false) {
				unset($wielefunkcji[$md]);
				array_push($this->dyrygent, $czlowiek);
			};

			$solo = array_search('solo', $wielefunkcji);
			if ($solo !== false) {
				unset($wielefunkcji[$solo]);
				array_push($this->solowki, $czlowiek);
			};

			$leadvoc = array_search('leadvoc', $wielefunkcji);
			if ($leadvoc !== false) {
				unset($wielefunkcji[$leadvoc]);
				array_push($this->lead, $czlowiek);
				if (($this->artystamode == 'leadvoc') or ($this->artystamode == 'lead')) {
					array_push($this->artysta, $czlowiek);
				};
			};

			$leadinstr = array_search('leadinstr', $wielefunkcji);
			if ($leadinstr !== false) {
				unset($wielefunkcji[$leadinstr]);
				array_push($this->lead, $czlowiek);
				if (($this->artystamode == 'leadinstr')  or ($this->artystamode == 'lead')) {
					array_push($this->artysta, $czlowiek);
				};
			};
			$bgvoc = array_search('bgvoc', $wielefunkcji);
			if ($bgvoc !== false) {
				unset($wielefunkcji[$bgvoc]);
				array_push($this->chorek, $czlowiek);
			};

			$ilosc = count($wielefunkcji);
			if ($ilosc > 1) {
				$czlowiek .= ' x' . $ilosc;
			};
			if ($ilosc > 0) {	// bo po unsetach może być 0.
				array_push($this->akompaniatorzy, $czlowiek);
			};
		};
	}
	function wypisz() {
		echo ' <small>';
		echo '(' . $this->nazwakoncertu . ': ' . $this->datanagrania . ')';
		echo '<br />';
		echo 'LEAD: ' . implode(', ', $this->lead);
		echo '<br />';
		echo 'SOLO: ' . implode(', ', $this->solowki);
		echo '<br />';
		echo 'BAND: ' . implode(', ', $this->akompaniatorzy);
		echo '<br />';
		echo 'BGVc: ' . implode(', ', $this->chorek);
		echo '<br />';
		echo 'MD: ' . implode(', ', $this->dyrygent);
		echo '<br />';
		echo '</small>';
	}
	function wypelnij($pdf) {
		wypelnij_pdf(
			$this->tytul,
			implode(', ', $this->artysta),
			$this->kompozytor,
			$this->autortekstu,
			$this->czastrwania,
			$this->datanagrania,
			$this->rodzajutworu,
			$this->rodzajwykonania,
			$this->nazwakoncertu,
			$this->fonogram,
			$this->video,
			$this->producent,
			$this->nrkat,
			$this->wydany,
			implode2($this->lead, 4),
			implode2($this->dyrygent, 2),
			implode2($this->solowki, 4),
			implode2($this->akompaniatorzy, 14),
			implode2($this->chorek, 6),
			$pdf
		);
	}
};

function inicjuj_pdf() {
	global $czcionka, $czcionkafile;
	define('FPDF_FONTPATH', 'font/');

	require_once('vendor/autoload.php');	// fpdi, fpdf

	$pdf = new \setasign\Fpdi\Fpdi();
	$pdf->SetAutoPageBreak(0);

	$czcionka = 'TimesNewRomanPSMT';
	$czcionkafile = 'timesnewromanpsmt.php';

	$pdf->AddFont($czcionka, '', $czcionkafile);

	return $pdf;
};

function zapisz_lub_wyslij_pdf($filename, $pdf, $tryb = 'F') {
	$pdf->Output($filename, $tryb);		// F: save to local file; D: download; I: open in browser

};

/* wypelnijpdf(
	'newpdf.pdf',
	"I'll Make Love To You + End Of The Road",
	'leadvoc',
	'kompozytor',
	'autortekstu',
	'cz:as:tr',
	'da/ta/nagr',
	'P',
	'Z',
	'Fajfy z jazzem',
	'nazwa fonogramu',
	'video',
	'producent',
	'CD0003',
	'T'
	'Renata Danel, Kasia Moś, Kuba Molęda, Kaja Karaplios, Magda Kempko, Alicja Kalinowska, Natalia Kała, Maciej Starnawski, Ania Sokołowska, Michał Kaczmarek, Gabi Rudawska, Marcelina Stoszek, Fabiola Kopczyńska, Adam Rymarz, Kinga Tomaszewska, Rafał Motycki',
	'Kamil Barański',
	'brak',
	implode("\n",explode(', ','Kamil Barański x10, Adam Drzewiecki, Andrzej Gondek, Paweł Kasprzyk, Bartek Rojek')),
	'Renata Danel, Kuba Molęda, Maciej Mielczarek, Alicja Kalinowska, Kasia Moś, Kaja Karaplios, Adam Rymarz, Magda Kempko, Natalia Kała, Fabiola Kopczyńska, Bogna Kicińska, Maciej Starnawski, Agnieszka Błońska, Ania Sokołowska, Kinga Tomaszewska, Gabi Rudawska, Rafał Motycki, Marcelina Stoszek, Beata Dobosz, Gosia Czaplińska, Michał Kaczmarek'
	);
*/

function wypelnij_pdf($tytul, $artysta, $kompozytor, $autortekstu, $czastrwania, $datanagrania, $rodzajutworu, $rodzajwykonania, $nazwakoncertu, $fonogram, $video, $producent, $nrkat, $wydany, $lead, $md, $solo, $band, $bgvoc, $pdf) {
	global $czcionka, $czcionkafile;

	// add a page
	$pdf->AddPage('P', 'A4');
	// set the sourcefile
	$iloscstron = $pdf->setSourceFile('formularz_deklaracji.pdf');
	// import page 1
	$tplIdx = $pdf->importPage(1);
	// use the imported page and place it at point 10,10 with a width of 100 mm
	$pdf->useTemplate($tplIdx);

	// now write some text above the imported page
	$pdf->SetFont($czcionka, '', 16);
	$pdf->SetTextColor(0, 0, 0);

	$pdf->SetXY(45, 40);
	//$pdf->MultiCell(153,8,$tytul.'ĄĆĘŁŃÓŚŻŹąćęłńóśżź',0,C,false);
	$pdf->MultiCell(153, 8, $tytul, 0, C, false);

	$pdf->SetFont($czcionka, '', 12);
	$pdf->SetXY(45, 57);
	$pdf->MultiCell(103, 10, $kompozytor, 0, C, false);

	$pdf->SetXY(45, 69);
	$pdf->MultiCell(103, 9, $autortekstu, 0, C, false);

	$pdf->SetXY(172, 57);
	$pdf->MultiCell(28, 10, $czastrwania, 0, C, false);

	$pdf->SetXY(167, 69);
	$pdf->MultiCell(32, 9, $datanagrania, 0, C, false);

	if (strpos($rodzajutworu, 'P') !== false) {					// piosenka
		$pdf->SetXY(105, 81);
		$pdf->MultiCell(3, 2, 'X', 0, C, false);
	};
	if (strpos($rodzajutworu, 'I') !== false) {					// utwór instrumentalny
		$pdf->SetXY(146, 81);
		$pdf->MultiCell(3, 2, 'X', 0, C, false);
	};
	if (strpos($rodzajutworu, 'S') !== false) {					// sygnał/jingiel
		$pdf->SetXY(162, 81);
		$pdf->MultiCell(3, 2, 'X', 0, C, false);
	};
	if (strpos($rodzajutworu, 'M') !== false) {					// muzyka ilustracyjna
		$pdf->SetXY(197, 81);
		$pdf->MultiCell(3, 2, 'X', 0, C, false);
	};

	if (strpos($rodzajwykonania, 'A') !== false) { 				// nagranie archiwalne
		$pdf->SetXY(48, 94.5);
		$pdf->MultiCell(3, 2, 'X', 0, C, false);
	};
	if (strpos($rodzajwykonania, 'Z') !== false) {					// rejestracja na żywo
		$pdf->SetXY(86, 94.5);
		$pdf->MultiCell(3, 2, 'X', 0, C, false);
	};

	$pdf->SetXY(13, 100);
	$pdf->MultiCell(188, 5, $nazwakoncertu, 0, C, false);			// nazwa koncertu / programu tv

	$pdf->SetXY(48, 108);
	$pdf->MultiCell(56, 2, $fonogram, 0, C, false);					// fonogram

	$pdf->SetXY(144, 108);
	$pdf->MultiCell(56, 2, $video, 0, C, false);						// video

	$pdf->SetXY(48, 115);
	$pdf->MultiCell(56, 2, $producent, 0, C, false);					// producent

	$pdf->SetXY(144, 115);
	$pdf->MultiCell(56, 2, $nrkat, 0, C, false);						// nrkat


	if (strlen($wydany)) {
		if ((strpos($wydany, 'T') !== false) or (strpos($wydany, 'Y') !== false)) {
			$pdf->SetXY(151, 121.5);
		} else {
			$pdf->SetXY(187, 121.5);
		};
		$pdf->MultiCell(3, 2, 'X', 0, C, false);
	};


	$pdf->SetFont($czcionka, '', 10);

	$pdf->SetXY(13, 137);
	$pdf->MultiCell(188, 4, $artysta, 0, C, false);					// nazwa solisty / zespołu

	$pdf->SetXY(48, 157);
	$pdf->MultiCell(113, 4, $lead, 0, L, false);						// solista / soliści
	$pdf->SetXY(48, 178);
	$pdf->MultiCell(113, 4, $md, 0, L, false);						// dyrygent
	$pdf->SetXY(48, 187);
	$pdf->MultiCell(113, 4, $solo, 0, L, false);						// wykonawcy instr.solówek
	$pdf->SetXY(48, 207);
	$pdf->MultiCell(113, 4, $band, 0, L, false);						// muzycy akompaniatorzy
	$pdf->SetXY(48, 267.5);
	$pdf->MultiCell(113, 4, $bgvoc, 0, L, false);					// chórek



	$tplIdx2 = $pdf->importPage(2);
	$pdf->AddPage('P', 'A4');
	$pdf->useTemplate($tplIdx2);

	/* --------------------------------------------------------------------------------------------------------------------------
	$pdf->SetXY(80,11);
	$pdf->MultiCell(118,4,$nazwakoncertu,0,L,false);

	$pdf->SetXY(20,27);
	$pdf->MultiCell(60,4,$tytul,0,L,false);
	
	$pdf->SetXY(79,29);
	$pdf->MultiCell(14,3,$czastrwania,0,C,false);
		
	$pdf->SetXY(93,29);
	$pdf->MultiCell(10,3,$rodzajutworu,0,C,false);
	-------------------------------------------------------------------------------------------------------------------------- */

	$pdf->SetXY(36, 240);
	$pdf->MultiCell(35, 4, date('j.m.Y'), 0, C, false);
};

function parsuj_txt($a) {
	global $ilosc;

	$utwory = array();
	$nrutworu = -1;

	echo '<h2>Dane wejściowe</h2>';
	echo '<div class="linie">';

	$ilosc = count($a);
	$dl = strlen($ilosc . ' ') - 1;

	for ($i = 0; $i <= $ilosc; $i++) {
		// echo $i.': '.$a[$i].'<br />';
		$l = trim($a[$i]);

		$l = iconv('UTF-8', 'windows-1250', $l);		// fpdf nie potrafi utf-8; są rozszerzenia, może kiedyś.

		$it = str_pad($i, $dl, '0', STR_PAD_LEFT);

		if (($l[0] == '#') or (strlen($l) < 2)) {
			// ----------------------------------------------------------------- komentarz i pusta linia
			echo '<span class="kom">K' . $it . ': ' . $a[$i] . '</span><br />';
		} else if ($l[0] == '-') {
			// ----------------------------------------------------------------- osoba
			echo '<span class="o">O' . $it . ': ' . $a[$i] . '</span><br />';
			$l = substr($l, 2);
			list($osoba, $funkcja) = explode(' - ', $l);
			$utwory[$nrutworu]->dodajlinie($osoba, $funkcja);
		} else if ($l[0] == 'D') {
			// ----------------------------------------------------------------- data
			echo '<span class="d">D' . $it . ': ' . $a[$i] . '</span><br />';
			$data = substr($l, 2);
		} else if ($l[0] == 'T') {
			// ----------------------------------------------------------------- typ (rodzaj utworu: P/I/S/M)
			echo '<span class="t">T' . $it . ': ' . $a[$i] . '</span><br />';
			$rodzajutworu = substr($l, 2);
		} else if ($l[0] == 'N') {
			// ----------------------------------------------------------------- nazwakoncertu
			echo '<span class="n">N' . $it . ': ' . $a[$i] . '</span><br />';
			$nazwakoncertu = substr($l, 2);
		} else if ($l[0] == 'F') {
			// ----------------------------------------------------------------- fonogram
			echo '<span class="f">F' . $it . ': ' . $a[$i] . '</span><br />';
			$fonogram = substr($l, 2);
		} else if ($l[0] == 'V') {
			// ----------------------------------------------------------------- video
			echo '<span class="v">V' . $it . ': ' . $a[$i] . '</span><br />';
			$video = substr($l, 2);
		} else if ($l[0] == 'P') {
			// ----------------------------------------------------------------- producent
			echo '<span class="p">P' . $it . ': ' . $a[$i] . '</span><br />';
			$producent = substr($l, 2);
		} else if ($l[0] == 'K') {
			// ----------------------------------------------------------------- nrkat
			echo '<span class="k">K' . $it . ': ' . $a[$i] . '</span><br />';
			$nrkat = substr($l, 2);
		} else if ($l[0] == 'Y') {
			// ----------------------------------------------------------------- wydany
			echo '<span class="y">Y' . $it . ': ' . $a[$i] . '</span><br />';
			$wydany = substr($l, 2);
		} else if ($l[0] == 'A') {
			// ----------------------------------------------------------------- artysta
			echo '<span class="a">A' . $it . ': ' . $a[$i] . '</span><br />';
			$artysta = substr($l, 2);
		} else if ($l[0] == 'W') {
			// ----------------------------------------------------------------- rodzaj wykonania (A/Z)
			echo '<span class="w">W' . $it . ': ' . $a[$i] . '</span><br />';
			$rodzajwykonania = substr($l, 2);
		} else if ($l[0] == '$') {
			// ----------------------------------------------------------------- kompozytor|autor tekstu
			echo '<span class="dolar">$' . $it . ': ' . $a[$i] . '</span><br />';
			list($utwory[$nrutworu]->kompozytor, $utwory[$nrutworu]->autortekstu) = explode('|', substr($l, 2));
		} else {
			echo '<span class="u">U' . $it . ': ' . $a[$i] . '</span><br />';
			$nrutworu++;
			$utwory[$nrutworu] = new piosenka;	// ------------------------------------- nowy utwór

			$tytul = substr(strstr($l, '. '), 2);
			list($tytul, $czastrwania) = explode('|', $tytul);

			$utwory[$nrutworu]->tytul = $tytul;
			$utwory[$nrutworu]->czastrwania = $czastrwania;
			$utwory[$nrutworu]->datanagrania = $data;
			$utwory[$nrutworu]->rodzajutworu = $rodzajutworu;
			$utwory[$nrutworu]->rodzajwykonania = $rodzajwykonania;
			$utwory[$nrutworu]->nazwakoncertu = $nazwakoncertu;
			$utwory[$nrutworu]->fonogram = $fonogram;
			$utwory[$nrutworu]->video = $video;
			$utwory[$nrutworu]->producent = $producent;
			$utwory[$nrutworu]->nrkat = $nrkat;
			$utwory[$nrutworu]->wydany = $wydany;

			if (($artysta == 'lead') or ($artysta == 'leadvoc') or ($artysta == 'leadinstr')) {
				$utwory[$nrutworu]->artystamode = $artysta;
			} else {
				array_push($utwory[$nrutworu]->artysta, $artysta);
			};
		};
	};

	echo '</div>';

	return $utwory;
};		// parsujtxt

function wypisz_debugowo($utwory) {
	echo '<a name="wynik"></a>';
	echo '<h2>Dane wyjściowe (wybór)</h2>';

	for ($i = 0; $i <= count($utwory) - 1; $i++) {
		echo ($i + 1) . ': ' . $utwory[$i]->tytul;
		$utwory[$i]->wypisz();
	};
};
