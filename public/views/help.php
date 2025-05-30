<?php

$appName = $config['appName'] ?? 'Kamper';


$faqs = [
    [
        'question' => 'Jak dodać nowego kampera (pojazd) do systemu?',
        'answer'   => 'Przejdź do zakładki „Kampery”, kliknij „Dodaj kampera” i wypełnij formularz z danymi pojazdu (marka, model, rok, VIN, pojemność).',
    ],
    [
        'question' => 'W jaki sposób zaplanować trasę dla kampera?',
        'answer'   => 'W zakładce „Trasy” wybierz „Nowa trasa”, wskaż punkt początkowy i końcowy (z listy Destinations), wybierz kampera i kliknij „Zapisz”.',
    ],
    [
        'question' => 'Jak ustawić przypomnienie o zbliżającym się przeglądzie technicznym?',
        'answer'   => 'Przejdź do profilu pojazdu (kampera), w sekcji Przeglądy dodaj nową datę przeglądu — system automatycznie wyśle powiadomienie przed upływem terminu.',
    ],
    [
        'question' => 'Jak zmienić ustawienia powiadomień e-mail?',
        'answer'   => 'W swoim profilu kliknij „Edytuj”, a następnie w sekcji Powiadomienia zaznacz, czy chcesz otrzymywać alerty o przeglądach, polisach i trasach.',
    ],
    [
        'question' => 'Gdzie mogę podejrzeć statystyki przebytych tras?',
        'answer'   => 'W dashboardzie statystyk (Dashboard) znajdziesz liczbę tras, łączny dystans i czas podróży wszystkich swoich kamperów.',
    ],
    [
        'question' => 'Jak dodać nowe miejsce docelowe?',
        'answer'   => 'Przejdź do zakładki „Miejsca docelowe”, kliknij „Dodaj miejsce”, uzupełnij nazwę, współrzędne i opis, a następnie zapisz.',
    ],
    [
        'question' => 'Jak edytować dane mojego profilu (email, imię, bio)?',
        'answer'   => 'Wybierz „Profil” → „Edytuj”, wprowadź zmiany i kliknij „Zapisz”. Po aktualizacji profil zostanie odświeżony.',
    ],
];

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ — <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/public/css/help.css">
    <link rel="stylesheet" href="/public/css/sidebar.css">
    <link rel="stylesheet" href="/public/css/footer.css">
    
    <link rel="stylesheet" href="/public/css/notifications_bell.css">
  <script defer src="/public/js/notifications.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="faq-page container">
        <header>
            <h1>Najczęściej zadawane pytania</h1>
        </header>
        <div class="faq-search">
            <input id="faq-search" type="text" placeholder="Szukaj w FAQ..."> <i class="fas fa-search"></i>
        </div>
        <section id="faq-list" class="faq-list">
            <?php foreach ($faqs as $item): ?>
                <div class="faq-item">
                    <h2 class="question"><?= htmlspecialchars($item['question'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <div class="answer"><?= nl2br(htmlspecialchars($item['answer'], ENT_QUOTES, 'UTF-8')) ?></div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
    <script src="/public/js/help.js" defer></script>
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>
