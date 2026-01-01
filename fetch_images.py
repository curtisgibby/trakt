import json
import os
import tvdb_v4_official
from dotenv import load_dotenv

load_dotenv()

TVDB_API_KEY = os.getenv('TVDB_API_KEY')

def main():
    # Initialize TVDB client
    tvdb = tvdb_v4_official.TVDB(TVDB_API_KEY)

    # First, let's see what artwork types are available
    artwork_types = tvdb.get_artwork_types()
    # print("Available artwork types:")
    # for art_type in artwork_types:
    #     print(f"  {art_type['id']}: {art_type['name']} ({art_type.get('recordType', 'N/A')})")

    # Find the banner type ID
    banner_type_id = None
    for art_type in artwork_types:
        if art_type['name'].lower() == 'banner':
            banner_type_id = art_type['id']
            break

    if banner_type_id:
        print(f"\nBanner type ID: {banner_type_id}")
    else:
        print("\nWarning: Could not find 'banner' artwork type")

    # Load shows
    with open('shows.json') as f:
        shows = json.load(f)

    # Find shows that need images
    shows_needing_images = {
        k: v for k, v in shows.items()
        if v.get('tvdb_id') and not v.get('image')
    }

    print(f"\nFound {len(shows_needing_images)} show(s) needing images")

    # Fetch images for each show (try eng, then spa, then any language)
    languages = ['eng', 'spa', None]  # None = all languages

    for show_id, show_data in shows_needing_images.items():
        tvdb_id = show_data['tvdb_id']
        image_url = None

        for lang in languages:
            try:
                artworks = tvdb.get_series_artworks(tvdb_id, lang=lang, type=banner_type_id)
                if artworks and 'artworks' in artworks:
                    banners = [a for a in artworks['artworks'] if a.get('type') == banner_type_id]
                    if banners:
                        # Get the highest scored banner
                        best_banner = max(banners, key=lambda x: x.get('score', 0))
                        image_url = best_banner.get('image', '')
                        lang_label = lang or 'any'
                        print(f"  {show_data['title']}: [{lang_label}] {image_url}")
                        break
            except Exception as e:
                print(f"  {show_data['title']}: Error ({lang}) - {e}")

        if image_url:
            # Strip protocol from URL
            image_url = image_url.replace('https://', '').replace('http://', '')
            shows[show_id]['image'] = image_url
        else:
            print(f"  {show_data['title']}: No banners found in any language")

    # Save updated shows, sorted numerically by Trakt ID
    # Reorder keys: title, image, tmdb_id, tvdb_id
    sorted_shows = {}
    for k in sorted(shows.keys(), key=int):
        show = shows[k]
        sorted_shows[k] = {
            'title': show.get('title'),
            'image': show.get('image', ''),
            'tmdb_id': show.get('tmdb_id'),
            'tvdb_id': show.get('tvdb_id')
        }
    with open('shows.json', 'w') as f:
        json.dump(sorted_shows, f, indent=4)

    print("\nUpdated shows.json")

if __name__ == '__main__':
    main()
