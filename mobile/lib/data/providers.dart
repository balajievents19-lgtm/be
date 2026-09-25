import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/network/api_client.dart';
import 'models/models.dart';
import 'repositories/auth_repository.dart';
import 'repositories/content_repository.dart';

final apiClientProvider = Provider<ApiClient>((ref) => ApiClient());

final contentRepositoryProvider = Provider<ContentRepository>((ref) {
  return ContentRepository(ref.watch(apiClientProvider));
});

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(ref.watch(apiClientProvider));
});

final homeProvider = FutureProvider<HomePayload>((ref) {
  return ref.watch(contentRepositoryProvider).fetchHome();
});

final servicesProvider = FutureProvider<List<ServiceItem>>((ref) {
  return ref.watch(contentRepositoryProvider).fetchServices();
});

final packagesProvider = FutureProvider<List<PackageItem>>((ref) {
  return ref.watch(contentRepositoryProvider).fetchPackages();
});

final galleryProvider = FutureProvider.family<List<GalleryItem>, String?>((ref, category) {
  return ref.watch(contentRepositoryProvider).fetchGallery(category: category);
});

final galleryCategoriesProvider = FutureProvider<List<GalleryCategory>>((ref) {
  return ref.watch(contentRepositoryProvider).fetchGalleryCategories();
});

final serviceDetailProvider = FutureProvider.family<ServiceItem, String>((ref, slug) {
  return ref.watch(contentRepositoryProvider).fetchService(slug);
});

final galleryDetailProvider = FutureProvider.family<GalleryItem, String>((ref, slug) {
  return ref.watch(contentRepositoryProvider).fetchGalleryItem(slug);
});

final blogProvider = FutureProvider<List<BlogItem>>((ref) {
  return ref.watch(contentRepositoryProvider).fetchBlog();
});

final faqsProvider = FutureProvider<List<FaqItem>>((ref) {
  return ref.watch(contentRepositoryProvider).fetchFaqs();
});

final testimonialsProvider = FutureProvider<List<TestimonialItem>>((ref) {
  return ref.watch(contentRepositoryProvider).fetchTestimonials();
});

final eventTypesProvider = FutureProvider<List<EventTypeItem>>((ref) {
  return ref.watch(contentRepositoryProvider).fetchEventTypes();
});

final blogDetailProvider = FutureProvider.family<BlogItem, String>((ref, slug) {
  return ref.watch(contentRepositoryProvider).fetchBlogPost(slug);
});

final oauthProvidersProvider = FutureProvider<OAuthProviderStatus>((ref) {
  return ref.watch(authRepositoryProvider).oauthProviders();
});
