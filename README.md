# HelpDesk — Plateforme de Gestion de Tickets 🎫

**Résumé:** HelpDesk est une application full-stack de gestion de tickets d'assistance basée sur **Laravel** (backend) et **Vue 3** (frontend). Elle permet aux utilisateurs authentifiés de créer, assigner, prioriser et suivre des tickets avec un système de rôles et permissions (Admin, Agent, Utilisateur). L'application offre une gestion complète du cycle de vie des tickets avec commentaires en temps réel, filtrage avancé et interface responsive.

---

## Aperçu

- **Langages:** **PHP 7.4+** (backend), **TypeScript/JavaScript** (frontend)
- **Framework Backend:** **Laravel 10+** avec Sanctum (authentification token-based)
- **Framework Frontend:** **Vue 3** avec Composition API, Pinia, Vue Router
- **Base de données:** **MySQL 8+**
- **Styling:** **Tailwind CSS v4**
- **Architecture Backend:** Services Pattern, Form Requests, Eloquent ORM
- **Gestion d'état Frontend:** **Pinia**
- **HTTP Client:** **Axios**

---

## Architecture (vue d'ensemble)

```
helpdesk/
│
├── backend/                              # Application Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/              # API Controllers (TicketController, UserController, etc.)
│   │   │   ├── Requests/                 # Form Requests (validation)
│   │   │   └── Resources/                # API Resources (JSON formatting)
│   │   ├── Services/                     # Business Logic (TicketService, UserService)
│   │   ├── Models/                       # Eloquent Models (User, Ticket, Comment, Role)
│   │   ├── Enums/                        # Constants (Role, Status, Priority)
│   │   └── Middleware/
│   ├── database/
│   │   ├── migrations/                   # Schema definitions
│   │   └── seeders/                      # Data seeders
│   ├── routes/
│   │   ├── api.php                       # Routes API (endpoints)
│   │   └── web.php
│   ├── .env                              # Configuration environment
│   ├── composer.json
│   └── artisan
│
└── frontend/                             # Application Vue 3
    ├── src/
    │   ├── views/
    │   │   ├── auth/                     # Login, Register pages
    │   │   ├── tickets/                  # Ticket listing, detail, create pages
    │   │   └── users/                    # User management pages
    │   ├── components/                   # Reusable Vue components
    │   ├── stores/                       # Pinia stores (auth, tickets, users)
    │   ├── services/                     # API service layer (axios calls)
    │   ├── router/
    │   │   └── index.js                  # Vue Router config
    │   ├── App.vue
    │   └── main.js
    ├── public/
    ├── index.html
    ├── vite.config.js
    ├── package.json
    └── .env.local                        # Frontend config
```

### Flux de données

```
User (Frontend)
    ↓
Vue Component (affichage + événements)
    ↓
Pinia Store (state management)
    ↓
Axios Service (requête HTTP)
    ↓
Laravel API Endpoint
    ↓
Form Request (validation)
    ↓
Controller → Service (logique métier)
    ↓
Eloquent Model → Database Query
    ↓
Response JSON
    ↓
Frontend Store → Component Re-render
```

---

## Outils & Logiciels Utilisés

### Backend

| Outil | Version | Usage |
|-------|---------|-------|
| **PHP** | 7.4+ | Langage |
| **Laravel** | 10+ | Framework web |
| **Sanctum** | Built-in | Authentification API |
| **Eloquent** | Built-in | ORM |
| **Composer** | Latest | Gestionnaire de dépendances |
| **MySQL** | 8+ | Base de données |
| **PHPUnit** | Built-in | Testing |

### Frontend

| Outil | Version | Usage |
|-------|---------|-------|
| **Vue** | 3 | Framework UI |
| **Pinia** | Latest | State management |
| **Vue Router** | 4+ | Routing |
| **Axios** | Latest | HTTP client |
| **Vite** | Latest | Build tool |
| **Tailwind CSS** | v4 | Styling |
| **Node.js** | 16+ | Runtime |
| **npm** | Latest | Gestionnaire de packages |

---

## Démarrage du Projet

### Prérequis Système

