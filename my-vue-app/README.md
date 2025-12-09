# Learning Portal (Vue)

Vue 3 + Vue Router admin/student portal with exercises, reading materials, and user management.

## Project scripts
- `npm install` – install deps
- `npm run serve` – dev server on http://localhost:8080
- `npm run build` – production bundle to `dist/`
- `npm run lint` – eslint

## Backend/API
- Expects PHP endpoints under `API_BASE` (see `src/apiConfig.js`), e.g. `admin.php`, `student.php`, `register.php`, `exercises.php`, `materials.php`.
- Authentication relies on localStorage keys: `admin_id`, `student_id`, optional `csrf_token`.
- Guards in `src/router/index.js` block admin routes (`/admin`, `/add`, `/add-material`) without `admin_id` and student home `/home` without login.

## Frontend features
- Admin: login, manage users (create/edit/delete, filters), manage exercises and questions, manage reading materials with inline create/edit/delete.
- Students: login/register toggle, exercises and materials browse.

## Notes
- Database name currently targeted by the dump: `LearningportalBackup` (update your server accordingly).
- If importing the provided SQL dump, ensure `exercise_questions` has `PRIMARY KEY (Question_Id)` before adding `AUTO_INCREMENT`.

## Deploy
1) Build with `npm run build`  
2) Serve the `dist/` directory with your static host or a simple server like `npx serve -s dist`
