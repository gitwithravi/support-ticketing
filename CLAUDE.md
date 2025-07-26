# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Eagle is a self-hosted support ticket management system built with Laravel 12 and Filament. It includes features for ticket management, client portals, help center with articles and forms, email-to-ticket conversion, SLA workflows, and group-based permissions.

## Key Commands

### Development
- `composer dev` - Start development environment (server, queue, logs, vite) concurrently
- `php artisan serve` - Start Laravel development server
- `php artisan queue:work` - Start queue worker for background jobs
- `php artisan pail --timeout=0` - View application logs in real-time
- `npm run dev` - Start Vite dev server for assets
- `npm run build` - Build production assets

### Testing & Code Quality
- `php artisan test` or `./vendor/bin/pest` - Run tests (uses Pest framework)
- `./vendor/bin/pint` - Run Laravel Pint code formatter

### Database & Setup
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Run database seeders
- `composer install` - Install PHP dependencies
- `npm install` - Install Node.js dependencies

### Scheduled Tasks
- `php artisan schedule:run` - Run scheduled commands (should be in cron)
- `php artisan queue:listen --tries=1` - Listen for queue jobs

## Architecture

### Core Models & Relationships
- **Ticket**: Central entity with priority, type, status enums. Belongs to Client (requester), User (assignee), Group. Has many comments, SLAs, fields, activity
- **Client**: End users who create tickets. Has authentication for client portal
- **User**: Staff members who handle tickets. Has permissions and group memberships
- **Group**: Teams/departments for ticket assignment and permissions
- **TicketComment**: Messages on tickets with templating support
- **TicketSla**: Service Level Agreement tracking per ticket

### Help Center
- **Category → Section → Article**: Hierarchical content organization
- **Form → FormField**: Customizable forms that create tickets
- Articles and forms support publishing/activation states

### Filament Admin Architecture
- **App Panel**: Main admin interface at `/admin`
- **Client Panel**: Customer portal at `/client`
- **Clusters**: Settings, Help Center groups for organization
- **Resources**: CRUD interfaces for models with Pages and RelationManagers
- **Widgets**: Dashboard components (stats, charts)

### Email Integration
- Uses `beyondcode/laravel-mailbox` for email-to-ticket conversion
- TicketMailbox handles incoming emails
- Support email addresses configured in GeneralSettings
- Email routing: `support@domain.com` and `support+{ticketId}@domain.com`

### Settings System
- Uses Spatie Laravel Settings with Filament plugin
- GeneralSettings, WorkflowSettings, AdvancedSettings classes
- Settings stored in database, cached for performance

### Key Services & Patterns
- **Observers**: TicketObserver, FormObserver, etc. for model lifecycle events
- **Scopes**: ClientScope, GroupScope for multi-tenancy
- **Traits**: HasNotes, HasPermissions, HasActiveScope for shared functionality
- **Enums**: TicketPriority, TicketStatus, TicketType for type safety
- **Jobs**: Background processing for email import, ticket closure
- **Policies**: Gate-based authorization for resources

### Frontend Stack
- **Filament**: Main UI framework with Livewire components
- **Alpine.js**: JavaScript reactivity
- **Tailwind CSS**: Styling framework
- **Vite**: Build tool for assets

### Multi-language Support
- Route localization with `{locale}` prefix
- SetDefaultLocaleForUrls middleware
- Translation files in `lang/` directory

## Development Notes

### Database
- Uses ULIDs for primary keys on main models (Ticket, etc.)
- Database prohibits destructive commands in production
- Model configurations prevent lazy loading and missing attributes

### Code Quality
- Model observers handle business logic on model events  
- Strict typing with enums for ticket properties
- Comprehensive factory and seeder setup for testing
- Pest testing framework with Laravel plugin

### Security
- Sanctum for API token authentication
- Custom PersonalAccessToken model
- Policy-based authorization
- HTTPS forced in production
- Email validation for mailbox routing