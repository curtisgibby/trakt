<?php
define('START_YEAR', 2014);
date_default_timezone_set('America/Denver');

function renderShowTable($showTotals, $shows) {
	echo "<table class='table table-striped'><thead><tr>
		<th>Title</th>
		<th>Image</th>
		<th>Links</th>
		<th>Watch Count</th>
	</tr></thead>";
	foreach ($showTotals as $showId => $watchCount) {
		$showImage = 'n/a';
		$showTitle = 'Unknown';
		$links = '';
		if (array_key_exists($showId, $shows)) {
			$showData = $shows[$showId];
			$showTitle = $showData['title'];
			if (!empty($showData['image'])) {
				$showImage = '<img src="https://images.weserv.nl/?url=' . $showData['image'] . '&h=50">';
			}
			// Trakt link
			$links .= '<a href="https://trakt.tv/shows/' . $showId . '" target="_blank"><img src="https://trakt.tv/assets/logos/logomark.square.gradient-b644b16c38ff775861b4b1f58c1230f6a097a2466ab33ae00445a505c33fcb91.svg" height="20"></a> ';
			// TVDB link
			if (!empty($showData['tvdb_id'])) {
				$links .= '<a href="https://www.thetvdb.com/dereferrer/series/' . $showData['tvdb_id'] . '" target="_blank"><img src="https://thetvdb.com/images/logo.svg" height="20"></a> ';
			}
			// TMDB link
			if (!empty($showData['tmdb_id'])) {
				$links .= '<a href="https://www.themoviedb.org/tv/' . $showData['tmdb_id'] . '" target="_blank"><img src="https://www.themoviedb.org/assets/2/v4/logos/v2/blue_square_2-d537fb228cf3ded904ef09b136fe3fec72548ebc1fea3fbbd1ad9e36364db38b.svg" height="20"></a> ';
			}
			// IMDb link
			if (!empty($showData['imdb_id'])) {
				$links .= '<a href="https://www.imdb.com/title/' . $showData['imdb_id'] . '" target="_blank"><img src="https://upload.wikimedia.org/wikipedia/commons/6/69/IMDB_Logo_2016.svg" height="20"></a>';
			}
		}
		echo "<tr>
		<td>" . $showTitle . '</td>
		<td>' . $showImage . '</td>
		<td>' . $links . '</td>
		<td>' . $watchCount . '</td>
	</tr>';
	}
	echo '</table>';
}
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
$showTotals = [];
$showsWithViewsBeforeStart = [];
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
		// Track shows with any views before the start date
		if ($watchTime < $startTime) {
			$showsWithViewsBeforeStart[$showId] = true;
		}
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
// Shows started this period (no views before start date)
$newShows = array_filter($showTotals, function($_, $showId) use ($showsWithViewsBeforeStart) {
	return !isset($showsWithViewsBeforeStart[$showId]);
}, ARRAY_FILTER_USE_BOTH);
$eventCount = count($events);
$pageTitle = "Trakt History - $startDate to $endDate";
?>
<html>
	<head>
		<title><?php echo $pageTitle; ?></title>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
		<style>
			.table {
				--bs-table-bg: #F2F2F2;
				--bs-table-striped-bg: #D7D7D7;
			}
		</style>
	</head>
	<body class="container">
		
		<?php
		echo '<h1>' . $pageTitle . '</h1>';
		echo '<p>Movie Count : ' . $movieCount . '</p>';
		echo '<p>TV Episode Count : ' . $episodeCount . '</p>';
		renderShowTable($showTotals, $shows);
		?>

		<h2>Shows Started This Year</h2>
		<?php renderShowTable($newShows, $shows); ?>

		<p><a href="https://trakt.tv/users/me/history.json">Trakt History</a></p>
		<ul class="list-inline">
		<?php
		foreach (range(START_YEAR, date('Y')) as $year) {
			echo '<li class="list-inline-item"><a href="history.php?start_date=' . $year . '-01-01&end_date=' . ($year + 1) . '-01-01">' . $year . '</a></li>';
		}
		?>
		</ul>
	</body>
</html>
