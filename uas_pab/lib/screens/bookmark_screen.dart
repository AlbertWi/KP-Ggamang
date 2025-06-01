import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:uas_pab/models/recipe.dart';
import 'package:uas_pab/screens/detail_screen.dart';
import 'package:uas_pab/screens/dashboard_screen.dart';

class BookmarkScreen extends StatefulWidget {
  const BookmarkScreen({super.key});

  @override
  State<BookmarkScreen> createState() => _BookmarkScreenState();
}

class _BookmarkScreenState extends State<BookmarkScreen> {
  List<Recipe> _favoriteRecipes = [];
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseAuth _auth = FirebaseAuth.instance;

  Future<void> _loadFavoriteRecipes() async {
    try {
      User? user = _auth.currentUser;
      if (user == null) {
        await _auth.signInAnonymously();
        user = _auth.currentUser;
        if (user == null) return;
      }

      // Ambil daftar nama resep favorit dari dokumen user
      DocumentSnapshot userDoc =
          await _firestore.collection('users').doc(user.uid).get();
      if (!userDoc.exists) {
        setState(() => _favoriteRecipes = []);
        return;
      }

      final userData = userDoc.data() as Map<String, dynamic>;
      List<String> favoriteNames =
          List<String>.from(userData['favoriteRecipes'] ?? []);

      if (favoriteNames.isEmpty) {
        setState(() => _favoriteRecipes = []);
        return;
      }

      // Query resep yang namanya ada di daftar favorit (batasi 10 atau sesuai kebutuhan)
      QuerySnapshot recipeQuery = await _firestore
          .collection('recipes')
          .where('name',
              whereIn: favoriteNames.length > 10
                  ? favoriteNames.sublist(0, 10)
                  : favoriteNames)
          .get();

      List<Recipe> recipes =
          recipeQuery.docs.map((doc) => Recipe.fromFirestore(doc)).toList();

      setState(() {
        _favoriteRecipes = recipes;
      });
    } catch (e) {
      print('Error loading favorite recipes: $e');
      setState(() {
        _favoriteRecipes = [];
      });
    }
  }

  @override
  void initState() {
    super.initState();
    _loadFavoriteRecipes();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bookmark'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            Navigator.pushAndRemoveUntil(
              context,
              MaterialPageRoute(
                builder: (context) => const DashboardScreen(),
              ),
              (route) => false,
            );
          },
        ),
      ),
      body: SafeArea(
        child: _favoriteRecipes.isEmpty
            ? const Center(child: Text('No bookmarked recipes found.'))
            : GridView.builder(
                shrinkWrap: true,
                physics: const AlwaysScrollableScrollPhysics(),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 8,
                  mainAxisSpacing: 8,
                ),
                padding: const EdgeInsets.all(8),
                itemCount: _favoriteRecipes.length,
                itemBuilder: (context, index) {
                  Recipe varRecipe = _favoriteRecipes[index];
                  return InkWell(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) =>
                              DetailScreen(varRecipe: varRecipe),
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
                                  top: Radius.circular(16)),
                              child: Image.memory(
                                base64Decode(varRecipe.image),
                                fit: BoxFit.cover,
                                errorBuilder: (context, error, stackTrace) =>
                                    Container(
                                  color: Colors.grey[300],
                                  child: const Icon(Icons.broken_image,
                                      size: 50, color: Colors.grey),
                                ),
                              ),
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.only(left: 16, top: 8),
                            child: Text(
                              varRecipe.name,
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                              ),
                              textAlign: TextAlign.left,
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.only(left: 16, bottom: 8),
                            child: Text(
                              'Calories: ${varRecipe.calories}',
                              style: const TextStyle(fontSize: 10),
                              textAlign: TextAlign.left,
                            ),
                          ),
                        ],
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }
}
