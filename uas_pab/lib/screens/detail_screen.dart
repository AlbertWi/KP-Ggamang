import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:uas_pab/models/recipe.dart';
import 'package:uas_pab/screens/cooking_steps_screen.dart';
import 'package:uas_pab/screens/edit_recipe_screen.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:geocoding/geocoding.dart';

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
  bool _isOwner = false;
  bool _isCheckingOwnership = true;
  bool _isDeleting = false;
  bool _isLoadingLocation = false;
  bool _isCommentsVisible = false;
  bool _isLoadingComments = false;
  bool _isPostingComment = false;

  String _locationName = '';
  List<Map<String, dynamic>> _comments = [];

  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseAuth _auth = FirebaseAuth.instance;
  final TextEditingController _commentController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _initializeScreen();
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  Future<void> _initializeScreen() async {
    await _checkRecipeOwnership();
    await _loadFavoriteStatus();
    await _saveRecipeView();
    await _loadLocationName();
    await _loadComments();
  }

  Future<void> _checkRecipeOwnership() async {
    setState(() => _isCheckingOwnership = true);

    try {
      User? user = _auth.currentUser;
      if (user == null) {
        await _auth.signInAnonymously();
        user = _auth.currentUser;
      }

      if (user != null) {
        QuerySnapshot querySnapshot = await _firestore
            .collection('recipes')
            .where('name', isEqualTo: widget.varRecipe.name)
            .limit(1)
            .get();

        if (querySnapshot.docs.isNotEmpty) {
          DocumentSnapshot recipeDoc = querySnapshot.docs.first;
          Map<String, dynamic>? recipeData =
              recipeDoc.data() as Map<String, dynamic>?;

          String recipeOwnerId = recipeData?['userId'] ?? '';
          setState(() => _isOwner = user!.uid == recipeOwnerId);
        }
      }
    } catch (e) {
      setState(() => _isOwner = false);
    } finally {
      setState(() => _isCheckingOwnership = false);
    }
  }

  Future<void> _loadLocationName() async {
    if (widget.latitude == null || widget.longitude == null) return;

    setState(() => _isLoadingLocation = true);

    try {
      List<Placemark> placemarks = await placemarkFromCoordinates(
        widget.latitude!,
        widget.longitude!,
      );

      if (placemarks.isNotEmpty) {
        final place = placemarks.first;
        String locationName = '';

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
      setState(() => _locationName = 'Location unavailable');
    } finally {
      setState(() => _isLoadingLocation = false);
    }
  }

  Future<void> _loadComments() async {
    setState(() => _isLoadingComments = true);

    try {
      QuerySnapshot snapshot = await _firestore
          .collection('recipes')
          .doc(widget.varRecipe.id)
          .collection('comments')
          .orderBy('timestamp', descending: true)
          .get();

      List<Map<String, dynamic>> comments = [];
      for (var doc in snapshot.docs) {
        Map<String, dynamic> commentData = doc.data() as Map<String, dynamic>;
        commentData['id'] = doc.id;
        comments.add(commentData);
      }

      setState(() => _comments = comments);
    } catch (e) {
      // Handle error silently
    } finally {
      setState(() => _isLoadingComments = false);
    }
  }

  Future<void> _postComment() async {
    if (_commentController.text.trim().isEmpty) {
      _showSnackBar('Please enter a comment');
      return;
    }

    setState(() => _isPostingComment = true);

    try {
      User? user = _auth.currentUser;
      if (user == null) {
        await _auth.signInAnonymously();
        user = _auth.currentUser;
      }

      if (user != null) {
        String userName = 'Anonymous User';
        try {
          DocumentSnapshot userDoc =
              await _firestore.collection('users').doc(user.uid).get();

          if (userDoc.exists) {
            Map<String, dynamic>? userData =
                userDoc.data() as Map<String, dynamic>?;
            userName = userData?['displayName'] ??
                userData?['email'] ??
                'Anonymous User';
          } else if (user.email != null) {
            userName = user.email!;
          }
        } catch (e) {
          // Use default name
        }

        await _firestore
            .collection('recipes')
            .doc(widget.varRecipe.id)
            .collection('comments')
            .add({
          'text': _commentController.text.trim(),
          'userId': user.uid,
          'userName': userName,
          'timestamp': FieldValue.serverTimestamp(),
        });

        _commentController.clear();
        await _loadComments();
        _showSnackBar('Comment posted successfully');
      }
    } catch (e) {
      _showSnackBar('Failed to post comment');
    } finally {
      setState(() => _isPostingComment = false);
    }
  }

  Future<void> _deleteComment(String commentId, String commentUserId) async {
    User? user = _auth.currentUser;
    if (user == null || user.uid != commentUserId) {
      _showSnackBar('You can only delete your own comments');
      return;
    }

    final confirm = await _showConfirmDialog(
      'Delete Comment',
      'Are you sure you want to delete this comment?',
    );

    if (confirm == true) {
      try {
        await _firestore
            .collection('recipes')
            .doc(widget.varRecipe.id)
            .collection('comments')
            .doc(commentId)
            .delete();

        await _loadComments();
        _showSnackBar('Comment deleted successfully');
      } catch (e) {
        _showSnackBar('Failed to delete comment');
      }
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
      setState(() => _isFavorite = false);
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
            setState(() => _isFavorite = false);
            _showSnackBar('${widget.varRecipe.name} removed from bookmark');
          } else {
            favoriteRecipes.add(widget.varRecipe.name);
            setState(() => _isFavorite = true);
            _showSnackBar('${widget.varRecipe.name} added to bookmark');
          }

          transaction.set(
            userDocRef,
            {
              'favoriteRecipes': favoriteRecipes,
              'lastUpdated': FieldValue.serverTimestamp(),
            },
            SetOptions(merge: true),
          );
        });

        await _saveBookmarkActivity();
      }
    } catch (e) {
      _showSnackBar('Error updating bookmark');
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
      // Handle error silently
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
      // Handle error silently
    }
  }

  Future<void> _openMap() async {
    if (widget.latitude == null || widget.longitude == null) {
      _showSnackBar("Lokasi tidak tersedia");
      return;
    }

    final uri = Uri.parse(
        "https://www.google.com/maps/search/?api=1&query=${widget.latitude},${widget.longitude}");
    final success = await launchUrl(uri, mode: LaunchMode.externalApplication);

    if (!mounted) return;
    if (!success) {
      _showSnackBar("Tidak bisa membuka Google Map");
    }
  }

  Future<void> _deleteRecipe() async {
    if (!_isOwner) {
      _showSnackBar('You can only delete your own recipes', isError: true);
      return;
    }

    setState(() => _isDeleting = true);

    try {
      User? user = _auth.currentUser;
      if (user == null) throw Exception('User not authenticated');

      QuerySnapshot querySnapshot = await _firestore
          .collection('recipes')
          .where('name', isEqualTo: widget.varRecipe.name)
          .where('userId', isEqualTo: user.uid)
          .get();

      if (querySnapshot.docs.isNotEmpty) {
        await querySnapshot.docs.first.reference.delete();

        DocumentReference userDocRef =
            _firestore.collection('users').doc(user.uid);
        await _firestore.runTransaction((transaction) async {
          DocumentSnapshot userDoc = await transaction.get(userDocRef);
          if (userDoc.exists) {
            Map<String, dynamic> userData =
                userDoc.data() as Map<String, dynamic>? ?? {};
            List<String> favoriteRecipes =
                List<String>.from(userData['favoriteRecipes'] ?? []);

            if (favoriteRecipes.contains(widget.varRecipe.name)) {
              favoriteRecipes.remove(widget.varRecipe.name);
              transaction
                  .update(userDocRef, {'favoriteRecipes': favoriteRecipes});
            }
          }
        });

        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('recipe_actions')
            .add({
          'action': 'deleted',
          'recipeName': widget.varRecipe.name,
          'timestamp': FieldValue.serverTimestamp(),
        });

        if (mounted) {
          _showSnackBar('Recipe deleted successfully');
          Navigator.pop(context, true);
        }
      } else {
        throw Exception('Recipe not found or permission denied');
      }
    } catch (e) {
      if (mounted) {
        _showSnackBar('Failed to delete recipe: ${e.toString()}',
            isError: true);
      }
    } finally {
      if (mounted) {
        setState(() => _isDeleting = false);
      }
    }
  }

  Future<void> _onSelectedMenu(String value) async {
    if (value == 'edit') {
      if (!_isOwner) {
        _showSnackBar('You can only edit your own recipes', isError: true);
        return;
      }

      final result = await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => EditRecipeScreen(
            recipeId: widget.varRecipe.id,
            initialRecipe: widget.varRecipe,
          ),
        ),
      );

      if (result == true) setState(() {});
    } else if (value == 'delete') {
      if (!_isOwner) {
        _showSnackBar('You can only delete your own recipes', isError: true);
        return;
      }

      final confirm = await _showDeleteConfirmDialog();
      if (confirm == true) await _deleteRecipe();
    }
  }

  void _showSnackBar(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red : Colors.green,
      ),
    );
  }

  Future<bool?> _showConfirmDialog(String title, String content) {
    return showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(content),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  Future<bool?> _showDeleteConfirmDialog() {
    return showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirm Delete'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Are you sure you want to delete this recipe?'),
            const SizedBox(height: 8),
            Text(
              'Recipe: ${widget.varRecipe.name}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            const Text(
              'This action cannot be undone.',
              style: TextStyle(color: Colors.red, fontSize: 12),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  String _formatTimeAgo(Timestamp? timestamp) {
    if (timestamp == null) return 'Just now';

    DateTime commentTime = timestamp.toDate();
    Duration difference = DateTime.now().difference(commentTime);

    if (difference.inDays > 0) {
      return '${difference.inDays}d ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours}h ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes}m ago';
    }
    return 'Just now';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Stack(
          children: [
            SingleChildScrollView(
              child: Column(
                children: [
                  _buildHeader(),
                  _buildRecipeInfo(),
                  _buildCommentsSection(),
                  _buildIngredients(),
                  _buildSteps(),
                  _buildCookButton(),
                ],
              ),
            ),
            if (_isDeleting) _buildLoadingOverlay(),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Stack(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
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
        Positioned(
          top: 16,
          left: 16,
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.8),
              shape: BoxShape.circle,
            ),
            child: IconButton(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(Icons.arrow_back, color: Colors.black),
            ),
          ),
        ),
        Positioned(
          top: 16,
          right: 16,
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.8),
              shape: BoxShape.circle,
            ),
            child: PopupMenuButton<String>(
              onSelected: _onSelectedMenu,
              icon: const Icon(Icons.more_vert, color: Colors.black),
              itemBuilder: (context) => [
                const PopupMenuItem(
                  value: 'edit',
                  child: Row(
                    children: [
                      Icon(Icons.edit, color: Colors.blue),
                      SizedBox(width: 8),
                      Text('Edit Recipe'),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: 'delete',
                  child: Row(
                    children: [
                      Icon(Icons.delete, color: Colors.red),
                      SizedBox(width: 8),
                      Text('Delete Recipe'),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildRecipeInfo() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  widget.varRecipe.name,
                  style: const TextStyle(
                      fontSize: 20, fontWeight: FontWeight.bold),
                ),
              ),
              IconButton(
                onPressed: _toggleFavorite,
                icon:
                    Icon(_isFavorite ? Icons.bookmark : Icons.bookmark_border),
                color: _isFavorite ? Colors.green : null,
              ),
            ],
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _buildInfoChip(
                  Icons.schedule, widget.varRecipe.cookingTime, Colors.orange),
              _buildInfoChip(
                  Icons.people, widget.varRecipe.servings, Colors.blue),
              _buildInfoChip(
                  Icons.whatshot, widget.varRecipe.difficulty, Colors.red),
            ],
          ),
          const SizedBox(height: 12),
          if (widget.latitude != null && widget.longitude != null)
            Row(
              children: [
                const Icon(Icons.location_on, color: Colors.red, size: 20),
                const SizedBox(width: 4),
                Expanded(
                  child: _isLoadingLocation
                      ? const Text('Loading location...',
                          style: TextStyle(fontSize: 14))
                      : Text(
                          _locationName.isNotEmpty
                              ? _locationName
                              : "${widget.latitude}, ${widget.longitude}",
                          style: const TextStyle(fontSize: 14),
                        ),
                ),
                IconButton(
                  icon: const Icon(Icons.map, color: Colors.green),
                  onPressed: _openMap,
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
    );
  }

  Widget _buildInfoChip(IconData icon, String text, Color color) {
    return Row(
      children: [
        Icon(icon, color: color, size: 18),
        const SizedBox(width: 4),
        Text(
          text.isNotEmpty ? text : "-",
          style: const TextStyle(fontSize: 14),
        ),
      ],
    );
  }

  Widget _buildCommentsSection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text(
                'Komentar',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              IconButton(
                icon: Icon(
                  _isCommentsVisible
                      ? Icons.arrow_drop_up
                      : Icons.arrow_drop_down,
                  color: Colors.green,
                ),
                onPressed: () {
                  setState(() {
                    _isCommentsVisible = !_isCommentsVisible;
                  });
                },
              ),
            ],
          ),
          if (_isCommentsVisible) ...[
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _commentController,
                    decoration: InputDecoration(
                      hintText: 'Tambahkan komentar...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(20),
                        borderSide: BorderSide(color: Colors.grey.shade300),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(20),
                        borderSide: BorderSide(color: Colors.grey.shade300),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(20),
                        borderSide: const BorderSide(color: Colors.green),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                    ),
                    maxLines: null,
                    textInputAction: TextInputAction.send,
                    onSubmitted: (_) => _postComment(),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  decoration: const BoxDecoration(
                    color: Colors.green,
                    shape: BoxShape.circle,
                  ),
                  child: IconButton(
                    onPressed: _isPostingComment ? null : _postComment,
                    icon: _isPostingComment
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor:
                                  AlwaysStoppedAnimation<Color>(Colors.white),
                            ),
                          )
                        : const Icon(Icons.send, color: Colors.white),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            if (_isLoadingComments)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (_comments.isEmpty)
              Container(
                padding: const EdgeInsets.all(20),
                width: double.infinity,
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  children: [
                    Icon(
                      Icons.comment_outlined,
                      size: 48,
                      color: Colors.grey.shade400,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Belum ada komentar',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Jadilah yang pertama untuk memberikan komentar!',
                      style: TextStyle(
                        color: Colors.grey.shade500,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              )
            else
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: _comments.length,
                itemBuilder: (context, index) =>
                    _buildComment(_comments[index]),
              ),
          ],
          const SizedBox(height: 16),
          const Divider(color: Colors.green, thickness: 1),
          const SizedBox(height: 16),
        ],
      ),
    );
  }

  Widget _buildComment(Map<String, dynamic> comment) {
    User? currentUser = _auth.currentUser;
    bool isOwner = currentUser != null && currentUser.uid == comment['userId'];
    String timeAgo = _formatTimeAgo(comment['timestamp'] as Timestamp?);

    return Card(
      margin: const EdgeInsets.symmetric(vertical: 4),
      elevation: 1,
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 16,
                  backgroundColor: Colors.green.shade200,
                  child: Text(
                    comment['userName']
                            ?.toString()
                            .substring(0, 1)
                            .toUpperCase() ??
                        'A',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        comment['userName'] ?? 'Anonymous User',
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      Text(
                        timeAgo,
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ),
                if (isOwner)
                  IconButton(
                    icon: const Icon(Icons.delete, size: 18),
                    color: Colors.red,
                    onPressed: () =>
                        _deleteComment(comment['id'], comment['userId']),
                  ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              comment['text'] ?? '',
              style: const TextStyle(fontSize: 14),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildIngredients() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Ingredients',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
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
                      const Icon(Icons.add, color: Colors.grey, size: 15),
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
    );
  }

  Widget _buildSteps() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Steps',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
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
                      const Icon(Icons.check, color: Colors.green, size: 15),
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
    );
  }

  Widget _buildCookButton() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
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
    );
  }

  Widget _buildLoadingOverlay() {
    return Container(
      color: Colors.black.withOpacity(0.5),
      child: const Center(
        child: Card(
          child: Padding(
            padding: EdgeInsets.all(20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                CircularProgressIndicator(),
                SizedBox(height: 16),
                Text('Deleting recipe...'),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
