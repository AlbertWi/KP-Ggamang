import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:uas_pab/models/recipe.dart';

class EditRecipeScreen extends StatefulWidget {
  final String recipeId;
  final Recipe initialRecipe;

  const EditRecipeScreen({
    super.key,
    required this.recipeId,
    required this.initialRecipe,
  });

  @override
  State<EditRecipeScreen> createState() => _EditRecipeScreenState();
}

class _EditRecipeScreenState extends State<EditRecipeScreen> {
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _nameController;
  late TextEditingController _descriptionController;
  late TextEditingController _ingredientsController;
  late TextEditingController _stepsController;
  late TextEditingController _cookingTimeController;
  late TextEditingController _caloriesController;
  late TextEditingController _servingsController;

  String _selectedDifficulty = 'easy';

  bool _isLoading = false;

  final List<String> _difficultyOptions = ['easy', 'medium', 'hard'];

  @override
  void initState() {
    super.initState();

    _nameController = TextEditingController(text: widget.initialRecipe.name);
    _descriptionController =
        TextEditingController(text: widget.initialRecipe.description);
    _ingredientsController = TextEditingController(
        text: widget.initialRecipe.ingredients.join(', '));
    _stepsController =
        TextEditingController(text: widget.initialRecipe.steps.join(', '));
    _cookingTimeController =
        TextEditingController(text: widget.initialRecipe.cookingTime);
    _caloriesController =
        TextEditingController(text: widget.initialRecipe.calories.toString());
    _servingsController =
        TextEditingController(text: widget.initialRecipe.servings);
    _selectedDifficulty = widget.initialRecipe.difficulty.toLowerCase();
    if (!_difficultyOptions.contains(_selectedDifficulty)) {
      _selectedDifficulty = 'easy'; // default fallback
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _ingredientsController.dispose();
    _stepsController.dispose();
    _cookingTimeController.dispose();
    _caloriesController.dispose();
    _servingsController.dispose();
    super.dispose();
  }

  Future<void> _saveChanges() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final firestore = FirebaseFirestore.instance;

      final updatedData = {
        'name': _nameController.text.trim(),
        'description': _descriptionController.text.trim(),
        'ingredients': _ingredientsController.text
            .split(',')
            .map((e) => e.trim())
            .toList(),
        'steps': _stepsController.text.split(',').map((e) => e.trim()).toList(),
        'cookingTime': _cookingTimeController.text.trim(),
        'calories': int.tryParse(_caloriesController.text.trim()) ?? 0,
        'servings': _servingsController.text.trim(),
        'difficulty': _selectedDifficulty,
        'updatedAt': FieldValue.serverTimestamp(),
      };

      await firestore
          .collection('recipes')
          .doc(widget.recipeId)
          .update(updatedData);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Recipe updated successfully!'),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to update recipe: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Widget _buildDifficultyRadio(String value) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Radio<String>(
          value: value,
          groupValue: _selectedDifficulty,
          onChanged: (String? newValue) {
            setState(() {
              _selectedDifficulty = newValue!;
            });
          },
        ),
        Text(
          value[0].toUpperCase() +
              value.substring(1), // Capitalize first letter
          style: const TextStyle(fontSize: 14),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Edit Recipe'),
        backgroundColor: Colors.green.shade200,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              // Recipe Name
              TextFormField(
                controller: _nameController,
                decoration: const InputDecoration(
                  labelText: 'Recipe Name',
                  prefixIcon: Icon(Icons.food_bank),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter the recipe name';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 12),

              // Description
              TextFormField(
                controller: _descriptionController,
                decoration: const InputDecoration(
                  labelText: 'Description',
                  prefixIcon: Icon(Icons.description),
                ),
                maxLines: 3,
              ),
              const SizedBox(height: 12),

              // Ingredients
              TextFormField(
                controller: _ingredientsController,
                decoration: const InputDecoration(
                  labelText: 'Ingredients (separate by comma)',
                  prefixIcon: Icon(Icons.shopping_cart),
                ),
                maxLines: 3,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter ingredients';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 12),

              // Steps
              TextFormField(
                controller: _stepsController,
                decoration: const InputDecoration(
                  labelText: 'Steps (separate by comma)',
                  prefixIcon: Icon(Icons.list),
                ),
                maxLines: 3,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Please enter steps';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 12),

              // Cooking Time
              TextFormField(
                controller: _cookingTimeController,
                decoration: const InputDecoration(
                  labelText: 'Cooking Time',
                  prefixIcon: Icon(Icons.timer),
                ),
              ),
              const SizedBox(height: 12),

              // Calories
              TextFormField(
                controller: _caloriesController,
                decoration: const InputDecoration(
                  labelText: 'Calories',
                  prefixIcon: Icon(Icons.local_fire_department),
                ),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),

              // Servings
              TextFormField(
                controller: _servingsController,
                decoration: const InputDecoration(
                  labelText: 'Servings',
                  prefixIcon: Icon(Icons.group),
                ),
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),

              // Difficulty (Radio buttons)
              const Text(
                'Difficulty',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              Row(
                children: _difficultyOptions
                    .map((diff) => _buildDifficultyRadio(diff))
                    .toList(),
              ),

              const SizedBox(height: 20),

              // Save Button
              ElevatedButton(
                onPressed: _isLoading ? null : _saveChanges,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green.shade400,
                  padding: const EdgeInsets.symmetric(vertical: 15),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(25),
                  ),
                ),
                child: _isLoading
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text(
                        'Save Changes',
                        style: TextStyle(fontSize: 18, color: Colors.white),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
