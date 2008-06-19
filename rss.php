<?

// RSS - feed za studente

// v3.9.1.0 (2008/04/30) + pocetak


$broj_poruka = 10;


require("lib/libvedran.php");
require("lib/zamger.php");
require("lib/config.php");

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);


// Pretvaramo rss id u userid
$id = my_escape($_REQUEST['id']);
$q1 = myquery("select auth from rss where id='$id'");
if (mysql_num_rows($q1)<1) {
	print "Greska! Nepoznat RSS ID $id";
	return 0;
}
$userid = mysql_result($q1,0,0);
// Update timestamp
$q2 = myquery("update rss set access=NOW() where id='$id'");


// Ime studenta
$q5 = myquery("select ime,prezime from auth where id=$userid");
if (mysql_num_rows($q5)<1) {
	print "Greska! Nepoznat userid $userid";
	return 0;
}
$ime = mysql_result($q5,0,0); $prezime = mysql_result($q5,0,1);

?>
<<?='?'?>xml version="1.0" encoding="utf-8"?>
<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.n
etscape.com/publish/formats/rss-0.91.dtd">
<rss version="0.91">
<channel>
        <title>Zamger RSS2</title>
        <link><?=$conf_site_url?></link>
        <description>Aktuelne informacije za studenta <?=$ime?> <?=$prezime?></description>
        <language>bs-ba</language>

<?



$vrijeme_poruke = array();
$code_poruke = array();

/*$vrijeme_poruke[1]=1;
$code_poruke[1]="<item>
		<title>hello</title>
		<link>$conf_site_url/index.php?sta=student/zadaca&amp;zadaca=$r10[0]&amp;predmet=$r10[4]</link>
		<description><![CDATA[hello hello]]>
	</item>";*/

print $code_poruke[1];

// Rokovi za slanje zadaća

$q10 = myquery("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave) from zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p where z.predmet=sp.predmet and sp.student=$userid and sp.predmet=pk.id and pk.predmet=p.id and z.rok>curdate() and z.aktivna=1 order by rok desc limit $broj_poruka");
while ($r10 = mysql_fetch_row($q10)) {
	$code_poruke["z".$r10[0]] = "<item>
		<title>Objavljena zadaća $r10[1], predmet $r10[3]</title>
		<link>$conf_site_url/index.php?sta=student/zadaca&amp;zadaca=$r10[0]&amp;predmet=$r10[4]</link>
		<description><![CDATA[Rok za slanje je ".date("d. m. Y",$r10[2]).".]]></description>
	</item>";
	$vrijeme_poruke["z".$r10[0]] = $r10[5];
}


// Objavljeni rezultati ispita

$q15 = myquery("select i.id, i.predmet, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum) from ispit as i, komponenta as k, student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=i.predmet and i.komponenta=k.id and i.predmet=pk.id and pk.predmet=p.id order by i.vrijemeobjave desc limit $broj_poruka");
while ($r15 = mysql_fetch_row($q15)) {
	if ($r15[3] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$code_poruke["i".$r15[0]] = "<item>
		<title>Objavljeni rezultati ispita $r15[2] (".date("d. m. Y",$r15[5]).") - predmet $r15[4]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r15[1]</link>
		<description></description>
	</item>";
	$vrijeme_poruke["i".$r15[0]] = $r15[3];
}



// konacna ocjena

$q17 = myquery("select ko.predmet, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and sp.predmet=ko.predmet and sp.predmet=pk.id and pk.predmet=p.id order by ko.datum desc limit $broj_poruka");
while ($r17 = mysql_fetch_row($q17)) {
	if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	$code_poruke["k".$r17[0]] = "<item>
		<title>Čestitamo! Dobili ste $r17[1] -- predmet $r17[3]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r17[0]</link>
		<description></description>
	</item>";
	$vrijeme_poruke["k".$r17[0]] = $r17[2];
}



// pregledane zadace
// (ok, ovo moze biti JAAAKO sporo ali dacemo sve od sebe da ne bude ;) )

$q18 = myquery("select zk.id, zk.redni_broj, UNIX_TIMESTAMP(zk.vrijeme), p.naziv, z.naziv, pk.id, z.id from zadatak as zk, zadaca as z, ponudakursa as pk, predmet as p where zk.student=$userid and zk.status!=1 and zk.status!=4 and zk.zadaca=z.id and z.predmet=pk.id and pk.predmet=p.id order by zk.id desc limit 10");
$zadaca_bila = array();
while ($r18 = mysql_fetch_row($q18)) {
	if (in_array($r18[6],$zadaca_bila)) continue; // ne prijavljujemo vise puta istu zadacu
	if ($r18[2] < time()-60*60*24*30) break; // IDovi bi trebali biti hronoloskim redom, tako da ovdje mozemo prekinuti petlju
	$code_poruke["zp".$r18[0]] = "<item>
		<title>Pregledana zadaća $r18[4], predmet $r18[3]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r18[5]</link>
		<description>Posljednja izmjena: ".date("d. m. Y. h:i:s",$r18[2])."</description>
	</item>";
	array_push($zadaca_bila,$r18[6]);
	$vrijeme_poruke["zp".$r18[0]] = $r18[2];
}



// PORUKE (izvadak iz inboxa)


// Zadnja akademska godina
$q20 = myquery("select id,naziv from akademska_godina order by id desc limit 1");
$ag = mysql_result($q20,0,0);

// Studij koji student trenutno sluša
$studij=0;
$q30 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
if (mysql_num_rows($q30)>0) {
	$studij = mysql_result($q30,0,0);
}



$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tip, posiljalac from poruka order by vrijeme desc");
while ($r100 = mysql_fetch_row($q100)) {
	$id = $r100[0];
	$opseg = $r100[2];
	$primalac = $r100[3];
	if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
		continue;
	if ($opseg==5) {
		// odredjujemo da li student slusa predmet
		$q110 = myquery("select count(*) from student_predmet where student=$userid and predmet=$primalac");
		if (mysql_result($q110,0,0)<1) continue;
	}
	if ($opseg==6) {
		// da li je student u labgrupi?
		$q115 = myquery("select count(*) from student_labgrupa where student=$userid and labgrupa=$primalac");
		if (mysql_result($q115,0,0)<1) continue;
	}
	$vrijeme_poruke[$id]=$r100[1];

	// Fino vrijeme
	$vr = $vrijeme_poruke[$id];
	$vrijeme="";
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "danas ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "juče ";
	else $vrijeme .= date("d.m. ",$vr);
	$vrijeme .= date("H:i",$vr);

	$naslov = $r100[4];
	if (strlen($naslov)>30) $naslov = substr($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	// Posiljalac
	if ($r100[6]==0) {
		$posiljalac="Administrator";
	} else {
		$q120 = myquery("select ime,prezime from auth where id=$r100[6]");
		if (mysql_num_rows($q120)>0) {
			$posiljalac=mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$posiljalac="Nepoznat";
		}
	}

	if ($r100[5]==1)
		$title="Obavijest";
	else
		$title="Poruka";

	$code_poruke[$id]="<item>
		<title>$title: $naslov ($vrijeme)</title>
		<link>$conf_site_url/index.php?sta=common/inbox&amp;poruka=$id</link>
		<description>Poslao: $posiljalac</description>
	</item>";
}


// Sortiramo po vremenu
arsort($vrijeme_poruke);
$count=0;
foreach ($vrijeme_poruke as $id=>$vrijeme) {
	print $id." ".$code_poruke[$id];
	$count++;
	if ($count==$broj_poruka) break; // prikazujemo 5 poruka
}




?>
</channel>
</rss>