```bash
# Vérifier les versions installées
php --version                    # PHP 7.4 minimum
node --version                   # Node.js 16+
npm --version
mysql --version                  # MySQL 8+
composer --version
```

### Installation Complète (Backend + Frontend)

#### 1️⃣ Cloner le repository

```bash
git clone https://github.com/YossrGalai/HelpDesk.git
cd helpdesk
```

#### 2️⃣ Configuration Backend

```bash
# Naviguer vers le dossier backend
cd backend

# Installer les dépendances PHP
composer install

# Créer le fichier .env
cp .env.example .env

# Générer la clé de l'application
php artisan key:generate

# Configuration de la base de données (éditer .env)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=helpdesk
# DB_USERNAME=root
# DB_PASSWORD=<votre_mot_de_passe>

# Créer la base de données MySQL
mysql -u root -p -e "CREATE DATABASE helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Exécuter les migrations
php artisan migrate

# Remplir la base avec des données test
php artisan db:seed

# Démarrer le serveur Laravel
php artisan serve
# Le backend est disponible sur http://localhost:8000
# Documentation API : http://localhost:8000/api/docs (si Swagger installé)
```

#### 3️⃣ Configuration Frontend

```bash
# Naviguer vers le dossier frontend
cd ../frontend

# Installer les dépendances Node
npm install

# Créer le fichier .env.local avec l'URL du backend
cat > .env.local << EOF
VITE_API_URL=http://localhost:8000
EOF

# Démarrer le serveur de développement
npm run dev
# L'application est disponible sur http://localhost:5173
```

#### 4️⃣ Vérification

Ouvrir deux terminaux :

**Terminal 1 (Backend):**
```bash
cd backend
php artisan serve
```

**Terminal 2 (Frontend):**
```bash
cd frontend
npm run dev
```

Accéder à `http://localhost:5173` dans le navigateur.

---

## Fonctionnalités Implémentées

### ✅ Phase 0 — Authentification

- Inscription (signup) avec validation email
- Connexion (login) avec token Sanctum
- Déconnexion (logout) avec invalidation token
- Routes protégées (authentification requise)
- Gestion d'état auth (Pinia store)

**Endpoints:**
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/user (authentifié)
```

### ✅ Phase 1 — Gestion des Tickets

- **Créer** un ticket (titre, description, créateur)
- **Éditer** un ticket (seulement par créateur ou admin)
- **Consulter** les détails d'un ticket
- **Lister** les tickets (avec pagination)
- **Fermer** un ticket (changement de statut)
- Statuts contrôlés : `Open`, `In Progress`, `Closed`

**Endpoints:**
```
GET    /api/tickets
POST   /api/tickets
GET    /api/tickets/{id}
PUT    /api/tickets/{id}
DELETE /api/tickets/{id}
```

### ✅ Phase 2 — Commentaires & Conversations

- Ajouter des commentaires aux tickets
- Afficher l'historique des commentaires (immuable = lecture seule)
- Timeline de conversation avec timestamps
- Affichage du commentaire + auteur + date

**Endpoints:**
```
POST   /api/tickets/{id}/comments
GET    /api/tickets/{id}/comments
```

### ✅ Phase 3 — Attribution & Priorités

- **Assigner** les tickets à des utilisateurs
- **Définir** la priorité : `Low`, `Medium`, `High`, `Critical`
- **Filtrer** les tickets par :
  - Statut (Open, In Progress, Closed)
  - Priorité (Low, Medium, High, Critical)
  - Utilisateur assigné
  - Créateur

**Endpoints:**
```
PUT    /api/tickets/{id}/assign
PUT    /api/tickets/{id}/priority
GET    /api/tickets?status=Open&priority=High&assigned_to=2
```

### ✅ Phase 4 — Rôles & Permissions

**Trois rôles implémentés:**

| Rôle | Permissions |
|------|------------|
| **Admin** | Accès complet (créer, éditer, supprimer, fermer, assigner, gérer rôles) |
| **Agent** | Gérer les tickets assignés, changer statut, ajouter commentaires |
| **Utilisateur** | Créer tickets, voir propres tickets, ajouter commentaires |

**Restrictions implémentées:**
- Seul un Admin/Agent peut **fermer** un ticket
- Un utilisateur ne peut **éditer** que ses propres tickets
- Seul un Admin peut **assigner** les tickets
- Seul un Admin peut **gérer les rôles** des utilisateurs

**Endpoints:**
```
GET    /api/users
POST   /api/users/{id}/role
GET    /api/users/{id}/permissions
```

---

## Choix Architecturaux & Justifications

| Décision | Justification |
|----------|---------------|
| **Services Pattern** | Séparer la logique métier des contrôleurs pour une meilleure testabilité et réutilisabilité |
| **Form Requests** | Centraliser la validation, réduire le code dans les contrôleurs |
| **Sanctum (Token-based Auth)** | Authentification légère adaptée aux SPAs, sans session côté serveur |
| **Eloquent ORM** | Abstraction de la base de données, relations intuitives, migrations versionnées |
| **Pinia (State Management)** | Alternative moderne à Vuex, API plus simple, performance optimisée |
| **Composition API** | Code réutilisable (composables), logique mieux organisée qu'avec Options API |
| **Tailwind CSS** | Utility-first, responsive out-of-the-box, réduction du CSS custom |
| **Axios + Service Layer** | Centraliser les appels API, gérer les intercepteurs, faciliter les tests |
| **Vue Router** | Routage côté client pour une SPA fluide et performante |

---

## Commandes Utiles

### Backend

```bash
# Tests unitaires
php artisan test

