# Simple Blog Application

Full-stack blog application built with **Laravel** and **React**. This project features a modern, responsive design, role-based access control, and a smooth user experience for sharing stories and engaging with others.

---

## Features

- **Feed**: Browse stories from the community in a beautiful, grid-based layout.
- **Story Management**: Authenticated users can create, edit, and delete their own posts via intuitive modals.
- **Interactive Comments**: Engage with stories through a real-time-like commenting system.
- **Role-Based Access Control**:
  - **User**: Can create posts and comments; manage their own content.
  - **Admin**: Has full control to moderate any post or comment across the platform.
- **Authentication**: Secure login and registration powered by Laravel Sanctum.
- **UI/UX**: Built with React 19 and Tailwind CSS 4, featuring glassmorphism, smooth animations, and a curated color palette.

---

## Tech Stack

### Backend
- **Framework**: Laravel 13
- **Language**: PHP 8.4
- **Authentication**: Laravel Sanctum
- **Database**: SQLite

### Frontend
- **Framework**: React 19 (Vite)
- **Language**: TypeScript
- **Styling**: Tailwind CSS 4
- **Routing**: React Router 7
- **HTTP Client**: Axios

---

## Getting Started

Follow these steps to set up the project locally.

### Prerequisites
- [PHP 8.4](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) & [pnpm](https://pnpm.io/)
- [SQLite](https://www.sqlite.org/index.html)

### Backend Setup (Laravel)

1. **Clone the repository**:
   ```bash
   git clone https://github.com/fantic14/simple-blog.git
   cd simple-blog/blog-app
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Configure environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Prepare the database**:
   - Ensure the `database` directory contains a `database.sqlite` file (or create it: `touch database/database.sqlite`).
   ```bash
   php artisan migrate --seed
   ```

5. **Start the server**:
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`.

### Frontend Setup (React)

1. **Navigate to the frontend directory**:
   ```bash
   cd simple-blog/blog-frontend
   ```

2. **Install dependencies**:
   ```bash
   pnpm install
   ```

3. **Start the development server**:
   ```bash
   pnpm run dev
   ```
   The application will be available at `http://localhost:5173`.

---

## API Endpoints

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | Register a new user | No |
| `POST` | `/api/login` | Log in and get token | No |
| `POST` | `/api/logout` | Log out and revoke token | **Yes** |
| `GET` | `/api/posts` | List all blog posts | No |
| `POST` | `/api/posts` | Create a new post | **Yes** |
| `GET` | `/api/posts/{id}` | Get specific post details | No |
| `PATCH` | `/api/posts/{id}` | Update an existing post | **Yes** (Author/Admin) |
| `DELETE` | `/api/posts/{id}` | Delete a post | **Yes** (Author/Admin) |
| `GET` | `/api/posts/{id}/comments` | List comments for a post | No |
| `POST` | `/api/posts/{id}/comments` | Add a comment to a post | **Yes** |
| `DELETE` | `/api/comments/{id}` | Delete a comment | **Yes** (Author/Admin) |
| `GET` | `/api/users/{id}` | Get user profile | No |

---

## Default Credentials

For testing purposes, the following admin account is created during seeding:

- **Email**: `admin@admin.com`
- **Password**: `admin`
- **Role**: `admin`

---

## Testing

Run Laravel feature tests to ensure API stability:
```bash
php artisan test
```

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
