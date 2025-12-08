========================================
FUNKTIONSLISTA – LÄROPORTAL
========================================

ADMINPANEL
• createUser() – Skapa nya användare med roll (student/teacher/admin) och hashat lösenord.
• updateUser() – Ändra användarnamn/e‑post/roll/lösenord.
• deleteUser() – Ta bort användare ur databasen.
• createExercise() – Skapa övning med blandade frågetyper (MCQ, Sant/Falskt, Ordning, Para ihop, Textluckor med ord‑bank).
• updateExercise() – Hämta och uppdatera befintlig övning och dess frågor.
• deleteExercise() – Radera övning.
• createMaterial() – Lägg till nytt läromaterial.
• updateMaterial() – Uppdatera befintligt material.
• deleteMaterial() – Ta bort material.
• loadUsers() – Lista användare för adminpanel.
• loadExercises() – Lista övningar för adminpanel.
• startEditExercise() – Hämta och ladda vald övning för redigering.
• addEditQuestion() – Lägg till fråga i redigerat test.
• removeEditQuestion() – Ta bort fråga i redigerat test.
• loadMaterials() – Hämta material för adminlistan.
• startEditMaterial() – Initiera redigering av valt material.
• saveMaterialEdit() – Spara ändrat material.
• cancelMaterialEdit() – Avbryt materialredigering.
• cancelExerciseEdit() – Avbryt övningsredigering.

EXTRA FUNKTIONER / GAMIFICATION
• renderProgressBar() – Visar XP och progression mot nästa nivå.
• levelSystem() – Dynamisk nivåökning baserad på XP (formel fallback om nivåtabell tar slut).
• passFailRule() – 70 % rätt krävs för godkänt; badgar/stjärnor visas därefter.
• animateScore() – Räknar upp procenttal vid resultatvisning.
• triggerConfetti() – Visar konfetti på godkänt (70 %+) och XP-vinst.
• scoreAnimation() – Animerar siffervisning av poäng.
• statusBadges() – Visar stjärnor/labels baserat på procentgränser.

CREATE/EDIT TEST (FRÅGETYPER)
• addQuestion() – Lägg till fråga av vald typ (MCQ/SantFalskt/Ordning/ParaIhop/Textluckor).
• updateQuestion() – Redigera frågedata per typ.
• saveExercise() – Persistar övning med alla frågor till databasen.
• normalizeType() – Mappar variantnamn till standardtyp innan spar.
• getEditor() – Väljer rätt editor‑komponent per frågetyp.
• addOption()/removeOption() – Hantera svarsalternativ per frågetyp.
• toggleBlank() – Markera/demarkera luckor i textluckor.
• ensureWords() – Säkerställ att ord‑bank innehåller markerade ord.

FRONTEND (VUE)
• dashboardView – Visar användarstatus (XP, nivå), senaste 5 resultat, filterbar övningslista.
• loginRegisterViews – Hanterar kontoinloggning/registrering för student/admin.
• responsiveDesign – Layout anpassad för mobil/surfplatta/dator.
• exercisePage – Renderar frågor per typ och beräknar resultat/XP.
• materialsView – Lista artiklar, förhandsvisning och modal för fulltext.
• adminView – Adminpanel för användare/övningar/material.
• addExerciseView – Formulär för ny övning (titel, beskrivning, typ, frågor).
• addMaterialView – Formulär för nytt läromaterial.
• accountView – Visar profil/XP/nivå/statistik för inloggad.
• appHeader – Navigationshuvud med sessionstatus och logga ut.
• filtersUI – Status/kategori/sök-sort-kontroller för övningslistan.
• resultBadge() – Färgad badge för pass/fail per övning.
• introFlow() – Startknapp som döljer intro och startar quizet.
• loginView – Adminlogin (api/login.php).
• studentLoginView – Studentlogin/registrering (api/student.php + api/register.php).
• materialsModal – Modal för fulltext i materialsidan.
• questionList – Loopar ut frågor på exercisePage med dynamiska komponenter.
• appRouter – Definierar rutter/guards för /home, /admin, /add, /materials, /exercise/:id, /account, /login, /login-student.

USER CONTENT / LÄRINNEHÅLL
• loadMaterials() – Hämtar läromaterial från databasen (inloggad krävs).
• renderContent() – Visar text (pre-wrap) i responsivt format; modal för fulltext.
• preview(text) – Skapar kort förhandsvisning av materialtext.

