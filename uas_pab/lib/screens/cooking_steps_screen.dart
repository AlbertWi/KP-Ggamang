import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';

class CookingStepsScreen extends StatefulWidget {
  final List<String> ingredients;
  final List<String> steps;

  const CookingStepsScreen({
    super.key,
    required this.ingredients,
    required this.steps,
  });

  @override
  _CookingStepsScreenState createState() => _CookingStepsScreenState();
}

class _CookingStepsScreenState extends State<CookingStepsScreen> {
  late List<bool> _checkedSteps;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;
  final FirebaseAuth _auth = FirebaseAuth.instance;

  @override
  void initState() {
    super.initState();
    _checkedSteps = List.generate(widget.steps.length, (index) => false);
    _loadCookingProgress();
  }

  Future<void> _loadCookingProgress() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        // Buat unique identifier untuk session memasak ini
        String cookingSessionId = _generateCookingSessionId();

        DocumentSnapshot progressDoc = await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('cooking_progress')
            .doc(cookingSessionId)
            .get();

        if (progressDoc.exists) {
          Map<String, dynamic>? data =
              progressDoc.data() as Map<String, dynamic>?;
          List<dynamic> savedProgress = data?['checkedSteps'] ?? [];

          setState(() {
            for (int i = 0;
                i < _checkedSteps.length && i < savedProgress.length;
                i++) {
              _checkedSteps[i] = savedProgress[i] ?? false;
            }
          });
        }
      }
    } catch (e) {
      print('Error loading cooking progress: $e');
    }
  }

  Future<void> _saveCookingProgress() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        String cookingSessionId = _generateCookingSessionId();

        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('cooking_progress')
            .doc(cookingSessionId)
            .set({
          'checkedSteps': _checkedSteps,
          'ingredients': widget.ingredients,
          'steps': widget.steps,
          'lastUpdated': FieldValue.serverTimestamp(),
        });
      }
    } catch (e) {
      print('Error saving cooking progress: $e');
    }
  }

  Future<void> _saveCookingCompletion() async {
    try {
      User? user = _auth.currentUser;
      if (user != null) {
        // Simpan ke koleksi completed_recipes
        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('completed_recipes')
            .add({
          'ingredients': widget.ingredients,
          'steps': widget.steps,
          'completedAt': FieldValue.serverTimestamp(),
          'stepsCount': widget.steps.length,
        });

        // Hapus progress setelah selesai
        String cookingSessionId = _generateCookingSessionId();
        await _firestore
            .collection('users')
            .doc(user.uid)
            .collection('cooking_progress')
            .doc(cookingSessionId)
            .delete();
      }
    } catch (e) {
      print('Error saving cooking completion: $e');
    }
  }

  String _generateCookingSessionId() {
    // Generate unique ID berdasarkan ingredients dan steps
    String combined = widget.ingredients.join('') + widget.steps.join('');
    return combined.hashCode.toString();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ingredients & Cooking Steps'),
        backgroundColor: Colors.green.shade200,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Ingredients Section
              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Ingredients',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 8),
                    for (var ingredient in widget.ingredients)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        child: Text(
                          ingredient,
                          style: const TextStyle(fontSize: 16),
                        ),
                      ),
                    const Divider(color: Colors.green, thickness: 1),
                  ],
                ),
              ),

              // Steps Section (with Checklist)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Steps',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 18,
                      ),
                    ),
                    const SizedBox(height: 8),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: widget.steps.length,
                      itemBuilder: (context, index) {
                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: Row(
                            children: [
                              Checkbox(
                                value: _checkedSteps[index],
                                onChanged: (bool? value) {
                                  setState(() {
                                    _checkedSteps[index] = value ?? false;
                                  });
                                  // Simpan progress setiap kali ada perubahan
                                  _saveCookingProgress();
                                },
                                activeColor: Colors.green.shade200,
                              ),
                              Expanded(
                                child: Text(
                                  widget.steps[index],
                                  style: const TextStyle(fontSize: 16),
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),

              // Button Finish Cooking
              if (_checkedSteps.every((checked) => checked))
                Padding(
                  padding: const EdgeInsets.all(20),
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green.shade200,
                      padding: const EdgeInsets.symmetric(vertical: 15),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(25),
                      ),
                      minimumSize: const Size(double.infinity, 50),
                    ),
                    onPressed: () async {
                      await _saveCookingCompletion();

                      showDialog(
                        context: context,
                        builder: (context) {
                          return AlertDialog(
                            title: const Text('Cooking Completed!'),
                            content: const Text(
                                'Congratulations, you have finished cooking. Enjoy your meal!'),
                            actions: [
                              TextButton(
                                onPressed: () {
                                  Navigator.popUntil(
                                    context,
                                    (route) => route.isFirst,
                                  );
                                },
                                child: const Text('OK'),
                              ),
                            ],
                          );
                        },
                      );
                    },
                    child: const Text(
                      'Finish Cooking!',
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