# Lister toutes les routes
php artisan route:list

# Créer une migration
php artisan make:migration create_table_name --create=table_name

# Créer un modèle avec migration
php artisan make:model TicketComment -m

# Créer un contrôleur avec méthodes CRUD
php artisan make:controller TicketController --resource

# Créer un Service
php artisan make:request StoreTicketRequest

# Rafraîchir les migrations (reset + migrate)
php artisan migrate:refresh

# Voir les logs
tail -f storage/logs/laravel.log
```

### Frontend

```bash
# Tests unitaires
npm run test

# Linter et formater le code
npm run lint

# Build pour la production
npm run build

# Prévisualiser le build
npm run preview
```

---

## Structure de la Base de Données

### Tables principales

```sql
-- Users
users (id, name, email, password, created_at, updated_at)

-- Roles
roles (id, name) -- Admin, Agent, User

-- User Roles (relation many-to-many)
user_roles (user_id, role_id)

-- Tickets
tickets (
  id, 
  title, 
  description, 
  status,           -- Open, In Progress, Closed
  priority,         -- Low, Medium, High, Critical
  created_by,       -- FK user
  assigned_to,      -- FK user (nullable)
  created_at, 
  updated_at
)

-- Comments
ticket_comments (
  id, 
  ticket_id,        -- FK tickets
  user_id,          -- FK users
  comment, 
  created_at
)
```

---

## Authentification & Sécurité

### Token Sanctum

```javascript
// Frontend : Envoyer le token dans chaque requête
const response = await axios.post('/api/tickets', {
  title: 'Mon ticket',
  description: 'Description...'
}, {
  headers: {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

### Validation Backend

```php
// Form Request (Laravel)
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'description' => 'required|string|min:10',
        'priority' => 'in:Low,Medium,High,Critical',
    ];
}
```

---

## Déploiement Production (Optionnel)

### Backend (Laravel sur serveur)

```bash
# Cloner et installer
git clone <repo>
cd backend
composer install --no-dev

# Configuration production
cp .env.example .env
php artisan key:generate
php artisan config:cache
php artisan route:cache

# Migrations
php artisan migrate --force

# Lancer avec Supervisor ou systemd
php artisan serve --host=0.0.0.0 --port=8000
```

### Frontend (Build static)

```bash
cd frontend
npm run build
# Envoyer le contenu de dist/ sur un serveur web (Nginx, Apache)
```

---

## Ressources & Documentation

- **Laravel:** https://laravel.com/docs
- **Vue 3:** https://vuejs.org
- **Pinia:** https://pinia.vuejs.org
- **Tailwind CSS:** https://tailwindcss.com

---

## Auteur & Contact

Yossr | Internship Project (HelpDesk)
yossrgalai02@gmail.com

---
