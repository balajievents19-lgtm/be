class JsonMap {
  const JsonMap._();

  static Map<String, dynamic> asMap(dynamic value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) return Map<String, dynamic>.from(value);
    return const {};
  }

  static List<dynamic> asList(dynamic value) {
    if (value is List) return value;
    return const [];
  }

  static String? str(dynamic value) {
    if (value == null) return null;
    final s = value.toString().trim();
    return s.isEmpty ? null : s;
  }

  static int? integer(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '');
  }

  static double? decimal(dynamic value) {
    if (value is double) return value;
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '');
  }

  static bool flag(dynamic value) => value == true || value == 1 || value == '1';
}

class HeroSlide {
  const HeroSlide({
    required this.id,
    this.title,
    this.titleHighlight,
    this.subtitle,
    this.buttonText,
    this.desktopImage,
    this.mobileImage,
  });

  factory HeroSlide.fromJson(Map<String, dynamic> json) {
    return HeroSlide(
      id: JsonMap.integer(json['id']) ?? 0,
      title: JsonMap.str(json['title']),
      titleHighlight: JsonMap.str(json['title_highlight']),
      subtitle: JsonMap.str(json['subtitle']),
      buttonText: JsonMap.str(json['button_text']),
      desktopImage: JsonMap.str(json['desktop_image']),
      mobileImage: JsonMap.str(json['mobile_image']),
    );
  }

  final int id;
  final String? title;
  final String? titleHighlight;
  final String? subtitle;
  final String? buttonText;
  final String? desktopImage;
  final String? mobileImage;

  String? get image => mobileImage ?? desktopImage;
}

class ServiceItem {
  const ServiceItem({
    required this.id,
    required this.name,
    required this.slug,
    this.shortDescription,
    this.fullDescription,
    this.featuredImage,
    this.bannerImage,
    this.icon,
    this.galleryImages = const [],
    this.relatedPackages = const [],
  });

  factory ServiceItem.fromJson(Map<String, dynamic> json) {
    return ServiceItem(
      id: JsonMap.integer(json['id']) ?? 0,
      name: JsonMap.str(json['name']) ?? '',
      slug: JsonMap.str(json['slug']) ?? '',
      shortDescription: JsonMap.str(json['short_description']),
      fullDescription: JsonMap.str(json['full_description']),
      featuredImage: JsonMap.str(json['featured_image']),
      bannerImage: JsonMap.str(json['banner_image']),
      icon: JsonMap.str(json['icon']),
      galleryImages: JsonMap.asList(json['gallery_images']).map((e) => e.toString()).toList(),
    );
  }

  final int id;
  final String name;
  final String slug;
  final String? shortDescription;
  final String? fullDescription;
  final String? featuredImage;
  final String? bannerImage;
  final String? icon;
  final List<String> galleryImages;
  final List<PackageItem> relatedPackages;
}

class PackageItem {
  const PackageItem({
    required this.id,
    required this.name,
    required this.slug,
    this.summary,
    this.description,
    this.priceLabel,
    this.priceAmount,
    this.currency,
    this.image,
    this.features = const [],
    this.serviceId,
  });

  factory PackageItem.fromJson(Map<String, dynamic> json) {
    final featuresRaw = json['features'];
    List<String> features = const [];
    if (featuresRaw is List) {
      features = featuresRaw.map((e) => e.toString()).where((e) => e.trim().isNotEmpty).toList();
    } else if (featuresRaw is Map) {
      features = featuresRaw.values.map((e) => e.toString()).toList();
    }

    return PackageItem(
      id: JsonMap.integer(json['id']) ?? 0,
      name: JsonMap.str(json['name']) ?? '',
      slug: JsonMap.str(json['slug']) ?? '',
      summary: JsonMap.str(json['summary']),
      description: JsonMap.str(json['description']),
      priceLabel: JsonMap.str(json['price_label']),
      priceAmount: JsonMap.decimal(json['price_amount']),
      currency: JsonMap.str(json['currency']),
      image: JsonMap.str(json['image']),
      features: features,
      serviceId: JsonMap.integer(json['service_id']),
    );
  }

