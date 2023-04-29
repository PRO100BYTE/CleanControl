// Импортируем необходимые библиотеки и пакеты
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:geolocator/geolocator.dart';
import 'package:intl/intl.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_database/firebase_database.dart';
import 'package:firebase_storage/firebase_storage.dart';

// Основная функция, которая запускает приложение
void main() async {
  // Инициализируем Firebase
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  // Запускаем виджет MyApp
  runApp(MyApp());
}

// Класс MyApp наследует StatelessWidget - базовый класс для виджетов без состояния
class MyApp extends StatelessWidget {
  // Переопределяем метод build для построения интерфейса
  @override
  Widget build(BuildContext context) {
    // Возвращаем виджет MaterialApp - основной виджет для создания приложений на Flutter
    return MaterialApp(
      // Задаем заголовок приложения
      title: 'Приложение для контроля работы службы клининга',
      // Задаем тему приложения
      theme: ThemeData(
        // Задаем основной цвет темы
        primarySwatch: Colors.blue,
      ),
      // Задаем домашний экран приложения
      home: HomeScreen(),
    );
  }
}

// Класс HomeScreen наследует StatefulWidget - базовый класс для виджетов с состоянием
class HomeScreen extends StatefulWidget {
  // Переопределяем метод createState для создания состояния виджета
  @override
  _HomeScreenState createState() => _HomeScreenState();
}

// Класс _HomeScreenState наследует State - класс для управления состоянием виджета
class _HomeScreenState extends State<HomeScreen> {
  // Создаем состояния для хранения данных
  List<Report> reports = []; // список отчетов
  String photo; // путь к фото текущего отчета
  String address; // адрес текущего отчета
  String startTime; // время начала уборки
  String endTime; // время конца уборки

  // Создаем ссылку на базу данных Firebase
  final databaseRef = FirebaseDatabase.instance.reference();

  // Подписываемся на изменения в базе данных Firebase при монтировании виджета
  @override
  void initState() {
    super.initState();
    databaseRef.child('reports').onChildAdded.listen((event) {
      setState(() {
        reports.add(Report.fromSnapshot(event.snapshot));
      });
    });
    databaseRef.child('reports').onChildChanged.listen((event) {
      var old = reports.singleWhere((report) => report.id == event.snapshot.key);
      setState(() {
        reports[reports.indexOf(old)] = Report.fromSnapshot(event.snapshot);
      });
    });
    databaseRef.child('reports').onChildRemoved.listen((event) {
      var old = reports.singleWhere((report) => report.id == event.snapshot.key);
      setState(() {
        reports.removeAt(reports.indexOf(old));
      });
    });
  }

  // Функция для выбора или съемки фото с помощью ImagePicker
  Future<void> choosePhoto() async {
    final picker = ImagePicker();
    final pickedFile = await picker.getImage(source: ImageSource.camera);
    if (pickedFile != null) {
      setState(() {
        photo = pickedFile.path;
      });
    }
  }

  // Функция для определения местоположения с помощью Geolocator
  Future<void> getLocation() async {
      Position position = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high);
      List<Placemark> placemarks = await placemarkFromCoordinates(
          position.latitude, position.longitude);
      if (placemarks.isNotEmpty) {
        setState(() {
          address = placemarks[0].street;
        });
      }
    }

    // Функция для записи времени начала уборки
    void startCleaning() {
      setState(() {
        startTime = DateFormat('HH:mm').format(DateTime.now());
      });
    }

    // Функция для записи времени конца уборки и отправки отчета в базу данных Firebase
    Future<void> endCleaning() async {
      setState(() {
        endTime = DateFormat('HH:mm').format(DateTime.now());
      });
      String date = DateFormat('dd.MM.yyyy').format(DateTime.now());
      if (photo != null && address != null && startTime != null && endTime != null) {
        String photoUrl = await uploadPhoto(photo, date, address);
        if (photoUrl != null) {
          Report report = Report(
              photo: photoUrl,
              address: address,
              startTime: startTime,
              endTime: endTime,
              date: date);
          databaseRef.child('reports').push().set(report.toJson());
          setState(() {
            photo = null;
            address = null;
            startTime = null;
            endTime = null;
          });
        }
      }
    }

    // Функция для загрузки фото в хранилище Firebase и получения ссылки на него
    Future<String> uploadPhoto(String filePath, String date, String address) async {
      File file = File(filePath);
      try {
        await FirebaseStorage.instance
            .ref('photos/$date/$address')
            .putFile(file);
        return await FirebaseStorage.instance
            .ref('photos/$date/$address')
            .getDownloadURL();
      } on FirebaseException catch (e) {
        print(e.message);
        return null;
      }
    }

    // Переопределяем метод build для построения интерфейса
    @override
    Widget build(BuildContext context) {
      // Возвращаем виджет Scaffold - основной виджет для построения экранов на Flutter
      return Scaffold(
        // Задаем аппбар экрана
        appBar: AppBar(
          // Задаем заголовок аппбара
          title: Text('Приложение для контроля работы службы клининга'),
        ),
        // Задаем тело экрана
        body: Column(
          // Располагаем виджеты по вертикали с равномерным пространством между ними
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          // Задаем список виджетов в колонке
          children: [
            // Виджет Text для отображения текста
            Text(
              'Фотоотчет: ${photo != null ? 'есть' : 'нет'}',
              style: TextStyle(fontSize: 18),
            ),
            Text(
              'Адрес: ${address ?? 'не определен'}',
              style: TextStyle(fontSize: 18),
            ),
            Text(
              'Время начала уборки: ${startTime ?? 'не записано'}',
              style: TextStyle(fontSize: 18),
            ),
            Text(
              'Время конца уборки: ${endTime ?? 'не записано'}',
              style: TextStyle(fontSize: 18),
            ),
            // Виджет Row для расположения виджетов по горизонтали
            Row(
              // Располагаем виджеты по горизонтали с равномерным пространством между ними
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              // Задаем список виджетов в строке
              children: [
                // Виджет ElevatedButton для отображения кнопки с эффектом при нажатии
                ElevatedButton(
                // Задаем текст кнопки
                 child: Text('Выбрать фото'),
                    // Задаем обработчик нажатия кнопки
                  onPressed: choosePhoto,
                ),
                ElevatedButton(
                  child: Text('Определить местоположение'),
                  onPressed: getLocation,
                ),
                ElevatedButton(
                  child: Text('Начать уборку'),
                  onPressed: startCleaning,
                ),
                ElevatedButton(
                  child: Text('Закончить уборку'),
                  onPressed: endCleaning,
                ),
              ],
            ),
            // Виджет Expanded для заполнения доступного пространства
            Expanded(
              // Виджет ListView для отображения списка прокручиваемых элементов
              child: ListView.builder(
                // Задаем количество элементов в списке
                itemCount: reports.length,
                // Задаем функцию для построения элементов списка
                itemBuilder: (context, index) {
                  // Возвращаем виджет ReportItem для отображения отчета
                  return ReportItem(report: reports[index]);
                  },
              ),
            ),
          ],
        ),
      );
  }
}

