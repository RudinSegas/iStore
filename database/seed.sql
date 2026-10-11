SET NAMES utf8mb4;
INSERT INTO categories (id,name,slug) VALUES
(1,'טלפונים','phones'),
(2,'מחשבים','computers'),
(3,'קונסולות','consoles'),
(4,'מסכים','monitors');
INSERT INTO products (category_id,name,short_description,description,price_cents,stock,image) VALUES
(1,'iPhone 15','128GB · צבע שחור','טלפון חכם עם מסך 6.1 אינץ׳, חיבור USB-C ומצלמה ראשית 48MP.',299900,15,'iphone15.svg'),
(2,'Lenovo ThinkPad E14','14 אינץ׳ · 16GB RAM · 512GB SSD','מחשב נייד לעבודה וללימודים עם זיכרון 16GB וכונן SSD בנפח 512GB.',349900,15,'thinkpad.svg'),
(3,'PlayStation 5','קונסולה · גרסת דיסק','קונסולת משחקים עם כונן דיסקים ובקר אלחוטי. פריט הדגמה לקטלוג.',239900,15,'ps5.svg'),
(4,'Samsung Odyssey G5','27 אינץ׳ · QHD · 144Hz','מסך גיימינג בגודל 27 אינץ׳ עם רזולוציית QHD וקצב רענון 144Hz.',129900,15,'odyssey.svg'),
(1,'Samsung Galaxy S24','256GB · צבע אפור','טלפון חכם עם אחסון בנפח 256GB, מסך AMOLED ומצלמות מתקדמות.',279900,15,'galaxy.svg'),
(2,'MacBook Air M2','13 אינץ׳ · 8GB RAM · 256GB SSD','מחשב נייד קל עם מעבד Apple M2. מתאים ללימודים, תכנות ועבודה יומיומית.',429900,15,'macbook.svg'),
(3,'Xbox Series X','1TB · בקר אלחוטי','קונסולת משחקים עם אחסון 1TB ובקר אלחוטי. מחירים ומלאי להמחשה בלבד.',229900,15,'xbox.svg'),
(4,'Dell UltraSharp U2722D','27 אינץ׳ · QHD · IPS','מסך עבודה עם פאנל IPS בגודל 27 אינץ׳ ורזולוציית QHD.',169900,15,'dell.svg'),
(1,'Google Pixel 8','128GB · צבע ירוק','טלפון Android עם חוויית מערכת נקייה ומצלמה איכותית.',249900,15,'pixel.svg'),
(2,'ASUS Vivobook 15','15.6 אינץ׳ · 16GB RAM · 512GB SSD','מחשב נייד לשימוש יומיומי עם מסך גדול, זיכרון 16GB ואחסון SSD.',259900,15,'vivobook.svg'),
(3,'Nintendo Switch OLED','64GB · בקרי Joy-Con','קונסולה ניידת עם מסך OLED ושני בקרי Joy-Con.',149900,15,'switch.svg'),
(4,'LG UltraGear 24','24 אינץ׳ · Full HD · 144Hz','מסך קומפקטי לגיימינג ולעבודה עם קצב רענון 144Hz.',79900,15,'lg.svg');
