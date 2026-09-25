import 'package:flutter_test/flutter_test.dart';

import 'package:balaji_royal_events/core/utils/html_plain_text.dart';
import 'package:balaji_royal_events/core/utils/media_url.dart';
import 'package:balaji_royal_events/data/models/models.dart';

void main() {
  test('MediaUrl resolves protected-media display paths against the live origin', () {
    final url = MediaUrl.resolve('/protected-media/abc');
    expect(url, 'https://www.balajiroyalevents.com/protected-media/abc');
    expect(MediaUrl.isProtectedDisplayPath(url), isTrue);
  });

  test('MediaUrl never invents a watch URL for YouTube', () {
    expect(MediaUrl.youtubeNocookieEmbed('dQw4w9wg'), 'https://www.youtube-nocookie.com/embed/dQw4w9wg');
    expect(MediaUrl.youtubeNocookieEmbed(null), isNull);
  });

  test('YouTube player HTML only wraps nocookie embed URLs', () {
    final html = MediaUrl.youtubePlayerHtml('https://www.youtube-nocookie.com/embed/abc123');
    expect(html, contains('youtube-nocookie.com/embed/abc123'));
    expect(html, contains('origin='));
    expect(html, contains('balajiroyalevents.com'));
    expect(html, isNot(contains('/watch')));
    expect(MediaUrl.youtubePlayerHtml('https://www.youtube.com/watch?v=abc123'), isNull);
  });

  test('GalleryItem maps YouTube embed without public watch URL', () {
    final item = GalleryItem.fromJson({
      'id': 1,
      'slug': 'mehndi',
      'title': 'Mehndi',
      'media_type': 'video',
      'video_source': 'youtube',
      'video_id': 'abc123',
      'youtube_url': null,
      'embed': {
        'embed_url': 'https://www.youtube-nocookie.com/embed/abc123',
        'open_url': null,
        'cta_label': null,
      },
    });
    expect(item.isYoutube, isTrue);
    expect(item.embedUrl, contains('youtube-nocookie.com'));
  });

  test('PackageItem hides pricing when backend omits it', () {
    final package = PackageItem.fromJson({'id': 1, 'name': 'Royal', 'slug': 'royal'});
    expect(package.displayPrice, isNull);
  });

  test('HomePayload parses featured services', () {
    final home = HomePayload.fromJson({
      'settings': {
        'company': {'name': 'Balaji Royal Events'},
        'brand': {},
        'theme': {'primary_color': '#f15b22'},
        'contact': {'phone': '+91-9462577065'},
        'social': {},
      },
      'hero': [
        {'id': 1, 'title': 'Celebrate', 'mobile_image': '/protected-media/x'}
      ],
      'featured_services': [
        {'id': 1, 'name': 'Wedding & Event Planning', 'slug': 'wedding'}
      ],
      'featured_gallery': [],
      'testimonials': [],
      'featured_blog': [],
      'featured_faqs': [],
      'event_types': [
        {'id': 1, 'name': 'Wedding', 'slug': 'wedding'}
      ],
      'office_locations': [],
      'gallery_categories': [],
      'external_media': [
        {
          'id': 9,
          'title': 'Royal Film',
          'provider': 'youtube',
          'media_type': 'video',
          'embed_url': 'https://www.youtube-nocookie.com/embed/abc123',
        }
      ],
    });
    expect(home.settings.companyName, 'Balaji Royal Events');
    expect(home.services.first.name, 'Wedding & Event Planning');
    expect(home.hero.first.image, '/protected-media/x');
    expect(home.externalMedia.first.isYoutube, isTrue);
    expect(home.externalMedia.first.embedUrl, contains('youtube-nocookie.com'));
  });

  test('htmlToPlainText strips CMS paragraph tags', () {
    expect(
      htmlToPlainText('<p>Hello</p><p>World</p>'),
      'Hello\nWorld',
    );
  });
}
