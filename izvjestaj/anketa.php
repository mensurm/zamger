<?
function izvjestaj_anketa(){

$predmet = intval($_REQUEST['predmet']);

// naziv predmeta
$result233=myquery("select p.naziv,pk.akademska_godina from predmet as p, ponudakursa as pk where pk.predmet=p.id and pk.id=$predmet ");
$naziv_predmeta = mysql_result($result233,0,0);
// akademska godina za datu ponudu kursa
$ag = mysql_result($result233,0,1);

/*// id aktelne akademske godine
$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
$ag = mysql_result($q10,0,0);*/

// naziv akdemske godine
$q0111=myquery("select naziv from akademska_godina where id = $ag");
$naziv_ak_god = mysql_result($q0111,0,0);
	
$rank = intval($_REQUEST['rank']);

	
	
	//aktuelna anketa
	$q12 = myquery("select id from anketa where ak_god=$ag");
	$anketa = mysql_result($q12,0,0);

if ($_REQUEST['komentar'] == "da") 
{  
	// ---------------------------------------------   IZVJESTAJ ZA KOMENTARE ---------------------------------------------
	
	$limit = 5; // broj kometara prikazanih po stranici
	$offset = intval($_REQUEST["offset"]);
	
	
	// ako je izvjestaj za komentare

	print "<center>";
	print "<h2>Prikaz svih komentara za predmet $naziv_predmeta za akademsku godinu $naziv_ak_god</h2>\n";

 
	$q30 = myquery("select count(*) from rezultat where predmet_id=$predmet and anketa_id = $anketa");
	$broj_anketa = mysql_result($q30,0,0);
	
	print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
	
	
	// pokupimo sve komentare za dati predmet
		
	$q60 = myquery("SELECT count(*) FROM odgovor_text WHERE rezultat_id IN (SELECT id FROM rezultat WHERE predmet_id =$predmet and anketa_id=$anketa)");
	
	$broj_odgovora = mysql_result($q60,0,0);
	
	$q61 = myquery(" SELECT response FROM odgovor_text WHERE rezultat_id IN (SELECT id FROM rezultat WHERE predmet_id =$predmet and anketa_id=$anketa) limit $offset, $limit");
	
	
	if ($broj_odgovora == 0)
			print "Nema rezultata!";
	else if ($broj_odgovora > $limit) {
			
			print "Prikazujem rezultate ".($offset+1)."-".($offset+5)." od $broj_odgovora. Stranica: ";

			for ($i=0; $i < $broj_odgovora; $i+=$limit) {
				$br = intval($i/$limit)+1;
				
				if ($i == $offset)
					print "<b>$br</b> ";
				else
					print "<a href=\"?sta=izvjestaj/anketa&predmet=$predmet&komentar=da&offset=$i\">$br</a> ";
			}
			print "<br/>";
		}

	
	?>
    
    <table width="650px"  >
    	 <tr>
        	<td bgcolor="#6699CC" height="10">   </td>
        </tr>
       
	    
	<?
    $i=0;
	while ($r61 = mysql_fetch_row($q61)) {
			
			print  "<tr >"; 
			print  "<td>  <hr/>  </td>"; 
			print  "</tr>";
			print  "<tr >";
			//print  "<td>".($i+1) .". </td>"; 
			print  "<td>    $r61[0] </td>"; 
			print  "</tr>";
			
			$i++;
		}	
	?>
    
    </table> 
    </center>
    <?
	
		

	}// kraj dijela koji se prikazuje ako su u je u pitanju izvjestaj za komentare

	else if ($_REQUEST['rank'] == "da") 
	{
	
	// -------------------------------------------------   IZVJESTAJ ZA RANK PITANJA  ------------------------------------------------------------------------

	
	print "<center>";

	print "<h2>Statistika za predmet $naziv_predmeta za akademsku godinu $naziv_ak_god</h2>\n";
 
	
    // Opste statistike (sumarno za predmet)


	$q30 = myquery("select count(*) from rezultat where predmet_id=$predmet and anketa_id = $anketa");
	$broj_anketa = mysql_result($q30,0,0);
	print "<h3> Broj studenata koji su pristupili anketi je : $broj_anketa </h3>";
	
	
	 
	// broj rank pitanja
	$result203=myquery("SELECT id FROM pitanje WHERE anketa_id =$anketa and tip_id =1");
	
	
	$i = 0;
	while ($r01 = mysql_fetch_row($result203)){
		
		$j=$i+1;
		$q60 = myquery(" SELECT avg( izbor_id )FROM odgovor_rank WHERE rezultat_id IN (SELECT id FROM rezultat WHERE predmet_id =$predmet and anketa_id = $anketa)
					AND pitanje_id = $r01[0]");
	
		$prosjek[$i]=mysql_result($q60,0,0);
		
		$i++;
	}
	
	
	//kupimo pitanja
	$result202=myquery("SELECT p.id, p.tekst,t.tip FROM pitanje p,tip_pitanja t WHERE p.tip_id = t.id and p.anketa_id =$anketa and p.tip_id=1");
   
	
	?>
    
    <table width="800px"  >
    	<tr> 
        	<td bgcolor="#6699CC"> Pitanje </td> <td bgcolor="#6699CC" width='350px'> Prosjek odgovora </td>
        </tr>
       
	<tr> 
        	<td colspan="2"> <hr/>  </td>
        </tr>
          <tr > 
             <td  > </td> <td bgcolor="#FF0000" width='350px'> &nbsp;MAX </td>
         </tr>
    
    
	<?
    $i=0;
	while ($r202 = mysql_fetch_row($result202)) {
			$procenat=($prosjek[$i]/5)*100;
			print "<tr height='35'>";
			print  "<td>".($i+1) .". $r202[1] </td> <td>    
				<table border='0' width='350px'>
    				<tr> 
        				<td height='30' width='$procenat%'  bgcolor='#CCCCFF'> &nbsp;". round($prosjek[$i],2) ." </td> <td width='".(100-$procenat)."%'> </td>
        			</tr>
      			</table> 
			</td> 
			</tr>";
			
			$i++;
		}	
		$prosjek = array_sum($prosjek)/sizeof($prosjek);

	?>
    <tr> 
        	<td colspan="2"> <hr/>  </td>
        </tr>
          <tr > 
             <td align="right"> Prosjek predmeta : </td> <td  width='350px'> &nbsp;<strong><?=round($prosjek,2)?> </strong> </td>
         </tr>
    </table> 
    </center>
    <?
}
}
?>