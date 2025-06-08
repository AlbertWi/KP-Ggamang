import 'package:flutter/material.dart';
import 'package:google_generative_ai/google_generative_ai.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:uas_pab/models/recipe.dart';

class AiAssistantScreen extends StatefulWidget {
  const AiAssistantScreen({super.key});

  @override
  State<AiAssistantScreen> createState() => _AiAssistantScreenState();
}

class _AiAssistantScreenState extends State<AiAssistantScreen> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<ChatMessage> _messages = [];
  bool _isLoading = false;

  // Ganti dengan API key Gemini Anda
  late final GenerativeModel _model;

  @override
  void initState() {
    super.initState();
    _initializeGemini();
    _addWelcomeMessage();
  }

  void _initializeGemini() {
    _model = GenerativeModel(
      model: 'gemini-1.5-flash',
      apiKey: 'AIzaSyAJfK7OdCaO4ek-btsz8hHcun2bJrZqwiY',
    );
  }

  void _addWelcomeMessage() {
    setState(() {
      _messages.add(ChatMessage(
        text: "👋 Halo! Saya adalah AI Assistant untuk resep makanan sehat!\n\n"
            "Saya akan merekomendasikan resep dari komunitas pengguna yang sudah berbagi resep mereka di aplikasi ini.\n\n"
            "💡 Anda bisa bertanya tentang:\n"
            "• Makanan rendah kalori (< 300 kkal)\n"
            "• Resep mudah dan cepat dibuat\n"
            "• Makanan tinggi protein\n"
            "• Resep dengan bahan tertentu\n"
            "• Makanan untuk diet sehat\n\n"
            "🍽️ Semua rekomendasi berasal dari resep nyata yang sudah di-upload oleh pengguna lain!\n\n"
            "Silakan tanya apa saja tentang makanan sehat! 😊",
        isUser: false,
        timestamp: DateTime.now(),
      ));
    });
  }

  Future<List<Recipe>> _getRecipesFromFirestore() async {
    try {
      final QuerySnapshot snapshot =
          await FirebaseFirestore.instance.collection('recipes').get();

      return snapshot.docs.map((doc) => Recipe.fromFirestore(doc)).toList();
    } catch (e) {
      print('Error fetching recipes: $e');
      return [];
    }
  }

  String _createRecipeContext(List<Recipe> recipes) {
    if (recipes.isEmpty)
      return "Saat ini belum ada resep yang tersedia di database. Silakan upload resep sehat Anda terlebih dahulu!";

    StringBuffer context = StringBuffer();
    context.writeln(
        "Berikut adalah daftar resep makanan sehat yang telah di-upload oleh komunitas pengguna:");

    for (int i = 0; i < recipes.length && i < 20; i++) {
      // Batasi untuk menghindari token limit
      Recipe recipe = recipes[i];

      // Format difficulty yang lebih user-friendly
      String difficultyText = recipe.difficulty;
      switch (recipe.difficulty.toLowerCase()) {
        case 'easy':
          difficultyText = 'Mudah';
          break;
        case 'medium':
          difficultyText = 'Sedang';
          break;
        case 'hard':
          difficultyText = 'Sulit';
          break;
      }

      context.writeln("""
${i + 1}. ${recipe.name}
   - Kalori: ${recipe.calories} kkal per porsi
   - Waktu Memasak: ${recipe.cookingTime}
   - Porsi: ${recipe.servings} orang
   - Tingkat Kesulitan: $difficultyText
   - Bahan Utama: ${recipe.ingredients.take(5).join(', ')}${recipe.ingredients.length > 5 ? ', dll.' : ''}
   - Langkah Memasak: ${recipe.steps.take(2).join('; ')}${recipe.steps.length > 2 ? '... (lanjutan)' : ''}
      """);
    }

    context.writeln(
        "\nTotal ${recipes.length} resep tersedia dari komunitas pengguna.");
    return context.toString();
  }

  Future<void> _sendMessage() async {
    if (_messageController.text.trim().isEmpty) return;

    final userMessage = _messageController.text.trim();
    _messageController.clear();

    setState(() {
      _messages.add(ChatMessage(
        text: userMessage,
        isUser: true,
        timestamp: DateTime.now(),
      ));
      _isLoading = true;
    });

    _scrollToBottom();

    try {
      // Ambil data resep dari Firestore
      final recipes = await _getRecipesFromFirestore();
      final recipeContext = _createRecipeContext(recipes);

      // Buat prompt untuk Gemini
      final prompt = """
Anda adalah asisten AI untuk aplikasi resep makanan sehat komunitas. 
Semua resep berikut ini adalah kontribusi NYATA dari pengguna aplikasi yang sudah berbagi resep mereka.

$recipeContext

Pertanyaan pengguna: "$userMessage"

TUGAS ANDA:
1. Analisis kebutuhan nutrisi dan preferensi dari pertanyaan pengguna
2. Pilih 2-3 resep TERBAIK dari daftar di atas yang paling sesuai dengan permintaan
3. Jelaskan MENGAPA setiap resep cocok (berdasarkan kalori, bahan, waktu memasak, kesulitan)
4. Berikan informasi lengkap tentang resep yang direkomendasikan
5. Tambahkan tips memasak atau variasi jika relevan
6. Apresiasi kontribusi komunitas pengguna

FORMAT JAWABAN:
- Mulai dengan sapaan hangat
- Sebutkan bahwa ini adalah resep dari komunitas pengguna
- Rekomendasikan resep dengan penjelasan detail mengapa cocok
- Berikan rangkuman informasi penting (kalori, waktu, kesulitan)
- Akhiri dengan motivasi untuk mencoba atau berkontribusi resep

KRITERIA UMUM:
- "rendah kalori" = kurang dari 300 kalori
- "tinggi protein" = fokus pada resep dengan telur, ayam, ikan, tahu, tempe
- "mudah dibuat" = tingkat kesulitan "easy"
- "cepat" = waktu memasak kurang dari 30 menit
- "diet" = kalori rendah dan bahan-bahan sehat

Jika tidak ada resep yang sesuai, sarankan pengguna untuk upload resep mereka sendiri dan berikan tips umum makanan sehat.

Jawab dalam bahasa Indonesia yang ramah dan antusias!
""";

      final content = [Content.text(prompt)];
      final response = await _model.generateContent(content);

      setState(() {
        _messages.add(ChatMessage(
          text: response.text ??
              "Maaf, terjadi kesalahan dalam memproses permintaan Anda.",
          isUser: false,
          timestamp: DateTime.now(),
          recommendedRecipes:
              _extractRecommendedRecipes(response.text ?? "", recipes),
        ));
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _messages.add(ChatMessage(
          text: "Maaf, terjadi kesalahan: ${e.toString()}",
          isUser: false,
          timestamp: DateTime.now(),
        ));
        _isLoading = false;
      });
    }

    _scrollToBottom();
  }

  List<Recipe> _extractRecommendedRecipes(
      String response, List<Recipe> allRecipes) {
    // Logika sederhana untuk mengekstrak resep yang direkomendasikan
    List<Recipe> recommended = [];

    for (Recipe recipe in allRecipes) {
      if (response.toLowerCase().contains(recipe.name.toLowerCase())) {
        recommended.add(recipe);
        if (recommended.length >= 3) break; // Batasi maksimal 3 rekomendasi
      }
    }

    return recommended;
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: const Text(
          'AI Assistant',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        backgroundColor: Colors.green[600],
        elevation: 0,
        centerTitle: true,
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                return ChatBubble(message: _messages[index]);
              },
            ),
          ),
          if (_isLoading)
            Container(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  const CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.green),
                  ),
                  const SizedBox(width: 16),
                  Text(
                    'AI sedang berpikir...',
                    style: TextStyle(
                      color: Colors.grey[600],
                      fontStyle: FontStyle.italic,
                    ),
                  ),
                ],
              ),
            ),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  offset: const Offset(0, -2),
                  blurRadius: 8,
                  color: Colors.black.withOpacity(0.1),
                ),
              ],
            ),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'Tanya tentang makanan sehat...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(25),
                        borderSide: BorderSide.none,
                      ),
                      filled: true,
                      fillColor: Colors.grey[100],
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 20,
                        vertical: 12,
                      ),
                    ),
                    maxLines: null,
                    textInputAction: TextInputAction.send,
                    onSubmitted: (_) => _sendMessage(),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  decoration: BoxDecoration(
                    color: Colors.green[600],
                    shape: BoxShape.circle,
                  ),
                  child: IconButton(
                    icon: const Icon(Icons.send, color: Colors.white),
                    onPressed: _isLoading ? null : _sendMessage,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }
}

