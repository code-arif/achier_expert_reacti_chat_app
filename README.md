# 💬 Reacti Backend

Welcome to the **Reacti** project — a **social chatting API** built with **Laravel**.  
It powers a modern **Flutter-based mobile application** with real-time communication, user connections, and social interactions.

---

## 🚀 Features

-   🔐 **User Authentication & Management** — Register, Login, Logout, and Profile management
-   💬 **Real-time Chat System** — Built with **Laravel Reverb** for smooth instant messaging
-   👥 **Friend Request System** — Send, Accept, Decline, and Cancel friend requests
-   🚫 **User Blocking System** — Block or unblock users easily
-   🧑‍🤝‍🧑 **User Relationship Management** — Manage and view connections seamlessly
-   📱 **Flutter Mobile App Integration** — Fully connected mobile frontend
-   📩 **Notifications & Status Updates** — Keep users updated in real time
-   ⚙️ **Clean RESTful API Design** — Follows standardized JSON response format

---

## 🛠️ Tech Stack

| Layer                       | Technology      |
| --------------------------- | --------------- |
| **Backend Framework**       | Laravel         |
| **Real-time Communication** | Laravel Reverb  |
| **Database**                | MySQL           |
| **Mobile Frontend**         | Flutter         |
| **Queue & Broadcasting**    | Redis / Pusher  |
| **Authentication**          | Laravel Sanctum |

---

## 📂 Project Structure

```
reacti-backend/
├── app/ # Application logic (Models, Controllers, Services)
├── routes/ # API and web routes
├── resources/ # Blade views (if any), assets
├── public/ # Public assets
├── database/ # Migrations, Seeders, Factories
└── config/ # App and package configurations
```


---

## 🏁 Getting Started

### 1️⃣ Clone the repository

```bash
git clone https://github.com/yourusername/reacti-backend.git
```

```base
composer install
```
```base
cp .env.example .env
```
```base
php artisan migrate --seed
```
```base
php artisan serve
```
```base
php artisan reverb:start
```

---

----------Made with ❤️ by the Stack Master Team----------