// Класс Report для представления отчета о работе клинера
class Report {
  // Создаем поля для хранения данных об отчете
  String id; // идентификатор отчета
  String photo; // ссылка на фото отчета
  String address; // адрес уборки
  String startTime; // время начала уборки
  String endTime; // время конца уборки
  String date; // дата уборки

  // Создаем конструктор с именованными параметрами
  Report(
      {this.id,
        this.photo,
        this.address,
        this.startTime,
        this.endTime,
        this.date});

  // Создаем статический метод для создания отчета из снимка базы данных Firebase
  static Report fromSnapshot(DataSnapshot snapshot) {
    return Report(
      id: snapshot.key,
      photo: snapshot.value['photo'],
      address: snapshot.value['address'],
      startTime: snapshot.value['startTime'],
      endTime: snapshot.value['endTime'],
      date: snapshot.value['date'],
    );
  }

  // Создаем метод для преобразования отчета в JSON-формат для записи в базу данных Firebase
  Map<String, dynamic> toJson() {
    return {
      'photo': photo,
      'address': address,
      'startTime': startTime,
      'endTime': endTime,
      'date': date,
    };
  }
}

// Класс ReportItem наследует StatelessWidget - базовый класс для виджетов без состояния
class ReportItem extends StatelessWidget {
  // Создаем поле для хранения данных об отчете
  final Report report;

  // Создаем конструктор с именованным параметром
  ReportItem({this.report});

  // Переопределяем метод build для построения интерфейса
  @override
  Widget build(BuildContext context) {
    // Возвращаем виджет Card - виджет для отображения карточки с тенью и скругленными углами
    return Card(
      // Задаем дочерний виджет Card - виджет ListTile - виджет для отображения элемента списка с иконкой, заголовком и подзаголовком
        child: ListTile(
        // Задаем иконку ListTile - виджет Image.network - виджет для отображения изображения по ссылке из интернета
        leading: Image.network(report.photo),
    // Задаем заголовок ListTile - виджет Text - виджет для отображения текста
    title: Text(report.address),
    // Задаем подзаголовок ListTile - виджет Text - виджет для отображения текста
    subtitle:
    Text('Время уборки: ${report.startTime} - ${report.endTime}\n
        Text('Дата уборки: ${report.date}'),
          // Задаем обработчик нажатия на ListTile
          onTap: () {
            // Переходим к экрану ReportScreen с передачей данных об отчете
            Navigator.push(
                context,
                MaterialPageRoute(
                    builder: (context) => ReportScreen(report: report)));
          },
        ),
    );
  }
}

// Класс ReportScreen наследует StatelessWidget - базовый класс для виджетов без состояния
class ReportScreen extends StatelessWidget {
  // Создаем поле для хранения данных об отчете
  final Report report;

  // Создаем конструктор с именованным параметром
  ReportScreen({this.report});

  // Переопределяем метод build для построения интерфейса
  @override
  Widget build(BuildContext context) {
    // Возвращаем виджет Scaffold - основной виджет для построения экранов на Flutter
    return Scaffold(
      // Задаем аппбар экрана
      appBar: AppBar(
        // Задаем заголовок аппбара
        title: Text('Отчет о работе клинера'),
      ),
      // Задаем тело экрана
      body: Center(
        // Виджет Column для расположения виджетов по вертикали
        child: Column(
          // Располагаем виджеты по вертикали с равномерным пространством между ними
          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
          // Задаем список виджетов в колонке
          children: [
            // Виджет Image.network - виджет для отображения изображения по ссылке из интернета
            Image.network(report.photo),
            // Виджет Text - виджет для отображения текста
            Text(
              'Адрес: ${report.address}',
              style: TextStyle(fontSize: 18),
            ),
            Text(
              'Время уборки: ${report.startTime} - ${report.endTime}',
              style: TextStyle(fontSize: 18),
            ),
            Text(
              'Дата уборки: ${report.date}',
              style: TextStyle(fontSize: 18),
            ),
          ],
        ),
      ),
    );
  }
}