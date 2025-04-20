CREATE DATABASE  IF NOT EXISTS `nngasu_bookshelf` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `nngasu_bookshelf`;
-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: localhost    Database: nngasu_bookshelf
-- ------------------------------------------------------
-- Server version	8.0.41
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
--
-- Table structure for table `Achievement`
--
DROP TABLE IF EXISTS `Achievement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Achievement` (
  `id_achievement` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `icon_url` varchar(512) DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_achievement`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `Achievement`
--
LOCK TABLES `Achievement` WRITE;
/*!40000 ALTER TABLE `Achievement` DISABLE KEYS */;
INSERT INTO `Achievement` VALUES (1,'Новичок на полке',NULL,'Прочитайте свою первую книгу на \"Книжной полке\".'),(2,'Любитель чтения',NULL,'Прочитайте 10 книг на \"Книжной полке\".'),(3,'Книжный марафонец',NULL,'Прочитайте 50 книг на \"Книжной полке\".'),(4,'Книжный гуру',NULL,'Прочитайте 100 книг на \"Книжной полке\".'),(5,'Первый отзыв',NULL,'Напишите свою первую рецензию на книгу.'),(6,'Критик',NULL,'Напишите 10 рецензий на книги.'),(7,'Литературный эксперт',NULL,'Напишите 50 рецензий на книги.'),(8,'Разнообразие жанров',NULL,'Прочитайте книги из 5 разных жанров.'),(9,'Мастер жанров',NULL,'Прочитайте книги из 20 разных жанров.'),(10,'Собиратель историй',NULL,'Добавьте 10 книг в свою библиотеку.'),(11,'Коллекционер',NULL,'Добавьте 50 книг в свою библиотеку.'),(12,'Социальный читатель',NULL,'Подпишитесь на 5 других пользователей.'),(13,'Друг книголюбов',NULL,'Подпишитесь на 20 других пользователей.'),(14,'Популяризатор чтения',NULL,'Получите 10 лайков на свои рецензии.'),(15,'Влиятельный критик',NULL,'Получите 100 лайков на свои рецензии.'),(16,'Исследователь классики',NULL,'Прочитайте 5 книг жанра \"Классическая литература\".'),(17,'Фанат фэнтези',NULL,'Прочитайте 5 книг жанра \"Фэнтези\".'),(18,'Любитель приключений',NULL,'Прочитайте 5 книг жанра \"Приключения\".'),(19,'Эрудит',NULL,'Прочитайте 3 книги жанра \"Научно-популярная литература\".'),(20,'Чат-мастер',NULL,'Напишите 50 сообщений в чате \"Книжной полки\".');
/*!40000 ALTER TABLE `Achievement` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `Book`
--
DROP TABLE IF EXISTS `Book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Book` (
  `id_book` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `cover_image_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id_book`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `Book`
--
LOCK TABLES `Book` WRITE;
/*!40000 ALTER TABLE `Book` DISABLE KEYS */;
INSERT INTO `Book` VALUES (1,'Убить пересмешника','История о расовом неравенстве и взрослении в небольшом американском городке 1930-х годов. Юная Скаут Финч и её брат Джем сталкиваются с жестокостью и несправедливостью, когда их отец, адвокат Аттикус Финч, защищает чернокожего мужчину, несправедливо обвинённого в преступлении.','/assets/img/Убить пересмешника.jpg'),(2,'1984','Антиутопия о тоталитарном обществе, где Большой Брат следит за каждым шагом граждан. Уинстон Смит пытается сохранить свою человечность и любовь в мире, где правда переписывается, а свобода — иллюзия.','/assets/img/1984.jpg'),(3,'Гарри Поттер и философский камень','Первая книга о мальчике-волшебнике Гарри Поттере, который узнаёт о своём магическом происхождении и поступает в школу чародейства Хогвартс. Вместе с друзьями он раскрывает тайну философского камня.','/assets/img/Гаррт Поттер и философский камень.jpg'),(4,'Гордость и предубеждение','История Элизабет Беннет и мистера Дарси, которые преодолевают социальные предрассудки и личные ошибки, чтобы найти любовь. Роман о семье, браке и общественных ожиданиях в Англии XIX века.','/assets/img/Гордость и предубеждение.jpg'),(5,'Песнь льда и пламени: Игра престолов','Эпическая сага о борьбе за Железный трон Вестероса. Семьи Старков, Ланнистеров и Таргариенов сражаются за власть, пока древнее зло пробуждается на севере.','/assets/img/Игра престолов.jpg'),(6,'Маленькие женщины','История четырёх сестёр Марч — Мег, Джо, Бет и Эми — которые взрослеют, сталкиваются с трудностями и находят своё место в мире во время и после Гражданской войны в США.','/assets/img/Маленькие женщины.jpg'),(7,'Скотный двор','Сатирическая аллегория на революцию и тоталитаризм. Животные на ферме восстают против хозяина, но их идеалы свободы быстро сменяются новой тиранией под руководством свиней.','/assets/img/Скотный двор.jpg'),(8,'Великий Гэтсби','История загадочного миллионера Джея Гэтсби и его любви к прекрасной Дейзи Бьюкенен на фоне роскоши и пустоты \"эпохи джаза\" 1920-х годов в Америке.','/assets/img/Великий Гэтсби.jpg'),(9,'Преступление и наказание','Раскольников, бедный студент, совершает убийство, считая себя \"сверхчеловеком\", но его мучает совесть. Роман исследует мораль, вину и искупление в России XIX века.','/assets/img/Преступление и наказание.jpg'),(10,'Над пропастью во ржи','Подросток Холден Колфилд, бунтующий против лицемерия взрослых, рассказывает о своих скитаниях по Нью-Йорку после исключения из школы.','/assets/img/Над пропастью во ржи.jpg'),(11,'Война и мир','Эпопея о жизни русских аристократов на фоне наполеоновских войн. Роман переплетает судьбы Пьера Безухова, Андрея Болконского и Наташи Ростовой с историческими событиями.','/assets/img/Война и мир.jpg'),(12,'Мастер и Маргарита','Сатана посещает Москву 1930-х годов, устраивая хаос, в то время как Мастер и его возлюбленная Маргарита борются за свою любовь. Роман сочетает мистику, сатиру и библейские мотивы.','/assets/img/Мастер и маргарита.jpg'),(13,'Сто лет одиночества','История семи поколений семьи Буэндиа в вымышленном городе Макондо. Роман исследует цикличность времени, одиночество и судьбу.','/assets/img/Сто лет одиночества.jpg'),(14,'Дюна','Пол Атрейдес становится мессией на пустынной планете Арракис, где добывают ценный ресурс — \"пряность\". Эпическая история о власти, религии и экологии.','/assets/img/Дюна.jpg'),(15,'Голодные игры','В антиутопическом мире Катнисс Эвердин участвует в жестоких Голодных играх, где подростки сражаются насмерть ради развлечения элиты.','/assets/img/Голодные игры.jpg'),(16,'Шерлок Холмс: Этюд в багровых тонах','Первое дело Шерлока Холмса и доктора Ватсона: они расследуют загадочное убийство в Лондоне, раскрывая мотивы мести, уходящие корнями в Америку.','/assets/img/Шерлок Холмс этюд в багровых тонах.jpg'),(17,'Автостопом по галактике','Артур Дент спасается с Земли перед её уничтожением и отправляется в комическое путешествие по галактике с эксцентричным инопланетянином Фордом Префектом.','/assets/img/Автостопом по галактике.jpg'),(18,'Цветы для Элджернона','Чарли Гордон, человек с низким интеллектом, проходит экспериментальную операцию, которая делает его гением. Но цена успеха оказывается слишком высока.','/assets/img/Цветы для элджерона.jpg'),(19,'Старик и море','Пожилой рыбак Сантьяго выходит в море, чтобы поймать огромную рыбу. История о стойкости, борьбе с природой и внутренними демонами.','/assets/img/Старик и море.jpg'),(20,'Анна Каренина','История трагической любви Анны Карениной и Алексея Вронского в России XIX века, переплетённая с размышлениями о морали, семье и обществе.','/assets/img/Анна каренина.jpg'),(21,'О дивный новый мир','В утопическом будущем люди генетически модифицированы и лишены эмоций. Бернард Маркс начинает сомневаться в идеальности этого мира.','/assets/img/о дивный новый мир.jpg'),(22,'Хроники Нарнии: Лев, колдунья и волшебный шкаф','Четверо детей через волшебный шкаф попадают в Нарнию, где им предстоит сразиться с Белой Колдуньей и помочь льву Аслану спасти мир.','/assets/img/Хроники Нарнии.jpg'),(23,'Моби Дик','Капитан Ахав одержим местью белому киту Моби Дику, который лишил его ноги. Эпическое путешествие о природе зла и человеческой одержимости.','/assets/img/Моби Дик.jpg'),(24,'Тёмные начала: Северное сияние','Лира Белаква отправляется на Север, чтобы спасти своего друга, раскрывая тайны магических частиц \"Пыль\" и параллельных миров.','/assets/img/Темные начала Северное Сияние.jpg'),(25,'Дракула','Джонатан Харкер сталкивается с графом Дракулой, вампиром, который угрожает Лондону. Группа людей объединяется, чтобы остановить его.','/assets/img/Дракула.jpg'),(26,'Зов Предков','Собака Бэк, похищенная из дома, становится ездовой на Аляске. История о выживании и возвращении к первобытным инстинктам.','/assets/img/Зов предков.jpg'),(27,'451 градус по Фаренгейту','В мире, где книги запрещены, пожарный Гай Монтэг начинает сомневаться в своей работе по их уничтожению и ищет смысл в литературе.','/assets/img/451 градус по фаренгейту.jpg'),(28,'Три товарища','История дружбы и любви трёх ветеранов Первой мировой войны в Германии 1920-х годов, где они сталкиваются с бедностью и утратой.','/assets/img/три товарища.jpg'),(29,'Дневник Анны Франк','Дневник еврейской девочки, которая с семьёй скрывалась от нацистов в Амстердаме во время Второй мировой войны.','/assets/img/Дневник Анны Франк.jpg'),(30,'Властелин колец: Братство кольца','Фродо Бэггинс и его друзья отправляются в опасное путешествие, чтобы уничтожить Кольцо Всевластия и победить тёмного владыку Саурона.','/assets/img/Властелин колец.jpg'),(31,'Алхимик','Молодой пастух Сантьяго отправляется в путешествие за сокровищем, которое приводит его к самопознанию и пониманию своей судьбы.','/assets/img/Алхимик.jpg'),(32,'Собачье сердце','Профессор Преображенский превращает собаку в человека, но эксперимент оборачивается сатирой на советское общество 1920-х годов.','/assets/img/Собачье сердце.jpg'),(33,'Матильда','Умная девочка Матильда, обделённая любовью родителей, открывает в себе телекинетические способности и использует их, чтобы защитить друзей от жестокой директрисы.','/assets/img/Матильда.jpg'),(34,'О мышах и людях','Два друга, Джордж и Ленни, мечтают о собственной ферме во времена Великой депрессии, но их мечты рушатся из-за трагических обстоятельств.','/assets/img/о мышах и людях.jpg'),(35,'Портрет Дориана Грея','Молодой Дориан Грей остаётся вечно юным, пока его портрет стареет и отражает его грехи. Роман о красоте, пороке и морали.','/assets/img/портрет дориана грея.jpg'),(36,'Крестный отец','История мафиозной семьи Корлеоне, где дон Вито и его сын Майкл борются за власть и выживание в криминальном мире Нью-Йорка.','/assets/img/Крестный отец.jpg'),(37,'Двенадцать стульев','Остап Бендер и Ипполит Воробьянинов ищут стул, в котором спрятаны драгоценности, в сатирическом путешествии по Советскому Союзу 1920-х годов.','/assets/img/двенадцать стульев.jpg'),(38,'Оно','Клоун Пеннивайз терроризирует детей небольшого городка, пробуждая их самые глубокие страхи. Группа друзей решает остановить его.','/assets/img/Оно.jpg'),(39,'Девушка с татуировкой дракона','Журналист Микаэль Блумквист и хакер Лисбет Саландер расследуют исчезновение девушки, раскрывая мрачные семейные тайны.','/assets/img/девушка с татуировкой дракона.jpg'),(40,'Благие знамения','Ангел Азирафель и демон Кроули объединяются, чтобы предотвратить Апокалипсис, потому что им слишком нравится жизнь на Земле.','/assets/img/благие знамения.jpg'),(41,'Кладбище домашних животных','Семья Луиса Крида переезжает в новый дом рядом с древним индейским кладбищем, где мёртвые возвращаются к жизни — но уже не такими, как прежде.','/assets/img/кладбище домашних животных.jpg'),(42,'Искры гениальности','Биография Джона Нэша, гениального математика, который боролся с шизофренией, но смог получить Нобелевскую премию.','/assets/img/обложка.jpg'),(43,'Путешествия с Чарли в поисках Америки ','Стейнбек и его пудель Чарли путешествуют по США, исследуя страну и её людей в 1960-х годах.','/assets/img/путешествие с чарли.jpg'),(44,'Краткая история времени','Популярное объяснение космологии, от Большого взрыва до чёрных дыр, написанное одним из величайших физиков современности.\r\n ','/assets/img/краткая история времени.jpg'),(45,'Ешь, молись, люби','После развода Лиз Гилберт отправляется в путешествие по Италии, Индии и Индонезии, чтобы найти себя через еду, духовность и любовь.','/assets/img/Ешь, молись, люби.jpg'),(46,'Как завоёвывать друзей и влиять на людей','Классическое руководство по общению, которое учит строить отношения, убеждать и добиваться успеха в личной и профессиональной жизни.','/assets/img/Как завоевывать друзей.jpg'),(47,'Сила привычки','Исследование того, как привычки формируют нашу жизнь, и как их можно изменить для достижения успеха.','/assets/img/Сила привычки.jpg'),(48,'Кулинарная книга NOMA','Сборник рецептов от шеф-повара знаменитого ресторана NOMA, с акцентом на скандинавскую кухню и локальные ингредиенты.','/assets/img/Noma.jpg'),(49,'История мира в 100 объектах','История человечества через 100 артефактов из Британского музея, от каменных орудий до современных технологий.','/assets/img/История мира в 100 объектах.jpg'),(50,'Приключения Тома Сойера','Весёлые и опасные приключения мальчика Тома Сойера и его друга Гекльберри Финна в американском городке XIX века.\r\n ','/assets/img/Приключения Тома Сойера.jpg'),(51,'Чарли и шоколадная фабрика','Чарли Бакет выигрывает билет на экскурсию по фабрике эксцентричного Вилли Вонки, где его ждут чудеса и испытания.','/assets/img/Чарли и шоколадная фабрика.jpg'),(52,'Пеппи Длинныйчулок','История о независимой и весёлой девочке Пеппи, которая живёт без родителей и устраивает невероятные приключения.','/assets/img/Пеппи Длинныйчулок.jpg'),(53,'Таинственный сад','Осиротевшая Мэри Леннокс находит заброшенный сад, который помогает ей и её друзьям исцелиться и обрести счастье.','/assets/img/Таинственный сад.jpg'),(54,'Алиса в Стране чудес','Алиса падает в кроличью нору и оказывается в абсурдном мире, полном странных существ и загадок.','/assets/img/Алиса в стране чудес.jpg'),(55,'Винни-Пух','Приключения медвежонка Винни-Пуха и его друзей в Стоакровом лесу, полные доброты и юмора.','/assets/img/Винни-Пух.jpg'),(56,'Хоббит','Бильбо Бэггинс, хоббит, отправляется в путешествие с гномами, чтобы вернуть их сокровища, охраняемые драконом Смаугом.','/assets/img/Хоббит.jpg'),(57,'Ромео и Джульетта','Трагическая история любви двух молодых людей из враждующих семей в Вероне, чья любовь заканчивается смертью.','/assets/img/ромео и джульетта.jpg'),(58,'Одиссея','Эпическое путешествие Одиссея домой после Троянской войны, полное встреч с богами, чудовищами и испытаниями.','/assets/img/Одиссея.jpg'),(59,'Гамлет','Принц Гамлет мстит за убийство своего отца, сталкиваясь с предательством, безумием и экзистенциальными вопросами.','/assets/img/Гамлет.jpg'),(60,'Божественная комедия','Поэтическое путешествие Данте через Ад, Чистилище и Рай, где он встречает души грешников, праведников и самого Бога.','/assets/img/Божественная комедия.jpg'),(61,'Код да Винчи','Роберт Лэнгдон расследует убийство в Лувре, раскрывая тайны, связанные с Леонардо да Винчи и Святым Граалем.','/assets/img/Код да Винчи.jpg'),(62,'Зеленая миля','Тюремный охранник Пол Эджкомб сталкивается с необычным заключённым Джоном Коффи, обладающим сверхъестественными способностями.\r\n ','/assets/img/Зеленая миля.jpg'),(63,'Унесенные ветром','История Скарлетт ОХары, которая переживает Гражданскую войну в США, теряя и обретая любовь на фоне разрушения Юга.','/assets/img/Унесенные ветром.jpg'),(64,'Дон Кихот','Пожилой идальго, начитавшись рыцарских романов, отправляется в путешествие, чтобы сражаться с ветряными мельницами и искать приключения.','/assets/img/Дон Кихот.jpg'),(65,'Франкенштейн','Виктор Франкенштейн создаёт живое существо из мёртвых тканей, но его творение становится монстром, жаждущим мести.','/assets/img/Франкенштейн.jpg'),(66,'Граф Монте-Кристо','Эдмон Дантес, несправедливо заключённый в тюрьму, сбегает и становится графом Монте-Кристо, чтобы отомстить своим врагам.\r\n ','/assets/img/Граф монте-кристо.jpg'),(67,'Мертвые души','Чичиков скупает \"мёртвые души\" — умерших крестьян, чтобы нажиться на мошенничестве, в сатирическом портрете России XIX века.','/assets/img/Мертвые души.jpg'),(68,'Дети капитана Гранта','Группа путешественников отправляется на поиски капитана Гранта, следуя за таинственным письмом, найденным в бутылке.','/assets/img/Дети капитана гранта.jpg'),(69,'Сказки братьев Гримм','Сборник классических сказок, включая \"Золушку\", \"Белоснежку\" и \"Гензель и Гретель\", полных магии и морали.','/assets/img/Сказки братьев гримм.jpg'),(70,'Герой нашего времени','История Печорина, молодого офицера, чья жизнь полна страстей, разочарований и дуэлей на Кавказе.','/assets/img/Герой нашего времени.jpg'),(71,'Путешествие к центру Земли','Профессор Лиденброк и его племянник Аксель спускаются в кратер вулкана, чтобы исследовать подземный мир, полный чудес и опасностей.','/assets/img/Путешествие к центру земле.jpg'),(72,'Вокруг света за 80 дней','Филеас Фогг заключает пари, что обогнёт Землю за 80 дней, и отправляется в невероятное путешествие, полное приключений.','/assets/img/Вокруг света за 80 дней.jpg'),(73,'Робинзон Крузо','Робинзон Крузо, потерпев кораблекрушение, проводит 28 лет на необитаемом острове, учась выживать и находить смысл в одиночестве.','/assets/img/Робинзон Крузо.jpg'),(74,'Таинственный остров','Группа беглецов из плена терпит крушение на необитаемом острове, где они сталкиваются с загадочными явлениями и пытаются выжить.','/assets/img/Таинственный остров.jpg'),(75,'Призрак Оперы','Таинственный Призрак, живущий под Парижской оперой, влюбляется в певицу Кристину и терроризирует театр ради её успеха.','/assets/img/Призрак оперы.jpg'),(76,'Гранатовый браслет','История безнадёжной любви мелкого чиновника Желткова к княгине Вере Шеиной, которая заканчивается трагически.','/assets/img/Гранатовый браслет.jpg'),(77,'Коралина','Девочка Коралина обнаруживает дверь в другой мир, где её \"другие\" родители кажутся идеальными — но скрывают мрачную тайну.','/assets/img/Коралина.jpg'),(78,'Звездная пыль','Тристан Торн отправляется в волшебную страну, чтобы поймать звезду для своей возлюбленной, но звезда оказывается живой девушкой.','/assets/img/Звездная пыль.jpg');
/*!40000 ALTER TABLE `Book` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `BookGenre`
--
DROP TABLE IF EXISTS `BookGenre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `BookGenre` (
  `id_book` int NOT NULL,
  `id_genre` int NOT NULL,
  PRIMARY KEY (`id_book`,`id_genre`),
  KEY `id_genre` (`id_genre`),
  CONSTRAINT `BookGenre_ibfk_1` FOREIGN KEY (`id_book`) REFERENCES `Book` (`id_book`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `BookGenre_ibfk_2` FOREIGN KEY (`id_genre`) REFERENCES `Genre` (`id_genre`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `BookGenre`
--
LOCK TABLES `BookGenre` WRITE;
/*!40000 ALTER TABLE `BookGenre` DISABLE KEYS */;
INSERT INTO `BookGenre` (`id_book`, `id_genre`) VALUES
(1, 1), (1, 2),
(2, 3), (2, 4),
(3, 5), (3, 6),
(4, 1), (4, 7),
(5, 5), (5, 8),
(6, 1), (6, 9),
(7, 10), (7, 11),
(8, 1), (8, 12),
(9, 1), (9, 13),
(10, 1), (10, 19),
(11, 14), (11, 15),
(12, 10), (12, 16),
(13, 17), (13, 18),
(14, 4), (14, 8),
(15, 3), (15, 19),
(16, 1), (16, 20),
(17, 4), (17, 21),
(18, 4), (18, 22),
(19, 1), (19, 23),
(20, 1), (20, 7),
(21, 3), (21, 4),
(22, 5), (22, 6),
(23, 1), (23, 24),
(24, 5), (24, 19),
(25, 25), (25, 26),
(26, 1), (26, 24),
(27, 3), (27, 4),
(28, 1), (28, 7), (28, 43),
(29, 28), (29, 29),
(30, 5), (30, 8),
(31, 23), (31, 27),
(32, 4), (32, 10),
(33, 5), (33, 6),
(34, 1), (34, 12),
(35, 1), (35, 26),
(36, 42),
(37, 10), (37, 21),
(38, 25), (38, 30),
(39, 20), (39, 30),
(40, 5), (40, 21),
(41, 25), (41, 30),
(42, 32), (42, 33),
(43, 28), (43, 35),
(44, 33), (44, 34),
(45, 28), (45, 35),
(46, 36), (46, 37),
(47, 33), (47, 37),
(48, 38), (48, 39),
(49, 29), (49, 33),
(50, 6), (50, 24),
(51, 5), (51, 6),
(52, 6), (52, 21),
(53, 1), (53, 6),
(54, 5), (54, 6),
(55, 6), (55, 40),
(56, 5), (56, 24),
(57, 1), (57, 12),
(58, 1), (58, 41),
(59, 1), (59, 12),
(60, 1), (60, 41),
(61, 20), (61, 30),
(62, 5), (62, 31),
(63, 7), (63, 14),
(64, 1), (64, 10),
(65, 25), (65, 26),
(66, 14), (66, 24),
(67, 1), (67, 10),
(68, 1), (68, 24),
(69, 6), (69, 40),
(70, 1), (70, 43),
(71, 4), (71, 24),
(72, 1), (72, 24),
(73, 1), (73, 24),
(74, 4), (74, 24),
(75, 7), (75, 26),
(76, 1), (76, 7),
(77, 6), (77, 25),
(78, 5), (78, 7);
/*!40000 ALTER TABLE `BookGenre` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `BookWriter`
--
DROP TABLE IF EXISTS `BookWriter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `BookWriter` (
  `id_book` int NOT NULL,
  `id_writer` int NOT NULL,
  PRIMARY KEY (`id_book`,`id_writer`),
  KEY `id_writer` (`id_writer`),
  CONSTRAINT `BookWriter_ibfk_1` FOREIGN KEY (`id_book`) REFERENCES `Book` (`id_book`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `BookWriter_ibfk_2` FOREIGN KEY (`id_writer`) REFERENCES `Writer` (`id_writer`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `BookWriter`
--
LOCK TABLES `BookWriter` WRITE;
/*!40000 ALTER TABLE `BookWriter` DISABLE KEYS */;
INSERT INTO `BookWriter` VALUES (1,1),(2,2),(3,3),(4,4),(5,5),(6,6),(7,2),(8,7),(9,8),(10,9),(11,10),(12,11),(13,12),(14,13),(15,14),(16,15),(17,16),(18,17),(19,18),(20,10),(21,19),(22,20),(23,21),(24,22),(25,23),(26,24),(27,26),(28,27),(29,28),(30,29),(31,30),(32,11),(33,31),(34,32),(35,33),(36,34),(37,35),(37,36),(38,37),(39,38),(40,39),(40,40),(41,37),(42,41),(43,32),(44,67),(45,64),(46,65),(47,66),(48,42),(49,43),(50,68),(51,31),(52,44),(53,45),(54,46),(55,47),(56,29),(57,49),(58,50),(59,49),(60,51),(61,52),(62,37),(63,53),(64,54),(65,55),(66,56),(67,57),(68,58),(69,59),(70,60),(71,58),(72,58),(73,61),(74,58),(75,62),(76,63),(77,39),(78,39);
/*!40000 ALTER TABLE `BookWriter` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `ChatMessage`
--
DROP TABLE IF EXISTS `ChatMessage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ChatMessage` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `id_sender` int NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message`),
  KEY `id_sender` (`id_sender`),
  CONSTRAINT `ChatMessage_ibfk_1` FOREIGN KEY (`id_sender`) REFERENCES `UserProfile` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `ChatMessage`
--
LOCK TABLES `ChatMessage` WRITE;
/*!40000 ALTER TABLE `ChatMessage` DISABLE KEYS */;
INSERT INTO `ChatMessage` VALUES (1,2,'Всем привет!','2025-04-20 06:12:38'),(2,3,'привет!','2025-04-20 06:18:33');
/*!40000 ALTER TABLE `ChatMessage` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `Genre`
--
DROP TABLE IF EXISTS `Genre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Genre` (
  `id_genre` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_genre`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `Genre`
--
LOCK TABLES `Genre` WRITE;
/*!40000 ALTER TABLE `Genre` DISABLE KEYS */;
INSERT INTO `Genre` VALUES (1,'Классическая литература','klassicheskaya-literatura'),(2,'Социальная драма','sotsialnaya-drama'),(3,'Антиутопия','antiutopiya'),(4,'Научная фантастика','nauchnaya-fantastika'),(5,'Фэнтези','fentezi'),(6,'Детская литература','detskaya-literatura'),(7,'Романтика','romantika'),(8,'Эпическая фантастика','epicheskaya-fantastika'),(9,'Семейная драма','semeynaya-drama'),(10,'Сатира','satira'),(11,'Политическая аллегория','politicheskaya-allegoriya'),(12,'Трагедия','tragediya'),(13,'Психологический триллер','psikhologicheskiy-triller'),(14,'Исторический роман','istoricheskiy-roman'),(15,'Эпическая литература','epicheskaya-literatura'),(16,'Мистическая литература','misticheskaya-literatura'),(17,'Магический реализм','magicheskiy-realizm'),(18,'Семейная сага','semeynaya-saga'),(19,'Молодёжная литература','molodyozhnaya-literatura'),(20,'Детектив','detektiv'),(21,'Юмор','yumor'),(22,'Психологическая драма','psikhologicheskaya-drama'),(23,'Притча','pritcha'),(24,'Приключения','priklyucheniya'),(25,'Ужасы','uzhasy'),(26,'Готическая литература','goticheskaya-literatura'),(27,'Философская проза','filosofskaya-proza'),(28,'Мемуары','memuary'),(29,'Историческая литература','istoricheskaya-literatura'),(30,'Триллер','triller'),(31,'Мистическая драма','misticheskaya-drama'),(32,'Биография','biografiya'),(33,'Научно-популярная литература','nauchno-populyarnaya-literatura'),(34,'Физика','fizika'),(35,'Путешествия','puteshestviya'),(36,'Саморазвитие','samorazvitie'),(37,'Психология','psikhologiya'),(38,'Кулинария','kulinariya'),(39,'Гастрономия','gastronomiya'),(40,'Сказка','skazka'),(41,'Эпическая поэзия','epicheskaya-poeziya'),(42,'Криминал','kriminal'),(43,'Современная проза','sovremennaya-proza'),(44,'Романтическая комедия','romanticheskaya-komediya'),(45,'Фантастический боевик','fantasticheskiy-boevik'),(46,'Исторический детектив','istoricheskiy-detektiv'),(47,'Киберпанк','kiberpank'),(48,'Постапокалиптика','postapokaliptika'),(49,'Паранормальная романтика','paranormalnaya-romantika'),(50,'Тёмное фэнтези','tyomnoe-fentezi'),(51,'Литературная фантастика','literaturnaya-fantastika'),(52,'Шпионский триллер','shpionskiy-triller'),(53,'Вестерн','vestern'),(54,'Космическая опера','kosmicheskaya-opera'),(55,'Социальная фантастика','sotsialnaya-fantastika'),(56,'Медицинский триллер','meditsinskiy-triller'),(57,'Юридический триллер','yuridicheskiy-triller'),(58,'Эротическая литература','eroticheskaya-literatura'),(59,'Поэзия','poeziya'),(60,'Графические романы и комиксы','graficheskie-romany-i-komiksy'),(61,'Бизнес-литература','biznes-literatura'),(62,'Фитнес и здоровье','fitnes-i-zdorove'),(63,'Духовная литература','dukhovnaya-literatura'),(64,'Литература для родителей','literatura-dlya-roditeley'),(65,'Экологическая литература','ekologicheskaya-literatura'),(66,'Литература о путешествиях во времени','literatura-o-puteshestviyakh-vo-vremeni'),(67,'Спортивная литература','sportivnaya-literatura'),(68,'Литература о моде','literatura-o-mode'),(69,'Литература о животных','literatura-o-zhivotnykh'),(70,'Культурология','kulturologiya');
/*!40000 ALTER TABLE `Genre` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `ReadBooks`
--
DROP TABLE IF EXISTS `ReadBooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ReadBooks` (
  `id_read_book` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_read_book`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `readbooks_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `UserProfile` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `ReadBooks`
--
LOCK TABLES `ReadBooks` WRITE;
/*!40000 ALTER TABLE `ReadBooks` DISABLE KEYS */;
INSERT INTO `ReadBooks` VALUES (1,2,'Война и мир','Толстой','2025-04-19 14:14:14'),(2,2,'Гранатовый браслет','Куприн','2025-04-19 14:15:21'),(3,3,'Война и мир','Толстой','2025-04-20 18:09:51'),(4,3,'Война','Толстой','2025-04-20 18:11:34');
/*!40000 ALTER TABLE `ReadBooks` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `Review`
--
DROP TABLE IF EXISTS `Review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Review` (
  `id_review` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_book` int NOT NULL,
  `rating` int NOT NULL,
  `review_text` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_review`),
  UNIQUE KEY `user_book_review_unique` (`id_user`,`id_book`),
  KEY `id_book` (`id_book`),
  CONSTRAINT `Review_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `UserProfile` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Review_ibfk_2` FOREIGN KEY (`id_book`) REFERENCES `Book` (`id_book`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `Review`
--
LOCK TABLES `Review` WRITE;
/*!40000 ALTER TABLE `Review` DISABLE KEYS */;
INSERT INTO `Review` VALUES (1,3,2,5,'Великолепная!','2025-04-20 12:26:07'),(2,3,82,5,'супер','2025-04-20 17:59:46');
/*!40000 ALTER TABLE `Review` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `Userachievement`
--
DROP TABLE IF EXISTS `Userachievement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Userachievement` (
  `id_user_achievement` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_achievement` int NOT NULL,
  PRIMARY KEY (`id_user_achievement`),
  UNIQUE KEY `user_achievement_unique` (`id_user`,`id_achievement`),
  KEY `id_achievement` (`id_achievement`),
  CONSTRAINT `UserAchievement_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `UserProfile` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `UserAchievement_ibfk_2` FOREIGN KEY (`id_achievement`) REFERENCES `Achievement` (`id_achievement`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `Userachievement`
--
LOCK TABLES `Userachievement` WRITE;
/*!40000 ALTER TABLE `Userachievement` DISABLE KEYS */;
/*!40000 ALTER TABLE `Userachievement` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `UserProfile`
--
DROP TABLE IF EXISTS `UserProfile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `UserProfile` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `profile_picture_url` varchar(512) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `about_me` text,
  `reading_goal` int DEFAULT '0',
  `read_books_count` int DEFAULT '0',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `UserProfile`
--
LOCK TABLES `UserProfile` WRITE;
/*!40000 ALTER TABLE `UserProfile` DISABLE KEYS */;
INSERT INTO `UserProfile` VALUES (1,'kolesova.aleksa05@mail.ru','kolesova.aleksa05@mail.ru','$2y$10$NcOHJcXxJ1ROstGK8BhhUO5LqO/nkV/vp56t98oU.63NyJa78wxo2','http://localhost/backend/uploads/avatars/avatar_2_680397f7673a76.83456029.png',NULL,'Нижний Новгород','Админ',NULL,0,0),(2,'Александра','kolesova05@mail.ru','$2y$10$zE7Bw357caxvKzUaYNYK6OQBFfwtbIQjQIPO0okELYPs50TN.iBeW','http://localhost/backend/uploads/avatars/avatar_2_680397f7673a76.83456029.png',19,'Нижний Новгород','Читатель','люблю романы',20,5),(3,'Соня','sova@mail.ru','$2y$10$FgpEOffz3e.7sp/CIbDiXOBsFR7jx..6BCbShr8QCh1KCwpnUwo6i',NULL,23,'Краснодар','Автор','люблю детективы',20,5);
/*!40000 ALTER TABLE `UserProfile` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `UserSubscription`
--
DROP TABLE IF EXISTS `UserSubscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `UserSubscription` (
  `id_subscription` int NOT NULL AUTO_INCREMENT,
  `id_follower_user` int NOT NULL,
  `id_following_user` int NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_subscription`),
  UNIQUE KEY `user_follow_unique` (`id_follower_user`,`id_following_user`),
  KEY `FK_Subscription_Following` (`id_following_user`),
  CONSTRAINT `FK_Subscription_Follower` FOREIGN KEY (`id_follower_user`) REFERENCES `UserProfile` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_Subscription_Following` FOREIGN KEY (`id_following_user`) REFERENCES `UserProfile` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `UserSubscription`
--
LOCK TABLES `UserSubscription` WRITE;
/*!40000 ALTER TABLE `UserSubscription` DISABLE KEYS */;
INSERT INTO `UserSubscription` VALUES (1,2,3,'2025-04-20 05:46:04');
/*!40000 ALTER TABLE `UserSubscription` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `WishlistItem`
--
DROP TABLE IF EXISTS `WishlistItem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `WishlistItem` (
  `id_wishlist_item` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_book` int NOT NULL,
  PRIMARY KEY (`id_wishlist_item`),
  UNIQUE KEY `user_book_wishlist_unique` (`id_user`,`id_book`),
  KEY `id_book` (`id_book`),
  CONSTRAINT `WishlistItem_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `UserProfile` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `WishlistItem_ibfk_2` FOREIGN KEY (`id_book`) REFERENCES `Book` (`id_book`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `WishlistItem`
--
LOCK TABLES `WishlistItem` WRITE;
/*!40000 ALTER TABLE `WishlistItem` DISABLE KEYS */;
INSERT INTO `WishlistItem` VALUES (12,2,37),(8,2,50),(9,2,63);
/*!40000 ALTER TABLE `WishlistItem` ENABLE KEYS */;
UNLOCK TABLES;
--
-- Table structure for table `Writer`
--
DROP TABLE IF EXISTS `Writer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Writer` (
  `id_writer` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `profile_picture_url` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id_writer`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
--
-- Dumping data for table `Writer`
--
LOCK TABLES `Writer` WRITE;
/*!40000 ALTER TABLE `Writer` DISABLE KEYS */;
INSERT INTO `Writer` VALUES (1,'Харпер Ли','/assets/img/Харпер Ли.jpg'),(2,'Джордж Оруэлл','/assets/img/Джордж Оруэлл.jpg'),(3,'Джоан Роулинг','/assets/img/Джоан Роулинг.jpg'),(4,'Джейн Остин','/assets/img/Джейн Остин.jpg'),(5,'Джордж Р. Р. Мартин','/assets/img/Джордж Р. Р. Мартин.jpg'),(6,'Луиза Мэй Олкотт','/assets/img/Луиза Мэй Олкотт.jpg'),(7,'Фрэнсис Скотт Фицджеральд','/assets/img/Фрэнсис Скотт Фицджеральд.jpg'),(8,'Фёдор Достоевский','/assets/img/Фёдор Достоевский.jpg'),(9,'Джером Д. Сэлинджер','/assets/img/Джером Д. Сэлинджер.jpg'),(10,'Лев Толстой','/assets/img/Лев Толстой.jpg'),(11,'Михаил Булгаков','/assets/img/Михаил Булгаков.jpg'),(12,'Габриэль Гарсиа Маркес','/assets/img/Габриэль Гарсиа Маркес.jpg'),(13,'Фрэнк Герберт','/assets/img/Фрэнк Герберт.jpg'),(14,'Сьюзен Коллинз','/assets/img/Сьюзен Коллинз.jpg'),(15,'Артур Конан Дойл','/assets/img/Артур Конан Дойл.jpg'),(16,'Дуглас Адамс','/assets/img/Дуглас Адамс.jpg'),(17,'Дэниел Киз','/assets/img/Дэниел Киз.jpg'),(18,'Эрнест Хемингуэй','/assets/img/Эрнест Хемингуэй.jpg'),(19,'Олдос Хаксли','/assets/img/Олдос Хаксли.jpg'),(20,'Клайв Стейплз Льюис','/assets/img/Клайв Стейплз Льюис.jpg'),(21,'Герман Мелвилл','/assets/img/Герман Мелвилл.jpg'),(22,'Филип Пулман','/assets/img/Филип Пулман.jpg'),(23,'Брэм Стокер','/assets/img/Брэм Стокер.jpg'),(24,'Джек Лондон','/assets/img/Джек Лондон.jpg'),(25,'Михаэль Энде','/assets/img/Михаэль Энде.jpg'),(26,'Рэй Брэдбери','/assets/img/Рэй Брэдбери.jpg'),(27,'Эрих Мария Ремарк','/assets/img/Эрих Мария Ремарк.jpg'),(28,'Анна Франк','/assets/img/Анна Франк.jpg'),(29,'Дж. Р. Р. Толкин','/assets/img/Дж. Р. Р. Толкин.jpg'),(30,'Пауло Коэльо','/assets/img/Пауло Коэльо.jpg'),(31,'Роальд Даль','/assets/img/Роальд Даль.jpg'),(32,'Джон Стейнбек','/assets/img/Джон Стейнбек.jpg'),(33,'Оскар Уайльд','/assets/img/Оскар Уайльд.jpg'),(34,'Марио Пьюзо','/assets/img/Марио Пьюзо.jpg'),(35,'Илья Ильф','/assets/img/Илья Ильф.jpg'),(36,'Евгений Петров','/assets/img/Евгений Петров.jpg'),(37,'Стивен Кинг','/assets/img/Стивен Кинг.jpg'),(38,'Стиг Ларссон','/assets/img/Стиг Ларссон.jpg'),(39,'Нил Гейман','/assets/img/Нил Гейман.jpg'),(40,'Терри Пратчетт','/assets/img/Терри Пратчетт.jpg'),(41,'Сильвия Назар','/assets/img/Сильвия Назар.jpg'),(42,'Рене Редзепи','/assets/img/Рене Редзепи.jpg'),(43,'Нил Макгрегор','/assets/img/Нил Макгрегор.jpg'),(44,'Астрид Линдгрен','/assets/img/Астрид Линдгрен.jpg'),(45,'Фрэнсис Ходжсон Бернетт','/assets/img/Фрэнсис Ходжсон Бернетт.jpg'),(46,'Льюис Кэрролл','/assets/img/Льюис Кэрролл.jpg'),(47,'Алан Александр Милн','/assets/img/Алан Александр Милн.jpg'),(48,'Антуан де Сент-Экзюпери','/assets/img/Антуан де Сент-Экзюпери.jpg'),(49,'Уильям Шекспир','/assets/img/Уильям Шекспир.jpg'),(50,'Гомер','/assets/img/Гомер.jpg'),(51,'Данте Алигьери','/assets/img/Данте Алигьери.jpg'),(52,'Дэн Браун','/assets/img/Дэн Браун.jpg'),(53,'Маргарет Митчелл','/assets/img/Маргарет Митчелл.jpg'),(54,'Мигель де Сервантес','/assets/img/Мигель де Сервантес.jpg'),(55,'Мэри Шелли','/assets/img/Мэри Шелли.jpg'),(56,'Александр Дюма','/assets/img/Александр Дюма.jpg'),(57,'Николай Гоголь','/assets/img/Николай Гоголь.jpg'),(58,'Жюль Верн','/assets/img/Жюль Верн.jpg'),(59,'Братья Гримм','/assets/img/Братья Гримм.jpg'),(60,'Михаил Лермонтов','/assets/img/Михаил Лермонтов.jpg'),(61,'Даниэль Дефо','/assets/img/Даниэль Дефо.jpg'),(62,'Гастон Леру','/assets/img/Гастон Леру.jpg'),(63,'Александр Куприн','/assets/img/Александр Куприн.jpg'),(64,'Элизабет Гилберт','/assets/img/Элизабет Гилберт.jpg'),(65,'Дейл Карнеги','/assets/img/Дейл Карнеги.jpg'),(66,'Чарльз Дахигг','/assets/img/Чарльз Дахигг.jpg'),(67,'Стивен Хокинг','/assets/img/Стивен Хокинг.jpg'),(68,'Марк Твен','/assets/img/Марк Твен.jpg');
/*!40000 ALTER TABLE `Writer` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- Dump completed on 2025-04-20 21:49:09
