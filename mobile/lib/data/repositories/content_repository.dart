import '../../core/network/api_client.dart';
import '../models/models.dart';

class ContentRepository {
  ContentRepository(this._api);

  final ApiClient _api;

  dynamic _unwrap(dynamic payload) {
    if (payload is Map && payload['data'] != null) return payload['data'];
    return payload;
  }

  Future<HomePayload> fetchHome() async {
    final data = JsonMap.asMap(_unwrap(await _api.get('/home')));
    return HomePayload.fromJson(data);
  }

  Future<BrandSettings> fetchSettings() async {
    return BrandSettings.fromJson(JsonMap.asMap(_unwrap(await _api.get('/settings'))));
  }

  Future<List<ServiceItem>> fetchServices() async {
    final data = _unwrap(await _api.get('/services'));
    return JsonMap.asList(data).map((e) => ServiceItem.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<ServiceItem> fetchService(String slug) async {
    return ServiceItem.fromJson(JsonMap.asMap(_unwrap(await _api.get('/services/$slug'))));
  }

  Future<List<PackageItem>> fetchPackages() async {
    final data = _unwrap(await _api.get('/service-packages'));
    return JsonMap.asList(data).map((e) => PackageItem.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<List<GalleryItem>> fetchGallery({String? category}) async {
    final query = <String, dynamic>{};
    if (category != null && category.isNotEmpty) query['category'] = category;
    final data = _unwrap(await _api.get('/gallery', query: query.isEmpty ? null : query));
    return JsonMap.asList(data).map((e) => GalleryItem.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<List<GalleryCategory>> fetchGalleryCategories() async {
    final data = _unwrap(await _api.get('/gallery/categories'));
    return JsonMap.asList(data).map((e) => GalleryCategory.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<GalleryItem> fetchGalleryItem(String slug) async {
    return GalleryItem.fromJson(JsonMap.asMap(_unwrap(await _api.get('/gallery/$slug'))));
  }

  Future<List<BlogItem>> fetchBlog() async {
    final data = _unwrap(await _api.get('/blog'));
    return JsonMap.asList(data).map((e) => BlogItem.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<BlogItem> fetchBlogPost(String slug) async {
    return BlogItem.fromJson(JsonMap.asMap(_unwrap(await _api.get('/blog/$slug'))));
  }

  Future<List<FaqItem>> fetchFaqs() async {
    final data = _unwrap(await _api.get('/faqs'));
    return JsonMap.asList(data).map((e) => FaqItem.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<List<TestimonialItem>> fetchTestimonials() async {
    final data = _unwrap(await _api.get('/testimonials'));
    return JsonMap.asList(data).map((e) => TestimonialItem.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<List<EventTypeItem>> fetchEventTypes() async {
    final data = _unwrap(await _api.get('/event-types'));
    return JsonMap.asList(data).map((e) => EventTypeItem.fromJson(JsonMap.asMap(e))).toList();
  }

  Future<EnquiryResult> submitEnquiry({
    required String name,
    required String mobile,
    String? email,
    required EventTypeItem eventType,
    required String eventLocation,
    required String eventDate,
    String? budget,
    String? notes,
    String source = 'slider',
  }) async {
    final messageParts = <String>[
      'Event Type: ${eventType.name}',
      'Event Location: $eventLocation',
      'Event Date: $eventDate',
      if (budget != null && budget.trim().isNotEmpty) 'Budget: ${budget.trim()}',
      if (notes != null && notes.trim().isNotEmpty) notes.trim(),
    ];
    final raw = await _api.post('/contact', body: {
      'name': name.trim(),
      'mobile': mobile.replaceAll(RegExp(r'\D'), ''),
      'email': email?.trim(),
      'subject': 'Slider inquiry: ${eventType.name}',
      'message': messageParts.join('\n'),
      'event_type_id': eventType.id,
      'service_interested': eventType.name,
      'event_date': eventDate,
      'event_location': eventLocation.trim(),
      'budget': (budget == null || budget.trim().isEmpty) ? null : budget.trim(),
      'source': source,
    });
    return EnquiryResult.fromJson(JsonMap.asMap(raw));
  }

  Future<String?> reverseGeocode({required double lat, required double lng}) async {
    final raw = await _api.get('/geo/reverse', query: {'lat': lat, 'lng': lng});
    final data = JsonMap.asMap(_unwrap(raw));
    return JsonMap.str(data['label']);
  }

  Future<List<int>> downloadGalleryItem(int id) {
    return _api.getBytes('/gallery/items/$id/download');
  }
}
