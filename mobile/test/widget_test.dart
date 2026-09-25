import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'package:balaji_royal_events/data/models/models.dart';
import 'package:balaji_royal_events/data/providers.dart';
import 'package:balaji_royal_events/main.dart';

void main() {
  testWidgets('Splash renders Balaji Royal Events branding', (tester) async {
    final home = HomePayload.fromJson({
      'settings': {
        'company': {'name': 'Balaji Royal Events'},
        'brand': {},
        'theme': {'primary_color': '#f15b22'},
        'contact': {'phone': '+91-9462577065'},
        'social': {},
      },
      'hero': [],
      'featured_services': [],
      'featured_gallery': [],
      'testimonials': [],
      'featured_blog': [],
      'featured_faqs': [],
      'event_types': [],
      'office_locations': [],
      'gallery_categories': [],
      'external_media': [],
    });

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          homeProvider.overrideWith((ref) async => home),
        ],
        child: const BalajiRoyalEventsApp(),
      ),
    );
    expect(find.text('Balaji Royal Events'), findsWidgets);
    await tester.pump(const Duration(milliseconds: 500));
    await tester.pump();
    await tester.pump(const Duration(milliseconds: 200));
    expect(find.text('Balaji Royal Events'), findsWidgets);
  });
}
