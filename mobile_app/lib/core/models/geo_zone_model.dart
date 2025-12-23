import 'package:google_maps_flutter/google_maps_flutter.dart';

class GeoZoneModel {
  final int id;
  final String name;
  final String type; // allowed, forbidden, parking
  final String color; // hex color like #00C853
  final List<LatLng> polygon;

  GeoZoneModel({
    required this.id,
    required this.name,
    required this.type,
    required this.color,
    required this.polygon,
  });

  factory GeoZoneModel.fromJson(Map<String, dynamic> json) {
    final List<dynamic> poly = json['polygon'] ?? [];

    final points = poly.map<LatLng>((p) {
      // Support {lat: .., lng: ..} or [lat, lng]
      final lat = (p is Map)
          ? (p['lat'] as num?)?.toDouble()
          : (p is List && p.isNotEmpty)
              ? (p[0] as num?)?.toDouble()
              : null;
      final lng = (p is Map)
          ? (p['lng'] as num?)?.toDouble()
          : (p is List && p.length > 1)
              ? (p[1] as num?)?.toDouble()
              : null;
      return LatLng(lat ?? 0.0, lng ?? 0.0);
    }).toList();

    return GeoZoneModel(
      id: json['id'] as int,
      name: json['name'] ?? '',
      type: json['type'] ?? 'allowed',
      color: json['color'] ?? '#00C853',
      polygon: points,
    );
  }
}


