# GenZ Auth Source Code

## Overview

GenZ Auth is an open-source, cloud-hosted authentication system designed to protect software against piracy. It provides license key management, user authentication, and anti-piracy measures, built to handle substantial user loads while maintaining security and preventing unauthorized access to licensed software.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
The frontend utilizes static HTML pages with minimal JavaScript, styled using Tailwind CSS for responsive design and Flowbite for UI components. All assets are served from CDN endpoints. A custom ultra-modern theme system (app/assets/modern-theme.css) provides gradients, animations, and glass-morphism effects.

### Backend Architecture
Built on PHP, the backend supports Apache and Nginx. It implements WebAuthn for security key authentication and provides a RESTful API for authentication and license validation.

### Database and Security
The system uses MySQL/MariaDB for user accounts, licenses, and application data, with Redis for caching and session management. Security features include FIDO2/WebAuthn integration, anti-piracy protection, and HTTPS-enforced communication.

### Replit Environment
The Replit environment is configured with PHP 8.4.10, MySQL/MariaDB connection (us9.endercloud.in:6555), a Redis compatibility layer, and a PHP built-in server running on 0.0.0.0:5000.

## External Dependencies

### Core Infrastructure
- **Web Servers**: Apache or Nginx
- **Database Systems**: MySQL or MariaDB
- **Caching**: Redis
- **Runtime**: PHP

### Frontend Libraries
- **Tailwind CSS**: Utility-first CSS framework
- **Flowbite**: Component library for UI elements
- **Animate.css**: Animation library
- **WebAuthn Library**: JavaScript for security key authentication
- **Modern Theme System**: Custom design system (`app/assets/modern-theme.css`)

### CDN Services
- **GenZ Auth CDN**: `cdn.keyauth.cc` for static assets.

### Third-Party Integrations
- **Social Media**: Twitter and Telegram
- **Analytics**: CodeFactor (for code quality)
- **Security**: WebAuthn/FIDO2 standards