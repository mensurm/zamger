<?php
require_once("lib/dBug.php");
function fetchProjects($predmet)
{
	$result = myquery("SELECT * FROM projekat WHERE predmet='$predmet' ORDER BY vrijeme DESC");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}
function getProject($id)
{
	$result = myquery("SELECT * FROM projekat WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];	
}

function fetchProjectMembers($id)
{
	$result = myquery("SELECT * FROM osoba o INNER JOIN osoba_projekat op ON o.id=op.student WHERE op.projekat='$id' ORDER BY prezime ASC, ime ASC");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function applyForProject($userid, $project, $predmet)
{
	$errorText = '';
	
	if (areApplicationsLockedForPredmet($predmet))
	{
		$errorText = 'Zaključane su prijave na projekte. Prijave nisu dozvoljene.';
		zamgerlog("student u$userid pokusao da se prijavi na projekat koji je zakljucan na predmetu p$predmet", 3);
		return $errorText;	
	}
	
	$teamLimitReached = isTeamLimitReachedForPredmet($predmet);
		
	$actualProjectForUser = getActualProjectForUserInPredmet($userid, $predmet);
	if (!empty($actualProjectForUser))
	{
		//user already in a project on this predmet
		$nMembers = getCountMembersForProject($actualProjectForUser[id]);
		//after I leave actual team, I will be able to create a new project team because this one will be empty...
		if ($nMembers -1 == 0)
		{
			//reset the team limit
			$teamLimitReached = false;
		}	
	}
	$newTeamDeny = isProjectEmpty($project) == true && $teamLimitReached == true;
	
	
	if ( $newTeamDeny == true )
	{
		$errorText = 'Limit timova dostignut. Nije moguće kreirati projektni tim. Prijavite se na drugi projekat.';
		zamgerlog("student u$userid pokusao da se prijavi na projekat na predmetu p$predmet iako je limit za broj timova dostignut.", 3);
		return $errorText;	
	}
	
	if (isProjectFull($project, $predmet) == true)
	{
		$errorText = 'Projekat je popunjen. Nije moguće prijaviti se.';
		zamgerlog("student u$userid pokusao da se prijavi na projekat koji je popunjen na predmetu p$predmet", 3);
		return $errorText;	
	}
	
	
	//clear person from all projects on this predmet
	$result = myquery("DELETE FROM osoba_projekat WHERE student='$userid' AND projekat IN (SELECT id FROM projekat WHERE predmet='$predmet')");
	$query = sprintf("INSERT INTO osoba_projekat (student, projekat) VALUES ('%d', '%d')", 
					$userid,
					$project
	);
	
	$result = myquery($query);
	
	if ($result == false)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;	
	}
	
	return $errorText;
}
function getOutOfProject($userid, $predmet)
{
	$errorText = '';
	
	if (areApplicationsLockedForPredmet($predmet))
	{
		$errorText = 'Zaključane su liste timova za projekte. Odustajanja nisu dozvoljena.';
		zamgerlog("student u$userid pokusao da se odjavi sa projekat predmetu p$predmet na kojem je zakljucano stanje timova i projekata", 3);		
		return $errorText;	
	}
	
	$actualProjectForUser = getActualProjectForUserInPredmet($userid, $predmet);
	if (empty($actualProjectForUser))
	{
		$errorText = 'Doslo je do greske. Molimo kontaktirajte administratora.';
		zamgerlog("student u$userid pokusao da se odjavi sa projekta na kojem nije bio prijavljen na predmetu p$predmet", 3);		
		return $errorText;
	}
	

	//clear person from all projects on this predmet - actually only one project
	$result = myquery("DELETE FROM osoba_projekat WHERE student='$userid' AND projekat IN (SELECT id FROM projekat WHERE predmet='$predmet')");
	
	if ($result == false)
	{
		$errorText = 'Doslo je do greske prilikom spasavanja podataka. Molimo kontaktirajte administratora.';
		return $errorText;	
	}
	
	return $errorText;
}

function generateIdFromTable($table)
{
	$result = myquery("select id from $table order by id desc limit 1");
	
	if (mysql_num_rows($result) == 0)
	{
		$id = 0;
	}
	else
	{	
		$id = mysql_fetch_row($result);
		$id = $id[0];
	}
		
	
	return intval($id+1);
}

function getPredmetParams($predmet)
{
	$result = myquery("SELECT * FROM predmet_parametri WHERE predmet='$predmet' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];	
}
function areApplicationsLockedForPredmet($predmet)
{
	
	$params = getPredmetParams($predmet);
	return $params[zakljucani_projekti] == 1;
}
function getCountProjectsForPredmet($predmet)
{
	$result = myquery("SELECT COUNT(id) FROM projekat WHERE predmet='$predmet'");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}	

function getCountMembersForProject($id)
{
	$result = myquery("SELECT COUNT(id) FROM osoba o INNER JOIN osoba_projekat op ON o.id=op.student WHERE op.projekat='$id'");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function getCountEmptyProjectsForPredmet($id)
{
	$count = 0;
	$projects = fetchProjects($id);
	foreach ($projects as $project)
	{	
		$nMembers = getCountMembersForProject($project[id]);
		if ($nMembers == 0)
			$count++;	
	}
	
	return $count;
}
function getCountNONEmptyProjectsForPredmet($id)
{
	$count = 0;
	$projects = fetchProjects($id);
	foreach ($projects as $project)
	{	
		$nMembers = getCountMembersForProject($project[id]);
		if ($nMembers > 0)
			$count++;	
	}
	
	return $count;
}

function isTeamLimitReachedForPredmet($id)
{

	$nTeams = getCountNONEmptyProjectsForPredmet($id);
	$predmetParams = getPredmetParams($id);
	
	if ($nTeams < $predmetParams['max_timova'])
		return false;
	
	return true;
}

function isProjectEmpty($project)
{
	$nMembers = getCountMembersForProject($project);
	
	if ($nMembers == 0)
		return true;
		
	return false;
} 

function isProjectFull($project, $predmet)
{
	$predmetParams = getPredmetParams($predmet);
	$nMembers = getCountMembersForProject($project);
	
	if ($nMembers < $predmetParams[max_clanova_tima])
		return false;
		
	return true;
}

function getActualProjectForUserInPredmet($userid, $predmet)
{
	$result = myquery("SELECT p.* FROM projekat p, osoba_projekat op WHERE p.id=op.projekat AND op.student='$userid' AND p.predmet='$predmet' LIMIT 1");
	
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];	
}


function filtered_output_string($string)
{
	//performing nl2br and stripslashes function to display text from the database
	return nl2br(stripslashes($string));
}

/**********************
projektneStrane
START
*********************/
function fetchLinksForProject($id, $offset, $rowsPerPage)
{
	$result = myquery("SELECT * FROM projekat_link WHERE projekat='$id' ORDER BY naziv ASC ");
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";

	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function isUserAuthorOfLink($link, $user)
{
	$result = myquery("SELECT id FROM projekat_link WHERE osoba='$user' AND id='$link' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getLink($id)
{
	$result = myquery("SELECT * FROM projekat_link WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getCountLinksForProject($id)
{
	$result = myquery("SELECT COUNT(id) FROM projekat_link WHERE projekat='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}


function fetchRSSForProject($id, $offset, $rowsPerPage)
{
	$result = myquery("SELECT * FROM projekat_rss WHERE projekat='$id' ORDER BY naziv ASC");
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";

	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function isUserAuthorOfRSS($rss, $user)
{
	$result = myquery("SELECT id FROM projekat_rss WHERE osoba='$user' AND id='$rss' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getRSS($id)
{
	$result = myquery("SELECT * FROM projekat_rss WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getCountRSSForProject($id)
{
	$result = myquery("SELECT COUNT(id) FROM projekat_rss WHERE projekat='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function fetchArticlesForProject($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM bl_clanak WHERE projekat='$id' ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list;	
}

function isUserAuthorOfArticle($article, $user)
{
	$result = myquery("SELECT id FROM bl_clanak WHERE osoba='$user' AND id='$article' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getArticle($id)
{
	$result = myquery("SELECT * FROM bl_clanak WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getCountArticlesForProject($id)
{
	$result = myquery("SELECT COUNT(id) FROM bl_clanak WHERE projekat='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}


function getAuthorOfArticle($id)
{
	$result = myquery("SELECT o.* FROM osoba o WHERE o.id=(SELECT b.osoba FROM bl_clanak b WHERE b.id='$id' LIMIT 1) LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function fetchFilesForProjectAllRevisions($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT * FROM projekat_file WHERE projekat='$id' AND file=0 ORDER BY vrijeme DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	$files = array();

	foreach ($list as $item)
	{
		$files[] = fetchAllRevisionsForFile($item[id]);	
	}
	return $files;	
}

function isUserAuthorOfFile($file, $user)
{
	$result = myquery("SELECT id FROM projekat_file WHERE osoba='$user' AND id='$file' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getFileFirstRevision($id)
{
	$result = myquery("SELECT * FROM projekat_file WHERE id='$id' AND revizija=1 LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getFile($id)
{
	$result = myquery("SELECT * FROM projekat_file WHERE id='$id' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function isThisFileFirstRevision($id)
{
	$result = myquery("SELECT id FROM projekat_file WHERE id='$id' AND revizija=1 LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}

function getFileLastRevision($id)
{
	$result = myquery("SELECT * FROM projekat_file WHERE file='$id' ORDER BY revizija DESC LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	if (empty($list))
	{
		//only one revision
		$list[0] = getFileFirstRevision($id);
	}
	
	return $list[0];
}
function fetchAllRevisionsForFile($id)
{
	$list = array();	
	$result = myquery("SELECT * FROM projekat_file WHERE file='$id' ORDER BY revizija DESC");
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;
	
	$list[] = getFileFirstRevision($id);
	return $list;	
}
function getCountFilesForProjectWithoutRevisions($id)
{
	$result = myquery("SELECT COUNT(id) FROM projekat_file WHERE projekat='$id' AND revizija=1 LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}


function fetchThreadsForProject($id, $offset = 0, $rowsPerPage = 0)
{
	$query = "SELECT t.* FROM bb_tema t WHERE t.projekat='$id' ORDER BY (SELECT p.vrijeme FROM bb_post p WHERE p.id=t.zadnji_post LIMIT 1) DESC ";
	if ($offset == 0 && $rowsPerPage == 0)
	{}
	else
		$query .="LIMIT $offset, $rowsPerPage";
	
	$result = myquery($query);
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	foreach ($list as $key => $item)
	{
		getExtendedInfoForThread($item[id], &$list[$key]);
	}
	
	return $list;	
}
function getExtendedInfoForThread($id, &$list)
{
	$result = myquery("SELECT p.naslov, t.prvi_post, t.zadnji_post FROM bb_post p, bb_tema t WHERE t.id='$id' AND p.tema='$id' AND p.id=t.prvi_post LIMIT 1");
	$row = mysql_fetch_assoc($result);
	$list['naslov'] = $row[naslov];
	
	
	$list['broj_odgovora'] = getCountRepliesToFirstPostInThread($id);
	$list['prvi_post'] = getPostInfoForThread($id, $row[prvi_post]);
	$list['zadnji_post'] = getPostInfoForThread($id, $row[zadnji_post]);
}
function getThreadAndPosts($thread)
{
	$result = myquery("SELECT * FROM bb_tema WHERE id='$thread' LIMIT 1");
	$row = mysql_fetch_assoc($result);
	
	if ($row == false || mysql_num_rows($result) == 0)
		return array();
	
	$item = $row;
	
	getExtendedInfoForThread($thread, &$item);
	
	$item[posts] = getPostsInThread($thread);
	
	return $item;
}

function getPostsInThread($id)
{
	$result = myquery("SELECT * FROM bb_post WHERE tema='$id' ORDER BY vrijeme ASC");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;

	foreach ($list as $key => $item)
	{		
		$result = myquery("SELECT tekst FROM bb_post_text WHERE post='$item[id]' LIMIT 1");
		$row = mysql_fetch_assoc($result);
		
		$list[$key]['tekst'] = $row['tekst'];
		$list[$key]['osoba'] = getOsobaInfoForPost($item[id]);		
	}
	
	return $list;
}


function getCountThreadsForProject($id)
{
	$result = myquery("SELECT COUNT(id) FROM bb_tema WHERE projekat='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function incrementThreadViewCount($thread)
{
	$result = myquery("UPDATE bb_tema SET pregleda=pregleda+1 WHERE id='$thread' LIMIT 1");
}


function getCountPostsInThread($id)
{
	$result = myquery("SELECT COUNT(id) FROM bb_post WHERE tema='$id' LIMIT 1");
	$row = mysql_fetch_row($result);
	$row = $row[0];
	
	return $row;
}

function getCountRepliesToFirstPostInThread($id)
{
	$nPosts = getCountPostsInThread($id);
	
	if ($nPosts == 0)
		return 0;
	else
		return $nPosts - 1;
}

function getPostInfoForThread($thread, $post)
{
	$result = myquery("SELECT * FROM bb_post WHERE tema='$thread' AND id='$post' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	$list[0][osoba] = getOsobaInfoForPost($post);
	
	return $list[0];
	
}

function getPost($id)
{
	$result = myquery("SELECT p.*, t.tekst FROM bb_post p, bb_post_text t WHERE p.id='$id' AND p.id=t.post LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function getOsobaInfoForPost($post)
{
	$result = myquery("SELECT o.ime, o.prezime FROM osoba o, bb_post p WHERE p.osoba=o.id  AND p.id='$post' LIMIT 1");
	$list = array();
	while ($row = mysql_fetch_assoc($result))
		$list[] = $row;	
	mysql_free_result($result);
	
	return $list[0];
}

function isUserAuthorOfPost($post, $user)
{
	$result = myquery("SELECT id FROM bb_post WHERE osoba='$user' AND id='$post' LIMIT 1");
	if (mysql_num_rows($result) > 0)
		return true;
	return false;
}





/**********************
projektneStrane
END
**********************/


?>