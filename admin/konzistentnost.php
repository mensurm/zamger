<?

// ADMIN/KONZISTENTNOST + vrsi provjeru konzistentnosti podataka u bazi i nudi mogucnost popravke

// v3.9.1.0 (2008/04/28) + Novi modul admin/konzistentnost



function admin_konzistentnost() {

global $userid;


// Akcije - popravke nekonzistentnosti
if ($_GET['akcija']=="upisi_studij") {
	$student=intval($_GET['student']);
	$studij=intval($_GET['studij']);
	$ag=intval($_GET['ag']);
	$semestar=intval($_GET['semestar']);

	// Ubacujemo studij
	$q520 = myquery("insert into student_studij set student=$student, studij=$studij, semestar=$semestar, akademska_godina=$ag");
	if ($semestar%2==0) {
		$q525 = myquery("select count(*) from student_studij where student=$student and studij=$studij and semestar=".($semestar-1)." and akademska_godina=$ag");
		if (mysql_result($q525,0,0)<1)
			$q530 = myquery("insert into student_studij set student=$student, studij=$studij, semestar=".($semestar-1).", akademska_godina=$ag");
	}
	zamgerlog("admin/pk: student u$student upisan na studij $studij, semestar $semestar, ag ag$ag",4);
	print "student $student upisan na studij $studij, semestar $semestar, ag $ag<br/>";
}

if ($_GET['akcija']=="ispisi_studij") {
	$student=intval($_GET['student']);
	$studij=intval($_GET['studij']);
	$ag=intval($_GET['ag']);
	$semestar=intval($_GET['semestar']);

	// Ubacujemo studij
	$q540 = myquery("delete from student_studij where student=$student and studij=$studij and semestar=$semestar and akademska_godina=$ag");
	if ($semestar%2==1) {
		$q545 = myquery("select count(*) from student_studij where student=$student and studij=$studij and semestar=".($semestar+1)." and akademska_godina=$ag");
		if (mysql_result($q545,0,0)>0)
			$q550 = myquery("delete from student_studij where student=$student and studij=$studij and semestar=".($semestar+1)." and akademska_godina=$ag");
	}
	zamgerlog("admin/pk: student u$student ispisan sa studija $studij, semestar $semestar, ag ag$ag",4);
	print "student $student ispisan sa studija $studij, semestar $semestar, ag $ag<br/>";
}

if ($_GET['akcija']=="promijeni_studij") {
	$student=intval($_GET['student']);
	$studij=intval($_GET['studij']);
	$ag=intval($_GET['ag']);
	$semestar=intval($_GET['semestar']);

	// Ubacujemo studij
	$q560 = myquery("update student_studij set studij=$studij where student=$student and semestar=$semestar and akademska_godina=$ag");
	if ($semestar%2==1) $s2 = $semestar+1; else $s2 = $semestar-1;
	$q565 = myquery("select count(*) from student_studij where student=$student and semestar=$s2 and akademska_godina=$ag");
	if (mysql_result($q565,0,0)>0)
		$q570 = myquery("update student_studij set studij=$studij where student=$student and semestar=$s2 and akademska_godina=$ag");
	zamgerlog("admin/pk: student u$student prebacen na studij $studij, semestar $semestar, ag ag$ag",4);
	print "student $student prebacen na studij $studij, semestar $semestar, ag $ag<br/>";
}

if ($_GET['akcija']=="brisiocjenu") {
	$student=intval($_GET['student']);
	$predmet=intval($_GET['predmet']);
	$ag=intval($_GET['ag']);

	// Odredjujemo ponudukursa
	$q500 = myquery("select pk.id from ponudakursa as pk, konacna_ocjena as ko where pk.predmet=$predmet and pk.akademska_godina=$ag and pk.id=ko.predmet and ko.student=$student");
	if (mysql_num_rows($q500)<1) {
		niceerror("Nije pronađena ocjena koju treba brisati! student: $student predmet: $predmet akademska_godina: $ag");
		zamgerlog("nije pronađena ocjena koju treba brisati! student: $student predmet: $predmet akademska_godina: $ag",3);
	} else {
		$pk = mysql_result($q500,0,0);
		$q510 = myquery("delete from konacna_ocjena where student=$student and predmet=$pk");
		zamgerlog("admin/pk: obrisana ocjena - student: u$student predmet: p$predmet akademska_godina: ag$ag",4);
		print "obrisana ocjena - student: $student predmet: $predmet akademska_godina: $ag<br/>";
	}
}

if ($_GET['akcija']=="upisi_predmet") {
	$student=intval($_GET['student']);
	$predmet=intval($_GET['predmet']);
	$ag=intval($_GET['ag']);
	$studij=intval($_GET['studij']);

	// Odredjujemo ponudukursa
	$q580 = myquery("select pk.id from ponudakursa as pk where pk.predmet=$predmet and pk.akademska_godina=$ag and pk.studij=$studij");
	if (mysql_num_rows($q580)>0) {
		$pk = mysql_result($q580,0,0);
		$q590 = myquery("insert into student_predmet set student=$student, predmet=$pk");
		zamgerlog("admin/pk: student u$student upisan na predmet p$pk",4);
		print "student $student upisan na predmet $pk<br/>";
	} else {
		zamgerlog("nije pronadjena ponudakursa za predmet $predmet, ag ag$ag, studij $studij",3);
		niceerror("Nije pronađena ponudakursa za predmet $predmet, ag $ag, studij $studij");
	}
}

if ($_GET['akcija']=="ispisi_predmet") {
	$student=intval($_GET['student']);
	$predmet=intval($_GET['predmet']);
	$ag=intval($_GET['ag']);

	// Odredjujemo ponudukursa
	$q600 = myquery("select pk.id from ponudakursa as pk, student_predmet as sp where pk.predmet=$predmet and pk.akademska_godina=$ag and pk.id=sp.predmet and sp.student=$student");
	if (mysql_num_rows($q600)>0) {
		$pk = mysql_result($q600,0,0);
		$q590 = myquery("delete from student_predmet where student=$student and predmet=$pk");
		zamgerlog("admin/pk: student u$student ispisan sa predmeta p$pk",4);
		print "student $student ispisan sa predmeta $pk<br/>";
	} else {
		zamgerlog("student u$student ne slusa nijedan predmet $predmet, ag ag$ag",3);
		niceerror("student $student ne slusa nijedan predmet $predmet, ag $ag");
	}
}




// Tip provjere: STUDENTI

if ($_GET['vrsta']=="studenti") {

	print "<br/><br/>Rezultati:<br/><ul>";

	// Cache imena predmeta
	$ip=array();
	$q5 = myquery("select id,naziv from predmet");
	while ($r5 = mysql_fetch_row($q5)) {
		$ip[$r5[0]]=$r5[1];
	}
	// Cache imena akademskih godina
	$iag = array();
	$maxag=0;
	$q6 = myquery("select id,naziv from akademska_godina");
	while ($r6 = mysql_fetch_row($q6)) {
		$iag[$r6[0]]=$r6[1];
		if ($r6[0]>$maxag) $maxag=$r6[0];
	}
	// Cache imena studija
	$istud = array();
	$q7 = myquery("select id,naziv from studij");
	while ($r7 = mysql_fetch_row($q7)) {
		$istud[$r7[0]]=$r7[1];
	}


	$q10 = myquery("select id,ime,prezime from auth where student=1 order by prezime,ime");
	while ($r10 = mysql_fetch_row($q10)) {
		$stud_id = $r10[0];
		$ime = $r10[1];
		$prezime = $r10[2];

		// Spisak studija
		$studiji=array();
		$ssemestar=array();
		$q20 = myquery("select studij,semestar,akademska_godina from student_studij where student=$stud_id order by akademska_godina,semestar");
		while ($r20 = mysql_fetch_row($q20)) {
			$studiji[$r20[2]]=$r20[0];
			if ($r20[1]%2==0 && $ssemestar[$r20[2]]<1) {
				?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> upisan na semestar <?=$r20[1]?> a nije bio upisan na <?=($r20[1]-1)?> u <?=$iag[$r20[2]]?><br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=upisi_studij&student=<?=$stud_id?>&studij=<?=$r20[0]?>&ag=<?=$r20[2]?>&semestar=<?=($r20[1]-1)?>">Upiši studenta na studij '<?=$istud[$r20[0]]?>', semestar <?=($r20[1]-1)?>, godina <?=$iag[$r20[2]]?></a><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=ispisi_studij&student=<?=$stud_id?>&studij=<?=$r20[0]?>&ag=<?=$r20[2]?>&semestar=<?=$r20[1]?>">Ispiši studenta sa studija '<?=$istud[$r20[0]]?>', semestar <?=$r20[1]?>, godina <?=$iag[$r20[2]]?></a>
				</li><?
			}
			$ssemestar[$r20[2]]=$r20[1];
		}
		
		// Kada je slusao predmet i na kojem studiju
		$predmeti = array();
		$pstudij = array();
		$psemestar = array();
		$q30 = myquery("select pk.predmet, pk.studij, pk.semestar, pk.akademska_godina from student_predmet as sp, ponudakursa as pk where sp.student=$stud_id and sp.predmet=pk.id order by pk.akademska_godina");
		while ($r30 = mysql_fetch_row($q30)) {
			$predmeti[$r30[0]]=$r30[3];
			$pstudij[$r30[0]]=$r30[1];
			$psemestar[$r30[0]]=$r30[2];
		}

		// Kada je ocijenjen
		$ocjene = array();
		$oocjene = array();
		$q40 = myquery("select pk.predmet, pk.akademska_godina, ko.ocjena from konacna_ocjena as ko, ponudakursa as pk where ko.student=$stud_id and ko.predmet=pk.id order by pk.akademska_godina");
		while ($r40 = mysql_fetch_row($q40)) {
			if ($ocjene[$r40[0]]>0) {
				?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> dvaput ocijenjen iz predmeta <?=$ip[$r40[0]]?>: jednom <?=$iag[$ocjene[$r40[0]]]?>, a drugi put <?=$iag[$r40[1]]?><br>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=brisiocjenu&student=<?=$stud_id?>&predmet=<?=$r40[0]?>&ag=<?=$ocjene[$r40[0]]?>">Obriši ocjenu <?=$oocjene[$r40[0]]?> iz <?=$iag[$ocjene[$r40[0]]]?></a><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=brisiocjenu&student=<?=$stud_id?>&predmet=<?=$r40[0]?>&ag=<?=$r40[1]?>">Obriši ocjenu <?=$r40[2]?> iz <?=$iag[$r40[1]]?></a>
				</li><?
			}
			$ocjene[$r40[0]]=$r40[1];
			$oocjene[$r40[0]]=$r40[2];
		}

		// Slusa predmete koje je vec polozio
		foreach ($ocjene as $predmet => $ag) {
			if ($predmeti[$predmet] > $ag) {
				?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> sluša predmet <?=$ip[$predmet]?> (<?=$iag[$predmeti[$predmet]]?>) koji je već položio <?=$iag[$ag]?><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=ispisi_predmet&student=<?=$stud_id?>&predmet=<?=$predmet?>&ag=<?=$predmeti[$predmet]?>">Ispiši studenta sa predmeta <?=$ip[$predmet]?> u <?=$iag[$predmeti[$predmet]]?></a><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=brisiocjenu&student=<?=$stud_id?>&predmet=<?=$predmet?>&ag=<?=$ag?>">Obriši ocjenu <?=$oocjene[$predmet]?> iz <?=$iag[$ag]?></a>
				</li><?
			}
		}

		// Prenio >1 predmeta ili preko dvije godine
		foreach ($ssemestar as $ag=>$semestar) {
			$prenio=0;
			$nazivi="";
			$ispis_ispis="";
			$prenio_predmet=0;
			if ($semestar%2==0) $s2=$semestar-1; else $s2=$semestar;
			foreach($predmeti as $predmet => $agp) {

				if ($psemestar[$predmet]<$s2-2 && $ocjene[$predmet]<1) {
					?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> je prenio predmet <?=$ip[$predmet]?> sa semestra <?=$psemestar[$predmet]?>, a sluša semestar <?=$semestar?>, godina <?=$iag[$ag]?>! Molimo razriješite ručno.
					</li><?
				}
				else if ($psemestar[$predmet]<$s2 && $ocjene[$predmet]<1) {
					$prenio++;
					$nazivi .= $ip[$predmet].", ";
					$ispis_ispis .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href=\"?sta=admin/konzistentnost&vrsta=studenti&akcija=ispisi_predmet&student=$stud_id&predmet=$predmet&ag=$predmeti[$predmet]\">Ispiši studenta sa predmeta ".$ip[$predmet]." u ".$iag[$predmeti[$predmet]]."</a><br/>\n";
					$prenio_predmet=$predmet;
				}
			}

			// Prenio više od 1 predmeta
			if ($prenio>1) {
				?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> je prenio <?=$prenio?> predmeta: <?=$nazivi?>. Molimo razriješite ručno.<br/><?=$ispis_ispis?></li><?

			// Ne sluša predmet koji je prenio
			} else if ($prenio==1 && $predmeti[$prenio_predmet]<$ag) {
				?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> je prenio predmet <?=$ip[$prenio_predmet]?> u godinu <?=$iag[$ag]?> a nije ga upisao<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=upisi_predmet&student=<?=$stud_id?>&predmet=<?=$prenio_predmet?>&ag=<?=$ag?>&studij=<?=$pstudij[$prenio_predmet]?>">Upiši studenta na predmet <?=$ip[$prenio_predmet]?> u <?=$iag[$ag]?></a><br/>
				</li><?
				
			}
//print "- $prezime $ime $stud_id $prenio<br/>";

			// Preskocio godinu?
			$zadnji_neparni=$semestar-1;
			if ($semestar%2==0) $zadnji_parni-=2;
			if ($ag>1 && $ssemestar[$ag-1]>0 && $ssemestar[$ag-1]<$zadnji_parni) {
				?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> je prešao iz <?=$ssemestar[$ag-1]?> semestra u <?=$iag[$ag-1]?> na <?=$semestar?> semestar u <?=$iag[$ag]?>. Molimo razriješite ručno</li><?
			}
		}

		$pisao = array();
		foreach ($predmeti as $predmet => $ag) {

			// Nije upisan na fakultet?
			if ($studiji[$ag]<1 || $ssemestar[$ag]<$psemestar[$predmet]) {
				if ($pisao[$psemestar[$predmet]]!=1) {
					?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> <?=$iag[$ag]?> slušao predmete sa <?=$psemestar[$predmet]?> semestra a nije bio upisan na fakultet<br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=upisi_studij&student=<?=$stud_id?>&studij=<?=$pstudij[$predmet]?>&ag=<?=$ag?>&semestar=<?=$psemestar[$predmet]?>">Upiši studenta na studij '<?=$istud[$pstudij[$predmet]]?>', semestar <?=$psemestar[$predmet]?>, godina <?=$iag[$ag]?></a><br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=ispisi_predmet&student=<?=$stud_id?>&predmet=<?=$predmet?>&ag=<?=$predmeti[$predmet]?>">Ispiši studenta sa predmeta <?=$ip[$predmet]?> u <?=$iag[$ag]?> (potencijalno još predmeta)</a>
					</li><?
					$pisao[$psemestar[$predmet]]=1;
				}
			}

			// Sluša predmet sa pogrešnog studija?
			else if ($pstudij[$predmet]!=$studiji[$ag] && $pstudij[$predmet]!=1) { // studij 1 = prva godina
				?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> <?=$iag[$ag]?> slušao predmet <?=$ip[$predmet]?> sa studija <?=$istud[$pstudij[$predmet]]?> a bio upisan na <?=$istud[$studiji[$ag]]?><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=promijeni_studij&student=<?=$stud_id?>&studij=<?=$pstudij[$predmet]?>&ag=<?=$ag?>">Prebaci studenta sa '<?=$istud[$studiji[$ag]]?>' na '<?=$istud[$pstudij[$predmet]]?>' u <?=$iag[$ag]?></a><br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=ispisi_predmet&student=<?=$stud_id?>&predmet=<?=$predmet?>&ag=<?=$predmeti[$predmet]?>">Ispiši studenta sa predmeta <?=$ip[$predmet]?> u <?=$iag[$ag]?> (potencijalno još predmeta)</a>
				</li>
				<?
			}
		}


		// Nije upisan na predmete sa trenutnog studija (a nije ih ranije polozio)
		foreach ($studiji as $ag=>$studij) {
			$s1 = $ssemestar[$ag];
			if ($s1%2==0) $s2=$s1-1; else $s2=$s1+1;
			$q50 = myquery("select id,predmet,semestar from ponudakursa where studij=$studij and akademska_godina=$ag and obavezan=1 and (semestar=$s1 or semestar=$s2)");
			while ($r50 = mysql_fetch_row($q50)) {
				$pk=$r50[0];
				$predmet=$r50[1];
				$semestar=$r50[2];
				if ($ssemestar[$ag]<$semestar) continue;
				if ($ocjene[$predmet]>0 && $ocjene[$predmet]<$ag) continue;
				$q60 = myquery("select count(*) from student_predmet where predmet=$pk and student=$stud_id");
				if (mysql_result($q60,0,0)<1) {
					?><li>Student <a href="?sta=studentska/studenti&akcija=edit&student=<?=$stud_id?>"><?=$prezime?> <?=$ime?></a> <?=$iag[$ag]?> nije slušao predmet <?=$ip[$predmet]?> a bio upisan na <?=$istud[$studij]?><br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=upisi_predmet&student=<?=$stud_id?>&predmet=<?=$predmet?>&ag=<?=$ag?>&studij=<?=$studij?>">Upiši studenta na predmet <?=$ip[$predmet]?> u <?=$iag[$ag]?></a><br/>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <a href="?sta=admin/konzistentnost&vrsta=studenti&akcija=ispisi_studij&student=<?=$stud_id?>&studij=<?=$studij?>&ag=<?=$ag?>&semestar=<?=$semestar?>">Ispiši studenta sa studija '<?=$istud[$studij]?>', semestar <?=$semestar?>, godina <?=$iag[$ag]?></a>
					</li><?
				}
			}
		}

		// Dodati test prenesenih predmeta

	}

	print "</ul>";	




} else { // if ($_GET['akcija']...
	
	?>
	
	<h3>Provjera konzistentnosti</h3>
	
	<ul>
	<li><a href="?sta=admin/konzistentnost&vrsta=studenti">Provjera konzistentnosti podataka za studente o upisanim predmetima, godina studija itd.</a></li>
	</ul>
	<?
}


}


?>