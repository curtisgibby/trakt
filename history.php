<?php
define('START_YEAR', 2014);
date_default_timezone_set('America/Denver');
$startTime = strtotime('January 1');
if (!empty($_GET['start_date'])) {
	$startTime = strtotime($_GET['start_date']);
}
$startDate = date('j M Y', $startTime);
$endTime = strtotime('11:59pm December 31');
if (!empty($_GET['end_date'])) {
	$endTime = strtotime($_GET['end_date']);
}
$endDate = date('j M Y', $endTime);
$historyFile = 'history.json';
$showsFile = 'shows.json';
$events = json_decode(file_get_contents($historyFile), true);
$shows = json_decode(file_get_contents($showsFile), true);
$movieCount = $episodeCount = 0;
$showTotals = array();
foreach ($events['movies'] as $movieId => $movie) {
	$watchTime = $movie[0];
	if ($watchTime < $startTime || $watchTime > $endTime) {
		continue;
	}
	$movieCount++;
}
foreach ($events['shows'] as $showId => $show) {
	foreach ($show['e'] as $episode) {
		$watchTime = $episode[0];
		if ($watchTime < $startTime || $watchTime > $endTime) {
			continue;
		}
		$episodeCount++;
		if (!isset($showTotals[$showId])) {
			$showTotals[$showId] = 0;
		}
		$showTotals[$showId]++;
	}
}
arsort($showTotals);
$eventCount = count($events);
$pageTitle = "Trakt History - $startDate to $endDate";
?>
<html>
	<head>
		<title><?php echo $pageTitle; ?></title>
		<link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
	</head>
	<body>
		
		<?php
		echo "<h1>$pageTitle</h1>";
		echo("<P>Movie Count : " . $movieCount);
		echo("<P>TV Episode Count : " . $episodeCount);
		echo "<table class='table table-striped'><thead><tr>
			<th>ID</th>
			<th>Title</th>
			<th>Image</th>
			<th>Watch Count</th>
		</tr></thead>";
		foreach ($showTotals as $showId => $watchCount) {
			$showImage = 'n/a';
			$showTitle = 'Unknown';
			if (array_key_exists($showId, $shows)) {
				$showTitle = $shows[$showId]['title'];
				$showTitle = '<a href=\'https://duckduckgo.com/?q=\\"' . urlencode($showTitle) . '"+inurl%3Aseries+site%3Athetvdb.com\'>' . $showTitle . '</a>';
				if (!empty($shows[$showId]['image'])) {
					$showImage = '<img src="https://images.weserv.nl/?url=thetvdb.com/banners/graphical/' . $shows[$showId]['image'] . '.jpg&h=50">';
				}
			}
			echo "<tr>
			<td><a href='http://trakt.tv/shows/" . $showId . "'>" . $showId . "</a></td>
			<td>" . $showTitle . "</td>
			<td>" . $showImage . "</td>
			<td>" . $watchCount . "</td>
		</tr>";
		}
		?>
		</table>

		<p><a href="https://trakt.tv/users/me/history.json">Trakt History</a></p>
		<ul class="list-inline">
		<?php
		foreach (range(START_YEAR, date('Y')) as $year) {
			echo '<li><a href="/history.php?start_date=' . $year . '-01-01&end_date=' . ($year + 1) . '-01-01">' . $year . '</a></li>';
		}
		?>
		</ul>
	</body>
</html>
