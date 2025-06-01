import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:uas_pab/screens/login_screen.dart'; // pastikan path ini benar

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final TextEditingController _instagramController = TextEditingController();
  final TextEditingController _facebookController = TextEditingController();
  final TextEditingController _tiktokController = TextEditingController();

  String? _profileImageBase64;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final user = FirebaseAuth.instance.currentUser;
    if (user != null) {
      DocumentSnapshot userDoc = await FirebaseFirestore.instance
          .collection('users')
          .doc(user.uid)
          .get();

      if (userDoc.exists) {
        final data = userDoc.data() as Map<String, dynamic>;
        setState(() {
          // Gunakan field name yang konsisten
          _nameController.text = data['name'] ?? data['username'] ?? '';
          _emailController.text = data['email'] ?? '';
          _passwordController.text = data['password'] ?? '';
          _instagramController.text = data['instagram'] ?? '';
          _facebookController.text = data['facebook'] ?? '';
          _tiktokController.text = data['tiktok'] ?? '';
          _profileImageBase64 = data['photo'] ?? '';
        });
      }
    }
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 50,
    );

    if (pickedFile != null) {
      final bytes = await File(pickedFile.path).readAsBytes();
      setState(() {
        _profileImageBase64 = base64Encode(bytes);
      });
    }
  }

  Future<void> _saveUserData() async {
    final user = FirebaseAuth.instance.currentUser;
    if (user != null) {
      // Simpan dengan field name yang konsisten
      await FirebaseFirestore.instance.collection('users').doc(user.uid).set({
        'name': _nameController.text, // Gunakan 'name' bukan 'username'
        'username': _nameController
            .text, // Tetap simpan username untuk backward compatibility
        'email': _emailController.text,
        'password': _passwordController.text,
        'instagram': _instagramController.text,
        'facebook': _facebookController.text,
        'tiktok': _tiktokController.text,
        'photo': _profileImageBase64 ?? '', // Gunakan 'photo' untuk konsistensi
        'profileImage': _profileImageBase64 ??
            '', // Tetap simpan profileImage untuk backward compatibility
      }, SetOptions(merge: true));

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Profile updated successfully!')),
      );

      Navigator.pop(context);
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
        title: const Text('Edit Profile'),
        centerTitle: true,
        backgroundColor: Colors.green.shade200,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            GestureDetector(
              onTap: _pickImage,
              child: CircleAvatar(
                radius: 50,
                backgroundColor: Colors.grey[300],
                backgroundImage: _profileImageBase64 != null
                    ? MemoryImage(base64Decode(_profileImageBase64!))
                    : null,
                child: _profileImageBase64 == null
                    ? const Icon(Icons.camera_alt,
                        size: 30, color: Colors.white)
                    : null,
              ),
            ),
            const SizedBox(height: 10),
            TextButton(
              onPressed: _pickImage,
              child: const Text('Ganti Foto Profil'),
            ),
            const SizedBox(height: 20),
            _buildTextField('Name', _nameController),
            _buildTextField('Email', _emailController),
            _buildTextField('Password', _passwordController, isPassword: true),
            const Divider(),
            _buildTextField('Instagram', _instagramController),
            _buildTextField('Facebook', _facebookController),
            _buildTextField('TikTok', _tiktokController),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: _saveUserData,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.green.shade200,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 50),
              ),
              child: const Text('Save'),
            ),
            const SizedBox(height: 12),
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
    );
  }

  Widget _buildTextField(String label, TextEditingController controller,
      {bool isPassword = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: TextField(
        controller: controller,
        obscureText: isPassword,
        decoration: InputDecoration(
          labelText: label,
          border: const OutlineInputBorder(),
        ),
      ),
    );
  }
}
