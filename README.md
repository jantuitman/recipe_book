# Recipe Book

Een slimme recepten-app met AI-ondersteuning voor het maken, verbeteren en beheren van je recepten.

## Features

### Receptbeheer
- **Recepten maken**: Voeg handmatig recepten toe met ingrediënten en bereidingsstappen
- **Versiegeschiedenis**: Elke wijziging wordt als nieuwe versie opgeslagen
- **Zoeken**: Doorzoek recepten op naam of ingrediënt

### AI-functies
- **AI Chat**: Vraag om receptsuggesties en sla ze direct op in je receptenboek
- **Slimme feedback**: Plaats een opmerking met feedback en de app detecteert automatisch of het een verbeterverzoek is
- **Receptverbetering**: Klik op "Verbeter recept!" bij feedback-opmerkingen om AI-gegenereerde verbeteringen te krijgen
- **Preview**: Bekijk AI-suggesties voordat je ze toepast

### Eenheden & Porties
- **Eenhedenvoorkeur**: Kies tussen metrisch of imperiaal stelsel in je instellingen
- **Automatische conversie**: Ingrediënten worden getoond in jouw voorkeursysteem
- **Portie-schaling**: Pas het aantal porties aan en alle hoeveelheden worden herberekend

### Gebruikerservaring
- **Opmerkingen per versie**: Volg discussies en feedback door alle versies heen
- **Versiebadges**: Zie direct bij welke versie een opmerking hoort
- **Responsive design**: Werkt op desktop en mobiel

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.3)
- **Frontend**: Blade templates, Tailwind CSS, StimulusJS
- **Database**: MySQL/MariaDB
- **AI**: OpenAI API (GPT-4)
- **Build**: Vite

## Installatie

### Vereisten
- PHP 8.3+
- Composer
- Node.js 18+
- MySQL of MariaDB
- OpenAI API key

### Stappen

1. **Clone de repository**
   ```bash
   git clone <repository-url>
   cd recipe_book
   ```

2. **Installeer dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configuratie**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configureren**

   Pas `.env` aan met je database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=recipe_book
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **OpenAI API key toevoegen**
   ```env
   OPENAI_API_KEY=sk-...
   ```

6. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed  # Optioneel: demo data
   ```

7. **Build assets**
   ```bash
   npm run build
   # Of voor development:
   npm run dev
   ```

8. **Start de server**
   ```bash
   php artisan serve
   ```

## Development

### DevContainer
Dit project bevat een DevContainer configuratie voor VS Code. Open de folder in VS Code en kies "Reopen in Container".

### Vite configuratie
Voor development in een container is Vite geconfigureerd met:
```javascript
server: {
    host: '0.0.0.0',
    origin: 'http://localhost:5173',
    cors: true,
}
```

### Tests draaien
```bash
php artisan test
```

## Project Structuur

```
app/
├── Http/Controllers/
│   ├── RecipeController.php    # CRUD + versies + AI feedback
│   ├── CommentController.php   # Opmerkingen + feedback detectie
│   ├── ChatController.php      # AI chat interface
│   └── SettingsController.php  # Gebruikersinstellingen
├── Models/
│   ├── Recipe.php              # Recept model
│   ├── RecipeVersion.php       # Versie met ingrediënten/stappen
│   ├── Comment.php             # Opmerkingen + has_feedback
│   └── User.php                # Gebruiker + unit_preference
└── Services/
    └── OpenAiService.php       # AI integratie

resources/
├── js/controllers/             # StimulusJS controllers
│   ├── units_controller.js     # Eenheden conversie
│   └── servings_controller.js  # Portie schaling
└── views/
    ├── recipes/                # Recept views
    ├── chat/                   # AI chat interface
    └── settings/               # Instellingen
```

## Kleurenpalet

De app gebruikt een warm, uitnodigend kleurenpalet:
- **Primary**: `#E07A5F` (Terracotta)
- **Secondary**: `#81B29A` (Sage groen)
- **Accent**: `#F2CC8F` (Zandgeel)
- **Dark**: `#3D405B` (Donkerblauw-grijs)
- **Light**: `#F4F1DE` (Crème)

## Licentie

MIT License
