<?

// IZVJESTAJ/PROLAZNOST - Pregled prolaznosti i ocjena po godini, odsjeku...

// v3.9.1.0 (2008/04/21) + Kopiran admin_izvjestaj, dodana tabela student_predmet, komponente, izvjestaj "ukupan broj bodova" prebacen na komponente
// v3.9.1.1 (2008/04/24) + Dodano bojenje po odsjeku



function izvjestaj_prolaznost() {


?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<?


// parametri izvjestaja
$akgod = intval($_REQUEST['_lv_column_akademska_godina']);
$studij = intval($_REQUEST['_lv_column_studij']);
$period = intval($_REQUEST['period']);
$semestar = intval($_REQUEST['semestar']);
$godina = intval($_REQUEST['godina']);
$ispit = intval($_REQUEST['ispit']);
$cista_gen = intval($_REQUEST['cista_gen']);
$studenti = intval($_REQUEST['studenti']);
$sortiranje = intval($_REQUEST['sortiranje']);
$oboji = $_REQUEST['oboji'];


// Naslov
$q10 = myquery("select naziv from studij where id=$studij");
$q20 = myquery("select naziv from akademska_godina where id=$akgod");

?>
<h2>Prolaznost</h2>
<p>Studij: <b><?=mysql_result($q10,0,0)?></b><br/>
Akademska godina: <b><?=mysql_result($q20,0,0)?></b><br/>
Godina/semestar studija: <b><?
if ($period==0) {
	if ($semestar==0) $semestar=1;
	print "$semestar. semestar";
} else {
	if ($godina==0) $godina=1;
	print "$godina. godina, ";
}
?></b><br/>
Obuhvaćeni studenti: <b><?
if ($cista_gen==0) print "Redovni, Ponovci, Preneseni predmeti";
elseif ($cista_gen==1) print "Redovni, Ponovci";
elseif ($cista_gen==2) print "Redovni studenti";
elseif ($cista_gen==3) print "Čista generacija";?></b><br/><br/>
Vrsta izvještaja: <b><?
if ($ispit==1) print "I parcijalni ispit";
elseif ($ispit==2) print "II parcijalni ispit";
elseif ($ispit==3) print "Ukupni bodovi";
elseif ($ispit==4) print "Konačna ocjena";
?></b><br/>
</p><?



// ($q30) Spisak predmeta na studij-semestru
if ($period==0) {
	$semestar_upit = "pk.semestar=$semestar";
	$sem_stud_upit = "semestar=$semestar";
} else {
	$semestar_upit = "(pk.semestar=".($godina*2-1)." or pk.semestar=".($godina*2).")";
	$sem_stud_upit = "semestar=".($godina*2-1); // blazi kriterij za studente koji slusaju
}
$q30 = myquery("select pk.id, p.naziv, pk.obavezan from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit order by pk.obavezan desc, p.naziv");

// Dodatak upitu za studente
$upit_studenti="";
if ($cista_gen>=1) {
	// Student trenutno upisan na dati studij/semestar
	$upit_studenti="and ss.studij=$studij and ss.$sem_stud_upit and ss.akademska_godina=$akgod";
}
if ($cista_gen==2) {
	// Student nije nikada prije slusao dati studij/semestar
	// FIXME: pretpostavka je da IDovi akademskih godina idu redom
	$upit_studenti .= " and (select count(*) from student_studij as ss2 where ss2.student=io.student and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0";
}
if ($cista_gen==3) {
	// Student nije nikada ponavljao godinu (nema zapisa o upisu u studij prije datog broja godina)
	// FIXME: pretpostavka je da IDovi akademskih godina idu redom
	$upisao_godine = $akgod;
	if ($period==0) {
		$upisao_godine -= intval(($semestar+1)/2);
	} else {
		$upisao_godine -= $godina;
	}

	$upit_studenti .= " and (select count(*) from student_studij as ss2 where ss2.student=io.student and ss2.akademska_godina<=$upisao_godine)=0";
}


// PODIZVJESTAJ 1
// 1 = I parc., 2 = II parc., 4 = Konacna ocjena
if ($ispit == 1 || $ispit == 2 || $ispit==3 || $ispit == 4) {
	global $polozio;
	$polozio = array(); // ne znam kako bez global :(
	global $suma_bodova;
	$suma_bodova = array();

	// Zaglavlja tabela, ovisno o tome da li su navedeni pojedinacni studenti ili ne
	print "<p>Pregled po studentima.";
	if ($sortiranje==1 && $ispit==4) 
		print " Spisak je sortiran po broju položenih predmeta i ocjenama.</p>\n";
	else if ($sortiranje==1) 
		print " Spisak je sortiran po broju položenih ispita i bodovima.</p>\n";
	else print " Spisak je sortiran po prezimenu.</p>\n";

	if ($oboji=="odsjek") {
		?>
		<table width="100%" border="0" cellpadding="4" cellspacing="4"><tr>
			<td align="left">
				<table border="1" bgcolor="#FF9999" width="100"><tr><td>&nbsp;</td></tr></table>
				Računarstvo i informatika
			</td>
			<td align="left">
				<table border="1" bgcolor="#99FF99" width="100"><tr><td>&nbsp;</td></tr></table>
				Automatika i elektronika
			</td>
			<td align="left">
				<table border="1" bgcolor="#9999FF" width="100"><tr><td>&nbsp;</td></tr></table>
				Elektroenergetika
			</td>
			<td align="left">
				<table border="1" bgcolor="#FF99FF" width="100"><tr><td>&nbsp;</td></tr></table>
				Telekomunikacije
			</td>
		</tr></table>
		<?
	}


	if ($studenti==0 && $ispit==4) { // $studenti = prikaz individualnih studenata
		?><table border="1" cellspacing="0" cellpadding="2">
			<tr><td><b>Predmet</b></td>
			<td><b>Upisalo</b></td>
			<td><b>Položilo</b></td>
			<td><b>%</b></td>
		</tr><?
	} else if ($studenti==0) {
		?><table border="1" cellspacing="0" cellpadding="2">
			<tr><td><b>Predmet</b></td>
			<td><b>Izašlo</b></td>
			<td><b>Položilo</b></td>
			<td><b>%</b></td>
		</tr><?
	} else {
		?>
		<table  border="1" cellspacing="0" cellpadding="2">
		<tr bgcolor="#CCCCCC">
			<td><b>R. br.</b></td>
			<td><b>Student</b></td>
			<td><b>Broj indexa</b></td>
		<?
		while ($r30 = mysql_fetch_row($q30)) {
			$kursevi[$r30[0]] = $r30[1];
			$naziv = $r30[1];
			if ($r30[2]==0) $naziv .= " *";
			print "<td><b>$naziv</b></td>\n";
		}
		print "<td><b>UKUPNO:</b></td></tr>\n";
	}


	// ($q40) Upit za spisak studenata

	if ($cista_gen==0) {
		// Redovni studenti + ponovci + preneseni studenti
		// (svi upisani na predmete sa studija/semestra)

		$q40 = myquery("select distinct sp.student from student_predmet as sp, ponudakursa as pk where sp.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit");
		$uk_studenata=mysql_num_rows($q40);

		// Statisticki podaci o generaciji

		// Redovni studenti
		$q50 = myquery("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");
		$redovnih = mysql_result($q50,0,0);

		// Posto su neki ponovci polozili sve iz ovog semestra, moramo vidjeti
		// koji ponovci slusaju ove predmete
		$q60 = myquery("select count(distinct sp.student) from student_studij as ss, student_predmet as sp, ponudakursa as pk where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and ss.student=sp.student and sp.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit");

		$ukupno_na_godini = mysql_result($q60,0,0);
		$ponovaca = $ukupno_na_godini - $redovnih;
		$prenesenih = $uk_studenata - $redovnih - $ponovaca;

		$ispis_br_studenata = "Predmete slušalo: <b>$redovnih</b> redovnih studenata + <b>$ponovaca</b> ponovaca + <b>$prenesenih</b> prenesenih predmeta";

		// Ova statistika se izvrsava presporo
		
		/*
		$q604a = myquery("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit");
		$q604 = myquery("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");
		$q604b = myquery("select count(*) from student_labgrupa as sl, labgrupa as l, ponudakursa as pk where sl.labgrupa=l.id and l.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit and (select count(*) from student_studij as ss where ss.student=sl.student and ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit)=0");

		$redovnih = mysql_result($q604,0,0);
		$ponovaca = mysql_result($q604a,0,0) - $redovnih;
		$prenesenih = mysql_result($q604b,0,0);
		$ispis_br_studenata = "Predmete slušalo: <b>$redovnih</b> redovnih studenata + <b>$ponovaca</b> ponovaca + <b>$prenesenih</b> prenesenih predmeta";
		*/

	} else if ($cista_gen==1) {
		// Redovni studenti i ponovci

		$q40 = myquery("select ss.student from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit");
		$q50 = myquery("select count(*) from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");

		$uk_studenata = mysql_num_rows($q40);
		$redovnih = mysql_result($q50,0,0);
		$ponovaca = $uk_studenata-$redovnih;
		$ispis_br_studenata = "Semestar upisalo: <b>$redovnih</b> redovnih studenata + <b>$ponovaca</b> ponovaca";

	} else if ($cista_gen==2) {
		// Samo redovni, bez ponovaca (nisu nikada slusali istu ak. godinu)

		$q40 = myquery("select ss.student from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.studij=$studij and ss2.$sem_stud_upit and ss2.akademska_godina<$akgod)=0");

		$uk_studenata = mysql_num_rows($q40);
		$ispis_br_studenata = "Semestar upisalo: <b>$uk_studenata</b> redovnih studenata";

	} else if ($cista_gen==3) {
		// Studenti koji nisu nikada nista ponavljali (upisali fakultet prije semestar/2 godina)
		// FIXME: Pretpostavka je da IDovi akademskih godina idu redom
		$upisao_godine = $akgod;
		if ($period==0) {
			$upisao_godine -= intval(($semestar+1)/2);
		} else {
			$upisao_godine -= $godina;
		}

		$q40 = myquery("select ss.student from student_studij as ss where ss.studij=$studij and ss.akademska_godina=$akgod and ss.$sem_stud_upit and (select count(*) from student_studij as ss2 where ss2.student=ss.student and ss2.akademska_godina<=$upisao_godine)=0");
		$uk_studenata = mysql_num_rows($q40);
		$ispis_br_studenata = "Semestar upisalo: <b>$uk_studenata</b> studenata &quot;čiste generacije&quot;";
	}


	// Cache ispita za I i II parcijalni ispit
	// Gledamo samo redovni rok a.k.a. prvi ispit datog tipa
	$cache_ispiti = $cache_predmeti = array();
	if ($ispit==1 || $ispit==2) {
		$q90 = myquery("select i.id, i.predmet from ispit as i, ponudakursa as pk, predmet as p where i.predmet=pk.id and pk.predmet=p.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit and i.komponenta=$ispit group by i.predmet,i.komponenta");
		while ($r90 = mysql_fetch_row($q90)) {
			array_push($cache_ispiti,$r90[0]);
			array_push($cache_predmeti,$r90[1]);
		}
	}


	// GLAVNA PETLJA
	// Izracunavanje statistickih podataka

	$max_broj_polozenih=0;
	while ($r40 = mysql_fetch_row($q40)) {
		$stud_id = $r40[0];

		// Zaglavlje za poimenicni spisak studenata
		if ($studenti==1) {
			$q100 = myquery("select ime, prezime, brindexa from auth where id=$stud_id");
			$imeprezime[$stud_id] = mysql_result($q100,0,1)." ".mysql_result($q100,0,0);
			$brindexa[$stud_id] = mysql_result($q100,0,2);
			/* Korisna informacija - kako je upotrijebiti?
			$q105 = myquery("select studij from student_studij where student=$stud_id");
			$st_studij[$stud_id] = mysql_result($q105,0,0);*/

			if ($oboji=="odsjek") {
				$q105 = myquery("select studij from student_studij where student=$stud_id and studij!=1 limit 1");
				$student_studij[$stud_id] = mysql_result($q105,0,0);
			}
		}

		// Upit za I i II parcijalni ispit
		if ($ispit==1 || $ispit==2) {
			$broj_polozenih=0;
			foreach ($cache_ispiti as $redni_broj=>$id_ispita) {
				$id_predmeta=$cache_predmeti[$redni_broj];

				$q100 = myquery("select ocjena from ispitocjene where ispit=$id_ispita and student=$stud_id");
				if (mysql_num_rows($q100)>0) {
					$ocjena = mysql_result($q100,0,0);
					$izaslo[$id_predmeta]++;
					if ($ocjena>=10) {
						$polozilo[$id_predmeta]++;
						$broj_polozenih++;
					}
					if ($studenti==1) {
						$ispitocjena[$stud_id][$id_predmeta] = $ocjena;
						$suma_bodova[$stud_id] += $ocjena;
					}
				} else {
					if ($studenti==1) $ispitocjena[$stud_id][$id_predmeta] = "/";
				}
			}
			$ispita_polozenih[$broj_polozenih]++;
			if ($broj_polozenih>$max_broj_polozenih) 
				$max_broj_polozenih = $broj_polozenih;
			if ($studenti==1) 
				$polozio[$stud_id] = $broj_polozenih;


		// Po ukupnom broju bodova
		} else if ($ispit==3) {
//			$stud_predmeti_ar=array();
			$broj_polozenih=0;
			$q200 = myquery("select kb.predmet, kb.bodovi from komponentebodovi as kb, ponudakursa as pk where kb.student=$stud_id and kb.predmet=pk.id and pk.studij=$studij and pk.akademska_godina=$akgod and $semestar_upit");
			while ($r200 = mysql_fetch_row($q200)) {
				$suma_bodova[$stud_id] += $r200[1];
				$ispitocjena[$stud_id][$r200[0]] += $r200[1];
//				array_push($stud_predmeti_ar,$r200[0]);
			}
			foreach ($ispitocjena[$stud_id] as $id_predmeta => $m_bodova) {
				if ($m_bodova>=40) {
					$polozilo[$id_predmeta]++;
					$broj_polozenih++;
				}
				$izaslo[$id_predmeta]++;
			}
			$ispita_polozenih[$broj_polozenih]++;
			if ($broj_polozenih>$max_broj_polozenih) 
				$max_broj_polozenih = $broj_polozenih;
			if ($studenti==1) 
				$polozio[$stud_id] = $broj_polozenih;

		// Konacna ocjena
		} else if ($ispit==4) {
			$q110 = myquery("select ko.predmet,ko.ocjena from konacna_ocjena as ko, ponudakursa as pk where ko.student=$stud_id and ko.predmet=pk.id and pk.studij=$studij and pk.akademska_godina=$akgod and $semestar_upit");
			$broj_polozenih=0;
			while ($r110 = mysql_fetch_row($q110)) {
				if ($r110[1] >= 6 ) {
					$polozilo[$r110[0]]++;
					$broj_polozenih++;
				}
				if ($studenti==1) {
					$ispitocjena[$stud_id][$r110[0]] = $r110[1];
					$suma_bodova[$stud_id] += $r110[1];
				}
			}
			$ispita_polozenih[$broj_polozenih]++;
			if ($broj_polozenih>$max_broj_polozenih) $max_broj_polozenih=$broj_polozenih;
			if ($studenti==1) $polozio[$stud_id] = $broj_polozenih;

			// Niz $izaslo punimo brojem studenata upisanih na predmet
			$q120 = myquery("select l.predmet from student_labgrupa as sl, labgrupa as l, ponudakursa as pk where sl.student=$stud_id and sl.labgrupa=l.id and l.predmet=pk.id and pk.akademska_godina=$akgod and pk.studij=$studij and $semestar_upit");
			while ($r120 = mysql_fetch_row($q120))
				$izaslo[$r120[0]]++;
		}
	}

	// Ispis podataka
	if ($studenti==0) {
		// Ispisujemo samo sumarne podatke
		while ($r30 = mysql_fetch_row($q30)) {
			$naziv = $r30[1];
			if ($r30[2]==0) $naziv .= " *";
			?><tr><td><?=$naziv?></td>
			<td><?=intval($izaslo[$r30[0]])?></td>
			<td><?=intval($polozilo[$r30[0]])?></td>
			<td><?=procenat($polozilo[$r30[0]],$izaslo[$r30[0]])?></td></tr><?
		}

	} else {
		// Sortiranje niza studenata
		if ($sortiranje==0) {
			// po prezimenu i imenu
			uasort($imeprezime,"bssort"); // bssort - bosanski jezik
		} else {
			// po broju bodova i polozenih ispita
			function tablica_sort($a, $b) {
				global $polozio,$suma_bodova;
				if ($polozio[$a]>$polozio[$b]) return -1;
				else if ($polozio[$a]<$polozio[$b]) return 1;
				else if ($suma_bodova[$a]>$suma_bodova[$b]) return -1;
				return 1;
			}
			uksort($imeprezime,"tablica_sort");
		}
		
		// Ispis redova za studente
		$rbr=0;
		$oldsuma=-1; $oldpolozio=-1;
		foreach ($imeprezime as $stud_id => $imepr) {
			$rbr++;
			// Kod sortiranja po broju bodova, 
			// redni broj se ne uvecava ako je broj bodova jednak
			if ($sortiranje==0 || $oldsuma != $suma_bodova[$stud_id] || $oldpolozio != $polozio[$stud_id]) {
				$rrbr=$rbr;
			}

			$bgcolor="#FFFFFF";
			if ($oboji=="odsjek") {
				if ($student_studij[$stud_id]==2) $bgcolor="#FFCCCC";
				else if ($student_studij[$stud_id]==3) $bgcolor="#CCFFCC";
				else if ($student_studij[$stud_id]==4) $bgcolor="#CCCCFF";
				else if ($student_studij[$stud_id]==5) $bgcolor="#FFCCFF";
			}

			?><tr bgcolor="<?=$bgcolor?>">
				<td><?=$rrbr?></td>
				<td><?=$imepr?></td>
				<td><?=$brindexa[$stud_id]?></td><?
			foreach ($kursevi as $kurs_id => $kurs) {
				if ($ispitocjena[$stud_id][$kurs_id]===NULL) $ispitocjena[$stud_id][$kurs_id]="/";
				print "<td>".$ispitocjena[$stud_id][$kurs_id]."</td>\n";
			}
			print "<td>".$polozio[$stud_id]."</td></tr>\n";
			$oldsuma = $suma_bodova[$stud_id];
			$oldpolozio = $polozio[$stud_id];
		}

		// Sumarni podaci na kraju tabele
		print '<tr><td colspan="3" align="right">';
		if ($ispit==1 || $ispit==2) 
			print 'PRISTUPILO ISPITU:&nbsp; </td>';
		else
			print 'UPISALO PREDMET:&nbsp; </td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			print "<td>".intval($izaslo[$kurs_id])."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";

		print '<tr><td colspan="3" align="right">POLOŽILO:&nbsp; </td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			print "<td>".intval($polozilo[$kurs_id])."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";

		print '<tr><td colspan="3" align="right">PROCENAT:&nbsp; </td>';
		foreach ($kursevi as $kurs_id => $kurs) {
			print "<td>".procenat($polozilo[$kurs_id],$izaslo[$kurs_id])."</td>\n";
		}
		print "<td>&nbsp;</td></tr>\n";
	}

	// Statistika broja studenata
	print "</table>\n* Predmet je izborni\n\n<br/><br/>$ispis_br_studenata<br/><br/>\n";
	
	// Suma po broju polozenih ispita/predmeta
	if ($ispit==4) $tekst="predmeta"; else $tekst="ispita";
	for ($i=$max_broj_polozenih; $i>=0; $i--) {
		print "Položilo $i $tekst: <b>".$ispita_polozenih[$i]."</b> (".procenat($ispita_polozenih[$i],$uk_studenata).")<br/>\n";
	}
}

// PODIZVJESTAJ 2: Ukupan zbir bodova, bez pojedinacnih studenata
else if ($studenti==0 && $ispit == 3) {
	// Ovo će biti komplikovano....
}



// PODIZVJESTAJ 5: Ukupan broj bodova, pojedinacni studenti
// ****   NEOPTIMIZOVANO
else if ($studenti==1 && $ispit==3) {


	// tabela kurseva i studenata
	$kursevi = array();
	$imeprezime = array();
	$brind = array();
	$sirina = 200;
	while ($r30 = mysql_fetch_row($q30)) {
		$kursevi[$r30[0]] = $r30[1];

		$q601 = myquery("select s.id, s.ime, s.prezime, s.brindexa from student as s, student_labgrupa as sl, labgrupa as l where sl.student=s.id and sl.labgrupa=l.id and l.predmet=$r30[0]");
		while ($r601 = mysql_fetch_row($q601)) {
			$imeprezime[$r601[0]] = "$r601[2] $r601[1]";
			$brind[$r601[0]] = $r601[3];
		}
		$sirina += 200;
	}

	uasort($imeprezime,"bssort"); // bssort - bosanski jezik

	// array zadaća - optimizacija
	$kzadace = array();
	foreach ($kursevi as $kurs_id => $kurs) {
		$q600a = myquery("select id, zadataka from zadaca where predmet=$kurs_id");
		$tmpzadaca = array();
		while ($r600a = mysql_fetch_row($q600a)) {
			$tmpzadaca[$r600a[0]] = $r600a[1];
		}
		$kzadace[$kurs_id] = $tmpzadaca;
	}

	?>
	<table width="<?=$sirina?>" border="1" cellspacing="0" cellpadding="2">
	<tr>
		<td rowspan="2" valign="center">R. br.</td>
		<td rowspan="2" valign="center">Broj indeksa</td>
		<td rowspan="2" valign="center">Prezime i ime</td>
	<?
	foreach ($kursevi as $kurs) {
		print '<td colspan="6" align="center">'.$kurs."</td>\n";
	}
	?>
		<td rowspan="2" valign="center" align="center">UKUPNO</td>
	</tr>
	<tr>
	<?
	for ($i=0; $i<count($kursevi); $i++) {
		?>
		<td align="center">I</td>
		<td align="center">II</td>
		<td align="center">Int</td>
		<td align="center">P</td>
		<td align="center">Z</td>
		<td align="center">Ocjena</td>
		<?
	}
	print "</tr>\n";
	$rbr=1;

	// Slušalo / položilo predmet
	$slusalo = array();
	$polozilo = array();

	foreach ($imeprezime as $stud_id => $stud_imepr) {
		?>
		<tr>
			<td><?=$rbr++?></td>
			<td><?=$brind[$stud_id]?></td>
			<td><?=$stud_imepr?></td>
		<?
		$polozio = 0;
		foreach ($kursevi as $kurs_id => $kurs) {
			$slusalo[$kurs_id]++;
			$q602 = myquery("select io.ocjena,i.komponenta from ispit as i, ispitocjene as io where io.ispit=i.id and io.student=$stud_id and i.predmet=$kurs_id");
			$ispit = array();
			$ispit[1] = $ispit[2] = $ispit[3] = "/";
			while ($r602 = mysql_fetch_row($q602)) {
				if ($r602[0] > $ispit[$r602[1]] || $ispit[$r602[1]] == "/") 
					$ispit[$r602[1]] = $r602[0];
			}
			for ($i=1; $i<4; $i++) {
				if ($ispit[$i] >= 0)
					print "<td>$ispit[$i]</td>\n";
				else
					print "<td>&nbsp;</td>\n";
			}

			$q603 = myquery("select count(*) from prisustvo as p,cas as c, labgrupa as l where p.student=$stud_id and p.cas=c.id and c.labgrupa=l.id and l.predmet=$kurs_id and p.prisutan=0");
			if (mysql_result($q603,0,0)<=3) {
				print "<td>10</td>\n";
				$ukupno += 10;
			} else
				print "<td>0</td>\n";

			$zadaca = 0;
			foreach ($kzadace[$kurs_id] as $zid => $zadataka) {
				for ($i=1; $i<=$zadataka; $i++) {
					$q605 = myquery("select status,bodova from zadatak where zadaca=$zid and redni_broj=$i and student=$stud_id order by id desc limit 1");
					if ($r605 = mysql_fetch_row($q605))
						if ($r605[0] == 5)
							$zadaca += $r605[1];
//					$zadaca .= $i." ";
				}
			}
			print "<td>$zadaca</td>\n";

			$q606 = myquery("select ocjena from konacna_ocjena where student=$stud_id and predmet=$kurs_id");
			if (mysql_num_rows($q606)>0) {
				$ocj = mysql_result($q606,0,0);
				print "<td>$ocj</td>\n";
				if ($ocj >= 6) $polozio++;
				$polozilo[$kurs_id]++;
			} else
				print "<td>&nbsp;</td>\n";
		}
		print "<td>$polozio</td></tr>\n";
		$i++;
	}
	print '<tr><td colspan="3" align="right">SLUŠALO</td>';
	foreach ($kursevi as $kurs_id => $kurs) {
		print '<td colspan="5">'.$slusalo[$kurs_id]."</td>\n";
	}
	print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">POLOŽILO</td>';
	foreach ($kursevi as $kurs_id => $kurs) {
		if (intval($polozilo[$kurs_id])==0) $polozilo[$kurs_id]="0";
		print '<td colspan="5">'.$polozilo[$kurs_id]."</td>\n";
	}
	print '<td>&nbsp;</td></tr><tr><td colspan="3" align="right">PROCENAT</td>';
	foreach ($kursevi as $kurs_id => $kurs) {
		$proc = intval(($polozilo[$kurs_id]/$slusalo[$kurs_id])*100)/100;
		print '<td colspan="5">'.$proc."%</td>\n";
	}
	print '<td>&nbsp;</td></tr></table>';
}

}