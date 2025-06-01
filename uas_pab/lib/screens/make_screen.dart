import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';

class MakeScreen extends StatefulWidget {
  final String recipeName;
  final List<String> steps;

  const MakeScreen({
    super.key,
    required this.recipeName,
    required this.steps,
  });

  @override
  State<MakeScreen> createState() => _MakeScreenState();
}

class _MakeScreenState extends State<MakeScreen> {
  late List<bool> completedSteps;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseAuth _auth = FirebaseAuth.instance;

  @override
  void initState() {
    super.initState();
    completedSteps = List<bool>.filled(widget.steps.length, false);
    _loadMakingProgress();
  }

  Future<void> _loadMakingProgress() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        String sessionId = _generateSessionId();

        DocumentSnapshot progressDoc = await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('making_progress')
            .doc(sessionId)
            .get();

        if (progressDoc.exists) {
          Map<String, dynamic>? data =
              progressDoc.data() as Map<String, dynamic>?;
          List<dynamic> savedProgress = data?['completedSteps'] ?? [];

          setState(() {
            for (int i = 0;
                i < completedSteps.length && i < savedProgress.length;
                i++) {
              completedSteps[i] = savedProgress[i] ?? false;
            }
          });
        }
      } else {
        // Sign in anonymous jika belum login
        await _auth.signInAnonymously();
        _loadMakingProgress();
      }
    } catch (e) {
      print('Error loading making progress: $e');
    }
  }

  Future<void> _saveMakingProgress() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        String sessionId = _generateSessionId();

        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('making_progress')
            .doc(sessionId)
            .set({
          'recipeName': widget.recipeName,
          'completedSteps': completedSteps,
          'steps': widget.steps,
          'totalSteps': widget.steps.length,
          'completedCount': completedSteps.where((step) => step).length,
          'lastUpdated': FieldValue.serverTimestamp(),
        });
      }
    } catch (e) {
      print('Error saving making progress: $e');
    }
  }

  Future<void> _saveMakingCompletion() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        // Simpan ke koleksi completed_makings
        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('completed_makings')
            .add({
          'recipeName': widget.recipeName,
          'steps': widget.steps,
          'totalSteps': widget.steps.length,
          'completedAt': FieldValue.serverTimestamp(),
          'duration': null, // Bisa ditambahkan tracking waktu jika diperlukan
        });

        // Update statistik user
        DocumentReference userStatsRef =
            _firestore.collection('users').doc(user.uid);

        await _firestore.runTransaction((transaction) async {
          DocumentSnapshot userDoc = await transaction.get(userStatsRef);

          Map<String, dynamic> userData = {};
          if (userDoc.exists) {
            userData = userDoc.data() as Map<String, dynamic>? ?? {};
          }

          int currentCompletedCount = userData['completedRecipesCount'] ?? 0;

          transaction.set(
              userStatsRef,
              {
                'completedRecipesCount': currentCompletedCount + 1,
                'lastCompletedRecipe': widget.recipeName,
                'lastCompletedAt': FieldValue.serverTimestamp(),
              },
              SetOptions(merge: true));
        });

        // Hapus progress setelah selesai
        String sessionId = _generateSessionId();
        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('making_progress')
            .doc(sessionId)
            .delete();
      }
    } catch (e) {
      print('Error saving making completion: $e');
    }
  }

  String _generateSessionId() {
    // Generate unique ID berdasarkan recipe name dan steps
    String combined = widget.recipeName + widget.steps.join('');
    return combined.hashCode.toString();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.recipeName),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Cooking Steps',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 10),
            Expanded(
              child: ListView.builder(
                itemCount: widget.steps.length,
                itemBuilder: (context, index) {
                  return Padding(
                    padding: const EdgeInsets.only(left: 8, bottom: 8),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Checkbox untuk melacak status langkah
                        Checkbox(
                          value: completedSteps[index],
                          onChanged: (value) {
                            setState(() {
                              completedSteps[index] = value ?? false;
                            });
                            // Simpan progress setiap kali ada perubahan
                            _saveMakingProgress();
                          },
                        ),
                        Expanded(
                          child: Text(
                            widget.steps[index],
                            style: TextStyle(
                              fontSize: 16,
                              decoration: completedSteps[index]
                                  ? TextDecoration.lineThrough
                                  : TextDecoration.none,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
            const SizedBox(height: 20),
            Center(
              child: ElevatedButton(
                onPressed: () async {
                  if (completedSteps.every((step) => step)) {
                    await _saveMakingCompletion();

                    showDialog(
                      context: context,
                      builder: (context) {
                        return AlertDialog(
                          title: const Text('Well Done!'),
                          content: Text(
                              'You have completed cooking "${widget.recipeName}"!'),
                          actions: [
                            TextButton(
                              onPressed: () {
                                Navigator.pop(context);
                              },
                              child: const Text('OK'),
                            ),
                          ],
                        );
                      },
                    );
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Please complete all steps first!'),
                      ),
                    );
                  }
                },
                child: const Text('Finish Cooking'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