  final int id;
  final String name;
  final String slug;
  final String? summary;
  final String? description;
  final String? priceLabel;
  final double? priceAmount;
  final String? currency;
  final String? image;
  final List<String> features;
  final int? serviceId;

  String? get displayPrice {
    if (priceLabel != null && priceLabel!.trim().isNotEmpty) return priceLabel;
    if (priceAmount != null) {
      final code = currency ?? '';
      return '$code ${priceAmount!.toStringAsFixed(0)}'.trim();
    }
    return null;
  }
}

class GalleryItem {
  const GalleryItem({
    required this.id,
    required this.slug,
    this.title,
    this.mediaType,
    this.image,
    this.thumbnail,
    this.caption,
    this.videoSource,
    this.videoId,
    this.embedUrl,
    this.categoryName,
    this.categorySlug,
    this.downloadAvailable = false,
  });

  factory GalleryItem.fromJson(Map<String, dynamic> json) {
    final embed = JsonMap.asMap(json['embed']);
    final category = JsonMap.asMap(json['category']);
    return GalleryItem(
      id: JsonMap.integer(json['id']) ?? 0,
      slug: JsonMap.str(json['slug']) ?? '',
      title: JsonMap.str(json['title']),
      mediaType: JsonMap.str(json['media_type']),
      image: JsonMap.str(json['image']),
      thumbnail: JsonMap.str(json['thumbnail']),
      caption: JsonMap.str(json['caption']) ?? JsonMap.str(json['description']),
      videoSource: JsonMap.str(json['video_source']),
      videoId: JsonMap.str(json['video_id']),
      embedUrl: JsonMap.str(embed['embed_url']),
      categoryName: JsonMap.str(category['name']),
      categorySlug: JsonMap.str(category['slug']),
      downloadAvailable: JsonMap.flag(json['download_available']),
    );
  }

  final int id;
  final String slug;
  final String? title;
  final String? mediaType;
  final String? image;
  final String? thumbnail;
  final String? caption;
  final String? videoSource;
  final String? videoId;
  final String? embedUrl;
  final String? categoryName;
  final String? categorySlug;
  final bool downloadAvailable;

  bool get isVideo => mediaType == 'video';
  bool get isYoutube => (videoSource ?? '').toLowerCase() == 'youtube';
  String? get preview => thumbnail ?? image;
}

class GalleryCategory {
  const GalleryCategory({required this.id, required this.name, required this.slug});

  factory GalleryCategory.fromJson(Map<String, dynamic> json) {
    return GalleryCategory(
      id: JsonMap.integer(json['id']) ?? 0,
      name: JsonMap.str(json['name']) ?? '',
      slug: JsonMap.str(json['slug']) ?? '',
    );
  }

  final int id;
  final String name;
  final String slug;
}

class EventTypeItem {
  const EventTypeItem({required this.id, required this.name, required this.slug});

  factory EventTypeItem.fromJson(Map<String, dynamic> json) {
    return EventTypeItem(
      id: JsonMap.integer(json['id']) ?? 0,
      name: JsonMap.str(json['name']) ?? '',
      slug: JsonMap.str(json['slug']) ?? '',
    );
  }

  final int id;
  final String name;
  final String slug;

  @override
  bool operator ==(Object other) => other is EventTypeItem && other.id == id;

  @override
  int get hashCode => id.hashCode;
}

class TestimonialItem {
  const TestimonialItem({
    required this.id,
    this.name,
    this.quote,
    this.rating,
    this.avatar,
    this.createdAt,
  });

  factory TestimonialItem.fromJson(Map<String, dynamic> json) {
    return TestimonialItem(
      id: JsonMap.integer(json['id']) ?? 0,
      name: JsonMap.str(json['name']) ?? JsonMap.str(json['client_name']) ?? JsonMap.str(json['author']),
      quote: JsonMap.str(json['quote']) ?? JsonMap.str(json['body']) ?? JsonMap.str(json['review']),
      rating: JsonMap.decimal(json['rating']),
      avatar: JsonMap.str(json['avatar']) ?? JsonMap.str(json['image']),
      createdAt: JsonMap.str(json['created_at']) ?? JsonMap.str(json['published_at']),
    );
  }

