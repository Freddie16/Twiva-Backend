🧠 Gamification Trivia Platform — Laravel Backend
This is the Laravel backend API for the Gamification Trivia Platform. It manages user authentication, game creation, session handling, and leaderboard logic.

🧰 Tech Stack
Framework: Laravel

Auth: Laravel Sanctum (API token-based)

Database: MySQL / PostgreSQL / SQLite

Package Manager: Composer

📋 Prerequisites
PHP 8.1+

Composer

A database server

Web server (Apache/Nginx or php artisan serve)

⚙️ Installation
1. Clone the Repository
bash
Copy
Edit
git clone <your_backend_repo_url>
cd your_backend_repo_directory
2. Install Dependencies
bash
Copy
Edit
composer install
3. Setup Environment
bash
Copy
Edit
cp .env.example .env
Then edit .env and configure:

Database credentials

APP_URL

APP_DEBUG=true (for development)

CORS settings

4. Generate App Key
bash
Copy
Edit
php artisan key:generate
5. Run Migrations & Seeders
bash
Copy
Edit
php artisan migrate
php artisan db:seed  # Optional
6. Install Laravel Sanctum
bash
Copy
Edit
php artisan sanctum:install
Ensure the following middleware are enabled in app/Http/Kernel.php:

EnsureFrontendRequestsAreStateful

EncryptCookies

7. Launch the Server
bash
Copy
Edit
php artisan serve
Default: http://127.0.0.1:8000

🔐 Authentication (Sanctum)

Endpoint	Method	Description
/api/register	POST	Register a new user
/api/login	POST	Login user and receive token
/api/logout	POST	Logout authenticated user
Use Authorization: Bearer <token> header for all protected routes.

🎮 Game Management (Auth Required)

Endpoint	Method	Description
/api/games	GET	List user’s games
/api/games	POST	Create a new game
/api/games/{id}	GET	Get game details
/api/games/{id}	PUT	Update game
/api/games/{id}	DELETE	Delete game
Games contain:

Title, description

Questions with text, points

Answers with text, is_correct flag

🧩 Game Session APIs

Endpoint	Method	Description
/api/games/{game}/sessions	POST	Create session
/api/game-sessions/join	POST	Join session via code
/api/game-sessions/{id}	GET	Session details
/api/game-sessions/{id}/start	POST	Start the session
/api/game-sessions/{id}/answer	POST	Submit an answer
/api/game-sessions/{id}/finish	POST	End the session
/api/game-sessions/{id}/leaderboard	GET	View leaderboard
🧱 Data Relationships
User → has many Games

Game → has many Questions

Question → has many Answers

GameSession → belongs to Game, has many Players

GameSessionPlayer → belongs to User and Session

PlayerAnswer → links Player and Question

