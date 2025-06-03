import 'package:cloud_firestore/cloud_firestore.dart';

class Recipe {
  final String id;
  final String name;
  final String description;
  final String image;
  final List<String> ingredients;
  final List<String> steps;
  final String cookingTime;
  final int calories;
  final String servings;
  final String difficulty;
  final double? latitude;
  final double? longitude;

  Recipe({
    required this.id,
    required this.name,
    required this.description,
    required this.image,
    required this.ingredients,
    required this.steps,
    required this.cookingTime,
    required this.calories,
    required this.servings,
    required this.difficulty,
    this.latitude,
    this.longitude,
  });

  factory Recipe.fromFirestore(DocumentSnapshot doc) {
    final data = doc.data() as Map<String, dynamic>;

    double? lat;
    double? lng;

    if (data['location'] != null) {
      final locationData = data['location'] as Map<String, dynamic>;
      lat = (locationData['latitude'] as num?)?.toDouble();
      lng = (locationData['longitude'] as num?)?.toDouble();
    } else {
      lat = (data['latitude'] as num?)?.toDouble();
      lng = (data['longitude'] as num?)?.toDouble();
    }

    int caloriesValue = 0;
    if (data['calories'] != null) {
      if (data['calories'] is int) {
        caloriesValue = data['calories'];
      } else if (data['calories'] is String) {
        caloriesValue = int.tryParse(data['calories']) ?? 0;
      } else if (data['calories'] is double) {
        caloriesValue = (data['calories'] as double).round();
      }
    }

    return Recipe(
      id: doc.id,
      name: data['name'] ?? '',
      description: data['description'] ?? '',
      image: data['image'] ?? '',
      ingredients: List<String>.from(data['ingredients'] ?? []),
      steps: List<String>.from(data['steps'] ?? []),
      cookingTime: data['cookingTime'] ?? '',
      calories: caloriesValue,
      servings: data['servings'] ?? '',
      difficulty: data['difficulty'] ?? '',
      latitude: lat,
      longitude: lng,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'name': name,
      'description': description,
      'image': image,
      'ingredients': ingredients,
      'steps': steps,
      'cookingTime': cookingTime,
      'calories': calories,
      'servings': servings,
      'difficulty': difficulty,
      'latitude': latitude,
      'longitude': longitude,
    };
  }
}
