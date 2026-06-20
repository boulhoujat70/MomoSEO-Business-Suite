<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mistral.php';

$result = '';

function saveHistory(PDO $pdo, string $module, string $input, string $generated): void
{
    $stmt = $pdo->prepare('INSERT INTO ai_history (module, input_text, generated_text) VALUES (?, ?, ?)');
    $stmt->execute([$module, $input, $generated]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['seo_generate'])) {
        $url = trim($_POST['url'] ?? '');
        $subject = trim($_POST['subject'] ?? '');

        $prompt = "Génère des balises SEO pour cette page.
URL : {$url}
Sujet : {$subject}

Contraintes :
- Titre SEO de moins de 60 caractères
- Meta-description de moins de 160 caractères
- Texte riche en mots-clés
- Format clair :
Titre SEO :
Meta-description :
Mots-clés :";

        $result = callMistralAI($prompt);
        saveHistory($pdo, 'Indexation & Métadonnées', $subject, $result);
    }

    if (isset($_POST['review_generate'])) {
        $review = trim($_POST['review'] ?? '');
        $tone = trim($_POST['tone'] ?? 'Professionnel');

        $prompt = "Rédige une réponse Google Business à cet avis client.

Avis :
{$review}

Ton demandé : {$tone}

Contraintes :
- Réponse professionnelle
- Répondre au positif ou traiter la réclamation
- Intégrer subtilement des mots-clés locaux :
service fiable, professionnel réactif, qualité de service, satisfaction client
- Réponse naturelle, courte et prête à publier.";

        $result = callMistralAI($prompt);
        saveHistory($pdo, 'Réponse Avis Client', $review, $result);
    }
}

$history = $pdo->query('SELECT * FROM ai_history ORDER BY created_at DESC LIMIT 20')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MomoSEO & Business Suite</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <div class="flex">
        <aside class="hidden md:block w-72 min-h-screen bg-slate-900 border-r border-slate-800 p-6">
            <div class="text-2xl font-bold mb-10">🚀 MomoSEO</div>
            <nav class="space-y-3 text-slate-300">
                <a class="block bg-blue-600 text-white px-4 py-3 rounded-xl" href="#seo">SEO Métadonnées</a>
                <a class="block hover:bg-slate-800 px-4 py-3 rounded-xl" href="#reviews">Avis Google</a>
                <a class="block hover:bg-slate-800 px-4 py-3 rounded-xl" href="#history">Historique</a>
            </nav>
        </aside>

        <main class="flex-1 p-6 md:p-10">
            <header class="mb-8">
                <h1 class="text-3xl md:text-4xl font-extrabold">MomoSEO & Business Suite</h1>
                <p class="text-slate-400 mt-2">Dashboard IA pour SEO, avis clients et réputation locale.</p>
            </header>

            <?php if ($result): ?>
                <section class="mb-8 bg-slate-900 border border-blue-500/40 rounded-2xl p-6">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-xl font-bold">Résultat généré</h2>
                        <button onclick="copyText('ai-result')" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-xl">Copier</button>
                    </div>
                    <textarea id="ai-result" class="w-full min-h-48 bg-slate-950 border border-slate-700 rounded-xl p-4 text-slate-100"><?= htmlspecialchars($result) ?></textarea>
                </section>
            <?php endif; ?>

            <section id="seo" class="grid lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-2xl font-bold mb-4">🔎 Indexation & Optimisation Métadonnées</h2>
                    <form method="POST" class="space-y-4">
                        <input name="url" type="url" placeholder="https://exemple.com/nouvelle-page" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-4 outline-none focus:border-blue-500">
                        <textarea name="subject" required placeholder="Décris le sujet de la page..." class="w-full min-h-36 bg-slate-950 border border-slate-700 rounded-xl p-4 outline-none focus:border-blue-500"></textarea>
                        <button name="seo_generate" class="w-full bg-blue-600 hover:bg-blue-500 font-bold py-4 rounded-xl">Générer les balises avec Mistral AI</button>
                    </form>
                </div>

                <div id="reviews" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                    <h2 class="text-2xl font-bold mb-4">💬 Assistant Avis Clients</h2>
                    <form method="POST" class="space-y-4">
                        <textarea name="review" required placeholder="Colle ici l'avis client Google..." class="w-full min-h-36 bg-slate-950 border border-slate-700 rounded-xl p-4 outline-none focus:border-green-500"></textarea>
                        <select name="tone" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-4">
                            <option>Professionnel</option>
                            <option>Chaleureux</option>
                            <option>Défensif</option>
                        </select>
                        <button name="review_generate" class="w-full bg-green-600 hover:bg-green-500 font-bold py-4 rounded-xl">Rédiger la réponse avec Mistral AI</button>
                    </form>
                </div>
            </section>

            <section id="history" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h2 class="text-2xl font-bold mb-4">📊 Historique & Suivi des Actions</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left bg-slate-800">
                                <th class="p-3">Date</th>
                                <th class="p-3">Action</th>
                                <th class="p-3">Texte d'origine</th>
                                <th class="p-3">Contenu IA</th>
                                <th class="p-3">Copier</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($history as $row): ?>
                            <tr class="border-t border-slate-800">
                                <td class="p-3 whitespace-nowrap"><?= htmlspecialchars($row['created_at']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($row['module']) ?></td>
                                <td class="p-3 max-w-xs truncate"><?= htmlspecialchars($row['input_text']) ?></td>
                                <td class="p-3 max-w-md truncate" id="copy-<?= $row['id'] ?>"><?= htmlspecialchars($row['generated_text']) ?></td>
                                <td class="p-3">
                                    <button onclick="copyText('copy-<?= $row['id'] ?>')" class="bg-purple-600 hover:bg-purple-500 px-3 py-2 rounded-lg">Copier</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

<script>
function copyText(id) {
    const el = document.getElementById(id);
    const text = el.value || el.innerText;
    navigator.clipboard.writeText(text);
    alert('Copié dans le presse-papier');
}
</script>
</body>
</html>
