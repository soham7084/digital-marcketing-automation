# SEO & Ranking Automation Suite (Action AI)

An end-to-end, AI-powered **SEO & Search Ranking Control Suite** built with **PHP (PDO)**, **MySQL**, **Vanilla JavaScript (ES6+)**, **Bootstrap 5**, **Chart.js**, and **n8n Automation Workflows**.

---

## 🚀 Key Modules & Capabilities

### 1. 🔍 Technical SEO Audit Engine
- **Configuration & Inputs**: User specifies target website URL and contact email address.
- **Automation Flow**: Triggers the **`seo audit_1`** n8n workflow node (`/webhook/seo-audit-1`).
- **Interactive Execution**: Features a real-time modal progress bar polling workflow execution status.
- **Audit Reports**: Visual scores (0–100) for **SEO**, **Performance**, **Accessibility**, and **Best Practices**, along with issue impact classifications (High, Medium, Low) and AI recommendations.

### 2. 🔑 Google Keyword Research Engine
- **Configuration & Inputs**: Topic, seed keywords, language, location targeting, and optional daily execution run times.
- **Automation Flow**: Triggers the **`keyword-research-free`** n8n workflow (`/webhook/keyword-research-free`).
- **Data Visualizations**: Search intent distribution pie chart (Informational, Commercial, Transactional), cluster grouping, and priority matrix.

### 3. 🎯 Business Account Ranking Engine
- **Configuration & Inputs**: Business name, zone/location, and priority sensitivity levels (Scale 1–5).
- **Automation Flow**: Triggers the **`business-ranking`** n8n workflow (`/webhook/business-ranking`).
- **Local Pack Tracking**: Monitors top competitor local pack rankings, rating averages, and search presence.

### 4. 🔔 Rank Alert Engine
- **Position Tracking & Alerts**: Logs rank fluctuations and position drops/gains to generate automated daily alerts.

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, Vanilla JavaScript (ES6+), Bootstrap 5.3, Bootstrap Icons, SweetAlert2, Chart.js.
- **Backend**: PHP 8+ (PDO Database Layer), RESTful JSON API endpoints.
- **Database**: MySQL / MariaDB (InnoDB, UTF-8 MB4).
- **Workflow Automation Engine**: n8n Webhook Workers.

---

## 📂 Project Architecture

```
fluanc/
├── api/
│   ├── business_ranking/
│   │   ├── business_cron_trigger.php
│   │   ├── business_input_config_get.php
│   │   ├── business_result_append.php
│   │   ├── business_result_fetch.php
│   │   └── get_n8n_keywords.php
│   ├── includes/
│   │   └── config.php               # PDO Database Connection
│   ├── keyword/
│   │   ├── keyword_cron_scheduler_trigger.php
│   │   ├── keyword_input_add_config.php
│   │   ├── keyword_input_get_config.php
│   │   ├── keyword_research_data_fetch.php
│   │   ├── keyword_result_append.php
│   │   └── keyword_status_check.php
│   ├── login_signup/                # Login & Registration endpoints
│   └── seo_audit/
│       ├── check_status.php         # Status polling endpoint for workflow progress
│       ├── create_audit.php         # Input saver & n8n "seo audit_1" trigger
│       ├── seo_cron_trigger.php     # Daily cron execution trigger endpoint for n8n workflow
│       ├── seo_result_append.php    # n8n callback receiver
│       └── seo_result_fetch.php     # Output data provider
├── css/
│   ├── global.css                   # Main theme design system tokens & custom components
│   └── dashboard_style.css
├── frontend_pages/
│   ├── business_account_ranking.php
│   ├── business_account_ranking_output.php
│   ├── dashboard_front.php
│   ├── keyword_output.php
│   ├── keyword_research.php
│   ├── seo_audit.php
│   ├── seo_output.php
│   ├── rank_alert_engine.php
│   ├── rank_alert_output.php
│   └── session_control.php
├── js/
│   ├── business_ranking_output.js
│   ├── keyword_output.js
│   ├── keyword_research.js
│   ├── ranking_engine.js
│   ├── seo_audit.js
│   └── seo_output.js
└── README.md
```

---

## 🗄️ Database Architecture

The application uses a **Tri-Layer Architecture** for each module (Inputs → Triggers → Results):

### 1. SEO Audit Tables
- `seo_audit_inputs`: Stores user-configured target URLs and recipient emails.
- `daily_schedule_trigger_seo`: Tracks workflow trigger status (`pending`, `processing`, `done`, `failed`).
- `seo_audit_results`: Stores performance scores, accessibility, SEO scores, and JSON detailed recommendations.

### 2. Keyword Research Tables
- `keyword_inputs`: User seed keywords, project topic, location, and run times.
- `daily_schedule_trigger`: Cron/instant execution status logs.
- `keyword_result`: Extracted keywords, search intent, clusters, and priorities.

### 3. Business Ranking Tables
- `business_account_ranking_inputs`: Business names, zone locations, and minimum priority levels.
- `daily_schedule_trigger_ba`: Execution logs per business account configuration.
- `business_account_ranking_result`: Competitor local pack rankings, rating metrics, and status positions.

---

## 🔌 API & n8n Integration Points

| Module | Webhook Endpoint | Method | Payload Data |
| :--- | :--- | :--- | :--- |
| **SEO Audit** | `/webhook/seo-audit-1` | `POST` | `trigger_id`, `audit_input_id`, `user_id`, `page_url`, `email` |
| **Keyword Research** | `/webhook/keyword-research-free` | `POST` | `trigger_id`, `topic`, `seeds`, `location`, `language_code` |
| **Business Ranking** | `/webhook/business-ranking` | `POST` | `trigger_id`, `user_id`, `priority_min`, `user_location`, `business_name` |

---

## ⚙️ Installation & Setup

### 1. Prerequisites
- **Web Server**: Apache / Nginx (or XAMPP / WampServer).
- **PHP**: Version 8.0 or higher (with `curl` and `pdo_mysql` extensions enabled).
- **Database**: MySQL 5.7+ / MariaDB 10.4+.
- **Automation Engine**: n8n instance running locally (`localhost:5678`) or remote server.

### 2. Database Configuration
1. Open XAMPP phpMyAdmin or your MySQL client and create a database named `fluanc`.
2. Configure database credentials in `api/includes/config.php`:

```php
$host = 'localhost';
$dbname = 'fluanc';
$username = 'root';
$password = '';
```

### 3. Running Locally
1. Place the project directory inside your web server document root (e.g., `C:/xampp/htdocs/fluanc`).
2. Navigate to `http://localhost/fluanc/frontend_pages/login_front.php` in your browser.
3. Log in or create an account to access the dashboard suite.

---

## 📄 License & Attribution
Designed and developed for **Action AI SEO & Search Ranking Suite**. All rights reserved.
