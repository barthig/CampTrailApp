<?php

declare(strict_types=1);

// src/RouterConfig.php

use src\Controllers\DefaultController;
use src\Controllers\DashboardController;
use src\Controllers\SecurityController;
use src\Controllers\CamperController;
use src\Controllers\DestinationController;
use src\Controllers\RouteController;
use src\Controllers\ProfileController;
use src\Controllers\NotificationsController;
use src\Controllers\PermissionController;
use src\Controllers\RoleController;
use src\Controllers\StatsController;
use src\Controllers\NotificationController;
use src\Controllers\AdminController;

// Home and error
Router::get('', DefaultController::class, 'index');
Router::get('help', DefaultController::class, 'help');

// Dashboard
Router::get('dashboard', DashboardController::class, 'index');
Router::get('admin/dashboard', AdminController::class, 'dashboard');
Router::get('admin/users/edit/{id}', AdminController::class, 'editUser');

// Authentication
Router::get('login',     SecurityController::class, 'showLoginForm');
Router::post('login',    SecurityController::class, 'login');
Router::get('register',  SecurityController::class, 'showRegisterForm');
Router::post('register', SecurityController::class, 'register');
Router::get('logout',    SecurityController::class, 'logout');
Router::get('password_reset', SecurityController::class, 'showPasswordResetForm');
Router::get('inProgress', SecurityController::class, 'inProgress');


// Camper management
Router::get('campers',          CamperController::class, 'list');
Router::get('campers/create',   CamperController::class, 'create');
Router::post('campers/create',  CamperController::class, 'create');
Router::get('campers/edit',     CamperController::class, 'edit');
Router::post('campers/edit',    CamperController::class, 'edit');
Router::post('campers/delete',  CamperController::class, 'delete');
Router::get('campers/view',     CamperController::class, 'view');

// Destination management
Router::get('destinations',         DestinationController::class, 'list');
Router::get('destinations/create',  DestinationController::class, 'create');
Router::post('destinations/create', DestinationController::class, 'create');
Router::get('destinations/edit',    DestinationController::class, 'edit');
Router::post('destinations/edit',   DestinationController::class, 'edit');
Router::post('destinations/delete', DestinationController::class, 'delete');
// destination detail
Router::get('destinations/view',    DestinationController::class, 'view');

// Route management
Router::get('routes',         RouteController::class, 'list');
Router::get('routes/create',  RouteController::class, 'create');
Router::post('routes/create', RouteController::class, 'create');
Router::get('routes/edit',    RouteController::class, 'edit');
Router::post('routes/edit',   RouteController::class, 'edit');
Router::post('routes/delete', RouteController::class, 'delete');

// User profile
Router::get('profile',                    ProfileController::class, 'view');
Router::get('profile/edit',               ProfileController::class, 'edit');
Router::post('profile/update',             ProfileController::class, 'update');
Router::post('profile/change-password', ProfileController::class, 'changePassword');
Router::post('profile/update-avatar', ProfileController::class, 'updateAvatar');
Router::post('profile/delete-account',     ProfileController::class, 'deleteAccount');
Router::post('profile/save-notifications', ProfileController::class, 'saveNotifications');
Router::post('profile/emergency-contact', ProfileController::class, 'updateEmergencyContact');
Router::get('profile/export-db',          ProfileController::class, 'exportDb');

// Notifications
Router::get('notifications',                 NotificationsController::class, 'list');
Router::post('notifications/mark-read',      NotificationsController::class, 'markAsRead');
Router::post('notifications/mark-all-read',  NotificationsController::class, 'markAllRead');
Router::post('notifications/delete',         NotificationsController::class, 'delete');
Router::get('notifications/unread', NotificationsController::class, 'unreadJson');

// Permissions
Router::get('permissions',    PermissionController::class, 'list');
Router::post('permissions',   PermissionController::class, 'update');

// Roles
Router::get('roles',          RoleController::class, 'list');
Router::post('roles',         RoleController::class, 'update');
Router::get('stats',          StatsController::class, 'dashboard');
