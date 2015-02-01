<?

// STUDENT/ZADACA - slanje zadace za studente

// v3.9.1.0 (2008/02/21) + Kopiran raniji stud_zadaca
// v3.9.1.1 (2008/03/21) + Popravljeni stari linkovi, $conf_files_path, typo u akcijaslanje(), popravljen logging
// v3.9.1.2 (2008/03/26) + Staza za diff je bila loša
// v3.9.1.3 (2008/03/28) + Navigacija v3.0 kopirana sa predmet.php, fixevi za widescreen
// v3.9.1.4 (2008/04/10) + Navigacija je prikazivala visak zadataka; dodan update komponente nakon slanja
// v3.9.1.5 (2008/04/27) + Zamijenjen obican zadatak i attachment u log zapisu
// v3.9.1.6 (2008/05/16) + Dodan link za odgovor na komentar tutora; dodano polje $komponenta u poziv update_komponente() radi brzeg izvrsenja
// v3.9.1.7 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.8 (2008/10/03) + Slanje zadace prebaceno na genform() radi sigurnosnih aspekata istog (kod slanja attachmenta ne moze?)
// v3.9.1.9 (2008/10/22) + Popravljen bug u slanju zadace - genform() nije ubacivao hidden polje zadatak jer se ono ponekad izracunava kao "zadnji neuradjeni" i slicno
// v3.9.1.9 (2008/10/27) + Isto i sa poljem zadaca
// v3.9.1.10 (2008/11/10) + Popravljen status nove zadace sa 2 (prepisana) na 4 (potrebno pregledati)
// v3.9.1.10 (2009/02/10) + Onemogucen spoofing predmeta i pogresna kombinacija predmet/zadaca; csrf zastita je sprjecavala slanje attachmenta
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/04/01) + Kod slanja zadace kao attachment status je bio postavljen na 1 (potrebna automatska kontrola) cak i ako nije odabran programski jezik
// v4.0.9.1 (2009/04/01) + Tabela zadaca preusmjerena sa ponudakursa na tabelu predmet; pobrisan neki iskomentirani kod
// v4.0.9.2 (2009/04/05) + Zadatak tipa attachment nije prikazivan osim ako je status 1
// v4.0.9.3 (2009/05/01) + Parametri su sada predmet i ag
// v4.0.9.4 (2009/05/15) + Direktorij za zadace je sada predmet-ag umjesto ponudekursa; Nemoj praviti direktorij ako nema potrebe za tim



function student_zadaca() {

global $userid,$conf_files_path;


// Akcije
if ($_REQUEST['akcija'] == "slanje") {
	akcijaslanje();
	return;
}


// Poslani parametri
$zadaca = intval($_REQUEST['zadaca']);
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznat predmet $predmet",3); // nivo 3: greska
	biguglyerror("Nepoznat predmet");
	return;
}

$q15 = myquery("select naziv from akademska_godina where id=$ag");
if (mysql_num_rows($q10)<1) {
	zamgerlog("nepoznata akademska godina $ag",3); // nivo 3: greska
	biguglyerror("Nepoznata akademska godina");
	return;
}

// Da li student slusa predmet?
$q17 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
if (mysql_num_rows($q17)<1) {
	zamgerlog("student ne slusa predmet pp$predmet", 3);
	biguglyerror("Niste upisani na ovaj predmet");
	return;
}
$ponudakursa = mysql_result($q17,0,0);


//  IMA LI AKTIVNIH?
// TODO: provjeriti da li je aktivan modul...

$q10 = myquery("select count(*) from zadaca where predmet=$predmet and akademska_godina=$ag and aktivna=1");
if (mysql_result($q10,0,0) == 0) {
	zamgerlog("nijedna zadaća nije aktivna, predmet pp$predmet", 3);
	niceerror("Nijedna zadaća nije aktivna");
	return;
}



//  ODREĐIVANJE ID ZADAĆE

