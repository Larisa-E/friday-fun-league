# Friday Fun League

The project is a tournament web app where users can add participants, register matches, view rankings, filter results, and open a statistics page.

## What the app does

- Shows a rank list with name, points, wins, losses, and win percentage
- Loads more rank-list rows with a lazy-loading `Load more` button when the player list gets long
- Shows the latest 10 matches
- Updates the rank list and latest matches with an async refresh button without reloading the whole page
- Loads more match-history rows with a lazy-loading `Load more` button
- Lets the user add, edit, and delete participants
- Lets the user add, edit, and delete matches
- Lets the user search and filter match history
- Shows a separate statistics page with charts and summary information

## Tech used

- Backend: Laravel
- Frontend: Blade + Bootstrap 5
- Build tool: Vite
- Icons: Inline SVG
- Database: MySQL / MariaDB
- Charts: Chart.js
- Tests: PHPUnit

## Why this project uses this stack

I chose Laravel because it gives the project a clear structure for routes, controllers, models, validation, and database work.
I chose Blade because it is easier to follow than a full frontend framework for a project like this.
Bootstrap helped me build the interface faster and made responsive layout easier.
Vite helps prepare the CSS and JavaScript files for the browser in a cleaner way.
MySQL stores the participants and match results in a structured relational database.
Chart.js was chosen because it makes it easy to show simple statistics in charts.

## Simple comparison with alternatives

- Laravel was chosen instead of a smaller PHP framework because Laravel already includes routing, validation, migrations, and Eloquent. A smaller framework could be lighter, but it would need more manual setup.
- Blade was chosen instead of React or Vue because Blade is easier for a beginner and works well for server-rendered pages. React or Vue would be stronger for a very dynamic app, but they would also add more frontend complexity.
- Bootstrap was chosen instead of writing all styling from scratch because it gave a fast responsive base for forms, layout, and buttons. Writing everything manually would take more time.
- Vite was chosen instead of relying only on CDN files or older build tools because it works well with Laravel and keeps CSS and JavaScript easier to organize.
- MySQL was chosen instead of SQLite for the main local setup because it fits relational data well and matches the XAMPP setup used for this project. SQLite is simpler for very small projects, but MySQL is more realistic for this type of app.
- Chart.js was chosen instead of a bigger chart library because it was simple to set up and already gives enough features for the statistics page.

## Versions used

- PHP: 8.4.20 locally (project requirement: ^8.2)
- Laravel: 12.56.0
- Composer: 2.9.7
- MariaDB/MySQL: 10.4.32-MariaDB
- Node.js: 24.15.0
- npm: 11.12.1
- Bootstrap: 5.3.8
- Chart.js: 4.5.1
- Vite: 7.3.2
- Laravel Vite Plugin: 2.1.0
- Tailwind Vite Plugin: 4.2.4
- PHPUnit: 11.5.55


## How to run the project

This project is set up for a local XAMPP MySQL or MariaDB database.

1. Start `MySQL` in XAMPP.
2. Create a database named `friday_fun_league_db` in phpMyAdmin.
3. Copy `.env.example` to `.env` if `.env` does not already exist.
4. Make sure the `.env` file points to your XAMPP MySQL database.
5. Install dependencies.
6. Generate the Laravel app key.
7. Run the migrations.
8. Start the Laravel development server.

