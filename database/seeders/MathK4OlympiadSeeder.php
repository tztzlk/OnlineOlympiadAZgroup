<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizCategory;
use Illuminate\Database\Seeder;

class MathK4OlympiadSeeder extends Seeder
{
    private const SUBJECT_ID = 13; // матем

    public function run(): void
    {
        $this->createOlympiad(
            'Математика К4 (9-10) — Русский',
            'К4',
            9,
            10,
            $this->russianQuestions()
        );

        $this->createOlympiad(
            'Математика К4 (9-10) — Казахский',
            'К4',
            9,
            10,
            $this->kazakhQuestions()
        );
    }

    private function createOlympiad(string $title, string $catLabel, int $gradeFrom, int $gradeTo, array $questions): void
    {
        $quiz = Quiz::create([
            'subject_id'   => self::SUBJECT_ID,
            'title'        => $title,
            'description'  => '',
            'price'        => 0,
            'time_limit'   => 60,
            'is_published' => false,
        ]);

        $cat = QuizCategory::create([
            'quiz_id'    => $quiz->id,
            'label'      => $catLabel,
            'grade_from' => $gradeFrom,
            'grade_to'   => $gradeTo,
            'sort_order' => 1,
        ]);

        foreach ($questions as $pos => $q) {
            $question = Question::create([
                'quiz_id'          => $quiz->id,
                'quiz_category_id' => $cat->id,
                'question'         => $q['question'],
                'explanation'      => $q['explanation'] ?? '',
                'image_source'     => '',
                'image_url'        => '',
                'image_path'       => '',
                'position'         => $pos + 1,
            ]);

            foreach ($q['answers'] as $aPos => $a) {
                Answer::create([
                    'question_id' => $question->id,
                    'label'       => $a['label'],
                    'answer'      => $a['answer'],
                    'position'    => $aPos + 1,
                    'is_correct'  => $a['label'] === $q['correct'],
                ]);
            }
        }
    }

