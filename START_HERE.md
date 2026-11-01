# iStore — Start Here / התחלה מהירה

iStore הוא פרויקט תיק עבודות של חנות מקוונת בעברית (RTL), המבוסס על PHP, ‏MySQL ו־JavaScript, עם עוזר קניות מבוסס Gemini AI.

**GitHub:** https://github.com/RudinSegas/iStore

## הפעלה מקומית באמצעות Docker (מומלץ)

דרישות: Git ו־Docker עם Docker Compose.

```bash
git clone https://github.com/RudinSegas/iStore.git
cd iStore
cp .env.example .env
```

ב־Windows PowerShell ניתן להשתמש בפקודה:

```powershell
Copy-Item .env.example .env
```

פתחו את `.env` והגדירו סיסמאות חזקות וייחודיות עבור `DB_PASSWORD`, ‏`DB_ROOT_PASSWORD` וסוד עבור `APP_SECRET`.

להפעלת Gemini, הגדירו גם `AI_ENABLED=1`, ‏`GEMINI_API_KEY` ו־`AI_RATE_SECRET`. אפשר להשאיר את Gemini כבוי; החנות עצמה עדיין פועלת. ודאו ששם המודל מתאים לחשבון ה־Gemini שלכם.

הריצו:

```bash
docker compose up --build -d
docker compose ps
```

פתחו בדפדפן: **http://127.0.0.1:8080**

לעצירה ללא מחיקת נתוני המסד:

```bash
docker compose down
```

**שימו לב:** אין להריץ `docker compose down -v` אלא אם רוצים למחוק במכוון את נתוני מסד הנתונים. קובצי ה־SQL ליצירת המסד נטענים אוטומטית רק כאשר נפח הנתונים חדש.

## הפעלה ללא Docker

1. התקינו PHP 8.2 ומעלה עם `pdo_mysql`, ‏`curl`, ‏`mbstring` ושרת MySQL/MariaDB.
2. צרו מסד ריק וייבאו לפי הסדר את `database/schema.sql`, ‏`database/seed.sql`, ‏`database/upgrade_ai_assistant.sql`, ‏`database/upgrade_v1.sql`.
3. הריצו `php scripts/setup.php` והגדירו את מסד הנתונים בקובץ הפרטי `config/config.php`.
4. להפעלת Gemini, הגדירו את המפתח בקובץ הפרטי `config/ai.php`.
5. הריצו `php -S 127.0.0.1:8080 -t public`.

אין להשתמש בשרת הפיתוח המובנה של PHP כשרת אינטרנט ציבורי.

## Google Cloud — מצב הפריסה

הקוד המעודכן כבר הועלה ל־GitHub. הפריסה ל־Google Cloud Compute Engine נמצאת בהכנה וטרם אומתה כאתר חי. היעד הוא Nginx כ־reverse proxy, לצד קונטיינרים של PHP/Apache ו־MySQL באמצעות Docker Compose.

בשרת יש ליצור `.env` פרטי חדש, להפעיל את הקונטיינרים, להגדיר HTTPS ולבדוק את האתר לפני פרסום כתובת ציבורית.

## חשוב לדעת

- זו **חנות הדגמה**: אין סליקה אמיתית ואין להזין פרטי אשראי או מידע אישי אמיתי.
- לעולם אין להעלות `.env`, סיסמאות, מפתחות Gemini או קובצי הגדרות פרטיים ל־GitHub.
- קובצי בדיקה עשויים להופיע בקוד המקור לצורכי תיק עבודות, אך אין לחשוף נקודות אבחון בשרת ציבורי.
- תיקיות `app/` ו־`public/` הן מקור הקוד הפעיל; אין להסתמך על תיקיית `build/infinityfree/htdocs/` הישנה.

לפרטים מלאים: [README.md](README.md). לתכנון העוזר: [docs/AI_ASSISTANT.md](docs/AI_ASSISTANT.md). לבדיקות: [docs/TEST_REPORT.md](docs/TEST_REPORT.md).
