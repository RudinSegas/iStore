# iStore v1.0 — התחלה מהירה

הפרויקט המעודכן נמצא בתיקיות `app/` ו־`public/`. קבצי הפריסה שנוצרו מהן נמצאים ב־`build/infinityfree/htdocs/`.

1. התקינו PHP עם `pdo_mysql`, `curl`, `mbstring` ומסד MySQL/MariaDB מקומי.
2. במסד חדש בלבד ייבאו את `schema.sql`, `seed.sql`, `upgrade_ai_assistant.sql`, `upgrade_v1.sql` מתוך `database/`.
3. הריצו `php scripts/setup.php` וערכו מקומית את פרטי המסד ב־`config/config.php`.
4. הריצו `php -S 127.0.0.1:8080 -t public`.
5. להפעלת Gemini הגדירו מפתח חדש בקובץ הפרטי `config/ai.php`, הגדירו `enabled` ל־`true` ושמרו את הסוד האקראי שנוצר. המפתח שהגיע בארכיון המקורי צריך החלפה.

אין חיוב כספי ואין איסוף כרטיס אשראי. השתמשו בנתוני הדגמה בלבד.

הוראות מלאות והפעלה אופציונלית ב־Docker: `README.md`. פריסה ל־InfinityFree ובדיקת החיבור המבודדת: `docs/INFINITYFREE.md`. תוצאות בדיקות שבוצעו בפועל ומגבלות: `docs/TEST_REPORT.md`.

האתר החי לא שונה. אין הבטחה ש־Gemini זמין מהחשבון שלכם ב־InfinityFree עד להרצת בדיקת החיבור שם. אין לשמור מפתח API בקוד הציבורי או להעלות קבצי הגדרות פרטיים ל־GitHub.