class ChatMessage {
  final String text;
  final bool isUser;
  final DateTime timestamp;
  final List<Recipe>? recommendedRecipes;

  ChatMessage({
    required this.text,
    required this.isUser,
    required this.timestamp,
    this.recommendedRecipes,
  });
}

class ChatBubble extends StatelessWidget {
  final ChatMessage message;

  const ChatBubble({super.key, required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      child: Column(
        crossAxisAlignment:
            message.isUser ? CrossAxisAlignment.end : CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: message.isUser
                ? MainAxisAlignment.end
                : MainAxisAlignment.start,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (!message.isUser) ...[
                CircleAvatar(
                  backgroundColor: Colors.green[600],
                  radius: 20,
                  child: const Icon(
                    Icons.smart_toy,
                    color: Colors.white,
                    size: 20,
                  ),
                ),
                const SizedBox(width: 8),
              ],
              Flexible(
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: message.isUser ? Colors.green[600] : Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        offset: const Offset(0, 2),
                        blurRadius: 8,
                        color: Colors.black.withOpacity(0.1),
                      ),
                    ],
                  ),
                  child: Text(
                    message.text,
                    style: TextStyle(
                      color: message.isUser ? Colors.white : Colors.black87,
                      fontSize: 16,
                      height: 1.4,
                    ),
                  ),
                ),
              ),
              if (message.isUser) ...[
                const SizedBox(width: 8),
                CircleAvatar(
                  backgroundColor: Colors.blue[600],
                  radius: 20,
                  child: const Icon(
                    Icons.person,
                    color: Colors.white,
                    size: 20,
                  ),
                ),
              ],
            ],
          ),
          if (message.recommendedRecipes?.isNotEmpty == true)
            Padding(
              padding: const EdgeInsets.only(top: 12),
              child: _buildRecipeRecommendations(message.recommendedRecipes!),
            ),
          Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(
              _formatTime(message.timestamp),
              style: TextStyle(
                color: Colors.grey[500],
                fontSize: 12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecipeRecommendations(List<Recipe> recipes) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Text(
            'Rekomendasi Resep:',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: Colors.green[700],
              fontSize: 14,
            ),
          ),
        ),
        ...recipes
            .map((recipe) => Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.green[50],
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.green[200]!),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        recipe.name,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${recipe.calories} kkal • ${recipe.cookingTime} • ${recipe.difficulty}',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ))
            .toList(),
      ],
    );
  }

  String _formatTime(DateTime time) {
    return '${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
  }
}
