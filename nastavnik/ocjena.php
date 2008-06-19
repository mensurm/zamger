<?

// NASTAVNIK/OCJENA - masovni unos konacnih ocjena

// v3.9.1.0 (2008/02/19) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/02/28) + Koristim lib/manip
// v3.9.1.2 (2008/05/20) + Podignut logging nivo sa 2 na 4



function nastavnik_ocjena() {

global $userid,$user_siteadmin;

require("lib/manip.php");
global $mass_rezultat; // za masovni unos studenata u grupe



$predmet=intval($_REQUEST['predmet']);
if ($predmet==0) { 
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}

$q1 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);

//$tab=$_REQUEST['tab'];
//if ($tab=="") $tab="Opcije";

//logthis("Admin Predmet $predmet - tab $tab");



// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin) {
	$q10 = myquery("select np.admin from nastavnik_predmet as np where np.nastavnik=$userid and np.predmet=$predmet");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("privilegije (predmet $predmet)",3);
		biguglyerror("Nemate pravo ulaska u ovu grupu!");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Konačna ocjena</h3></p>

<?


# Masovni unos konačnih ocjena

if ($_POST['akcija'] == "massinput" && strlen($_POST['nazad'])<1) {

	if ($_POST['fakatradi'] != 1) $ispis=1; else $ispis=0; // fakatradi=0 --> ispis=1

	$greska=mass_input($ispis); // Funkcija koja parsira podatke

	if (count($mass_rezultat)==0) {
//		niceerror("Niste unijeli ništa.");
//		return;
		print "Niste unijeli nijedan upotrebljiv podatak<br/><br/>\n";
		$greska=1;
	}

	if ($ispis) {
		?>Akcije koje će biti urađene:<br/><br/>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<?
	}

	foreach ($mass_rezultat['ime'] as $student=>$ime) {
		$prezime = $mass_rezultat['prezime'][$student];
		$ocjena = $mass_rezultat['podatak1'][$student];

		// Student neocijenjen (prazno mjesto za ocjenu)
		if (intval($ocjena)==0 && strpos($ocjena,"0")===FALSE) {
			if ($ispis)
				print "Student '$prezime $ime' - nije ocijenjen (nije unesena ocjena $ocjena)<br/>";
			continue;
		}

		// Ocjena mora biti u opsegu 6-10
		$ocjena = intval($ocjena);
		if ($ocjena<6 || $ocjena>10) {
			if ($ispis) {
				print "-- GREŠKA! Za studenta '$prezime $ime' ocjena nije u opsegu 6-10 (ocjena: $ocjena)<br/>";
				$greska=1;
				continue;
			}
		}

		// Da li vec ima ocjena u bazi?
		$q100 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$predmet");
		if (mysql_num_rows($q100)>0) {
			if ($ispis) {
				$oc2 = mysql_result($q100,0,0);
				print "-- GREŠKA! Student '$prezime $ime' je već ranije ocijenjen ocjenom $oc2 (a sada sa $ocjena). Izmjena unesene ocjene trenutno nije moguća.<br/>";
				$greska=1;
				continue;
			}
		}

		if ($ispis) {
//			print "Student '$prezime $ime' (ID: $student) - ocjena: $ocjena<br/>";
			print "Student '$prezime $ime' - ocjena: $ocjena<br/>";
		} else {
			$q110 = myquery("insert into konacna_ocjena set student=$student, predmet=$predmet, ocjena=$ocjena");
		}
	}

	if ($ispis) {
		print '<input type="submit" name="nazad" value=" Nazad "> ';
		if ($greska==0) print ' <input type="submit" value=" Potvrda">';
		print "</form>";
		return;
	} else {
		zamgerlog("masovno upisane ocjene na predmet p$predmet",4);
	}
}




// Masovni unos konačnih ocjena

$format = intval($_POST['format']);
if (!$_POST['format']) {
	$q110 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-format'");
	if (mysql_num_rows($q110)>0) $format = mysql_result($q110,0,0);
	else //default vrijednost
		$format=0;
}

$separator = intval($_POST['separator']);
if (!$_POST['separator']) {
	$q120 = myquery("select vrijednost from preference where korisnik=$userid and preferenca='mass-input-separator'");
	if (mysql_num_rows($q120)>0) $separator = mysql_result($q120,0,0);
	else //default vrijednost
		$separator=0;
}


?><p><b>Masovni unos konačnih ocjena</b><br/>
<?=genform("POST")?>
<input type="hidden" name="fakatradi" value="0">
<input type="hidden" name="akcija" value="massinput">
<input type="hidden" name="nazad" value="">
<input type="hidden" name="brpodataka" value="1">
<input type="hidden" name="duplikati" value="0">

<textarea name="massinput" cols="50" rows="10"><?
if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
?></textarea><br/>
<br/>Format imena i prezimena: <select name="format" class="default">
<option value="0" <? if($format==0) print "SELECTED";?>>Prezime[TAB]Ime</option>
<option value="1" <? if($format==1) print "SELECTED";?>>Ime[TAB]Prezime</option>
<option value="2" <? if($format==2) print "SELECTED";?>>Prezime Ime</option>
<option value="3" <? if($format==3) print "SELECTED";?>>Ime Prezime</option></select>&nbsp;
Separator: <select name="separator" class="default">
<option value="0" <? if($separator==0) print "SELECTED";?>>Tab</option>
<option value="1" <? if($separator==1) print "SELECTED";?>>Zarez</option></select><br/><br/>
<input type="submit" value="  Dodaj  ">
</form></p>
<?



}

?>