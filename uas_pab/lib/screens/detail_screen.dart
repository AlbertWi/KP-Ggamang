import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:uas_pab/models/recipe.dart';
import 'package:uas_pab/screens/cooking_steps_screen.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:geocoding/geocoding.dart'; // Tambahkan package ini

class DetailScreen extends StatefulWidget {
  final Recipe varRecipe;
  final double? latitude;
  final double? longitude;

  const DetailScreen({
    super.key,
    required this.varRecipe,
    this.latitude,
    this.longitude,
  });

  @override
  State<DetailScreen> createState() => _DetailScreenState();
}

class _DetailScreenState extends State<DetailScreen> {
  bool _isFavorite = false;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseAuth _auth = FirebaseAuth.instance;
  String _locationName = ''; // Tambahkan untuk menyimpan nama lokasi
  bool _isLoadingLocation = false;

  @override
  void initState() {
    super.initState();
    _loadFavoriteStatus();
    _saveRecipeView();
    _loadLocationName(); // Load nama lokasi
  }

  // Tambahkan method untuk reverse geocoding
  Future<void> _loadLocationName() async {
    if (widget.latitude == null || widget.longitude == null) return;

    setState(() {
      _isLoadingLocation = true;
    });

    try {
      List<Placemark> placemarks = await placemarkFromCoordinates(
        widget.latitude!,
        widget.longitude!,
      );

      if (placemarks.isNotEmpty) {
        final place = placemarks.first;
        String locationName = '';

        // Format: Kota, Negara
        if (place.locality?.isNotEmpty == true) {
          locationName = place.locality!;
        } else if (place.subAdministrativeArea?.isNotEmpty == true) {
          locationName = place.subAdministrativeArea!;
        } else if (place.administrativeArea?.isNotEmpty == true) {
          locationName = place.administrativeArea!;
        }

        if (place.country?.isNotEmpty == true) {
          if (locationName.isNotEmpty) {
            locationName += ', ${place.country}';
          } else {
            locationName = place.country!;
          }
        }

        setState(() {
          _locationName =
              locationName.isNotEmpty ? locationName : 'Unknown Location';
        });
      }
    } catch (e) {
      setState(() {
        _locationName = 'Location unavailable';
      });
    } finally {
      setState(() {
        _isLoadingLocation = false;
      });
    }
  }

  Future<void> _loadFavoriteStatus() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        DocumentSnapshot userDoc =
            await _firestore.collection('users').doc(user.uid).get();