    private function russianQuestions(): array
    {
        return [
            [
                'question' => 'a, b, c — положительные действительные числа и a·b·c ≠ 1, при этом log(abc) a + log(abc) b = 2. Выразите log(abc) c.',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '1'],
                    ['label' => 'B', 'answer' => '2'],
                    ['label' => 'C', 'answer' => '3'],
                    ['label' => 'D', 'answer' => '-1'],
                    ['label' => 'E', 'answer' => '-2'],
                ],
            ],
            [
                'question' => 'Сторона квадрата равна 1. Найди площадь окрашенной детали (квадрат с вписанными дугами окружностей из четырёх углов).',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => 'π/2 − 1'],
                    ['label' => 'B', 'answer' => 'π/2 − 2'],
                    ['label' => 'C', 'answer' => 'π/2 − 3'],
                    ['label' => 'D', 'answer' => 'π/2 − 4'],
                    ['label' => 'E', 'answer' => 'π/2 − 5'],
                ],
            ],
            [
                'question' => '(p − 2)x^(p²−3p+4) + x + q = 0 является квадратным уравнением с одной неизвестной. Если один из корней равен «1», то каков второй корень?',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '-1'],
                    ['label' => 'B', 'answer' => '-1/2'],
                    ['label' => 'C', 'answer' => '0'],
                    ['label' => 'D', 'answer' => '1/2'],
                    ['label' => 'E', 'answer' => '2'],
                ],
            ],
            [
                'question' => 'Для многочлена P(x) с положительным старшим коэффициентом, если P(x−1)·P(x+1) = 9x²−6x−8, то каково сумма коэффициентов многочлена P(x+5)?',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '15'],
                    ['label' => 'B', 'answer' => '16'],
                    ['label' => 'C', 'answer' => '17'],
                    ['label' => 'D', 'answer' => '18'],
                    ['label' => 'E', 'answer' => '19'],
                ],
            ],
            [
                'question' => 'Сколько существует натуральных чисел, меньших 1000, которые не делятся ни на 5, ни на 7?',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => '686'],
                    ['label' => 'B', 'answer' => '687'],
                    ['label' => 'C', 'answer' => '689'],
                    ['label' => 'D', 'answer' => '690'],
                    ['label' => 'E', 'answer' => '691'],
                ],
            ],
            [
                'question' => 'Треугольник ABC вписан в окружность. Сторона AB=5, BC=4, угол между ними 60°. Найдите значение отношения третьей стороны к радиусу данной окружности.',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => '√3'],
                    ['label' => 'B', 'answer' => '√6'],
                    ['label' => 'C', 'answer' => '√2'],
                    ['label' => 'D', 'answer' => '√5'],
                    ['label' => 'E', 'answer' => '√7'],
                ],
            ],
            [
                'question' => 'a, b, c — положительные целые числа, a·b·c = 360. Сколько существует упорядоченных троек (a, b, c), которые удовлетворяют этому равенству?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '30'],
                    ['label' => 'B', 'answer' => '60'],
                    ['label' => 'C', 'answer' => '90'],
                    ['label' => 'D', 'answer' => '180'],
                    ['label' => 'E', 'answer' => '189'],
                ],
            ],
            [
                'question' => 'sin(2x)·sin(x) = cos(2x). Сколько различных корней имеет уравнение на интервале [0, 2π]?',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '3'],
                    ['label' => 'B', 'answer' => '4'],
                    ['label' => 'C', 'answer' => '5'],
                    ['label' => 'D', 'answer' => '6'],
                    ['label' => 'E', 'answer' => '7'],
                ],
            ],
            [
                'question' => 'Пусть последовательность {aₙ} задаётся формулой aₙ = [(n²+8n+10)/(n+9)], где [x] — целая часть x. Найдите значение суммы ∑aₙ при n от 1 до 30.',
                'correct'  => 'E',
                'answers'  => [
                    ['label' => 'A', 'answer' => '443'],
                    ['label' => 'B', 'answer' => '444'],
                    ['label' => 'C', 'answer' => '446'],
                    ['label' => 'D', 'answer' => '447'],
                    ['label' => 'E', 'answer' => '445'],
                ],
            ],
            [
                'question' => 'Пусть x > 0, y > 0, xy = 8 и P = 2(log₂ x)² + (log₂ y)². Положим X = log₂ x. Выразите P через X и найдите минимальное значение P.',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '4'],
                    ['label' => 'B', 'answer' => '5'],
                    ['label' => 'C', 'answer' => '6'],
                    ['label' => 'D', 'answer' => '7'],
                    ['label' => 'E', 'answer' => '8'],
                ],
            ],
            [
                'question' => 'Для многочлена P(x) выполняется равенство (x+1)P(x) = (x+1)³ + m(x+1)² + 5x + 5. Многочлен принимает положительные значения для всех действительных x. Какой из следующих интервалов является наибольшим для значения m?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '(−√5; √5)'],
                    ['label' => 'B', 'answer' => '(−2√15; 2√15)'],
                    ['label' => 'C', 'answer' => '(−√10; 2√10)'],
                    ['label' => 'D', 'answer' => '(−2√5; 2√5)'],
                    ['label' => 'E', 'answer' => '(−√10; √10)'],
                ],
            ],
            [
                'question' => 'В магазине есть 8 футболок: по одной каждого размера S, M, L, XL двух цветов — красного и синего. Случайно выбирают 2 футболки без возвращения, при этом хотя бы одна из выбранных должна быть размера XL. Какова вероятность того, что среди выбранных будет хотя бы одна красная футболка?',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => '10/13'],
                    ['label' => 'B', 'answer' => '7/16'],
                    ['label' => 'C', 'answer' => '4/13'],
                    ['label' => 'D', 'answer' => '12/13'],
                    ['label' => 'E', 'answer' => '11/16'],
                ],
            ],
            [
                'question' => 'В первой четверти координатной плоскости проведена окружность, касающаяся обеих координатных осей. Касательная к этой окружности, проведённая из точки (−7, 0), пересекает ось y в точке (0, 24). Найдите радиус этой окружности.',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '18'],
                    ['label' => 'B', 'answer' => '20'],
                    ['label' => 'C', 'answer' => '21'],
                    ['label' => 'D', 'answer' => '36'],
                    ['label' => 'E', 'answer' => '30'],
                ],
            ],
            [
                'question' => 'В противоположных (наиболее удалённых друг от друга) вершинах единичного куба сидят муха и паук. Паук может ползать только по внешним граням куба. Найти минимальную длину пути паука по поверхности куба.',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '√3'],
                    ['label' => 'B', 'answer' => '√6'],
                    ['label' => 'C', 'answer' => '√2'],
                    ['label' => 'D', 'answer' => '√5'],
                    ['label' => 'E', 'answer' => '√7'],
                ],
            ],
            [
                'question' => 'На сторонах AB и AC треугольника ABC, в котором AB = BC, взяты точки M и N соответственно так, что описанная около треугольника AMN окружность касается стороны BC в точке P. Пусть Q — вторая точка пересечения прямой MP с описанной около треугольника CNP окружностью. Найдите отношение AP/QM.',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '3'],
                    ['label' => 'B', 'answer' => '1'],
                    ['label' => 'C', 'answer' => '2'],
                    ['label' => 'D', 'answer' => '1,5'],
                    ['label' => 'E', 'answer' => '2,5'],
                ],
            ],
            [
                'question' => 'Функция f(x), определённая на множестве действительных чисел, задана как f(x) = 2^x / (2^x + 2^(1−x)). Чему равна сумма f(1/2023) + f(2/2023) + f(3/2023) + … + f(2022/2023)?',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '2022'],
                    ['label' => 'B', 'answer' => '1011'],
                    ['label' => 'C', 'answer' => '2023'],
                    ['label' => 'D', 'answer' => '1'],
                    ['label' => 'E', 'answer' => '0'],
                ],
            ],
            [
                'question' => 'Вне квадрата ABCD взяли такую точку P, что AP = AB и ∠ADP = 10°. Какие возможные значения может иметь величина угла ∠APB?',
                'correct'  => 'E',
                'answers'  => [
                    ['label' => 'A', 'answer' => '15'],
                    ['label' => 'B', 'answer' => '110'],
                    ['label' => 'C', 'answer' => '70'],
                    ['label' => 'D', 'answer' => '35'],
                    ['label' => 'E', 'answer' => '55'],
                ],
            ],
            [
                'question' => '9sin²x + 12sin(x)·cos(x) + 4cos²x. Каково наибольшее значение выражения?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '10'],
                    ['label' => 'B', 'answer' => '11'],
                    ['label' => 'C', 'answer' => '12'],
                    ['label' => 'D', 'answer' => '13'],
                    ['label' => 'E', 'answer' => '14'],
                ],
            ],
            [
                'question' => 'Из горячего крана ванна заполняется за 23 минуты, из холодного — за 17 минут. Маша открыла сначала горячий кран. Через сколько минут она должна открыть холодный, чтобы к моменту наполнения ванны горячей воды налилось в 1,5 раза больше, чем холодной?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '4'],
                    ['label' => 'B', 'answer' => '5'],
                    ['label' => 'C', 'answer' => '6'],
                    ['label' => 'D', 'answer' => '7'],
                    ['label' => 'E', 'answer' => '8'],
                ],
            ],
            [
                'question' => 'У Максата есть 2023 спички. Он выкладывает из них в два ряда шестиугольники, примыкающие друг к другу. Сколько шестиугольников у него получится?',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '504'],
                    ['label' => 'B', 'answer' => '505'],
                    ['label' => 'C', 'answer' => '506'],
                    ['label' => 'D', 'answer' => '503'],
                    ['label' => 'E', 'answer' => '501'],
                ],
            ],
        ];
    }

    private function kazakhQuestions(): array
    {
        return [
            [
                'question' => 'a, b, c — оң нақты сандар және a·b·c ≠ 1 болған жағдайда log(abc) a + log(abc) b = 2. log(abc) c өрнектеңдер?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '1'],
                    ['label' => 'B', 'answer' => '2'],
                    ['label' => 'C', 'answer' => '3'],
                    ['label' => 'D', 'answer' => '-1'],
                    ['label' => 'E', 'answer' => '-2'],
                ],
            ],
            [
                'question' => 'Квадраттың қабырғасы 1-ге тең. Боялған бөліктің ауданын тап (шаршыға төрт бұрышынан сызылған доға-доғалармен қиылысқан фигура).',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => 'π/2 − 1'],
                    ['label' => 'B', 'answer' => 'π/2 − 2'],
                    ['label' => 'C', 'answer' => 'π/2 − 3'],
                    ['label' => 'D', 'answer' => 'π/2 − 4'],
                    ['label' => 'E', 'answer' => 'π/2 − 5'],
                ],
            ],
            [
                'question' => 'Бір белгісізі бар квадрат теңдеу берілген: (p − 2)x^(p²−3p+4) + x + q = 0. Егер бұл теңдеудің бір түбірі «1»-ге тең болса, онда екінші түбірі қандай болады?',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '-1'],
                    ['label' => 'B', 'answer' => '-1/2'],
                    ['label' => 'C', 'answer' => '0'],
                    ['label' => 'D', 'answer' => '1/2'],
                    ['label' => 'E', 'answer' => '2'],
                ],
            ],
            [
                'question' => 'Бас коэффициенті оң болатын P(x) көпмүшесі үшін P(x−1)·P(x+1) = 9x²−6x−8 теңдеуі берілген. P(x+5) көпмүшесінің коэффициенттерінің қосындысын табыңыз.',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '15'],
                    ['label' => 'B', 'answer' => '16'],
                    ['label' => 'C', 'answer' => '17'],
                    ['label' => 'D', 'answer' => '18'],
                    ['label' => 'E', 'answer' => '19'],
                ],
            ],
            [
                'question' => '1000-нан кіші, 5-ке де, 7-ге де бөлінбейтін қанша натурал сан бар?',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => '686'],
                    ['label' => 'B', 'answer' => '687'],
                    ['label' => 'C', 'answer' => '689'],
                    ['label' => 'D', 'answer' => '690'],
                    ['label' => 'E', 'answer' => '691'],
                ],
            ],
            [
                'question' => 'ABC үшбұрышы шеңбердің ішіне сызылған. AB = 5, BC = 4, ал олардың арасындағы бұрыш 60°. Үшінші қабырғаның ұзындығы мен осы шеңбердің радиусының қатынасын табыңдар.',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => '√3'],
                    ['label' => 'B', 'answer' => '√6'],
                    ['label' => 'C', 'answer' => '√2'],
                    ['label' => 'D', 'answer' => '√5'],
                    ['label' => 'E', 'answer' => '√7'],
                ],
            ],
            [
                'question' => 'a, b, c натурал сандары үшін a·b·c = 360. Бұл теңдеуді қанағаттандыратын реттелген үштіктер (a, b, c) саны қанша?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '30'],
                    ['label' => 'B', 'answer' => '60'],
                    ['label' => 'C', 'answer' => '90'],
                    ['label' => 'D', 'answer' => '180'],
                    ['label' => 'E', 'answer' => '189'],
                ],
            ],
            [
                'question' => 'sin(2x)·sin(x) = cos(2x). [0, 2π] аралығында теңдеудің неше түрлі түбірі бар?',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '3'],
                    ['label' => 'B', 'answer' => '4'],
                    ['label' => 'C', 'answer' => '5'],
                    ['label' => 'D', 'answer' => '6'],
                    ['label' => 'E', 'answer' => '7'],
                ],
            ],
            [
                'question' => '{aₙ} тізбегі aₙ = [(n²+8n+10)/(n+9)] формуласы арқылы берілген, мұндағы [x] — x-тің бүтін бөлігі. ∑aₙ (n=1-ден 30-ға дейін) мәнін табыңдар.',
                'correct'  => 'E',
                'answers'  => [
                    ['label' => 'A', 'answer' => '443'],
                    ['label' => 'B', 'answer' => '444'],
                    ['label' => 'C', 'answer' => '446'],
                    ['label' => 'D', 'answer' => '447'],
                    ['label' => 'E', 'answer' => '445'],
                ],
            ],
            [
                'question' => 'x > 0, y > 0, xy = 8 болсын. P = 2(log₂ x)² + (log₂ y)² деп анықтайық. X = log₂ x деп қояйық. Осы арқылы P-ны X арқылы өрнектеңдер және P-ның ең кіші мәнін табыңдар.',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '4'],
                    ['label' => 'B', 'answer' => '5'],
                    ['label' => 'C', 'answer' => '6'],
                    ['label' => 'D', 'answer' => '7'],
                    ['label' => 'E', 'answer' => '8'],
                ],
            ],
            [
                'question' => '(x+1)P(x) = (x+1)³ + m(x+1)² + 5x + 5 болған жағдайда P(x) көпмүшенің әр x нақты саны үшін әрдайын оң мәндер алатынына байланысты, m үшін ең үлкен мән аралығы қайсысы болады?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '(−√5; √5)'],
                    ['label' => 'B', 'answer' => '(−2√15; 2√15)'],
                    ['label' => 'C', 'answer' => '(−√10; 2√10)'],
                    ['label' => 'D', 'answer' => '(−2√5; 2√5)'],
                    ['label' => 'E', 'answer' => '(−√10; √10)'],
                ],
            ],
            [
                'question' => 'Дүкенде 8 футболка бар: S, M, L, XL өлшемдерінің әрқайсысының біреуі және екі түсті — қызыл және көк. Қайтарусыз кездейсоқ түрде 2 футболка таңдалады, және таңдалған екеудің кем дегенде біреуі XL өлшемінде болуы шарт. Таңдалған футболкалардың арасында кем дегенде бір қызыл футболка болуының ықтималдығын табыңдар.',
                'correct'  => 'A',
                'answers'  => [
                    ['label' => 'A', 'answer' => '10/13'],
                    ['label' => 'B', 'answer' => '7/16'],
                    ['label' => 'C', 'answer' => '4/13'],
                    ['label' => 'D', 'answer' => '12/13'],
                    ['label' => 'E', 'answer' => '11/16'],
                ],
            ],
            [
                'question' => 'Декарт координаталық жазықтықтың бірінші ширегінде осьтерге жанама шеңбер сызылған. (−7, 0) нүктесінен осы шеңберге жүргізілген жанама y-осімен (0, 24) нүктесінде қиылысады. Шеңбердің радиусы қанша бірлік?',
                'correct'  => 'C',
                'answers'  => [
                    ['label' => 'A', 'answer' => '18'],
                    ['label' => 'B', 'answer' => '20'],
                    ['label' => 'C', 'answer' => '21'],
                    ['label' => 'D', 'answer' => '36'],
                    ['label' => 'E', 'answer' => '30'],
                ],
            ],
            [
                'question' => 'Кубтың екі қарсы (бір-бірінен ең алыс) төбелерінде шыбын мен өрмекші отыр. Өрмекші тек кубтың сыртқы жақтарымен ғана жорғалай алады. Өрмекші шыбынға жету үшін кубтың бетімен жүретін ең қысқа жолдың ұзындығын табыңдар. Кубтың жанының ұзындығы 1.',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '√3'],
                    ['label' => 'B', 'answer' => '√6'],
                    ['label' => 'C', 'answer' => '√2'],
                    ['label' => 'D', 'answer' => '√5'],
                    ['label' => 'E', 'answer' => '√7'],
                ],
            ],
            [
                'question' => 'AB = BC болатын ABC үшбұрышында AB және AC қабырғаларында сәйкесінше M және N нүктелері алынған. AMN үшбұрышының сырттай сызылған шеңбері BC қабырғасына P нүктесінде жанасады. Q нүктесі — MP түзуінің CNP үшбұрышының сырттай сызылған шеңберімен екінші қиылысу нүктесі болсын. AP/QM қатынасын табыңдар.',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '3'],
                    ['label' => 'B', 'answer' => '1'],
                    ['label' => 'C', 'answer' => '2'],
                    ['label' => 'D', 'answer' => '1,5'],
                    ['label' => 'E', 'answer' => '2,5'],
                ],
            ],
            [
                'question' => 'f(x) функциясы нақты сандар жиынында анықталған және f(x) = 2^x / (2^x + 2^(1−x)) түрде берілген. f(1/2023) + f(2/2023) + f(3/2023) + … + f(2022/2023) қосындысының мәнін табыңдар.',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '2022'],
                    ['label' => 'B', 'answer' => '1011'],
                    ['label' => 'C', 'answer' => '2023'],
                    ['label' => 'D', 'answer' => '1'],
                    ['label' => 'E', 'answer' => '0'],
                ],
            ],
            [
                'question' => 'ABCD квадратынан тысқары P нүктесі алынған, және AP = AB, ∠ADP = 10° шарттары орындалады. ∠APB бұрышы қандай мүмкін мәндерге ие болуы мүмкін?',
                'correct'  => 'E',
                'answers'  => [
                    ['label' => 'A', 'answer' => '15'],
                    ['label' => 'B', 'answer' => '110'],
                    ['label' => 'C', 'answer' => '70'],
                    ['label' => 'D', 'answer' => '35'],
                    ['label' => 'E', 'answer' => '55'],
                ],
            ],
            [
                'question' => '9sin²x + 12sin(x)·cos(x) + 4cos²x өрнегінің ең үлкен мәні қандай?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '10'],
                    ['label' => 'B', 'answer' => '11'],
                    ['label' => 'C', 'answer' => '12'],
                    ['label' => 'D', 'answer' => '13'],
                    ['label' => 'E', 'answer' => '14'],
                ],
            ],
            [
                'question' => 'Ыстық краннан ванна 23 минутта толады, суық краннан — 17 минутта толады. Маша әуелі ыстық кранды ашты. Ванна толған сәтте ыстық судың мөлшері суық судың мөлшерінен 1,5 есе көп болу үшін, ол суық кранды неше минуттан кейін ашуы керек?',
                'correct'  => 'D',
                'answers'  => [
                    ['label' => 'A', 'answer' => '4'],
                    ['label' => 'B', 'answer' => '5'],
                    ['label' => 'C', 'answer' => '6'],
                    ['label' => 'D', 'answer' => '7'],
                    ['label' => 'E', 'answer' => '8'],
                ],
            ],
            [
                'question' => 'Мақсатта 2023 сіріңке бар. Ол сіріңкелермен екі қатарға бір-біріне қосылып тұрған алтыбұрыштар жасайды. Ол қанша алтыбұрыш ала алады?',
                'correct'  => 'B',
                'answers'  => [
                    ['label' => 'A', 'answer' => '504'],
                    ['label' => 'B', 'answer' => '505'],
                    ['label' => 'C', 'answer' => '506'],
                    ['label' => 'D', 'answer' => '503'],
                    ['label' => 'E', 'answer' => '501'],
                ],
            ],
        ];
    }
}