  final int id;
  final String? name;
  final String? quote;
  final double? rating;
  final String? avatar;
  final String? createdAt;
}

class BlogItem {
  const BlogItem({
    required this.id,
    required this.slug,
    this.title,
    this.excerpt,
    this.image,
    this.content,
    this.publishedAt,
    this.author,
  });

  factory BlogItem.fromJson(Map<String, dynamic> json) {
    return BlogItem(
      id: JsonMap.integer(json['id']) ?? 0,
      slug: JsonMap.str(json['slug']) ?? '',
      title: JsonMap.str(json['title']),
      excerpt: JsonMap.str(json['excerpt']) ?? JsonMap.str(json['summary']),
      image: JsonMap.str(json['featured_image']) ?? JsonMap.str(json['image']) ?? JsonMap.str(json['thumbnail']),
      content: JsonMap.str(json['content']),
      publishedAt: JsonMap.str(json['published_at']),
      author: JsonMap.str(json['author']),
    );
  }

  final int id;
  final String slug;
  final String? title;
  final String? excerpt;
  final String? image;
  final String? content;
  final String? publishedAt;
  final String? author;
}

class FaqItem {
  const FaqItem({required this.id, this.question, this.answer, this.slug, this.categoryName});

  factory FaqItem.fromJson(Map<String, dynamic> json) {
    final category = JsonMap.asMap(json['category']);
    return FaqItem(
      id: JsonMap.integer(json['id']) ?? 0,
      question: JsonMap.str(json['question']),
      answer: JsonMap.str(json['answer']),
      slug: JsonMap.str(json['slug']),
      categoryName: JsonMap.str(category['name']),
    );
  }

  final int id;
  final String? question;
  final String? answer;
  final String? slug;
  final String? categoryName;
}

class CustomerProfile {
  const CustomerProfile({
    required this.id,
    this.name,
    this.email,
    this.phone,
    this.phoneMasked,
    this.username,
    this.emailVerified = false,
    this.needsEmailVerification = false,
    this.mobileVerified = false,
    this.verifiedForEnquiry = false,
    this.pendingEmail,
    this.emailVerifiedAt,
  });

  factory CustomerProfile.fromJson(Map<String, dynamic> json) {
    return CustomerProfile(
      id: JsonMap.integer(json['id']) ?? 0,
      name: JsonMap.str(json['name']),
      email: JsonMap.str(json['email']),
      phone: JsonMap.str(json['phone']),
      phoneMasked: JsonMap.str(json['phone_masked']),
      username: JsonMap.str(json['username']),
      emailVerified: JsonMap.flag(json['email_verified']),
      needsEmailVerification: JsonMap.flag(json['needs_email_verification']),
      mobileVerified: JsonMap.flag(json['mobile_verified']),
      verifiedForEnquiry: JsonMap.flag(json['verified_for_enquiry']) ||
          (JsonMap.flag(json['email_verified']) && !JsonMap.flag(json['needs_email_verification'])),
      pendingEmail: JsonMap.str(json['pending_email']),
      emailVerifiedAt: JsonMap.str(json['email_verified_at']),
    );
  }

  final int id;
  final String? name;
  final String? email;
  final String? phone;
  final String? phoneMasked;
  final String? username;
  final bool emailVerified;
  final bool needsEmailVerification;
  final bool mobileVerified;
  final bool verifiedForEnquiry;
  final String? pendingEmail;
  final String? emailVerifiedAt;
}

class EnquiryResult {
  const EnquiryResult({this.id, this.message});

  factory EnquiryResult.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map ? JsonMap.asMap(json['data']) : json;
    return EnquiryResult(
      id: JsonMap.integer(data['id']),
      message: JsonMap.str(json['message']) ?? JsonMap.str(data['message']),
    );
  }

  final int? id;
  final String? message;
}

class OfficeLocation {
  const OfficeLocation({
    required this.id,
    this.name,
    this.address,
    this.city,
    this.state,
    this.pincode,
    this.phone,
    this.email,
    this.latitude,
    this.longitude,
    this.mapEmbed,
    this.isPrimary = false,
  });

