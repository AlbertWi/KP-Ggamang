import 'dart:convert';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/material.dart';
import 'package:uas_pab/screens/login_screen.dart'; // pastikan path ini benar
import 'edit_profile.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String name = "Guest";
  String email = "example@gmail.com";
  String instagram = "";
  String facebook = "";
  String tiktok = "";
  String? photoBase64;

  final currentUser = FirebaseAuth.instance.currentUser;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    if (currentUser == null) return;

    final doc = await FirebaseFirestore.instance
        .collection('users')
        .doc(currentUser!.uid)
        .get();

    if (doc.exists) {
      final data = doc.data()!;

      // Coba ambil dari field 'photo' dulu, kalau tidak ada ambil dari 'profileImage'
      final rawPhoto = data['photo'] ?? data['profileImage'];

      // Membersihkan prefix jika ada
      photoBase64 = rawPhoto != null && rawPhoto.contains(',')
          ? rawPhoto.split(',').last
          : rawPhoto;

      setState(() {
        name = data['name'] ?? data['username'] ?? 'Guest';
        email = data['email'] ?? 'example@gmail.com';
        instagram = data['instagram'] ?? '';
        facebook = data['facebook'] ?? '';
        tiktok = data['tiktok'] ?? '';
      });

      // Debug log (opsional)
      print("Loaded photoBase64: ${photoBase64?.substring(0, 30)}...");
    }
  }

  Future<void> _logout() async {
    await FirebaseAuth.instance.signOut();
    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (_) => const LoginScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Personal Profile',
            style: TextStyle(fontWeight: FontWeight.w600, fontSize: 20)),
        centerTitle: true,
        backgroundColor: Colors.green.shade200,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            Container(
              color: Colors.white,
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 50,
                    backgroundColor: Colors.grey.shade300,
                    backgroundImage:
                        (photoBase64 != null && photoBase64!.isNotEmpty)
                            ? MemoryImage(base64Decode(photoBase64!))
                            : null,
                    child: (photoBase64 == null || photoBase64!.isEmpty)
                        ? const Icon(Icons.person,
                            size: 50, color: Colors.white)
                        : null,
                  ),
                  const SizedBox(height: 10),
                  Text(name,
                      style: const TextStyle(
                          fontSize: 20, fontWeight: FontWeight.bold)),
                  Text(email),
                  const SizedBox(height: 10),
                  const Text(
                    '"Healthy mind, healthy body, healthy life."',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                        fontStyle: FontStyle.italic, color: Colors.black54),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      ElevatedButton(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const EditProfileScreen(),
                            ),
                          ).then((_) =>
                              _loadUserData()); // Refresh data setelah kembali dari edit
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: Colors.green,
                          side: const BorderSide(color: Colors.green),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                        ),
                        child: const Text('Edit Profile'),
                      ),
                      const SizedBox(width: 10),
                      ElevatedButton(
                        onPressed: () {
                          // Share logic
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: Colors.green,
                          side: const BorderSide(color: Colors.green),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                        ),
                        child: const Text('Share Profile'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  const Divider(),
                  const SizedBox(height: 10),
                  _buildProfileDetail('Name', name),
                  const Divider(),
                  _buildProfileDetail('Email', email),
                  const Divider(),
                  _buildProfileDetail('Instagram', instagram),
                  const Divider(),
                  _buildProfileDetail('Facebook', facebook),
                  const Divider(),
                  _buildProfileDetail('TikTok', tiktok),
                  const SizedBox(height: 20),
                  // Logout button
                  OutlinedButton(
                    onPressed: _logout,
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(color: Colors.red.shade300),
                      foregroundColor: Colors.red,
                      minimumSize: const Size(double.infinity, 50),
                    ),
                    child: const Text('Logout'),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileDetail(String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title,
              style:
                  const TextStyle(fontSize: 16, fontWeight: FontWeight.w500)),
          Text(value,
              style: const TextStyle(fontSize: 16, color: Colors.black54)),
        ],
      ),
    );
  }
}
