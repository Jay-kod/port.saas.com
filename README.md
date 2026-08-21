<p align="center">
  <strong>🚀 DevFolio.AI</strong><br>
  <em>The AI-Powered Developer Portfolio & Career Platform</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/Filament-5.7-FBBF24?style=flat-square" alt="Filament 5.7">
  <img src="https://img.shields.io/badge/Livewire_Volt-1.11-4E56A6?style=flat-square" alt="Livewire Volt">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Tests-57_passing-22C55E?style=flat-square" alt="57 Tests Passing">
</p>

---

## What is DevFolio.AI?

DevFolio.AI is a platform that lets developers create beautiful, themed personal portfolio websites — complete with AI-powered resume tailoring, cover letter generation, job application tracking, and PDF export — all managed through a sleek admin dashboard.

It works in **two modes** from the same codebase:

| Mode | Who it's for | How it works |
|------|-------------|--------------|
| **Self-Hosted** (default) | A single developer who wants their own portfolio site | Install it on your server, manage your portfolio through the admin panel, done. |
| **SaaS Platform** | An operator who wants to offer portfolio hosting to many developers | Enable multi-tenant mode and every developer gets their own `yoursite.com/their-name` URL. Includes billing, plans, and team features. |

---

## Table of Contents

- [Features at a Glance](#features-at-a-glance)
- [Quick Start (5 minutes)](#quick-start-5-minutes)
- [Your First Portfolio Setup](#your-first-portfolio-setup)
- [Admin Dashboard Guide](#admin-dashboard-guide)
- [Portfolio Themes](#portfolio-themes)
- [AI Features](#ai-features)
- [Job Application Tracker](#job-application-tracker)
- [Custom Domains (SaaS Mode)](#custom-domains-saas-mode)
- [Plans & Billing (SaaS Mode)](#plans--billing-saas-mode)
- [Team Collaboration (Agency Plan)](#team-collaboration-agency-plan)
- [SaaS Mode Setup](#saas-mode-setup)
- [Environment Variables Reference](#environment-variables-reference)
- [Deployment to Production](#deployment-to-production)
- [Troubleshooting](#troubleshooting)
- [Tech Stack](#tech-stack)

---

## Features at a Glance

### 🎨 Portfolio & Design
- **7 handcrafted themes** — Cyber Matrix, Bioluminescent, Toxic Cyberpunk, Slate Professional, Warm Editorial, Ocean, Classic Mono
- **Light & dark mode** with system auto-detection and manual toggle
- **Responsive design** that looks great on all devices
- **Custom domains** — point your own domain to your portfolio (Pro/Agency)

### 🤖 AI-Powered Tools
- **Smart Resume Tailoring** — paste a job description, get a resume tailored to that specific role
- **Resume Import** — upload or paste your existing resume and the AI automatically fills in your portfolio (experience, skills, projects)
- **Cover Letter Generator** — AI-writes a personalized cover letter for any job posting
- **ATS-Ready PDF Export** — generate clean, recruiter-friendly PDF resumes

### 📋 Career Management
- **Job Application Kanban Board** — track applications across 5 stages: Wishlist → Applied → Interviewing → Offer → Rejected
- **GitHub Sync** — automatically import your repositories and tech stack
- **Portfolio Analytics** — see who's viewing your portfolio

### 🏢 Business Features (SaaS Mode)
- **Multi-tenant architecture** — hundreds of developers, each with their own portfolio
- **3 subscription tiers** — Free, Pro Developer, Agency
- **Stripe billing** integration with Customer Portal
- **Team collaboration** — invite editors and viewers to manage portfolios
- **White-label branding** — agencies can remove DevFolio branding and add their own logo
- **GDPR compliance** — data export, account deletion, cookie consent, privacy pages

---

## Quick Start (5 minutes)

### Prerequisites

You need these installed on your computer:

| Software | Version | Check with |
|----------|---------|------------|
| PHP | 8.3 or higher | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18 or higher | `node -v` |
| npm | 9 or higher | `npm -v` |

### Installation

```bash
# 1. Clone the repository
git clone <your-repo-url> devfolio
cd devfolio

# 2. Install PHP dependencies
composer install

# 3. Create your environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Create the database (SQLite, zero config needed)
touch database/database.sqlite     # Linux/macOS
# On Windows: copy NUL database\database.sqlite

# 6. Run database migrations and seed starter data
php artisan migrate --seed

# 7. Install frontend dependencies and build assets
npm install
npm run build

# 8. Start the development server
php artisan serve
```

### 🎉 That's it! Open your browser:

| URL | What you'll see |
|-----|----------------|
| `http://localhost:8000` | Your portfolio homepage |
| `http://localhost:8000/admin` | Admin dashboard login |

### Default Login Credentials

| Field | Value |
|-------|-------|
| Email | `admin@example.com` |
| Password | `password` |

> ⚠️ **Important**: Change these credentials immediately after your first login. Go to the admin panel and update your email and password.

---

## Your First Portfolio Setup

After logging into the admin dashboard for the first time, here's how to build your portfolio step by step:

### Step 1: Edit Your Profile

Go to **Profiles** in the left sidebar and click on the existing profile (or create a new one).

Fill in:
- **Full Name** — your display name (e.g., "Sarah Jenkins")
- **Slug** — your URL path (e.g., `sarah-jenkins` → `yoursite.com/sarah-jenkins`)
- **Headline** — one line about you (e.g., "Senior Full-Stack Developer")
- **Bio** — a paragraph about your background and expertise
- **Email** — your contact email
- **Location** — where you're based (e.g., "San Francisco, CA")
- **Is Published** — toggle this ON when you're ready for the world to see your portfolio

### Step 2: Add Your Experience

Go to **Experiences** and add your work history:
- Job title, company, start/end dates
- Description of what you did
- Toggle "Is Current" for your current position

### Step 3: Add Your Projects

Go to **Projects** and showcase your best work:
- Title and description
- GitHub URL and/or live demo URL
- Technologies used (tags)
- Toggle "Featured" for projects you want highlighted

### Step 4: Add Your Skills

Go to **Skills** and list your technical abilities:
- Name (e.g., "React", "Python", "Docker")
- Category (e.g., "Frontend", "Backend", "DevOps")
- Proficiency level

### Step 5: Add Certificates

Go to **Certificates** for any certifications:
- Title, issuer, date
- Certificate URL or credential ID

### Step 6: Choose a Theme

Go to **Theme Selector** in the sidebar:
- Browse the 7 available themes with live color previews
- Click to activate any theme — your portfolio updates instantly
- Each theme supports both light and dark mode

### Step 7: Preview Your Portfolio

Open `http://localhost:8000` in a new browser tab to see your live portfolio. Use the ☀️/🌙 toggle in the top-right corner to switch between light and dark mode.

---

## Admin Dashboard Guide

The admin dashboard (powered by Filament) is your command center. Here's everything available in the left sidebar:

### Content Management

| Menu Item | What it does |
|-----------|-------------|
| **Profiles** | Edit your personal info (name, bio, headline, location, photo) |
| **Experiences** | Manage your work history timeline |
| **Projects** | Add and edit portfolio projects with descriptions, links, and tags |
| **Skills** | List your technical skills by category |
| **Certificates** | Track professional certifications |
| **Templates** | Manage resume PDF templates (Modern, Classic) |
| **Resume Generations** | View history of AI-generated tailored resumes |
| **Cover Letters** | View generated cover letters |

### AI Tools

| Menu Item | What it does |
|-----------|-------------|
| **Resume Import** | Paste resume text → AI extracts your info → fills your portfolio automatically |
| **Resume Generations** | Create AI-tailored resumes for specific job descriptions |
| **Cover Letters** | Generate tailored cover letters for job applications |

### Career Tools

| Menu Item | What it does |
|-----------|-------------|
| **Job Tracker** | Kanban board to manage your job application pipeline |

### Settings

| Menu Item | What it does |
|-----------|-------------|
| **Theme Selector** | Browse and activate portfolio themes (7 themes with dark/light modes) |
| **AI Settings** | Configure your own OpenAI/Anthropic API key (Bring Your Own Key) |
| **GitHub Settings** | Connect your GitHub account for automatic repo sync |
| **Domain Settings** | Connect a custom domain to your portfolio (Pro/Agency only) |
| **Billing** | View your plan, AI usage meter, and manage your Stripe subscription |
| **Team Settings** | Invite team members and manage roles (Agency only) |
| **Agency Branding** | Upload custom logo and brand name, hide platform branding (Agency only) |
| **Privacy & Data** | Download your data archive (GDPR) or delete your account |

---

## Portfolio Themes

DevFolio ships with 7 professionally designed themes. Each theme includes carefully tuned color palettes for both dark and light modes.

| Theme | Dark Mode Vibe | Best For |
|-------|---------------|----------|
| 🟢 **Cyber Matrix** | Neon green on deep navy | Developers, hackers, tech-forward portfolios |
| 🔵 **Bioluminescent** | Ocean blue bioluminescence | Data scientists, researchers, clean aesthetic |
| 🟡 **Toxic Cyberpunk** | Electric lime on pitch black | Creative developers, game devs, bold statements |
| ⚪ **Slate Professional** | Clean gray tones | Corporate environments, consultants |
| 🟠 **Warm Editorial** | Amber warmth on dark surfaces | Writers, designers, editorial feel |
| 🌊 **Ocean** | Teal and aquamarine | Environmental tech, calm professional presence |
| ⚫ **Classic Mono** | Pure black and white | Minimalists, typographers, print designers |

### How to change your theme

1. Log into the admin dashboard
2. Click **Theme Selector** in the sidebar
3. Browse the theme cards with color swatches
4. Click a theme to activate it — your public portfolio updates instantly

### Light/Dark Mode

Every visitor to your portfolio can toggle between light and dark mode using the button in the top-right corner. Their preference is saved in their browser.

You can set the default mode (light, dark, or auto-detect from system) in your profile settings.

---

## AI Features

### Resume Import (Free)

Already have a resume? Don't type everything manually.

1. Go to **Resume Import** in the admin sidebar
2. Paste your resume text (or upload a PDF)
3. Click **Parse Resume** — the AI extracts your name, headline, experience, skills, and projects
4. Review and edit the extracted data
5. Click **Import** — everything is saved to your portfolio

### AI Resume Tailoring

Have a job you want to apply to? Generate a tailored resume.

1. Go to **Resume Generations** → Create New
2. Paste the job description
3. The AI rewrites your resume to highlight relevant experience and match keywords
4. Download as a clean, ATS-friendly PDF

### Cover Letter Generator

1. Go to **Cover Letters** → Create New
2. Paste the job description
3. The AI generates a personalized cover letter based on your profile
4. Copy, edit, and send

### AI Usage Limits

| Plan | Monthly AI Generations |
|------|----------------------|
| Free | 3 per month |
| Pro | Unlimited |
| Agency | Unlimited |

**Bring Your Own Key (BYOK)**: On any plan, you can add your own OpenAI or Anthropic API key in **AI Settings**. This gives you unlimited generations at your own API cost.

---

## Job Application Tracker

Track every job application in one place with the built-in Kanban board.

### How to use it

1. Go to **Job Tracker** in the admin sidebar
2. Click **Add Application**
3. Fill in: Company name, role, salary range, notes
4. Your application appears in the **Wishlist** column
5. As you progress, drag cards across the 5 stages:

```
Wishlist → Applied → Interviewing → Offer → Rejected
```

Each card shows the company, role, salary range, and when it was last updated.

---

## Custom Domains (SaaS Mode)

> This feature requires **SaaS Mode** enabled and a **Pro** or **Agency** plan.

Connect your own domain (e.g., `portfolio.yourname.com`) to your DevFolio portfolio.

### Setup Steps

1. Go to **Domain Settings** in the admin sidebar
2. Enter your domain name (e.g., `portfolio.yourname.com`)
3. Follow the DNS instructions shown:
   - Add a **CNAME** record pointing to your DevFolio server
   - Add a **TXT** record for domain verification
4. Click **Verify** — once DNS propagates, your portfolio is live on your custom domain

When a custom domain is connected, visitors see your portfolio at the root (`portfolio.yourname.com/`) with no slug prefix needed.

---

## Plans & Billing (SaaS Mode)

> Billing features require **SaaS Mode** enabled and **Stripe** configured.

### Plan Comparison

| Feature | Free | Pro Developer | Agency / Team |
|---------|------|---------------|---------------|
| Portfolio website | ✅ | ✅ | ✅ |
| 7 premium themes | ✅ | ✅ | ✅ |
| Light & dark mode | ✅ | ✅ | ✅ |
| PDF resume export | ✅ | ✅ | ✅ |
| AI generations/month | 3 | Unlimited | Unlimited |
| Custom domain | ❌ | ✅ | ✅ |
| Remove branding | ❌ | ✅ | ✅ |
| Team members | ❌ | ❌ | Unlimited |
| Client profiles | 1 | 1 | Unlimited |
| White-label branding | ❌ | ❌ | ✅ |

### Managing Your Subscription

1. Go to **Billing** in the admin sidebar
2. See your current plan and AI usage meter
3. Click **Upgrade** on a higher tier to open Stripe Checkout
4. Click **Manage Subscription** to open the Stripe Customer Portal (change card, cancel, etc.)

---

## Team Collaboration (Agency Plan)

> This feature requires the **Agency** plan.

### Inviting Team Members

1. Go to **Team Settings** in the admin sidebar
2. Enter the email of someone you want to invite
3. Choose their role:

| Role | Can view content | Can edit content | Can manage billing & team |
|------|-----------------|-----------------|--------------------------|
| **Viewer** | ✅ | ❌ | ❌ |
| **Editor** | ✅ | ✅ | ❌ |
| **Owner** | ✅ | ✅ | ✅ |

### Managing Multiple Client Profiles

Agency accounts can create unlimited portfolio profiles for different clients. Switch between them in the admin dashboard dropdown at the top of the sidebar.

### White-Label Branding

1. Go to **Agency Branding** in the admin sidebar
2. Upload your agency's logo
3. Set a custom brand name
4. Toggle **Hide Platform Branding** to remove the "Powered by DevFolio" footer from all your client portfolios

---

## SaaS Mode Setup

By default, DevFolio runs in **self-hosted mode** — perfect for a single developer. To run it as a multi-tenant SaaS platform serving many users:

### 1. Enable SaaS Mode

In your `.env` file:

```env
SAAS_MODE=true
```

### 2. Configure Stripe (for billing)

```env
STRIPE_KEY=pk_live_your_publishable_key
STRIPE_SECRET=sk_live_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
STRIPE_PRICE_PRO=price_xxx    # Your Pro plan price ID from Stripe
STRIPE_PRICE_AGENCY=price_xxx  # Your Agency plan price ID from Stripe
```

### 3. What changes in SaaS Mode

| Aspect | Self-Hosted (default) | SaaS Mode |
|--------|----------------------|-----------|
| Homepage (`/`) | Your portfolio | Marketing landing page |
| Portfolio URLs | `/about`, `/projects`, etc. | `/{username}/about`, `/{username}/projects`, etc. |
| Registration | Disabled (you're the only user) | Open self-service sign-up at `/admin/register` |
| Pricing page | Not shown | Available at `/pricing` |
| Developer directory | Not shown | Public at `/discover` |
| Billing | Not needed | Stripe integration active |
| Teams | Not needed | Available on Agency plan |

### 4. Reverting to Self-Hosted

If anything goes wrong, simply set `SAAS_MODE=false` in `.env`. This instantly restores single-tenant behavior — it's the built-in safety valve.

---

## Environment Variables Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | `Laravel` | Your application name (shown in title bars and emails) |
| `APP_URL` | `http://localhost` | The base URL of your site |
| `APP_ENV` | `local` | Set to `production` in production |
| `APP_DEBUG` | `true` | Set to `false` in production |
| `DB_CONNECTION` | `sqlite` | Database driver (`sqlite`, `mysql`, `pgsql`) |
| `SAAS_MODE` | `false` | Enable multi-tenant SaaS mode |
| `STRIPE_KEY` | — | Stripe publishable key (SaaS mode billing) |
| `STRIPE_SECRET` | — | Stripe secret key (SaaS mode billing) |
| `STRIPE_WEBHOOK_SECRET` | — | Stripe webhook signing secret |
| `STRIPE_PRICE_PRO` | — | Stripe Price ID for Pro plan |
| `STRIPE_PRICE_AGENCY` | — | Stripe Price ID for Agency plan |
| `MAIL_MAILER` | `log` | Mail driver (`smtp`, `ses`, `mailgun`, `log`) |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | "From" address for outgoing emails |

---

## Deployment to Production

### Recommended Server Requirements

- PHP 8.3+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`
- Composer 2.x
- Node.js 18+ (for building frontend assets)
- A web server (Nginx, Apache, or Caddy)
- SQLite, MySQL 8.0+, or PostgreSQL 15+

### Production Deployment Steps

```bash
# 1. Clone and install
git clone <your-repo-url> /var/www/devfolio
cd /var/www/devfolio
composer install --no-dev --optimize-autoloader

# 2. Environment setup
cp .env.example .env
php artisan key:generate
# Edit .env with your production values:
#   APP_ENV=production
#   APP_DEBUG=false
#   APP_URL=https://yourdomain.com
#   DB_CONNECTION=mysql (or your preferred database)

# 3. Database
php artisan migrate --force --seed

# 4. Build frontend
npm ci
npm run build

# 5. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache

# 6. Set file permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Nginx Configuration (Example)

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/devfolio/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Troubleshooting

### "No profile has been set up yet"

You're seeing the public homepage without any profile data. Log into `/admin`, go to **Profiles**, create one, and toggle **Is Published** to ON.

### Admin panel is blank or CSS is broken

Run the frontend build:

```bash
npm install
npm run build
```

### "Class not found" or autoloading errors

```bash
composer dump-autoload
php artisan clear-compiled
```

### Database migration errors

If you're starting fresh:

```bash
php artisan migrate:fresh --seed
```

> ⚠️ This will erase all data and re-seed the default theme and template data.

### Theme not applying

1. Make sure you've activated a theme in **Theme Selector**
2. Hard-refresh your browser (Ctrl+Shift+R / Cmd+Shift+R)
3. Check that `npm run build` completed without errors

### AI features not working

AI features (resume tailoring, cover letters, resume import) need an API key. Either:
- You're on a paid plan (Pro/Agency) that includes AI generations, OR
- You've added your own API key in **AI Settings** → set **Provider** (OpenAI or Anthropic), paste your key, and toggle **Is Active** to ON

### Stripe billing not working

1. Make sure `SAAS_MODE=true` in `.env`
2. Verify all 5 Stripe environment variables are set
3. Set up a Stripe webhook pointing to `https://yourdomain.com/stripe/webhook`
4. The webhook should listen for: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend Framework** | Laravel 13 |
| **Admin Panel** | Filament 5.7 |
| **Frontend Reactivity** | Livewire Volt |
| **CSS Framework** | Tailwind CSS 4 |
| **PDF Generation** | DomPDF |
| **PDF Parsing** | smalot/pdfparser |
| **Billing** | Laravel Cashier + Stripe |
| **Database** | SQLite (default), MySQL, PostgreSQL |
| **Language** | PHP 8.3+ |
| **Tests** | PHPUnit (57 tests, 465 assertions) |

---

## Running Tests

```bash
php artisan test
```

Expected output:
```
Tests:    57 passed (465 assertions)
Duration: ~33s
```

The test suite covers:
- Public portfolio page rendering
- Multi-tenant data isolation
- User registration and onboarding
- SaaS routing (marketing pages + tenant slugs)
- AI quota metering and BYOK exemptions
- Theme dual-mode CSS generation
- Custom domain resolution
- Resume import and portfolio hydration
- Cover letter generation
- Job tracker Kanban workflow
- Team management and RBAC
- White-label branding
- GDPR data export and cascade deletion
- 10 real-world end-to-end user scenarios

---

## Project Structure (Key Files)

```
├── app/
│   ├── Filament/              # Admin dashboard (pages, resources, widgets)
│   │   ├── Pages/             # Theme Selector, Job Tracker, Billing, etc.
│   │   ├── Resources/         # CRUD for Profiles, Projects, Skills, etc.
│   │   └── Widgets/           # Dashboard checklist widget
│   ├── Models/                # Eloquent models (Profile, Project, Skill, etc.)
│   └── Services/              # Business logic (AI, themes, GDPR, usage guards)
├── config/
│   ├── plans.php              # Subscription tier definitions
│   └── saas.php               # SaaS mode feature flag
├── database/
│   ├── migrations/            # All database table schemas
│   └── seeders/               # Theme, template, and demo data
├── resources/views/
│   ├── layouts/app.blade.php  # Public portfolio layout (theme engine)
│   ├── pages/                 # Public Volt pages (home, about, projects, etc.)
│   └── resumes/templates/     # PDF resume blade templates (modern, classic)
├── routes/web.php             # All public and SaaS routing
├── tests/Feature/             # 11 comprehensive test suites
└── .env                       # Your environment configuration
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