Recommended `.env` database values for XAMPP:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=friday_fun_league_db
DB_USERNAME=root
DB_PASSWORD=
```

Commands for a fresh setup:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Optional shortcuts:

```bash
composer setup
npm run dev
npm run build
```

- Use `npm run dev` while you are editing CSS or JavaScript.
- Use `npm run build` when you want the final production assets.
- Use `composer setup` only after the MySQL database already exists.
- If you use `php artisan serve`, Apache from XAMPP is not required.
- If you want to use XAMPP Apache instead, do not run Apache and `php artisan serve` for the same project at the same time.

Open the app at:

```text
http://127.0.0.1:8000
```

## User guide

1. Open the dashboard.
2. Check the rank list to see points, wins, losses, matches, and win percentage.
3. Use `Refresh` if you want the top dashboard data to update without reloading the page.
4. Use the `Add` tab to open the `Add Participant` and `Register Match` tools.
5. Add a new participant or register a new match from that tab.
6. Switch to the `Manage` tab if you need to edit, delete, search, or filter saved data.
7. Use the search and filter area to narrow the match history.
8. Open the `Statistics` page to see charts and summary numbers.

Screenshots:



## FAQ

- Why are not all players shown immediately?
	The rank list uses lazy loading, so the page starts smaller and faster.

- What does the `Refresh` button do?
	It updates the top dashboard data in the background and now shows a success toast when the refresh finishes.

- Where can I see app activity?
	Important participant and match actions are written to the `league` log file in `storage/logs`.

- Why do the charts not load instantly?
	The statistics charts use lazy loading, so they load when the chart area is needed.

## How to run the tests

```bash
php artisan test
```

The tests use the Laravel test setup in `phpunit.xml` and run with SQLite in memory, so they do not change your real XAMPP MySQL database.

- Tests are small automatic checks for the app.
- They help make sure important parts still work after changes.
- In this project, tests check things like the dashboard, participants, matches, and filters.
- This helps find mistakes early and makes the project safer to change.

## Logs

Logs are simple notes the app writes about what happened.

This project now uses its own daily log files for important participant and match actions:

- `storage/logs/league-YYYY-MM-DD.log` for normal app usage
- `storage/logs/league-testing-YYYY-MM-DD.log` during test runs

The files are created automatically the first time the app writes to them.

How logs help:

- They help you see what happened in the app.
- They help you debug problems faster.
- They help you check if a form or button really worked.
- They help when you are testing or showing the project to someone.

How to view the log file on Windows:

```bash
type storage\logs\league-2026-04-30.log
```

If the file is long, you can view only the latest lines with:

```bash
powershell -Command "Get-Content storage\\logs\\league-2026-04-30.log -Tail 30"
```

## Project structure

- `app/Http/Controllers` contains the controller logic
- `config` contains app settings, including the logging setup
- `app/Models` contains the database models
- `app/Services/LeagueStatsService.php` contains the standings recalculation logic
- `resources/css` contains the main app styles
- `resources/js` contains the main frontend scripts
- `resources/views` contains the Blade UI files
- `docs/screenshots` contains screenshots used in the documentation
- `database/migrations` contains the table definitions
- `tests` contains feature and unit tests

## Database and points logic

- `participants` is the table that stores each player's name, optional avatar emoji, points, wins, losses, and matches played.
- `match_games` is the table that stores each saved match, including the winner, loser, score, game type, and played time.
- A win gives `3` points and a loss gives `0` points.
- The app uses match history as the main source for the rank list.
- `LeagueStatsService` recalculates points, wins, losses, and matches played after a match is created, edited, or deleted.

## Diagram images

Exported diagram images are saved in `docs/diagrams`.

### Architecture overview

![Architecture overview](docs/diagrams/architecture-overview.png)

This diagram shows the app at a high level.
The user opens the app in the browser, Laravel handles the main logic, and MySQL stores the data.

### MVC structure

![MVC structure](docs/diagrams/MVC%20structure.png)

This diagram shows how the main Laravel parts work together.
The request goes through the routes to the controllers, the controllers work with models and services, and Blade views return the page back to the browser.

### Database ER diagram

![Database ER diagram](docs/diagrams/database-er-diagram.png)

This diagram shows the two main database tables and how they are connected.
`participants` stores player and ranking data, and `match_games` stores each saved match with a winner and a loser.

### Register match sequence diagram

![Register match sequence diagram](docs/diagrams/register-match-sequence.png)

This diagram shows what happens when a user saves a new match result.
The app validates the form, saves the match, recalculates the standings, clears the cache, and then sends the user back to the dashboard with a success message.

## UI design choices

- The page uses a larger `H1` for the main page title and a smaller `H2` for card and section titles.
- This makes it easier to see the difference between the main title and the smaller section titles.
- Key actions now use small inline SVG icons, for example `Refresh`, `Statistics`, `Add Participant`, `Register Match`, `Edit`, `Delete`, and `Back to Dashboard`.
- The icons are used in a simple way so the page still looks clean and easy to read.

## Design pattern choices

- `Leaderboard-First Command Center` means the app shows the most important tournament information first.
- `Progressive Disclosure` means extra tools are still there, but they are shown step by step so the page does not feel too crowded.
- A partial `Split Workspace Pattern` is used by separating `Add` and `Manage` areas.

## Async Refresh

The `Refresh` button is an async feature.

- When the user clicks `Refresh`, the browser asks the server for new rank-list and latest-match data in the background.
- Then only the top dashboard area is updated, without reloading the whole page.
- If the user already loaded more rank-list rows, `Refresh` keeps those rows visible instead of going back to the first rows only.
- After a successful refresh, the user sees a small success toast.

Async refresh means the page updates data that is already visible on the screen.

## Lazy Loading

The project now uses lazy loading in a few places.

- The first page load shows only the first 10 rank-list rows.
- When the user opens the `Manage` tab, it starts with the first 10 history rows.
- When the user clicks `Load more` under the rank list, the browser asks the server for the next players in the background.
- When the user clicks `Load more` under match history, the browser asks the server for the next match rows in the background.
- The new rows are added to the same page section without reloading the page.
- The statistics page loads the chart scripts only when the chart area is reached.
- The dashboard uses shared Edit popups. When the user clicks Edit, the app fills the correct popup with the matching data. This is better than loading a hidden popup for every row at the start.

Lazy loading means the page does not load all extra content at the start. It loads more only when the user needs it.

## Lighthouse Improvements

Some frontend files were cleaned up to help the Lighthouse score.

Vite is a frontend build tool. It prepares CSS and JavaScript files so the browser can load them more cleanly and quickly.

- I removed CSS that the page was not really using, so the browser has less styling to read at the start.
- I moved statistics-only styling into its own file, so the dashboard does not load extra stats styling when it does not need it.
- I stopped loading extra web font files and used a system font stack, so the text can appear faster.
- I made Bootstrap JavaScript load in smaller pieces, so the page does not download dashboard-only code everywhere.
- I made the charts load later, only when the statistics area is needed.
- I moved the heavy manage section on the dashboard so it loads when the user opens that tab, instead of loading all of it immediately.
- I added cache and compression handling for the local server, so files can be sent more efficiently during local testing.
- I cached repeated statistics queries for a short time, so the stats page does not repeat the same work on every request.


## Assignment and submission notes

- Laravel backend with Blade views
- MySQL database
- Bootstrap 5 responsive GUI
- async refresh and lazy loading
- PHPUnit tests
- GitHub repository
- edit, delete, search, filter, and statistics features
- logs for service desk style support
- architecture and documentation support

## File Explanations

- `config/logging.php` sets up the log channels and includes a separate `league` log file for app activity.
- `DashboardController.php` loads the dashboard shell, statistics data, refresh data, load-more data, and the lazy workspace partials.
- `MatchGameController.php` saves, edits, and deletes match results.
- `ParticipantController.php` saves, edits, and deletes participants.
- `LeagueStatsService.php` recalculates participant points, wins, losses, and matches played from match history.
- `resources/views/layouts/app.blade.php` loads the built frontend files with Vite.
- `resources/css/app.css` contains the Bootstrap CSS import and the shared app styles.
- `resources/js/app.js` loads the main frontend JavaScript and the Bootstrap JavaScript parts the app needs.
- `resources/js/dashboard.js` contains the dashboard JavaScript for Refresh, Load more, shared popups, and scroll restore.
- `resources/js/stats-page.js` lazy loads the local chart files when the statistics section is needed.
- `resources/js/stats-charts.js` builds the statistics charts after Chart.js has loaded.
- `dashboard.blade.php` shows the dashboard shell and provides the HTML and hidden data used by the dashboard JavaScript.
- `resources/views/dashboard/partials/manage-workspace.blade.php` contains the manage tools, shared edit popups, and match-history UI.
- `stats.blade.php` shows the statistics page and provides the hidden data used by the statistics JavaScript.
- `routes/web.php` connects each URL to the correct controller method.
- `docs/screenshots` stores the screenshots used in the documentation.
- The feature test files check that the main user actions still work correctly.

## Monitoring and event management

The project includes a simple monitoring setup through logging.

- Important participant and match actions are written to the `league` log channel.
- Validation failures for edit actions are also logged.
- This makes it easier to see important events during testing and debugging.

## ITIL 4 Practices In This Project

This project is not a full company-level ITIL setup, but you can still see several ITIL 4 ideas in a simple school-project way.

- Service Configuration Management: The project structure, dependency files, routes, and database migrations make the app easier to track and understand. Examples are `composer.json`, `package.json`, `routes/web.php`, and `database/migrations`.
- Change Enablement: Git and GitHub help manage code changes, and the controllers handle create, update, and delete actions in a controlled way. This helps make changes safer.
- Service Validation and Testing: The app uses PHPUnit tests, and the GitHub Actions workflow runs tests on pushes and pull requests. This helps check that important features still work after changes.
- Knowledge Management: The README, diagrams, screenshots, and folder structure help explain how the project works. This makes the system easier to learn and maintain.
- Continual Improvement: The project was improved through better validation feedback, stronger tests, accessibility updates, clearer async feedback, and planned future improvements.
- Service Design: The dashboard and statistics page were built around the user's main tasks, like checking rankings, registering matches, filtering history, and viewing tournament insights.
- Problem Management: `LeagueStatsService` recalculates standings from match history instead of using fragile manual counters. This helps reduce leaderboard mistakes and makes problems easier to fix.

## Troubleshooting

- If `php artisan migrate` says the database does not exist, create `friday_fun_league_db` first in phpMyAdmin.
- If you see a MySQL connection or access-denied error, make sure XAMPP MySQL is running and that your `.env` values match your local host, port, database, username, and password.
- If Laravel says the application key is missing, run `php artisan key:generate`.
- If CSS or JavaScript changes do not appear, run `npm run dev` while you are working or run a fresh `npm run build` and `php artisan optimize:clear`.
- If port `8000` is already busy, start Laravel with `php artisan serve --port=8001`.
- If you use XAMPP Apache for this project, do not also keep `php artisan serve` running for the same app.


## Conclusion and future improvements

This project worked well with Laravel, Blade, Bootstrap, and Vite because the app was easier to build, manage, and update.

- MySQL was a good choice for the current version of the project.
- In a bigger version, React or Vue could be added for a more interactive frontend.
- SQL Server could also be a good option if the project needed stronger Microsoft integration or more enterprise-style reporting.
- Login, user roles, and more automated tests would be good next steps.

## Notes

- No login/authentication is included because it was not required for the assignment.
- The app uses match history as the source of truth for standings.
- A service class is used to recalculate standings after create, edit, and delete actions.
