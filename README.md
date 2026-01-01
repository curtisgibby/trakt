# Trakt History Viewer

A simple PHP web app to view your Trakt.tv watch history with links to Trakt, TVDB, TMDB, and IMDb.

## Setup

### 1. Download Your Trakt History

1. Log in to [Trakt.tv](https://trakt.tv)
2. Download your history from: https://trakt.tv/users/me/history.json
3. Save it as `history.json` in this directory

### 2. Create API Credentials

#### Trakt API
1. Go to https://trakt.tv/oauth/applications/new
2. Create a new application
3. Note your Client ID and Client Secret

#### TVDB API
1. Go to https://thetvdb.com/dashboard/account/apikey
2. Create a new API key

### 3. Configure Environment

Create a `.env` file with your credentials:

```
TRAKT_CLIENT_ID=your_trakt_client_id
TRAKT_CLIENT_SECRET=your_trakt_client_secret
TVDB_API_KEY=your_tvdb_api_key
```

### 4. Install Python Dependencies

```bash
pip install PyTrakt python-dotenv tvdb_v4_official
```

## Updating Data

### Fetch New Shows

When you have new shows in your history that aren't in `shows.json`:

```bash
python3 fetch_shows.py
```

On first run, you'll be prompted to authenticate with Trakt via device auth.

### Fetch Banner Images

To fetch banner images from TVDB for shows missing them:

```bash
python3 fetch_images.py
```

## Viewing History

Start a local PHP server:

```bash
php -S localhost:8000
```

Then open http://localhost:8000/history.php in your browser.
