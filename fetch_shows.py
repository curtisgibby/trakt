import json
import os
import trakt
import trakt.core
from dotenv import load_dotenv
from trakt.tv import TVShow

load_dotenv()

# Fixes outdated API URL in PyTrakt
trakt.core.BASE_URL = 'https://api.trakt.tv/'

# Use device auth (for CLI apps)
trakt.core.AUTH_METHOD = trakt.core.DEVICE_AUTH

# Load credentials from .env
CLIENT_ID = os.getenv('TRAKT_CLIENT_ID')
CLIENT_SECRET = os.getenv('TRAKT_CLIENT_SECRET')


def main():
    # Load history and existing shows
    with open('history.json') as f:
        history = json.load(f)
    with open('shows.json') as f:
        shows = json.load(f)

    # Find show IDs in history that aren't in shows.json
    history_show_ids = set(history['shows'].keys())
    known_show_ids = set(shows.keys())
    unknown_show_ids = history_show_ids - known_show_ids

    if not unknown_show_ids:
        print("No unknown shows to fetch.")
        return

    print(f"\nFound {len(unknown_show_ids)} unknown show(s). Fetching from Trakt...")

    # Load cached credentials, or prompt for device auth if not found
    trakt.core.load_config()
    if not trakt.core.OAUTH_TOKEN:
        trakt.init(client_id=CLIENT_ID, client_secret=CLIENT_SECRET, store=True)

    # Fetch unknown shows
    for show_id in unknown_show_ids:
        try:
            show = TVShow(show_id)
            shows[show_id] = {
                'title': show.title,
                'tmdb_id': show.tmdb,
                'tvdb_id': show.tvdb
            }
            print(f"  {show_id}: {show.title} (tmdb: {show.tmdb}, tvdb: {show.tvdb})")
        except Exception as e:
            print(f"  {show_id}: Error - {e}")

    # Save updated shows, sorted numerically by Trakt ID
    sorted_shows = {k: shows[k] for k in sorted(shows.keys(), key=int)}
    with open('shows.json', 'w') as f:
        json.dump(sorted_shows, f, indent=4)

    print("\nUpdated shows.json")

if __name__ == '__main__':
    main()
