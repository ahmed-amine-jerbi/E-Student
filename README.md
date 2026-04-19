# E-Student

E-Student is a simple PHP-based classroom platform for students and teachers. It provides a centralized entry point for authentication, course channels, schedules, assignments, and academic data.

## Features

- User registration and login with secure password hashing
- Role-aware dashboard access (Student, Teacher, Admin)
- Dynamic filiere / niveau / groupe selection during signup
- **Classroom channel discovery with password-protected joining**
- **Display of channel instructor (Enseignant) information**
- Grades and timetable overview with live database integration
- Assignment tracking by filiere, level, and group
- Database-driven user, channel, notes, and group management

## Project Structure

- `index.php` — Root redirect to `src/index.php`
- `src/index.php` — Public landing page and registration/login links
- `src/pages/login.php` — Login page and authentication handling
- `src/pages/register.php` — Registration page with validation and user creation
- `src/pages/dashboard.php` — Role-based dashboard router
- `src/pages/dashboards/etudiant.php` — Student dashboard with channels and grades
- `src/pages/dashboards/enseignant.php` — Teacher dashboard (placeholder)
- `src/pages/dashboards/admin.php` — Admin dashboard (placeholder)
- `src/prefabs/auth.php` — Session and authentication helper functions
- `src/prefabs/database_connection.php` — PDO database connection helper
- `src/prefabs/header.php` — Shared site header/navbar partial
- `src/prefabs/logout.php` — Logout handler
- `src/prefabs/JoinChannel.php` — Dedicated channel joining page with password entry
- `src/prefabs/ChannelView.php` — Channel messaging and communication dashboard
- `src/bases/pfa_esen_2026.sql` — MySQL dump containing the schema and seed data

## Database Setup

1. Import the `pfa_esen_2026` database.
2. Verify your local MySQL server is running.
3. Update database credentials in `src/prefabs/database_connection.php` if needed.

Example import command:

```bash
mysql -u root -p pfa_esen_2026 < src/bases/pfa_esen_2026.sql
```

## Tables of Interest

The SQL dump defines the following core tables:

- `utilisateurs` — Registered users with roles and group assignment
- `filieres` — Academic departments / programs
- `groupes` — Class groups linked to a filiere and niveau
- `niveaux` — Academic year levels (License, Master, etc.)
- `channels` — Classroom channels tied to a filiere
- `joined_channels` — User-channel membership mapping
- `matieres` — Course subjects and coefficients
- `notes` — Student grades for specific matieres

> Note: The dashboard code now reads `notes.Valeur` and uses the subject coefficient from `matieres`, matching the SQL schema.

## Channel Joining

Students can browse and join classroom channels through a dedicated page:

1. Navigate to the **Available Channels** section on the student dashboard.
2. For each channel, you will see:
   - Channel name (Libelle)
   - Instructor name (Enseignant - fetched from the utilisateurs table)
   - Channel description
   - Join/View button with status badge if already joined
3. Click **Join Channel** to navigate to the dedicated join page (`src/prefabs/JoinChannel.php`).
4. The join page displays:
   - Full channel information (name, description, instructor)
   - A password entry form
   - Instructions to contact the instructor for the password
5. Enter the correct channel password and submit to join.
6. Upon successful authentication, you will be added to the channel and see a success message.
7. If already joined, the page shows a confirmation message and allows you to return to the dashboard.
8. If the password is incorrect, an error message appears.

## Channel Messaging & Communication

Once a student joins a channel, they can access the **Channel View** page to communicate with instructors and submit assignments:

### Channel Dashboard Features

- **Message Thread**: Displays all messages in chronological order
- **Instructor Messages**: Highlighted with a lighter yellow/orange background (`#fef3cd`) for easy identification
- **Message Composition**: Side panel with textarea for typing messages
- **File Uploads**: Students can attach documents, images, or assignments (max 10MB)
  - Supported file types: PDF, Office files (DOC, DOCX, XLS, XLSX), images (PNG, JPG), archives (ZIP)
- **Channel Info Sidebar**: Shows instructor name and total message count

### Workflow

1. From the dashboard **Available Channels** section, click **View Channel** for any channel you've joined
2. On the channel page, you can:
   - Read all messages from instructors and classmates
   - Type and submit your own messages
   - Upload files (for assignments, documentation, etc.)
   - See messages organized by date/time with author names
3. Instructor messages automatically appear with the yellow/orange highlight and "Instructor" badge
4. All messages are stored in the `messages` table with:
   - Channel ID (for channel-specific messaging)
   - User ID (to identify sender)
   - File reference (for attachments)
   - Timestamp

### File Storage

- Uploaded files are stored in `src/uploads/` directory
- Files are renamed with unique identifiers to prevent conflicts
- File links in messages allow download/preview

## Usage

1. Place the repository in your web server root, for example `c:/xampp/htdocs/PFA`.
2. Start Apache and MySQL (XAMPP control panel).
3. Visit `http://localhost/PFA/`.
4. Use the landing page to register a new student account or login.
5. After login, use the dashboard to review channels, grades, assignments, and timetable.
6. Join channels using the password provided by your instructor.
7. Click **View Channel** to enter the messaging/communication space.
8. Submit messages and assignments directly in the channel.

## Important Improvements

- Added comments to all main PHP files for maintainability.
- Preserved registration form values on validation failure.
- Corrected dashboard channel and note queries to match the database structure.
- Improved user feedback for registration errors.
- Created dedicated `JoinChannel.php` page with similar design to `Login.php` for password-protected channel joining.
- **Created `ChannelView.php` with full messaging capability**
- **Instructor messages highlighted with yellow/orange background for visual distinction**
- **File upload support for assignment submission**
- **Enhanced dashboard with real-time channel status and instructor information display**

## Notes

- The application uses PHP session storage for authentication.
- `src/prefabs/database_connection.php` currently connects to `localhost` with an empty password. Update this if your MySQL server uses credentials.
- Channel passwords are stored in plain text in the `channels.Cle` column. Consider implementing password hashing for production use.
- Students are stored in `joined_channels` table once they successfully enter the correct channel password.
- The `messages` table now supports channel-specific messaging with optional file attachments.
- Ensure the `src/uploads/` directory exists and is writable for file uploads to work properly.
