# Admin Game Manager

This document outlines the admin functionality for managing games in the Vintage Consoles application.

## Features

The admin game manager provides a comprehensive interface for managing games across all console platforms:

### Core Functionality
- **Add Games**: Create new game entries for any console platform
- **Edit Games**: Modify existing game details and metadata
- **Delete Games**: Remove games from the system
- **Search & Filter**: Find games by title or publisher
- **Console Selection**: Manage games for different console types (NES, SNES, etc.)

### Game Data Management
Each game entry includes:
- Basic information (title, publisher, release year, description)
- Media assets (poster, box art, cartridge images)
- Technical details (ROM file, rating, support features)
- Metadata (genres, screenshots, multiplayer support)

## Access Control

The admin interface is protected by role-based access control using Spatie Laravel Permission:

- **Admin Role Required**: Only users with the 'admin' role can access the game manager
- **Middleware Protection**: Routes are protected by the `AdminMiddleware`
- **UI Integration**: Admin menu items only appear for users with admin privileges

## Technical Implementation

### Architecture
- **Service Layer**: `GameManager` service handles JSON file operations
- **Livewire Component**: `Admin\GameManager` provides reactive UI
- **Middleware**: `AdminMiddleware` enforces access control
- **Data Storage**: Games data stored in `storage/data/vintage-consoles.json`

### Key Files
```
app/
├── Http/Middleware/AdminMiddleware.php
├── Livewire/Admin/GameManager.php
└── Service/GameManager.php

resources/views/livewire/admin/
└── game-manager.blade.php

routes/web.php (admin routes)
```

## Usage

### Accessing the Admin Panel
1. Log in as a user with admin role
2. Click on your profile dropdown
3. Select "Game Manager"
4. You'll be redirected to `/admin/games`

### Managing Games
1. **Select Console**: Choose the console platform from the dropdown
2. **Search**: Use the search bar to filter games by title or publisher
3. **Add Game**: Click "Add Game" button to create a new game entry
4. **Edit Game**: Click "Edit" button next to any game in the table
5. **Delete Game**: Click "Delete" button and confirm deletion

### Game Form Fields
- **Required Fields**: Title, Publisher, Release Year, Description, Rating, ROM File
- **Optional Fields**: Poster URL, Box Art URL, Cartridge URL
- **Features**: Multiplayer Support, Save State Support, Free Game flags
- **Dynamic Fields**: Add/remove genres and screenshots as needed

## Security

- All admin routes require authentication and admin role
- Form validation prevents invalid data entry
- File operations are protected and validated
- XSS protection through Laravel's built-in escaping

## Data Format

Games are stored in JSON format with the following structure:
```json
{
  "id": 1,
  "title": "Game Title",
  "slug": "game-title",
  "publisher": "Publisher Name",
  "release_year": "1985",
  "description": "Game description...",
  "rating": "0.89",
  "rom": "game-file.nes",
  "poster": "https://example.com/poster.jpg",
  "genres": [
    {
      "name": "platformer",
      "description": "Platform jumping game"
    }
  ],
  "screenshots": ["https://example.com/screenshot1.jpg"],
  "multiplayer_support": true,
  "save_state_support": true,
  "is_free": false
}
```

## Testing

Run the admin functionality tests with:
```bash
php artisan test tests/Feature/Admin/GameManagerTest.php
```

## Troubleshooting

### Common Issues
1. **403 Access Denied**: Ensure user has admin role assigned
2. **File Not Found**: Check if `storage/data/vintage-consoles.json` exists
3. **Permission Issues**: Verify storage directory permissions

### Debug Commands
```bash
# Check user roles
php artisan tinker
>>> User::find(1)->roles

# Verify routes
php artisan route:list --name=admin

# Clear cache if needed
php artisan cache:clear
php artisan config:clear
``` 