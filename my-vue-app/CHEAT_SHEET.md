# Lärportalen – Snabbguide

## Vad den gör
- Studenter loggar in, läser material och kör övningar (MCQ, Sant/Falskt, Ordning, Para ihop, Textluckor med ord‑bank/drag‑drop).
- 70 % krävs för godkänt; XP och nivå uppdateras när man klarar en övning.
- Admin kan skapa/redigera övningar och material samt hantera användare.

## Viktiga flöden
- **Login:** Student via `/login-student` (kan registrera nytt konto), admin via `/login`. Session + CSRF sätts på inloggning.
- **Dashboard (/home):** Visar nivå/XP, nästa nivå, senaste 5 resultat, och en filtrerbar övningslista (status, kategori, sök, sortering).
- **Köra övning (/exercise/:id):** Introtext → “Börja” → frågor visas. Efter “Kontrollera” får man pass/fail, rätt/fel och XP om ≥70 %.
- **Adminpanel (/admin):** Flikar för användare, övningar, material. Skapa/redigera övningar (blandade typer), redigera/ta bort material, skapa/redigera användare.
- **Material (/materials):** Lista artiklar, klick öppnar modal med fulltext. Admin kan lägga till via `/add-material`.

## Frågetyper (hur de funkar)
- **MCQ:** Radio-knappar, en rätt. Facit lagras i frågedata.
- **Sant/Falskt:** Två val, facit i frågedata.
- **Ordning:** Knappen upp/ner flyttar rader; korrekt om ordning matchar facit.
- **Para ihop:** Vänster etikett + select för höger alternativ; korrekt om matchar facit.
- **Textluckor (ord‑bank):** Admin markerar ord i texten som luckor och bygger en ord‑bank. Student drar eller klickar ord från banken till luckorna; korrekta ord i rätt position krävs.

## Datamodell (högnivå)
- `exercises`: ett övningshuvud (titel, beskrivning, typ).
- `exercise_questions`: rader kopplade till `exercises` via `Exercise_Id`. Varje rad har `Question_Type` + `Data` (JSON) med den faktiska frågedatan (t.ex. text, alternativ, facit).
- `user_results`: resultat per användare och övning (poäng, completed).
- `users`: konton, XP, roll. `materials`: läsmaterial.

## Flöde till/från databasen
- Admin sparar övning: POST JSON → PHP (`create_exercise`/`exercises`) → `exercises` + `exercise_questions`.
- Student sparar resultat: POST score/answers → PHP (`save_result`/`student`) → `user_results`, XP uppdateras i `users`.
- Dashboard/profil: GET API (`user_progress`, `get_user_stats`, `get_user_results`) → renderar nivå, XP, snitt, senaste resultat.

## Skapa/redigera övning (admin)
1) Gå till `/add` eller “Redigera” i adminpanelen.
2) Fyll titel, beskrivning, välj övningstyp (eller “mixed”).
3) Lägg till frågor; välj typ per fråga och fyll data (alternativ, par, ordning, luckor + ord‑bank).
4) Spara – skickar JSON till backend med CSRF‑token.

## Resultat & XP
- Poäng räknas på antal rätt / total * 100.
- ≥70 % → godkänt, pass-badge, XP tilldelas och sparas på användaren.
- Resultat sparas i DB; dashboard och profil hämtar nivå, XP, snitt och senaste resultat via API.

## Säkerhetsnoter (kort)
- Sessionshärdning (httponly, secure vid HTTPS, samesite=Lax), CSRF‑token på skrivande API-anrop, prepared statements, grundläggande headers (CSP, X-Frame-Options, nosniff).
- Legacy inloggning tillåter plaintext/admin123 fallback (svagt) men nya konton hashas.

## Snabba felsökningstips
- Ser du inga data? Kontrollera att API_BASE i `src/apiConfig.js` pekar rätt och att du är inloggad (cookies).
- CSRF-fel? Se till att token skickas (lagras i localStorage vid login) och att du är inloggad som rätt roll.
- Drag/drop textluckor: ord‑banken måste innehålla de markerade orden; annars finns inget att dra.
