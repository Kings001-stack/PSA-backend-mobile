# Pharmacy Management System with AI Chat

## Overview
This is a modern, full-stack Pharmacy Management System built for the **MediCare Demo Pharmacy**. It allows pharmacists to manage medication inventory, track stock levels, and provides an AI-powered assistant to handle customer inquiries about availability and services.

## Technology Stack
- **Framework**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3 with Inertia.js
- **Styling**: Tailwind CSS (v4)
- **Database**: SQLite / MySQL
- **AI Integration**: Google Gemini API
- **Tooling**: Vite, Ziggy

## Key Features

### 1. Inventory Management
- **Medication Tracking**: Add and manage medications with details (dosage, type, manufacturer).
- **Stock Control**: dedicated Inventory section to track batches, quantities, and expiry dates.
- **Tenant Isolation**: Multi-tenant architecture ensures data privacy ( Global Scopes).

### 2. AI Pharmacy Assistant
- **Smart Chat Interface**: Real-time chat UI with a collapsible history sidebar.
- **Inventory Awareness**: The AI reads the live database to answer questions like "Do you have Amoxicillin?" with actual stock availability.
- **Safety First**: Includes strict guardrails (SafetyEngine) to prevent medical advice on critical topics (dosage, diagnosis) and forces escalation for emergencies.
- **Smart Titles**: Automatically generates concise 3-5 word titles for conversations using AI summarization.

### 3. Modern UI/UX
- **Responsive Design**: Mobile-friendly layout using Tailwind CSS.
- **Instant Feedback**: Flash messages and validation alerts prevents "blank screen" confusion.
- **Interactive**: Smooth transitions and loading states.

## Setup & Running

### Prerequisites
- PHP 8.2+
- Node.js & NPM
- Composer

### Installation
1. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```
2. **Environment Setup**:
   - Copy `.env.example` to `.env`.
   - Set `GEMINI_API_KEY` in `.env`.
   - Configure database settings.
3. **Database Migration**:
   ```bash
   php artisan migrate
   ```
4. **Run the Application**:
   - Start Backend: `php artisan serve`
   - Start Frontend: `npm run dev`

## Recent Updates & Fixes
- **White Screen Fix**: Resolved a critical crash caused by incorrect imports (`@/ziggy`) and Vite alias configuration in `vite.config.js`.
- **Chat Enhancements**: Added sidebar toggle and AI title generation. Fixed a bug where titles weren't saving due to a missing `title` column and missing `fillable` attribute in the model.
- **Inventory Logic**: Connected ChatController to `Inventory` model for real-time stock checks. Fixed medication availability logic to explicitly check quantities.

## Project Structure
- `app/Http/Controllers`: Logic for Inventory, Medications, and Chat.
- `app/Services`: `GeminiService` and `SafetyEngine` for AI logic.
- `resources/js/Pages`: Vue components for the frontend.
- `routes/web.php`: Application routing.

---
*Built with Laravel Boost & Antigravity.*
