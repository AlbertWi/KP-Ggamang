import 'package:flutter/material.dart';
import 'package:firebase_auth/firebase_auth.dart' as auth;
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:uas_pab/models/recipe.dart';
import 'package:uas_pab/models/user.dart';
import 'package:uas_pab/screens/detail_screen.dart';
import 'package:uas_pab/screens/profile_screen.dart';
import 'dart:convert';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final TextEditingController searchController = TextEditingController();
  String searchQuery = '';

  @override
  void initState() {
    super.initState();
    searchController.addListener(() {
      setState(() {
        searchQuery = searchController.text.toLowerCase();
      });
    });
  }

  @override
  void dispose() {
    searchController.dispose();
    super.dispose();
  }

  Future<User?> getCurrentUser() async {
    final currentUser = auth.FirebaseAuth.instance.currentUser;
    if (currentUser == null) return null;

    DocumentSnapshot userDoc = await FirebaseFirestore.instance
        .collection('users')
        .doc(currentUser.uid)
        .get();

    if (!userDoc.exists) return null;

    return User.fromFirestore(userDoc);
  }

  Widget buildRecipeImage(String imageData) {
    if (imageData.isEmpty) {
      return Container(
        color: Colors.grey[300],
        child:
            const Icon(Icons.image_not_supported, size: 50, color: Colors.grey),
      );
    }

    try {
      return Image.memory(
        base64Decode(imageData),
        fit: BoxFit.cover,
        width: double.infinity,
        errorBuilder: (context, error, stackTrace) => Container(
          color: Colors.grey[300],
          child: const Icon(Icons.broken_image, size: 50, color: Colors.grey),
        ),
      );
    } catch (e) {
      return Container(
        color: Colors.grey[300],
        child: const Icon(Icons.image, size: 50, color: Colors.grey),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    Query collectionQuery = FirebaseFirestore.instance
        .collection('recipes')
        .orderBy('createdAt', descending: true);

    if (searchQuery.isNotEmpty) {
      collectionQuery = collectionQuery
          .where('name_lowercase', isGreaterThanOrEqualTo: searchQuery)
          .where('name_lowercase', isLessThanOrEqualTo: searchQuery + '\uf8ff');
    }

    return SafeArea(
      child: SingleChildScrollView(
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(16.0),
              decoration: BoxDecoration(
                color: Colors.green.shade200,
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(20),
                  bottomRight: Radius.circular(20),
                ),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: FutureBuilder<User?>(
                      future: getCurrentUser(),
                      builder: (context, snapshot) {
                        String userName = 'Guest';
                        String? base64Photo;
                        if (snapshot.hasData) {
                          userName = snapshot.data!.name;
                          base64Photo = snapshot.data!.photo;
                        }
                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Hello, $userName!',
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Discover, Cook, and Enjoy Healthy Meals!',
                              style:
                                  TextStyle(fontSize: 14, color: Colors.white),
                            ),
                            const SizedBox(height: 10),
                            TextField(
                              controller: searchController,
                              decoration: InputDecoration(
                                hintText: 'Search',
                                prefixIcon: const Icon(Icons.search),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                filled: true,
                                fillColor: Colors.white,
                              ),
                            ),
                          ],
                        );
                      },
                    ),
                  ),
                  const SizedBox(width: 16),
                  FutureBuilder<User?>(
                    future: getCurrentUser(),
                    builder: (context, snapshot) {
                      String? photo = snapshot.data?.photo;
                      return GestureDetector(
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const ProfileScreen(),
                            ),
                          );
                        },
                        child: Container(
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 3),
                            boxShadow: const [
                              BoxShadow(
                                color: Colors.black26,
                                blurRadius: 10,
                                offset: Offset(0, 5),
                              ),
                            ],
                          ),
                          child: CircleAvatar(
                            radius: 30,
                            backgroundColor: Colors.grey.shade300,
                            backgroundImage: (photo != null && photo.isNotEmpty)
                                ? MemoryImage(base64Decode(photo))
                                : null,
                            child: (photo == null || photo.isEmpty)
                                ? const Icon(Icons.person,
                                    size: 30, color: Colors.white)
                                : null,
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              "Explore New Healthy Dishes",
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              textAlign: TextAlign.left,
            ),
            StreamBuilder<QuerySnapshot>(
              stream: collectionQuery.snapshots(),
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const CircularProgressIndicator();
                }
                if (snapshot.hasError) {
                  return Text('Error: ${snapshot.error}');
                }
                if (!snapshot.hasData || snapshot.data!.docs.isEmpty) {
                  return const Text('No recipes found.');
                }

                final recipes = snapshot.data!.docs
                    .map((doc) => Recipe.fromFirestore(doc))
                    .toList();

                return GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 8,
                    mainAxisSpacing: 8,
                  ),
                  padding: const EdgeInsets.all(8),
                  itemCount: recipes.length,
                  itemBuilder: (context, index) {
                    final recipe = recipes[index];
                    return InkWell(
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => DetailScreen(
                              varRecipe: recipe,
                              latitude: recipe.latitude,
                              longitude: recipe.longitude,
                            ),
                          ),
                        );
                      },
                      child: Card(
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        margin: const EdgeInsets.all(6),
                        elevation: 1,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Expanded(
                              child: ClipRRect(
                                borderRadius: const BorderRadius.vertical(
                                  top: Radius.circular(16),
                                ),
                                child: buildRecipeImage(recipe.image),
                              ),
                            ),
                            Padding(
                              padding: const EdgeInsets.only(left: 16, top: 8),
                              child: Text(
                                recipe.name,
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                ),
                                textAlign: TextAlign.left,
                              ),
                            ),
                            Padding(
                              padding:
                                  const EdgeInsets.only(left: 16, bottom: 8),
                              child: Text(
                                'Calories: ${recipe.calories}',
                                style: const TextStyle(fontSize: 10),
                                textAlign: TextAlign.left,
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