        if (userDoc.exists) {
          Map<String, dynamic>? userData =
              userDoc.data() as Map<String, dynamic>?;
          List<String> favoriteRecipes =
              List<String>.from(userData?['favoriteRecipes'] ?? []);

          setState(() {
            _isFavorite = favoriteRecipes.contains(widget.varRecipe.name);
          });
        }
      } else {
        await _auth.signInAnonymously();
        _loadFavoriteStatus();
      }
    } catch (e) {
      setState(() {
        _isFavorite = false;
      });
    }
  }

  Future<void> _toggleFavorite() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        DocumentReference userDocRef =
            _firestore.collection('users').doc(user.uid);

        await _firestore.runTransaction((transaction) async {
          DocumentSnapshot userDoc = await transaction.get(userDocRef);

          Map<String, dynamic> userData = {};
          if (userDoc.exists) {
            userData = userDoc.data() as Map<String, dynamic>? ?? {};
          }

          List<String> favoriteRecipes =
              List<String>.from(userData['favoriteRecipes'] ?? []);

          if (_isFavorite) {
            favoriteRecipes.remove(widget.varRecipe.name);
            setState(() {
              _isFavorite = false;
            });
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                content:
                    Text('${widget.varRecipe.name} removed from bookmark')));
          } else {
            favoriteRecipes.add(widget.varRecipe.name);
            setState(() {
              _isFavorite = true;
            });
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                content: Text('${widget.varRecipe.name} added to bookmark')));
          }

          transaction.set(
              userDocRef,
              {
                'favoriteRecipes': favoriteRecipes,
                'lastUpdated': FieldValue.serverTimestamp(),
              },
              SetOptions(merge: true));
        });

        await _saveBookmarkActivity();
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Error updating bookmark')));
    }
  }

  Future<void> _saveBookmarkActivity() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('bookmark_history')
            .add({
          'recipeName': widget.varRecipe.name,
          'action': _isFavorite ? 'added' : 'removed',
          'timestamp': FieldValue.serverTimestamp(),
          'recipeDetails': {
            'name': widget.varRecipe.name,
            'description': widget.varRecipe.description,
            'calories': widget.varRecipe.calories,
          }
        });
      }
    } catch (e) {
      // ignore
    }
  }

  Future<void> _saveRecipeView() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('recipe_views')
            .add({
          'recipeName': widget.varRecipe.name,
          'viewedAt': FieldValue.serverTimestamp(),
          'recipeDetails': {
            'name': widget.varRecipe.name,
            'description': widget.varRecipe.description,
            'calories': widget.varRecipe.calories,
            'ingredientsCount': widget.varRecipe.ingredients.length,
            'stepsCount': widget.varRecipe.steps.length,
          }
        });
      }
    } catch (e) {
      // ignore
    }
  }

  Future<void> openMap() async {
    if (widget.latitude == null || widget.longitude == null) {
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text("Lokasi tidak tersedia")));
      return;
    }
    final uri = Uri.parse(
        "https://www.google.com/maps/search/?api=1&query=${widget.latitude},${widget.longitude}");
    final success = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!mounted) return;
    if (!success) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("Tidak bisa membuka Google Map")));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            children: [
              // Bagian Atas: gambar dan tombol kembali
              Stack(
                children: [
                  Padding(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Image.memory(
                        base64Decode(widget.varRecipe.image),
                        fit: BoxFit.cover,
                        width: double.infinity,
                        height: 250,
                        errorBuilder: (context, error, stackTrace) => Container(
                          color: Colors.grey[300],
                          height: 250,
                          child: const Icon(Icons.broken_image,
                              size: 50, color: Colors.grey),
                        ),
                      ),
                    ),
                  ),
                  Container(
                    margin: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.5),
                      shape: BoxShape.circle,
                    ),
                    child: IconButton(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.arrow_back),
                    ),
                  ),
                ],
              ),

              // Judul, lokasi, bookmark, dan deskripsi
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        Expanded(
                          child: Text(
                            widget.varRecipe.name,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                        IconButton(
                          onPressed: _toggleFavorite,
                          icon: Icon(_isFavorite
                              ? Icons.bookmark
                              : Icons.bookmark_border),
                          color: _isFavorite ? Colors.green : null,
                        ),
                      ],
                    ),
                    // Update baris lokasi dengan nama kota/negara
                    if (widget.latitude != null && widget.longitude != null)
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on,
                            color: Colors.red,
                            size: 20,
                          ),
                          const SizedBox(width: 4),
                          Expanded(
                            child: _isLoadingLocation
                                ? const Text(
                                    'Loading location...',
                                    style: TextStyle(fontSize: 14),
                                  )
                                : Text(
                                    _locationName.isNotEmpty
                                        ? _locationName
                                        : "${widget.latitude}, ${widget.longitude}",
                                    style: const TextStyle(fontSize: 14),
                                  ),
                          ),
                          IconButton(
                            icon: const Icon(
                              Icons.map,
                              color: Colors.green,
                            ),
                            onPressed: openMap,
                            tooltip: "Buka di Google Map",
                          ),
                        ],
                      ),
                    const SizedBox(height: 16),
                    const Divider(color: Colors.green, thickness: 1),
                    const SizedBox(height: 16),
                    Text(
                      widget.varRecipe.description,
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 16),
                    const Divider(color: Colors.green, thickness: 1),
                    const SizedBox(height: 16),
                  ],
                ),
              ),

              // Ingredients
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Ingredients',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      height: 200,
                      child: ListView.builder(
                        padding: EdgeInsets.zero,
                        itemCount: widget.varRecipe.ingredients.length,
                        itemBuilder: (context, index) {
                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 4),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(Icons.add,
                                    color: Colors.grey, size: 15),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    widget.varRecipe.ingredients[index],
                                    style: const TextStyle(fontSize: 14),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                    const Divider(color: Colors.green, thickness: 1),
                  ],
                ),
              ),

              // Steps
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Steps',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      height: 200,
                      child: ListView.builder(
                        padding: EdgeInsets.zero,
                        itemCount: widget.varRecipe.steps.length,
                        itemBuilder: (context, index) {
                          return Padding(
                            padding: const EdgeInsets.only(left: 8, bottom: 8),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(Icons.check,
                                    color: Colors.green, size: 15),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    widget.varRecipe.steps[index],
                                    style: const TextStyle(fontSize: 14),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),

              // Tombol Cook It Now
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green.shade200,
                    padding: const EdgeInsets.symmetric(vertical: 15),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(25),
                    ),
                    minimumSize: const Size(double.infinity, 50),
                  ),
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => CookingStepsScreen(
                          ingredients: widget.varRecipe.ingredients,
                          steps: widget.varRecipe.steps,
                        ),
                      ),
                    );
                  },
                  child: const Text(
                    'Cook It Now!',
                    style: TextStyle(fontSize: 18, color: Colors.white),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