STUDENTKOMPONENTER (FRONTEND)
• MultipleChoiceQuestion – Radio‑val, en korrekt option, emittar correctness + val.
• TrueFalseQuestion – Två radioknappar, emittar correctness + val.
• OrderingQuestion – Flytta upp/ner, emittar ordning + correctness.
• MatchQuestion – Vänster/höger‑selects, emittar parning + correctness.
• FillBlankQuestion – Drag/drop eller klick från ord‑bank till luckor, emittar svar + correctness.
• ResultFeedback – Inbyggd rätt/fel‑visning i respektive komponent (disabled‑läge).

EDITOR KOMPONENTER (FRONTEND)
• MCQEditor – Redigera text + alternativ, markera korrekt.
• TrueFalseEditor – Ange text + sant/falskt‑svar.
• OrderingEditor – Ange instruktion + ordning av items.
• MatchEditor – Ange instruktion + vänster/höger‑par.
• FillBlankEditor – Markera luckor i text + hantera ord‑bank.
• QuestionCard – Wrapper för editor med typväljare och ta‑bort.
• AddExerciseForm – Helhetsformulär för ny övning (titel/beskrivning/typ + frågelista).
• AddMaterialForm – Formulär för nytt material (titel/innehåll).
• AdminLists – Lista användare/övningar/material med redigera/ta bort.

API ENDPOINTS (BACKEND)
• login.php – Admin‑inloggning, session + CSRF.
• student.php – Studentinloggning och save_result (XP) med CSRF och rollkontroll.
• admin.php – Admin‑login + CRUD för users/exercises/materials.
• create_exercise.php – Admin: skapa övning + frågor (mixed payload).
• exercises.php – Lista övningar (student/admin); admin CRUD (create/update/delete) med CSRF.
• exercise.php – Hämta en övning + frågor (inloggade).
• materials.php – Lista material (inloggade); admin CRUD med CSRF.
• save_result.php – Spara score + XP (inloggade, CSRF).
• get_user_stats.php – Returnerar XP/level/stats; nivå‑fallback via formel.
• get_user_results.php – Hämtar resultat för användare (rollkontroll).
• user_progress.php – Aggregerar progress (total/average/recent) för dashboard.
• auth_helpers.php – Sessionhärdning, CORS, CSRF‑helpers, headers, rollkontroller.
• register.php – Studentregistrering med hashed lösenord.
• apiConfig.js – Frontend base‑URL till API.

DATA/XP/LOGIK
• xpAward() – XP‑beräkning vid ≥70 % score (formel i student/save_result).
• levelFallback() – När nivåtabell saknar nästa nivå, beräkna via formel (triangulär * 100).
• passThreshold() – 70 % används i UI/XP‑logik och badges.
• normalizeType() – Mappar frågetyper till standard (mcq, true_false, ordering, match, fill_blank).
• storeAnswers() – Svar lagras per fråga i exercise payload (JSON).

ROUTING/NAV
• routerGuards() – Skyddar admin/add/materials/home/account med rollkrav.
• appHeader() – Visa länkar baserat på session (student/admin), tema, logga ut.
• introFlow() – “Börja övningen” visar/döljer beskrivning innan frågor.

SÄKERHET (IMPLEMENTERAT)
• preparedStatements() – PDO + preparerade statements för DB‑anrop.
• csrfProtect() – CSRF‑token på skrivande endpoints.
• hardenSession() – session_regenerate_id, cookies httponly/secure (vid HTTPS), samesite=Lax.
• securityHeaders() – CSP (self), X-Frame-Options SAMEORIGIN, X-Content-Type-Options nosniff, Referrer-Policy no-referrer-when-downgrade.
• cors() – Vitlistade origin; credentials tillåts.
• requireLoggedIn()/requireRole() – Stoppar otillåten åtkomst per endpoint.
• allowCors() – Begränsar till godkända origin, med credentials.
• issueCsrf()/verifyCsrf() – Skapar och validerar CSRF‑token.

KÄNDA AVVIKELSER/VAL
• legacyLoginFallback() – Tillåter plaintext/admin123 för vissa konton (svaghet); nya konton hashas.
• achievements() – Inte implementerat.
• dragDropSorting() – Ingen separat drag‑and‑drop‑sorterare; drag/drop finns i textluckor med ord‑bank.
