import 'package:google_generative_ai/google_generative_ai.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:uas_pab/models/recipe.dart';

class GeminiService {
  static const String _apiKey = 'AIzaSyAJfK7OdCaO4ek-btsz8hHcun2bJrZqwiY';
  late final GenerativeModel _model;

  GeminiService() {
    _model = GenerativeModel(
      model: 'gemini-1.5-flash',
      apiKey: _apiKey,
      generationConfig: GenerationConfig(
        temperature: 0.7,
        topK: 40,
        topP: 0.95,
        maxOutputTokens: 1024,
      ),
    );
  }

  Future<String> getHealthyFoodRecommendation({
    required String userQuery,
    required List<Recipe> availableRecipes,
  }) async {
    try {
      final prompt = _buildPrompt(userQuery, availableRecipes);
      final content = [Content.text(prompt)];
      final response = await _model.generateContent(content);

      return response.text ??
          "Maaf, saya tidak dapat memproses permintaan Anda saat ini.";
    } catch (e) {
      throw Exception('Error generating response: $e');
    }
  }

  String _buildPrompt(String userQuery, List<Recipe> recipes) {
    final recipeContext = _formatRecipesForPrompt(recipes);

    return """
Anda adalah AI Assistant untuk aplikasi resep makanan sehat berbasis komunitas.
Semua resep yang akan Anda rekomendasikan adalah kontribusi NYATA dari pengguna aplikasi.

DATA RESEP KOMUNITAS:
$recipeContext

PERTANYAAN PENGGUNA: "$userQuery"

INSTRUKSI DETAIL:
1. Analisis kebutuhan nutrisi dan preferensi dari pertanyaan pengguna
2. Pilih 2-3 resep TERBAIK dari komunitas yang paling sesuai
3. Jelaskan mengapa setiap resep cocok (kalori, bahan, waktu, kesulitan)
4. Berikan informasi lengkap tentang resep (bahan, langkah, tips)
5. Apresiasi kontribusi pengguna yang sudah berbagi resep
6. Motivasi untuk mencoba resep atau berbagi resep sendiri

FORMAT JAWABAN:
- Sapaan hangat dan mention bahwa ini resep dari komunitas
- Rekomendasi resep dengan penjelasan detail
- Informasi nutrisi dan praktis
- Tips memasak atau variasi
- Ajakan untuk berkontribusi ke komunitas

KRITERIA PENCARIAN:
- "rendah kalori" = < 300 kalori per porsi
- "tinggi protein" = resep dengan telur, ayam, ikan, tahu, tempe
- "mudah dibuat" = tingkat kesulitan "easy" 
- "cepat" = waktu memasak < 30 menit
- "diet sehat" = kombinasi rendah kalori + bahan sehat

JIKA TIDAK ADA RESEP YANG COCOK:
- Berikan saran umum tentang makanan sehat
- Ajak pengguna untuk upload resep mereka sendiri
- Jelaskan manfaat berbagi resep dengan komunitas

Jawab dalam bahasa Indonesia yang antusias dan ramah!
""";
  }

  String _formatRecipesForPrompt(List<Recipe> recipes) {
    if (recipes.isEmpty) {
      return "Tidak ada resep yang tersedia saat ini.";
    }

    final StringBuffer buffer = StringBuffer();

    for (int i = 0; i < recipes.length && i < 15; i++) {
      // Batasi untuk menghindari token limit
      final recipe = recipes[i];
      buffer.writeln("""
${i + 1}. ${recipe.name}
   Kalori: ${recipe.calories} kkal
   Waktu: ${recipe.cookingTime}
   Porsi: ${recipe.servings}
   Kesulitan: ${recipe.difficulty}
   Bahan utama: ${recipe.ingredients.take(3).join(', ')}
   Deskripsi: ${recipe.description}
""");
    }

    return buffer.toString();
  }

  // Method untuk menganalisis intent pengguna
  Map<String, dynamic> analyzeUserIntent(String query) {
    final lowerQuery = query.toLowerCase();

    return {
      'isLowCalorie': _containsKeywords(
          lowerQuery, ['rendah kalori', 'diet', 'kurus', 'turun berat']),
      'isHighProtein': _containsKeywords(
          lowerQuery, ['protein tinggi', 'protein', 'otot', 'gym']),
      'isQuick': _containsKeywords(
          lowerQuery, ['cepat', 'praktis', 'mudah', 'simple']),
      'isVegetarian':
          _containsKeywords(lowerQuery, ['vegetarian', 'sayur', 'nabati']),
      'maxCalories': _extractCalorieLimit(lowerQuery),
      'ingredients': _extractIngredients(lowerQuery),
    };
  }

  bool _containsKeywords(String query, List<String> keywords) {
    return keywords.any((keyword) => query.contains(keyword));
  }

  int? _extractCalorieLimit(String query) {
    final regex = RegExp(r'(\d+)\s*kalori');
    final match = regex.firstMatch(query);
    return match != null ? int.tryParse(match.group(1)!) : null;
  }

  List<String> _extractIngredients(String query) {
    // Daftar bahan makanan umum
    final commonIngredients = [
      'ayam',
      'ikan',
      'daging',
      'telur',
      'tahu',
      'tempe',
      'brokoli',
      'bayam',
      'wortel',
      'tomat',
      'mentimun',
      'nasi',
      'oat',
      'quinoa',
      'kentang',
      'ubi'
    ];

    return commonIngredients
        .where((ingredient) => query.toLowerCase().contains(ingredient))
        .toList();
  }
}

// Service untuk mengelola data Recipe dari Firestore
class RecipeService {
  static final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  static Future<List<Recipe>> getAllRecipes() async {
    try {
      final QuerySnapshot snapshot = await _firestore
          .collection('recipes')
          .orderBy('createdAt', descending: true)
          .get();

      return snapshot.docs.map((doc) => Recipe.fromFirestore(doc)).toList();
    } catch (e) {
      print('Error fetching recipes: $e');
      return [];
    }
  }

  static Future<List<Recipe>> getFilteredRecipes({
    int? maxCalories,
    String? difficulty,
    List<String>? ingredients,
  }) async {
    try {
      Query query = _firestore.collection('recipes');

      if (maxCalories != null) {
        query = query.where('calories', isLessThanOrEqualTo: maxCalories);
      }

      if (difficulty != null) {
        query = query.where('difficulty', isEqualTo: difficulty);
      }

      final QuerySnapshot snapshot = await query.get();
      List<Recipe> recipes =
          snapshot.docs.map((doc) => Recipe.fromFirestore(doc)).toList();

      // Filter berdasarkan bahan jika ada
      if (ingredients != null && ingredients.isNotEmpty) {
        recipes = recipes.where((recipe) {
          return ingredients.any((ingredient) => recipe.ingredients.any(
              (recipeIngredient) => recipeIngredient
                  .toLowerCase()
                  .contains(ingredient.toLowerCase())));
        }).toList();
      }

      return recipes;
    } catch (e) {
      print('Error fetching filtered recipes: $e');
      return [];
    }
  }

  static Stream<List<Recipe>> getRecipesStream() {
    return _firestore
        .collection('recipes')
        .orderBy('createdAt', descending: true)
        .snapshots()
        .map((snapshot) =>
            snapshot.docs.map((doc) => Recipe.fromFirestore(doc)).toList());
  }
}
