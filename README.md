# Admin Auth Package

This package provides authentication features for the admin section of your Laravel application.

---

## ✨ Features

- Secure admin login/logout  
- Password hashing  
- Middleware protection for admin routes  
- Session management  

---

## 🛠️ Update `composer.json`

To use the package from a local or VCS path, add the following to your `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/jahnavimathur29/admin-auth.git"
    }
]
```
## 📦 Installation

```json
composer require admin/admin_auth

```

## 🚀 Usage
```json
php artisan vendor:publish --provider="admin\\admin_auth\\AdminModuleServiceProvider"
```

## Run migrations:
```json
php artisan migrate
```

## Protect admin routes using middleware:
```json
Route::middleware(['admin.auth'])->group(function () {
    // Admin routes here
});
```
