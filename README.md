![Eagle](https://raw.githubusercontent.com/liamseys/eagle/main/.github/banner.jpg)

# University Support Ticketing 🎓

A specialized support ticket management system built with Laravel 12 and Filament, enhanced for **university campus maintenance and support operations**.

## About This Fork

This repository is based on the original [Eagle](https://github.com/liamseys/eagle) project by [Liam Seys](https://github.com/liamseys). Credit and appreciation go to the original author for creating this excellent foundation. 

**This fork has been extensively enhanced for university environments**, adding specialized features for campus maintenance management, student/staff support, and institutional workflows.

## 🎓 University-Specific Features

### Campus Infrastructure Management
- **Building Management System**: Comprehensive building database with codes, addresses, floor plans, and room counts
- **Maintenance Categories**: Specialized tracking for maintenance, damages, breakages, and missing items
- **Breakage Tracking**: Dedicated system for recording breakages with responsible party registration numbers
- **Building Supervisors**: Assign dedicated supervisors to manage specific campus buildings

### Role-Based University Hierarchy
- **Student/Staff Portal**: Self-service portal with unique ID (registration/employee number) authentication
- **Multi-Tier User System**:
  - **Admin**: Full system access and user management
  - **Agents**: Handle tickets and provide general support
  - **Category Supervisors**: Manage specific service categories (IT, Facilities, etc.)
  - **Building Supervisors**: Oversee maintenance for assigned campus buildings

### Advanced Ticket Management
- **University ID Integration**: Track tickets by student registration numbers or employee IDs
- **Category-Based Assignment**: Automatic routing based on issue type and department
- **Building-Specific Filtering**: View and manage tickets by campus location
- **Maintenance Term Classification**: Categorize issues as maintenance, damages, breakages, or missing items
- **Excel Export**: Comprehensive reporting for administrative oversight
- **Auto-Closure System**: Automatic timestamp recording when tickets are resolved

### Campus Help Center
- **Departmental Knowledge Base**: Organize articles by campus services and departments
- **Custom Service Forms**: Create specialized forms for different campus services
- **Multi-language Support**: Essential for diverse university communities

## Core Features

- **Email-to-Ticket Conversion**: Automatically process support emails using Laravel Mailbox
- **SLA Workflows**: Service Level Agreement tracking and automation per ticket
- **Real-time Notifications**: Keep stakeholders informed of ticket progress
- **Comprehensive Reporting**: Excel export functionality with detailed ticket analytics
- **Role-Based Permissions**: Secure access control using Filament Shield
- **Background Processing**: Queue-based system for email import and automation

## Installation

1. Clone the repository:

```bash
git clone https://github.com/gitwithravi/support-ticketing.git
cd support-ticketing
```

2. Install dependencies:

```bash
composer install
npm install
```

3. Set up your environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env` and run migrations:

```bash
php artisan migrate
php artisan db:seed
```

5. Build the assets:

```bash
npm run build
```

6. Set up a cron job to run the scheduler every minute:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

7. Start the development environment:

```bash
# For development (runs server, queue, logs, and vite concurrently)
composer dev

# Or start services individually:
php artisan serve
php artisan queue:work
php artisan queue:listen --tries=1
```

## Development Commands

### Daily Development
- `composer dev` - Start complete development environment
- `php artisan test` or `./vendor/bin/pest` - Run tests
- `./vendor/bin/pint` - Format code
- `php artisan pail --timeout=0` - View real-time logs

### Database & Maintenance
- `php artisan migrate` - Run migrations
- `php artisan db:seed` - Seed database with sample data

## Technical Stack

- **Backend**: Laravel 12 with Filament admin panel
- **Frontend**: Livewire, Alpine.js, Tailwind CSS  
- **Database**: MySQL/PostgreSQL with ULID primary keys
- **Authentication**: Dual authentication system (Staff + Student/Client portals)
- **Authorization**: Filament Shield with role-based permissions
- **Email**: Laravel Mailbox for email-to-ticket conversion
- **Queue**: Background job processing for automation
- **Exports**: Excel export functionality via pxlrbt/filament-excel
- **Testing**: Pest PHP testing framework with comprehensive unit tests

## University Use Cases

This system is specifically designed for:

- **Campus Maintenance**: Manage building repairs, equipment issues, and facility requests
- **IT Support**: Handle student/staff technology support requests with proper categorization
- **Student Services**: Process academic support, housing, and general service requests
- **Facilities Management**: Track and resolve campus infrastructure issues
- **Multi-Department Coordination**: Route tickets to appropriate campus departments
- **Compliance Reporting**: Generate reports for administrative oversight and auditing

## Recent Enhancements

- ✅ **Breakage Management System**: Track damaged items with responsible party identification
- ✅ **Building-Specific Workflows**: Assign supervisors and filter tickets by campus buildings  
- ✅ **University ID Authentication**: Registration number/employee ID based access control
- ✅ **Enhanced Reporting**: Comprehensive Excel exports for administrative use
- ✅ **Multi-Role Support**: Specialized user types for university hierarchies
- ✅ **Auto-Closure Tracking**: Automatic timestamp recording for resolved tickets
- ✅ **Category Supervision**: Department-specific oversight and ticket routing

## License

University Support Ticketing is released under the Creative Commons Attribution-NonCommercial 4.0 International license. See the [LICENSE](LICENSE) file for more details. A human-friendly summary is available at [creativecommons.org](https://creativecommons.org/licenses/by-nc/4.0/).

Dependencies may be subject to their own licenses.

## Security

If you discover any security-related issues, please email [liam.seys@gmail.com](mailto:liam.seys@gmail.com) instead of using the issue tracker. All security vulnerabilities will be promptly addressed.