  factory OfficeLocation.fromJson(Map<String, dynamic> json) {
    return OfficeLocation(
      id: JsonMap.integer(json['id']) ?? 0,
      name: JsonMap.str(json['name']),
      address: JsonMap.str(json['address']),
      city: JsonMap.str(json['city']),
      state: JsonMap.str(json['state']),
      pincode: JsonMap.str(json['pincode']),
      phone: JsonMap.str(json['phone']),
      email: JsonMap.str(json['email']),
      latitude: JsonMap.str(json['latitude']),
      longitude: JsonMap.str(json['longitude']),
      mapEmbed: JsonMap.str(json['map_embed']),
      isPrimary: JsonMap.flag(json['is_primary']),
    );
  }

  final int id;
  final String? name;
  final String? address;
  final String? city;
  final String? state;
  final String? pincode;
  final String? phone;
  final String? email;
  final String? latitude;
  final String? longitude;
  final String? mapEmbed;
  final bool isPrimary;

  String get fullAddress {
    return [address, city, state, pincode].whereType<String>().where((e) => e.isNotEmpty).join(', ');
  }
}

class ContactInfo {
  const ContactInfo({
    this.phone,
    this.whatsapp,
    this.email,
    this.address,
    this.mapEmbed,
  });

  factory ContactInfo.fromJson(Map<String, dynamic> json) {
    return ContactInfo(
      phone: JsonMap.str(json['phone']),
      whatsapp: JsonMap.str(json['whatsapp']),
      email: JsonMap.str(json['email']),
      address: JsonMap.str(json['address']),
      mapEmbed: JsonMap.str(json['google_map_embed']),
    );
  }

  final String? phone;
  final String? whatsapp;
  final String? email;
  final String? address;
  final String? mapEmbed;
}

class SocialLinks {
  const SocialLinks({this.facebook, this.instagram, this.youtube, this.linkedin, this.twitter});

  factory SocialLinks.fromJson(Map<String, dynamic> json) {
    return SocialLinks(
      facebook: JsonMap.str(json['facebook']),
      instagram: JsonMap.str(json['instagram']),
      youtube: JsonMap.str(json['youtube']),
      linkedin: JsonMap.str(json['linkedin']),
      twitter: JsonMap.str(json['twitter']),
    );
  }

  final String? facebook;
  final String? instagram;
  final String? youtube;
  final String? linkedin;
  final String? twitter;

  List<(String, String)> get entries {
    final list = <(String, String)>[];
    if (facebook != null) list.add(('Facebook', facebook!));
    if (instagram != null) list.add(('Instagram', instagram!));
    if (youtube != null) list.add(('YouTube', youtube!));
    if (linkedin != null) list.add(('LinkedIn', linkedin!));
    if (twitter != null) list.add(('X', twitter!));
    return list;
  }
}

class BrandSettings {
  const BrandSettings({
    this.companyName,
    this.tagline,
    this.description,
    this.logo,
    this.primaryColor,
    this.secondaryColor,
    this.aboutVision,
    this.aboutMission,
    this.workingHours,
    required this.contact,
    required this.social,
  });

  factory BrandSettings.fromJson(Map<String, dynamic> json) {
    final company = JsonMap.asMap(json['company']);
    final brand = JsonMap.asMap(json['brand']);
    final theme = JsonMap.asMap(json['theme']);
    final about = JsonMap.asMap(json['about']);
    final business = JsonMap.asMap(json['business']);
    return BrandSettings(
      companyName: JsonMap.str(company['name']),
      tagline: JsonMap.str(company['tagline']),
      description: JsonMap.str(company['description']) ?? JsonMap.str(about['description']),
      logo: JsonMap.str(brand['logo']),
      primaryColor: JsonMap.str(theme['primary_color']),
      secondaryColor: JsonMap.str(theme['secondary_color']),
      aboutVision: JsonMap.str(about['vision']),
      aboutMission: JsonMap.str(about['mission']),
      workingHours: JsonMap.str(business['working_hours']),
      contact: ContactInfo.fromJson(JsonMap.asMap(json['contact'])),
      social: SocialLinks.fromJson(JsonMap.asMap(json['social'])),
    );
  }