// Da li neko pokušava da spoofa zadaću?
if ($zadaca!=0) {
	$q20 = myquery("SELECT count(*) FROM zadaca as z, student_predmet as sp, ponudakursa as pk
	WHERE sp.student=$userid and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
	if (mysql_result($q20,0,0)==0) {
		zamgerlog("student nije upisan na predmet (zadaca z$zadaca)",3);
		biguglyerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}
}

// Ili predmet
if ($ponudakursa != 0) {
	$q25 = myquery("select count(*) from student_predmet where student=$userid and predmet=$ponudakursa");
	if (mysql_result($q25,0,0)==0) {
		zamgerlog("student nije upisan na predmet (predmet p$ponudakursa)",3);
		biguglyerror("Niste upisani na ovaj predmet");
		return;
	}
	// Odgovarajuci predmet i zadaca
	if ($zadaca != 0) {
		$q27 = myquery("select count(*) from zadaca where id=$zadaca and predmet=$predmet and akademska_godina=$ag");
		if (mysql_result($q27,0,0)==0) {
			zamgerlog("zadaca i predmet ne odgovaraju (predmet p$ponudakursa, zadaca z$zadaca)",3);
			biguglyerror("Ova zadaća nije iz vašeg predmeta");
			return;
		}
	}
}

// Nije izabrana konkretna zadaca
if ($zadaca==0) {
	// Zadnja zadaca na kojoj je radio/la
	$q30 = myquery("SELECT z.id FROM zadatak as zk, zadaca as z
	WHERE z.id=zk.zadaca and z.aktivna=1 and z.rok>curdate() and z.predmet=$predmet and z.akademska_godina=$ag and zk.student=$userid
	ORDER BY z.id DESC LIMIT 1");

	if (mysql_num_rows($q30)>0)
		$zadaca = mysql_result($q30,0,0);
	else {
		// Nije radio ni na jednoj od aktivnih zadaca$predmet_id
		// Daj najstariju aktivnu zadacu
		$q40 = myquery("select id from zadaca where predmet=$predmet and akademska_godina=$ag and rok>curdate() and aktivna=1 order by id limit 1");

		if (mysql_num_rows($q40)>0)
			$zadaca = mysql_result($q40,0,0);
		else {
			// Ako ni ovdje nema rezultata, znači da je svim 
			// zadaćama istekao rok. Daćemo zadnju zadaću.
			// Da li ima aktivnih provjerili smo u $q10
			$q50 = myquery("select id from zadaca where predmet=$predmet and akademska_godina=$ag and aktivna=1 order by id desc limit 1");
			$zadaca = mysql_result($q50,0,0);
		}
	}
}



// Standardna lokacija zadaca:

$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$userid/";



// Ove vrijednosti će nam trebati kasnije
$q60 = myquery("select naziv,zadataka,UNIX_TIMESTAMP(rok),programskijezik,attachment,dozvoljene_ekstenzije from zadaca where id=$zadaca");
$naziv = mysql_result($q60,0,0);
$brojzad = mysql_result($q60,0,1);
$rok = mysql_result($q60,0,2);
$jezik = mysql_result($q60,0,3);
$attachment = mysql_result($q60,0,4);
$zadaca_dozvoljene_ekstenzije = mysql_result($q60,0,5);



//  ODREĐIVANJE ZADATKA

// Poslani parametar:
$zadatak = intval($_REQUEST['zadatak']);

if ($zadatak==0) { 
	// Prvi neurađeni zadatak u datoj zadaći
	// NOTE: subquery
	$q70 = myquery("select zk.redni_broj from zadatak as zk where zk.student=$userid and zk.zadaca=$zadaca and (select count(*) from zadatak as zk2 where zk2.student=$userid and zk2.zadaca=$zadaca and zk2.redni_broj=zk.redni_broj)=0 order by zk.redni_broj limit 1");
	
	if (mysql_num_rows($q70)>0) 
		$zadatak=mysql_result($q70,0,0);
	// Sve je uradio, daj zadnji
	else 
		$zadatak=$brojzad;
}



// Akcije vezane za autotest

if ($_REQUEST['akcija'] == "test_detalji") {
	$test = intval($_REQUEST['test']);
	$q1000 = myquery("SELECT a.kod, a.global_scope, a.rezultat, a.alt_rezultat, a.fuzzy, ar.nalaz, ar.izlaz_programa, ar.status FROM autotest AS a, autotest_rezultat AS ar WHERE a.id=$test AND autotest=$test AND a.zadaca=$zadaca AND a.zadatak=$zadatak AND ar.student=$userid");
	if (mysql_num_rows($q1000)==0) {
		print "Nije testirano.";
		return;
	}
	
	$r1000 = mysql_fetch_row($q1000);
	
	$kod = str_replace("&", "&amp;", $r1000[0]);
	$kod = str_replace("<", "&lt;", $kod);
	$kod = str_replace(">", "&gt;", $kod);
	
	$global = htmlentities($r1000[1]);

	$nalaz = str_replace("\n", "<br>", $r1000[5]);

	$ulaz =  str_replace("\n", "<br>", $r1000[2]);
	$ulaz =  str_replace("\\n", "<br>", $r1000[2]);
	$ulaz =  str_replace(" ", "&nbsp;", $ulaz);
	$alt_ulaz =  str_replace("\n", "<br>", $r1000[3]);
	$alt_ulaz =  str_replace(" ", "&nbsp;", $alt_ulaz);
	$izlaz =  str_replace("\n", "<br>", $r1000[6]);
	$izlaz =  str_replace(" ", "&nbsp;", $izlaz);
	
	?>
		<h2>Detaljnije informacije o testu</h2>
		<h3>Kod testa:</h3>
		<pre><?=$kod?></pre>
		<?
		if ($r1000[1] != "") {
		?>
		<p>U globalnom opsegu:</p>
		<pre><?=$global?></pre>
		<?
		}
		
		if ($r1000[7] != "no_func") {
			?>
			<p><a href="<?=genuri()?>&akcija=test_sa_kodom">Prikaži kod testa unutar zadaće</a></p>
			<?
		}
		?>
		<hr>
		<h3>Izlaz programa</h3>
		<table border="0" cellspacing="5">
			<tr><td>Očekivan je izlaz:</td>
			<td><span style="background: #fcc"><code><?=$ulaz?></code></span></td></tr>
			<?
			if ($r1000[3] != "") {
				?>
				<tr><td>Alternativni izlaz:</td>
				<td><span style="background: #fcc"><code><?=$alt_ulaz?></code></span></td></tr>
				<?
			}
			?>
			<tr><td>Vaš program je ispisao:</td>
			<td><span style="background: #cfc"><code><?=$izlaz?></code></span></td></tr>
		</table>
		<?
		if ($r1000[4] == 1) {
		  print "<p><i>Fuzzy matching</i></p>\n";
		}
		?>
		<hr>
		<h3>Nalaz testa:</h3>
		<code><?=$nalaz?></code>
		<?
	
	
	if (mysql_num_rows($q1000)>0) {
		$k = mysql_fetch_row($q1000);
		print $k[0];
	}
	else
		print "Nije testirano.";
	return;
}



if ($_REQUEST['akcija'] == "test_sa_kodom") {
	if ($attachment) {
		niceerror("Download zadaće poslane kao attachment sa ugrađenim testnim kodom trenutno nije podržano.");
		return;
	}

	$test_id = intval($_REQUEST['test']);

	// Uzimanje koda
	$q130 = myquery("select naziv, ekstenzija from programskijezik where id=$jezik");
	$programski_jezik = mysql_result($q130,0,0);
	$ekst = mysql_result($q130,0,1);

	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	$kod = "";
	if (file_exists("$conf_files_path/zadace/$predmet-$ag") && file_exists($the_file)) $kod = join("",file($the_file)); 

	// Popravke u kodu koje su potrebne da bi testcase-ovi radili
	$q100 = myquery("select tip, specifikacija, zamijeni from autotest_replace where zadaca=$zadaca and zadatak=$zadatak");
	while ($r100 = mysql_fetch_row($q100)) {
		$spec = $r100[1];
		$zamjena = $r100[2];

		// Tip zamjene: funkcija
		if ($r100[0] == "funkcija" && ($programski_jezik == "C" || $programski_jezik == "C++" || $programski_jezik == "Java")) {

			// Pretvaramo spec u validan regex
			$spec = str_replace(" ", "\\s+", $spec);
			$spec = str_replace("(", "\s*\\(\s*", $spec);
			$spec = str_replace(")", ".*?\\)", $spec);
			$spec = str_replace("TIP,", ".*?,", $spec);
			$spec = str_replace("TIP", ".*?", $spec);
			$spec = str_replace(",", ".*?,\s*", $spec);
			$spec = str_replace("FUNKCIJA", "(\\w+)", $spec);
			$results = preg_match("/$spec/", $kod, $matches);

			if ($results == 0) {
				niceerror("Nije pronađena funkcija sa očekivanim prototipom $r100[1]");
				return;
			}
			
			// Ako se u specifikaciji ne nalazi ključna riječ "FUNKCIJA", ovo je samo assert da funkcija postoji
			if (strstr($r100[1], "FUNKCIJA") && $zamjena != $matches[1]) { 
				$kod = "#define $zamjena $matches[1]\n" . $kod;
			}
		}
	}

	// Uzimamo odabrani test
	$q110 = myquery("select kod, rezultat, alt_rezultat, fuzzy, global_scope, pozicija_globala from autotest where zadaca=$zadaca and zadatak=$zadatak and id=$test_id");
	$r110 = mysql_fetch_row($q110);

	$testni_kod = "";
	$test = $r110[0];
	$global="\n".$r110[4]."\n";
	$pozicija_globala=$r110[5];

	// Sadržaj maina
	if ($programski_jezik == "C") {
		$testni_kod .= "printf(\"====TEST$rbr====\");\n $test\n printf(\"====KRAJ$rbr====\");\n";
	} else if ($programski_jezik == "C++") {
		// Hvatanje izuzetaka
		$testni_kod .= "try {\n std::cout<<\"====TEST$rbr====\";\n $test\n std::cout<<\"====KRAJ$rbr====\";\n } catch (...) {\n cout<<\"====IZUZETAK$rbr====\";\n }\n";
	}

	// Ubacujem u kod zadaće
	// U slučaju C i C++ dodaćemo naš kod na početak main-a i završiti returnom
	if ($programski_jezik == "C" || $programski_jezik == "C++") {
		$testni_kod .= "\nreturn 0;\n";
		$kod = preg_replace("/main\s?\(/", "_main(", $kod);
		if ($pozicija_globala == "prije_maina")
			$kod .= "\n$global\n"."int main() {\n$testni_kod\n}\n";
		else if ($pozicija_globala == "prije_svega")
			$kod = "$global\n\n$kod\n\nint main() {\n$testni_kod\n}\n";
	}

	?>
	<textarea rows="20" cols="80" name="program" wrap="off"><?=$kod?></textarea>
	<?

	return;
}



//  NAVIGACIJA

print "<br/><br/><center><h1>$naziv, Zadatak: $zadatak</h1></center>\n";


// Statusne ikone:
$stat_icon = array("zad_bug", "zad_preg", "zad_copy", "zad_bug", "zad_preg", "zad_ok");
$stat_tekst = array("Bug u programu", "Pregled u toku", "Zadaća prepisana", "Bug u programu", "Pregled u toku", "Zadaća OK");
$stat_autotest = array("ok" => "OK", "wrong" => "Pogrešan rezultat", "error" => "Ne može se kompajlirati", "no_func" => "Ne postoji funkcija", "exec_fail" => "Ne može se izvršiti", "too_long" => "Predugo izvršavanje", "crash" => "Testni program se krahira", "find_fail" => "Nije pronađen rezultat", "oob" => "Pristup ilegalnom pokazivaču", "uninit" => "Nije inicijalizovano", "memleak" => "Curenje memorije", "invalid_free" => "Loša dealokacija", "mismatched_free" => "Pogrešan dealokator");


?>


<!-- zadace -->
<center>
<table cellspacing="0" cellpadding="2" border="0" id="zadace">
	<thead>
		<tr>
<?



?>
	<td>&nbsp;</td>
<?

// Zaglavlje tabele - potreban nam je max. broj zadataka u zadaci

$q20 = myquery("select zadataka from zadaca where predmet=$predmet and akademska_godina=$ag order by zadataka desc limit 1");
$broj_zadataka = mysql_result($q20,0,0);
for ($i=1;$i<=$broj_zadataka;$i++) {
	?><td>Zadatak <?=$i?>.</td><?
}

?>
		<td>Rok za slanje</td>
		</tr>
	</thead>
<tbody>
<?


// Tijelo tabele

// LEGENDA STATUS POLJA:
// 0 - nepoznat status
// 1 - nova zadaća
// 2 - prepisana
// 3 - ne može se kompajlirati
// 4 - prošla test, predstoji kontrola
// 5 - pregledana


/* Ovo se sve moglo kroz SQL rijesiti, ali necu iz razloga:
1. PHP je citljiviji
2. MySQL <4.1 ne podrzava subqueries */


$bodova_sve_zadace=0;

$q21 = myquery("select id, naziv, bodova, zadataka, UNIX_TIMESTAMP(rok) from zadaca where predmet=$predmet and akademska_godina=$ag order by komponenta, id");
while ($r21 = mysql_fetch_row($q21)) {
	$m_zadaca = $r21[0];
	$m_mogucih += $r21[2];
	$m_maxzadataka = $r21[3];
	?><tr>
	<th><?=$r21[1]?></th>
	<?

	for ($m_zadatak=1;$m_zadatak<=$broj_zadataka;$m_zadatak++) {
		// Ako tekuća zadaća nema toliko zadataka, ispisujemo blank polje
		if ($m_zadatak>$m_maxzadataka) {
			?><td>&nbsp;</td><?
			continue;
		}

		// Uzmi samo rjesenje sa zadnjim IDom
		$q22 = myquery("select status,bodova,komentar from zadatak where student=$userid and zadaca=$m_zadaca and redni_broj=$m_zadatak order by id desc limit 1");
		if ($m_zadaca==$zadaca && $m_zadatak==$zadatak)
			$bgcolor = ' bgcolor="#DDDDFF"'; 
		else 	$bgcolor = "";
		if (mysql_num_rows($q22)<1) {
			?><td <?=$bgcolor?>><a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$m_zadaca?>&zadatak=<?=$m_zadatak?>"><img src="images/16x16/zad_novi.png" width="16" height="16" border="0" align="center" title="Novi zadatak" alt="Novi zadatak"></a></td><?
		} else {
			$status = mysql_result($q22,0,0);
			$bodova_zadatak = mysql_result($q22,0,1);
			if (strlen(mysql_result($q22,0,2))>2)
				$imakomentar = "<img src=\"images/16x16/komentar.png\"  width=\"15\" height=\"14\" border=\"0\" title=\"Ima komentar\" alt=\"Ima komentar\" align=\"center\">";
			else
				$imakomentar = "";
			?><td <?=$bgcolor?>><a href="?sta=student/zadaca&predmet=<?=$predmet?>&ag=<?=$ag?>&zadaca=<?=$m_zadaca?>&zadatak=<?=$m_zadatak?>"><img src="images/16x16/<?=$stat_icon[$status]?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status]?>"> <?=$bodova_zadatak?> <?=$imakomentar?></a></td>
	<?
		}
	}
	?>
		<td><?
		if ($r21[4]<time()) print "<font color=\"red\">";
		print date("d. m. Y. H:i:s", $r21[4]);
		if ($r21[4]<time()) print "</font>";
		?></td>
	</tr>
	<?
}



?>
</tbody>
</table>
</center>
<?






//  PORUKE I KOMENTARI


// Upit za izvjestaj skripte i komentar tutora

?>
<br/><br/>
<center>
<table width="600" border="0"><tr><td>
<?

$q110 = myquery("select izvjestaj_skripte, komentar, userid, status, bodova from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
if (mysql_num_rows($q110)>0) {
	$poruka = mysql_result($q110,0,0);
	$komentar = mysql_result($q110,0,1);
	$tutor = mysql_result($q110,0,2);
	$status_zadace = mysql_result($q110,0,3);
	$bodova = mysql_result($q110,0,4);

	// Statusni ekran
	if ($status_zadace == 3) {
		$bgcolor = "#fcc";
		$status_tekst = "<b>Ne može se kompajlirati</b>";
		$status_ikona = "zad_bug";
	}
	else if ($status_zadace == 2) {
		$bgcolor = "#fcc";
		$status_tekst = "<b>Zadaća prepisana</b>";
		$status_ikona = "zad_copy";
	}
	else if ($status_zadace == 1 || $status_zadace == 4) {
		$bgcolor = "#ffc";
		$status_tekst = "<b>Pregled u toku</b>";
		$status_ikona = "zad_preg";
	}
	else if ($status_zadace == 5) {
		$status_tekst = "<b>Zadaća pregledana: $bodova bodova</b>";
		$status_ikona = "zad_ok";
	}

	// Status testova
	if ($status_zadace == 1 || $status_zadace == 4 || $status_zadace == 5) {
		$q111 = myquery("SELECT COUNT(*) FROM autotest AS a, autotest_rezultat AS ar WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak AND a.id=ar.autotest AND ar.student=$userid");
		$ukupno_testova = mysql_result($q111,0,0);
	}
	if ($status_zadace == 4 || $status_zadace == 5) {
		$q112 = myquery("SELECT COUNT(*) FROM autotest AS a, autotest_rezultat AS ar WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak AND a.id=ar.autotest AND ar.student=$userid AND ar.status='ok'");
		$proslo_testova = mysql_result($q112,0,0);
	}

	if ($status_zadace == 1 || $status_zadace == 3) {
		if ($ukupno_testova > 0)
			$status_tekst .= "<br>Ispod su stari rezultati testova za prošlu verziju zadaće";
	}
	else if ($status_zadace == 4 || $status_zadace == 5) {
		if ($ukupno_testova > 0 && $ukupno_testova > $proslo_testova) {
			$bgcolor = "#ffc";
			$status_tekst .= ". <b>".($ukupno_testova-$proslo_testova)." od $ukupno_testova testova nije prošlo</b>";
		}
		else if ($ukupno_testova > 0) {
			$bgcolor = "#cfc";
			$status_tekst .= ". <b>Prošli svi testovi</b>";
		} else if ($status_zadace == 5) {
			$bgcolor = "#cfc";
		} else {
			$bgcolor = "#ffc";
		}
	}

	?>
	<table width="95%" style="border: 1px solid silver; background-color: <?=$bgcolor?>" cellpadding="5">
	<tr><td align="center">
		<p>Status zadaće:<br>
		<img src="images/16x16/<?=$status_ikona?>.png" width="16" height="16" border="0" align="center" title="<?=$stat_tekst[$status]?>" alt="<?=$stat_tekst[$status_zadace]?>"> <?=$status_tekst?></p>
	</td></tr>
	</table>
	<?
	
	// Vrijeme slanja
	$q113 = myquery("SELECT UNIX_TIMESTAMP(vrijeme) FROM zadatak WHERE student=$userid AND userid=$userid AND zadaca=$zadaca AND redni_broj=$zadatak ORDER BY id DESC LIMIT 1");
	
	if (mysql_num_rows($q113)>0) {
		?>
		<p>Zadatak poslan: <?=date("d.m.Y. H:i:s", mysql_result($q113,0,0))?></p>
		<?
	} else {
		?>
		<p>Zadatak nije poslan (tutor upisao/la bodove)</p>
		<?
	}
	
	// Rezultati automatskog testiranja
	$q115 = myquery("SELECT a.id, ar.status, UNIX_TIMESTAMP(ar.vrijeme) FROM autotest AS a, autotest_rezultat AS ar WHERE a.zadaca=$zadaca AND a.zadatak=$zadatak AND a.id=ar.autotest AND ar.student=$userid");
	if (mysql_num_rows($q115)>0) {
		?>
		<p>Rezultati testiranja:</p>
		<table border="1" cellspacing="0" cellpadding="2">
			<thead><tr>
				<th>Test</th>
				<th>Rezultat</th>
				<th>Vrijeme testiranja</th>
				<th>&nbsp;</th>
			</tr></thead>
		<?
	}
	$rbr=1;
	while ($r115 = mysql_fetch_row($q115)) {
		if ($r115[1] == "ok") $ikona = "zad_ok"; else $ikona = "brisanje";
		$fino_vrijeme = date("d. m. y. H:i:s", $r115[2]);
		?>
		<tr>
			<td><?=$rbr++?></td>
			<td><img src="images/16x16/<?=$ikona?>.png" width="8" height="8"> <?=$stat_autotest[$r115[1]]?></td>
			<td><?=$fino_vrijeme?></td>
			<td>
				<a href="<?=genuri()?>&test=<?=$r115[0]?>&akcija=test_detalji">Detalji</a>
			</td>
		</tr>
		<?
	}
	
	if (mysql_num_rows($q115)>0) {
		?>
		</table>
		<?
	}
	
	// Poruke i komentari tutora
	if (preg_match("/\w/",$poruka)) {
		$poruka = str_replace("\n","<br/>\n",$poruka);
		?><p>Poruka kod kompajliranja:<br/><b><?=$poruka?></b></p><?
	}
	if (preg_match("/\w/",$komentar)) {
		$komentar = str_replace("\n","<br/>\n",$komentar);
		// Link za odgovor na komentar
		$link="";
		if ($tutor>0) {
			$q115 = myquery("select a.login,o.ime,o.prezime from auth as a, osoba as o where o.id=$tutor and a.id=o.id");

			$naslov = urlencode("Odgovor na komentar ($naziv, Zadatak $zadatak)");
			$tekst = urlencode("> $komentar");
			$primalac = urlencode(mysql_result($q115,0,0)." (".mysql_result($q115,0,1)." ".mysql_result($q115,0,2).")");

			$link = " (<a href=\"?sta=common/inbox&akcija=compose&naslov=$naslov&tekst=$tekst&primalac=$primalac\">odgovor</a>)";
		}
		?><p>Komentar tutora: <b><?=$komentar?></b><?=$link?><?
	}
}


// Istek roka za slanje zadace

if ($rok <= time()) {
	print "<p><b>Vrijeme za slanje ove zadaće je isteklo.</b></p>";
	// Ovo je onemogućavalo copy&paste u Firefoxu :(
	//$readonly = "DISABLED";
} else {
	$readonly = "";
}




//  FORMA ZA SLANJE


if ($attachment) {
	print "</td></tr></table>\n";

	// Attachment
	$q120 = myquery("select filename,UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid order by id desc limit 1");
	if (mysql_num_rows($q120)>0) {
		$filename = mysql_result($q120,0,0);
		$the_file = "$lokacijazadaca/$zadaca/$filename";
		if ($filename && file_exists("$conf_files_path/zadace/$predmet-$ag") && file_exists($the_file)) {
			// Utvrđujemo stvarno vrijeme slanja
			$q130 = myquery("SELECT UNIX_TIMESTAMP(vrijeme) from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid and userid=$userid order by id desc limit 1");
			if (mysql_num_rows($q130)>0)
				$vrijeme = mysql_result($q130,0,0);
			else
				$vrijeme = mysql_result($q120,0,1);
			$vrijeme = date("d. m. Y. H:i:s",$vrijeme);
			$velicina = nicesize(filesize($the_file));
			$icon = "images/mimetypes/" . getmimeicon($the_file);
			$dllink = "index.php?sta=common/attachment&zadaca=$zadaca&zadatak=$zadatak";
			?>
			<center><table width="75%" border="1" cellpadding="6" cellspacing="0" bgcolor="#CCCCCC"><tr><td>
			<a href="<?=$dllink?>"><img src="<?=$icon?>" border="0"></a>
			</td><td>
			<p>Poslani fajl: <b><a href="<?=$dllink?>"><?=$filename?></a></b><br/>
			Datum slanja: <b><?=$vrijeme?></b><br/>
			Veličina: <b><?=$velicina?></b></p>
			</td></tr></table></center>
			<?
			print "<p>Ako želite promijeniti datoteku iznad, izaberite novu i kliknite na dugme za slanje:</p>";
		}
	} else {
		print "<p>Izaberite datoteku koju želite poslati i kliknite na dugme za slanje.";
		if ($zadaca_dozvoljene_ekstenzije != "")
			print " Dozvoljeni su sljedeći tipovi datoteka: <b>$zadaca_dozvoljene_ekstenzije</b>.";
		print "</p>\n";
	}

	?>

	<form action="index.php" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="sta" value="student/zadaca">
	<input type="hidden" name="akcija" value="slanje">
	<input type="hidden" name="predmet" value="<?=$predmet?>">
	<input type="hidden" name="ag" value="<?=$ag?>">
	<input type="hidden" name="zadaca" value="<?=$zadaca?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<input type="hidden" name="labgrupa" value="<?=$labgrupa?>">
	<input type="file" name="attachment" size="50">
	</center>
	<p>&nbsp;</p>
	<?

} else {

	// Forma
	$q130 = myquery("select ekstenzija from programskijezik where id=$jezik");
	$ekst = mysql_result($q130,0,0);

	if ($status_zadace == 2) {
		?><p>Zadaća je prepisana i ne može se ponovo poslati</p><?
	} else if ($rok > time()) {
 		?><p>Kopirajte vaš zadatak u tekstualno polje ispod:</p>
		</td></tr></table>

		<?
	}


	// Moze li se izbaciti labgrupa ispod?

	?>
	
		</td></tr></table>
	<center>
	<?=genform("POST")?>
	<input type="hidden" name="zadaca" value="<?=$zadaca?>">
	<input type="hidden" name="zadatak" value="<?=$zadatak?>">
	<input type="hidden" name="akcija" value="slanje">
	<input type="hidden" name="labgrupa" value="<?=$labgrupa?>">
	
	<textarea rows="20" cols="80" name="program" <?=$readonly?> wrap="off"><? 
	$the_file = "$lokacijazadaca$zadaca/$zadatak$ekst";
	$tekst_zadace = "";
	if (file_exists("$conf_files_path/zadace/$predmet-$ag") && file_exists($the_file)) $tekst_zadace = join("",file($the_file)); 
	$tekst_zadace = htmlspecialchars($tekst_zadace);
	print $tekst_zadace;
	?></textarea>
	</center>	

	<?
}

?>

<center><input type="submit" value=" Pošalji zadatak! "></center>
</form>
<?


} // function student_zadaca()



function akcijaslanje() {

	global $userid,$conf_files_path;
	require ("lib/manip.php"); // update komponente nakon slanja

	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	$zadaca = intval($_POST['zadaca']); 
	$zadatak = intval($_POST['zadatak']);
	$program = $_POST['program'];
	
	$povratak_url = "?sta=student/zadaca&predmet=$predmet&ag=$ag&zadaca=$zadaca&zadatak=$zadatak";
	$povratak_html = "<a href=\"$povratak_url\">Nastavak</a>";
	$povratak_js = "<script>window.onload = function() { setTimeout('redirekcija()', 3000); }\nfunction redirekcija() { window.location='$povratak_url'; } </script>\n";

	// Da li student slusa predmet?
	$q195 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (mysql_num_rows($q195)<1) {
		biguglyeerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}
	$ponudakursa = mysql_result($q195,0,0);	


	// Standardna lokacija zadaca
	$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$userid/";
	if (!file_exists("$conf_files_path/zadace/$predmet-$ag")) {
		mkdir ("$conf_files_path/zadace/$predmet-$ag",0777, true);
	}


	// Da li neko pokušava da spoofa zadaću?
	$q200 = myquery("SELECT count(*) FROM zadaca as z, student_predmet as sp, ponudakursa as pk
	WHERE sp.student=$userid and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
	if (mysql_result($q200,0,0)==0) {
		biguglyeerror("Ova zadaća nije iz vašeg predmeta");
		return;
	}

	// Ovo je potrebno radi pravljenja diff-a
	if (get_magic_quotes_gpc()) {
		$program = stripslashes($program);
	}

	// Podaci o zadaći
	$q210 = myquery("select programskijezik, UNIX_TIMESTAMP(rok), attachment, naziv, komponenta, dozvoljene_ekstenzije, automatsko_testiranje from zadaca where id=$zadaca");
	$jezik = mysql_result($q210,0,0);
	$rok = mysql_result($q210,0,1);
	$attach = mysql_result($q210,0,2);
	$naziv_zadace = mysql_result($q210,0,3);
	$komponenta = mysql_result($q210,0,4);
	$zadaca_dozvoljene_ekstenzije = mysql_result($q210,0,5);
	$automatsko_testiranje = mysql_result($q210,0,6);

	// Ako je aktivno automatsko testiranje, postavi status na 1 (automatska kontrola), inace na 4 (ceka pregled)
	if ($automatsko_testiranje==1) $prvi_status=1; else $prvi_status=4;

	// Provjera roka
	if ($rok <= time()) {
		niceerror("Vrijeme za slanje zadaće je isteklo!");
		zamgerlog("isteklo vrijeme za slanje zadaće z$zadaca",3); // nivo 3 - greska
		print $povratak_html;
		return; 
	}

	// Prepisane zadaće se ne mogu ponovo slati
	$q240 = myquery("select status from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid order by id desc limit 1");
	if (mysql_num_rows($q240) > 1 && mysql_result($q240,0,0) == 2) { // status = 2 - prepisana zadaća
		niceerror("Zadaća je prepisana i ne može se ponovo poslati.");
		print $povratak_html;
		return; 
	}

	// Pravimo potrebne puteve
	if (!file_exists($lokacijazadaca)) mkdir ($lokacijazadaca,0777);
	if ($zadaca>0 && !file_exists("$lokacijazadaca$zadaca")) 
		mkdir ("$lokacijazadaca$zadaca",0777);

	// Vrsta zadaće: textarea ili attachment
	if ($attach == 0) { // textarea
		if (!check_csrf_token()) {
			niceerror("Forma za slanje zadaće je istekla.");
			print "<p>Kada otvorite prozor za unos zadaće, imate određeno vrijeme (npr. 15 minuta) da pošaljete zadaću, u suprotnom zahtjev neće biti prihvaćen iz sigurnosnih razloga. Preporučujemo da zadaću ne radite direktno u prozoru za slanje zadaće nego u nekom drugom programu (npr. CodeBlocks) iz kojeg kopirate u Zamger.</p>";
			print $povratak_html;
			return;
		}

		// Određivanje ekstenzije iz jezika
		$q220 = myquery("select ekstenzija from programskijezik where id=$jezik");
		$ekst = mysql_result($q220,0,0);

		$filename = "$lokacijazadaca$zadaca/$zadatak$ekst";

		// Temp fajl radi određivanja diff-a 
		$diffing=0;
		if (file_exists($filename)) {
			if (file_exists("$lokacijazadaca$zadaca/difftemp")) 
				unlink ("$lokacijazadaca$zadaca/difftemp");
			rename ($filename, "$lokacijazadaca$zadaca/difftemp"); 
			$diffing=1;
		}

		// Kreiranje datoteke
		if (strlen($program)<=10) {
			niceerror("Pokušali ste poslati praznu zadaću!");
			print "<p>Vjerovatno ste zaboravili kopirati kod u prozor za slanje.</p>";
			zamgerlog("poslao praznu zadacu z$zadaca zadatak $zadatak",3); // nivo 3 - greska
			print $povratak_html;
			return;
		} else if ($zadaca>0 && $zadatak>0 && ($f = fopen($filename,'w'))) {
			fwrite($f,$program);
			fclose($f);

			// Tabela "zadatak" funkcioniše kao log događaja u
			// koji se stvari samo dodaju
			$q230 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=$prvi_status, vrijeme=now(), filename='$zadatak$ekst', userid=$userid");

			// Pravljenje diffa
			if ($diffing==1) {
				$diff = `/usr/bin/diff -u $lokacijazadaca$zadaca/difftemp $filename`;
				$diff = my_escape($diff);
				if (strlen($diff)>1) {
					$q240 = myquery("select id from zadatak where zadaca=$zadaca and redni_broj=$zadatak and student=$userid and status=1 order by id desc limit 1");
					if (mysql_num_rows($q240) > 0) {
						$id = mysql_result($q240,0,0);
						$q250 = myquery("insert into zadatakdiff set zadatak=$id, diff='$diff'");
					}
				}
				unlink ("$lokacijazadaca$zadaca/difftemp");
			}

			nicemessage($naziv_zadace."/Zadatak ".$zadatak." uspješno poslan!");
			update_komponente($userid,$ponudakursa);
			zamgerlog("poslana zadaca z$zadaca zadatak $zadatak",2); // nivo 2 - edit
			print $povratak_html;
			print $povratak_js;
			return;
		} else {
			zamgerlog("greska pri slanju zadace (zadaca z$zadaca zadatak $zadatak filename $filename)",3);
			niceerror("Greška pri slanju zadaće. Kontaktirajte tutora.");
			print $povratak_html;
			return;
		}

	} else { // if ($attach==0)...
		$program = $_FILES['attachment']['tmp_name'];
		if ($program && (file_exists($program)) && $_FILES['attachment']['error']===UPLOAD_ERR_OK) {
			// Nećemo pokušavati praviti diff
			$ime_fajla = strip_tags(basename($_FILES['attachment']['name']));

			// Ukidam HTML znakove radi potencijalnog XSSa
			$ime_fajla = str_replace("&", "", $ime_fajla);
			$ime_fajla = str_replace("\"", "", $ime_fajla);
			$puni_put = "$lokacijazadaca$zadaca/$ime_fajla";

			// Provjeravamo da li je ekstenzija na spisku dozvoljenih
			$ext = ".".pathinfo($ime_fajla, PATHINFO_EXTENSION); // FIXME: postojeći kod očekuje da ekstenzije počinju tačkom...
			$db_doz_eks = explode(',',$zadaca_dozvoljene_ekstenzije);
			if ($zadaca_dozvoljene_ekstenzije != "" && !in_array($ext, $db_doz_eks)) {
				niceerror("Tip datoteke koju ste poslali nije dozvoljen.");
				print "<p>Na ovoj zadaći dozvoljeno je slati samo datoteke jednog od sljedećih tipova: <b>$zadaca_dozvoljene_ekstenzije</b>.<br>
				Vi ste poslali datoteku tipa: <b>$ext</b>.</p>";
				zamgerlog("pogresan tip datoteke (z$zadaca)", 3);
				print $povratak_html;
				return;
			}

			if (file_exists($puni_put)) unlink ($puni_put);
			rename($program, $puni_put);

			// Escaping za SQL
			$ime_fajla = my_escape($ime_fajla);

			$q260 = myquery("insert into zadatak set zadaca=$zadaca, redni_broj=$zadatak, student=$userid, status=$prvi_status, vrijeme=now(), filename='$ime_fajla', userid=$userid");

			nicemessage("Z".$naziv_zadace."/".$zadatak." uspješno poslan!");
			update_komponente($userid,$ponudakursa,$komponenta);
			zamgerlog("poslana zadaca z$zadaca zadatak $zadatak (attachment)",2); // nivo 2 - edit
			print $povratak_html;
			print $povratak_js;
			return;
		} else {
			switch ($_FILES['attachment']['error']) { 
				case UPLOAD_ERR_OK:
					$greska="Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_INI_SIZE: 
					$greska="Poslana datoteka je veća od dozvoljene. Trenutno maksimalna dozvoljena veličina je ".ini_get('upload_max_filesize'); 
					break;
				case UPLOAD_ERR_FORM_SIZE: 
					$greska="Poslana datoteka je veća od dozvoljene."; // jednom ćemo omogućiti nastavniku da ograniči veličinu kroz formu
					break;
				case UPLOAD_ERR_PARTIAL: 
					$greska="Slanje datoteke je prekinuto, vjerovatno zbog problema sa vašom konekcijom. Molimo pokušajte ponovo."; 
					break;
				case UPLOAD_ERR_NO_FILE: 
					$greska="Poslali ste praznu ili nepostojeću datoteku.";
					break;
				case UPLOAD_ERR_NO_TMP_DIR: 
					$greska="1 Greška u konfiguraciji Zamgera: nepostojeći TMP direktorij.";
					break;
				case UPLOAD_ERR_CANT_WRITE: 
					$greska="2 Greška u konfiguraciji Zamgera: nemoguće pisati u TMP direktorij.";
					break;
				case UPLOAD_ERR_EXTENSION: 
					$greska="3 Greška u konfiguraciji Zamgera: neka ekstenzija sprječava upload.";
					break;
				default: 
					$greska="Nepoznata greška u slanju datoteke. Kod: ".$_FILES['attachment']['error'];
			} 
			zamgerlog("greska kod attachmenta (z$zadaca): $greska",3);
			niceerror("$greska");
			print $povratak_html;
			return;
		}
	}
}

?>
