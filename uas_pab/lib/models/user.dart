import 'package:cloud_firestore/cloud_firestore.dart';

class User {
  final String name;
  final String email;
  final String password;
  final String instagram;
  final String facebook;
  final String tiktok;
  final String? photo;

  User({
    required this.name,
    required this.email,
    required this.password,
    required this.instagram,
    required this.facebook,
    required this.tiktok,
    required this.photo,
  });

  // Menambahkan properti Instagram, Facebook, dan TikTok saat mengonversi data dari Firestore
  factory User.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;
    return User(
      name: data['name'] ?? '',
      email: data['email'] ?? '',
      password: data['password'] ?? '',
      instagram: data['instagram'] ?? '',
      facebook: data['facebook'] ?? '',
      tiktok: data['tiktok'] ?? '',
      photo: data['photo'],
    );
  }

  // Menambahkan properti Instagram, Facebook, dan TikTok saat mengonversi data ke map untuk disimpan di Firestore
  Map<String, dynamic> toMap() {
    return {
      'name': name,
      'email': email,
      'password': password,
      'instagram': instagram,
      'facebook': facebook,
      'tiktok': tiktok,
    };
  }
}