  final String? companyName;
  final String? tagline;
  final String? description;
  final String? logo;
  final String? primaryColor;
  final String? secondaryColor;
  final String? aboutVision;
  final String? aboutMission;
  final String? workingHours;
  final ContactInfo contact;
  final SocialLinks social;
}

class HomePayload {
  const HomePayload({
    required this.settings,
    required this.hero,
    required this.services,
    required this.gallery,
    required this.externalMedia,
    required this.testimonials,
    required this.blog,
    required this.faqs,
    required this.eventTypes,
    required this.locations,
    required this.galleryCategories,
  });

  factory HomePayload.fromJson(Map<String, dynamic> json) {
    return HomePayload(
      settings: BrandSettings.fromJson(JsonMap.asMap(json['settings'])),
      hero: JsonMap.asList(json['hero']).map((e) => HeroSlide.fromJson(JsonMap.asMap(e))).toList(),
      services: JsonMap.asList(json['featured_services']).map((e) => ServiceItem.fromJson(JsonMap.asMap(e))).toList(),
      gallery: JsonMap.asList(json['featured_gallery']).map((e) => GalleryItem.fromJson(JsonMap.asMap(e))).toList(),
      externalMedia: JsonMap.asList(json['external_media']).map((e) => ExternalMediaItem.fromJson(JsonMap.asMap(e))).toList(),
      testimonials: JsonMap.asList(json['testimonials']).map((e) => TestimonialItem.fromJson(JsonMap.asMap(e))).toList(),
      blog: JsonMap.asList(json['featured_blog']).map((e) => BlogItem.fromJson(JsonMap.asMap(e))).toList(),
      faqs: JsonMap.asList(json['featured_faqs']).map((e) => FaqItem.fromJson(JsonMap.asMap(e))).toList(),
      eventTypes: JsonMap.asList(json['event_types']).map((e) => EventTypeItem.fromJson(JsonMap.asMap(e))).toList(),
      locations: JsonMap.asList(json['office_locations']).map((e) => OfficeLocation.fromJson(JsonMap.asMap(e))).toList(),
      galleryCategories: JsonMap.asList(json['gallery_categories']).map((e) => GalleryCategory.fromJson(JsonMap.asMap(e))).toList(),
    );
  }

  final BrandSettings settings;
  final List<HeroSlide> hero;
  final List<ServiceItem> services;
  final List<GalleryItem> gallery;
  final List<ExternalMediaItem> externalMedia;
  final List<TestimonialItem> testimonials;
  final List<BlogItem> blog;
  final List<FaqItem> faqs;
  final List<EventTypeItem> eventTypes;
  final List<OfficeLocation> locations;
  final List<GalleryCategory> galleryCategories;
}

class ExternalMediaItem {
  const ExternalMediaItem({
    required this.id,
    this.title,
    this.mediaType,
    this.provider,
    this.url,
    this.embedUrl,
    this.thumbnail,
    this.description,
    this.mode,
  });

  factory ExternalMediaItem.fromJson(Map<String, dynamic> json) {
    return ExternalMediaItem(
      id: JsonMap.integer(json['id']) ?? 0,
      title: JsonMap.str(json['title']),
      mediaType: JsonMap.str(json['media_type']),
      provider: JsonMap.str(json['provider']),
      url: JsonMap.str(json['url']),
      embedUrl: JsonMap.str(json['embed_url']),
      thumbnail: JsonMap.str(json['thumbnail']),
      description: JsonMap.str(json['description']),
      mode: JsonMap.str(json['mode']),
    );
  }

  final int id;
  final String? title;
  final String? mediaType;
  final String? provider;
  final String? url;
  final String? embedUrl;
  final String? thumbnail;
  final String? description;
  final String? mode;

  bool get isYoutube {
    final embed = embedUrl ?? '';
    final host = Uri.tryParse(embed)?.host.toLowerCase() ?? '';
    return (provider ?? '').toLowerCase() == 'youtube' || host.endsWith('youtube-nocookie.com');
  }

  String get typeLabel {
    final p = (provider ?? mediaType ?? 'media').replaceAll('_', ' ');
    if (p.isEmpty) return 'Media';
    return p[0].toUpperCase() + p.substring(1);
  }
}
