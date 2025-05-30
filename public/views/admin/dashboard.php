<?php
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora</title>
    <link rel="stylesheet" href="/public/css/admin_dashboard.css">
</head>

<body>
    <header class="admin-header">
        <div class="container">
            <h1>Panel Administratora</h1>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/dashboard" class="active">Dashboard</a></li>
                    <li><a href="/dashboard">Dashboard(user)</a></li>
                    <li><a href="/logout">Wyloguj</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-main container">
        <?php if (!empty($flashes)) : ?>
            <div class="flashes">
                <?php foreach ($flashes as $type => $messages) : ?>
                    <?php foreach ($messages as $message) : ?>
                        <div class="flash flash-<?= htmlspecialchars($type) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (in_array('manage_users', $permissions)) : ?>
            <section class="users-table">
                <h2>Zarządzanie użytkownikami</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Imię</th>
                            <th>Nazwisko</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role_name']) ?></td>
                                <td>
                                    <a href="/admin/users/edit/<?= (int)$user['id'] ?>">Edytuj</a> |
                                    <a href="/admin/users/delete/<?= (int)$user['id'] ?>" onclick="return confirm('Czy na pewno usunąć użytkownika?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <?php if (in_array('manage_campers', $permissions)) : ?>
            <section class="campers-table">
                <h2>Zarządzanie pojazdami</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campers as $camper) : ?>
                            <tr>
                                <td><?= (int)$camper['id'] ?></td>
                                <td><?= htmlspecialchars($camper['name']) ?></td>
                                <td><?= htmlspecialchars($camper['model']) ?></td>
                                <td><?= htmlspecialchars($camper['type']) ?></td>
                                <td>
                                    <a href="/admin/campers/edit/<?= (int)$camper['id'] ?>">Edytuj</a> |
                                    <a href="/admin/campers/delete/<?= (int)$camper['id'] ?>" onclick="return confirm('Czy na pewno usunąć ten pojazd?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <?php if (in_array('manage_destinations', $permissions)) : ?>
            <section class="destinations-table">
                <h2>Zarządzanie destynacjami</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>lokalizacja</th>
                            <th>cena</th>
                            <th>pojemnosc</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $dest) : ?>
                            <tr>
                                <td><?= (int)$dest['id'] ?></td>
                                <td><?= htmlspecialchars($dest['name']) ?></td>
                                <td><?= htmlspecialchars($dest['location']) ?></td>
                                <td><?= htmlspecialchars($dest['price']) ?></td>
                                <td><?= htmlspecialchars($dest['capacity']) ?></td>
                                <td>
                                    <a href="/admin/destinations/edit/<?= (int)$dest['id'] ?>">Edytuj</a> |
                                    <a href="/admin/destinations/delete/<?= (int)$dest['id'] ?>" onclick="return confirm('Czy na pewno usunąć destynację?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <?php if (in_array('manage_routes', $permissions)) : ?>
            <section class="routes-table">
                <h2>Zarządzanie trasami</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>utworzona</th>
                            <th>start</th>
                            <th>meta</th>
                            <th>kamper</th>
                            <th>Długość (km)</th>
                            <th>user_id</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routes as $route) : ?>
                            <tr>
                                <td><?= (int)$route['route_id'] ?></td>
                                <td><?= htmlspecialchars($route['created_at']) ?></td>
                                <td><?= htmlspecialchars($route['origin']) ?></td>

                                <td><?= htmlspecialchars($route['destination']) ?></td>
                                <td><?= htmlspecialchars($route['camper_name']) ?></td>
                                <td><?= (float)$route['distance'] ?></td>
                                <td><?= htmlspecialchars($route['user_id']) ?></td>
                                <td>
                                    <a href="/admin/routes/edit/<?= (int)$route['route_id'] ?>">Edytuj</a> |
                                    <a href="/admin/routes/delete/<?= (int)$route['route_id'] ?>" onclick="return confirm('Czy na pewno usunąć trasę?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <?php if (in_array('manage_notifications', $permissions)) : ?>
            <section class="notifications-table">
                <h2>Zarządzanie powiadomieniami</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ID użytkownika</th>
                            <th>Treść</th>
                            <th>Przeczytane</th>
                            <th>Data utworzenia</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $note) : ?>
                            <tr>
                                <td><?= (int)$note['id'] ?></td>
                                <td><?= (int)$note['user_id'] ?></td>
                                <td><?= htmlspecialchars($note['message']) ?></td>
                                <td><?= $note['is_read'] ? 'Tak' : 'Nie' ?></td>
                                <td><?= htmlspecialchars($note['created_at']) ?></td>
                                <td>
                                    <a href="/admin/notifications/edit/<?= (int)$note['id'] ?>">Edytuj</a> |
                                    <a href="/admin/notifications/delete/<?= (int)$note['id'] ?>" onclick="return confirm('Czy na pewno usunąć powiadomienie?');">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

    </main>

    <footer class="admin-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> CampTrail</p>
        </div>
    </footer>
</body>

</html>