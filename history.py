import json
from datetime import datetime

with open('history.json') as f:
    events = json.load(f)
with open('shows.json') as f:
    shows = json.load(f)

startDateString = "2025-01-01T00:00:00"
startDate = datetime.strptime(startDateString, '%Y-%m-%dT%H:%M:%S')

endDateString = "2026-01-01T00:00:00"
endDate = datetime.strptime(endDateString, '%Y-%m-%dT%H:%M:%S')

episodeCount = movieCount = 0
viewsByShow = {}

for movieId in events['movies']:
    movieView = events['movies'][movieId]
    movieViewDatetime = datetime.fromtimestamp(movieView[0])
    if startDate <= movieViewDatetime <= endDate:
        movieCount += 1

for showId in events['shows']:
    showViews = events['shows'][showId]
    show = shows.get(showId)
    for episodeId in showViews['e']:
        episodeViews = showViews['e'][episodeId]
        episodeViewDatetime = datetime.fromtimestamp(episodeViews[0])
        if startDate <= episodeViewDatetime <= endDate:
            if showId in viewsByShow:
                viewsByShow[showId] += 1
            else:
                viewsByShow[showId] = 1
            episodeCount += 1

print 'movieCount: {}'.format(movieCount)
print 'episodeCount: {}'.format(episodeCount)

sortedViewsByShow = sorted(viewsByShow.items(), key=lambda x:x[1], reverse=True)
rank = 0
print("{: >5} {: <35} {: >5}".format("Rank", "Show Title", "View Count"))
print("{: >5} {: <35} {: >5}".format("----", "----------", "----------"))
for showId, showViewCount in sortedViewsByShow:
    if showId in shows:
        showTitle = shows[showId]['title']
    else:
        showTitle = 'Unknown Show: https://trakt.tv/shows/' + showId

    rank += 1
    print("{: >5} {: <35} {: >5}".format(rank, showTitle, showViewCount))
