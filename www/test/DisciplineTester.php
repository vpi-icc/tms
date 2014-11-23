

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<?
include $_SERVER['DOCUMENT_ROOT']."/app/bootstrap.php";
    // todo: преобразовать в объектно-ориентированный формат

$subjects = array(
	'Иностранный язык',
	'Междисциплинарный курсовой проект',
	'Системы искусственного интеллекта',
	'Компьютерная графика',
	'Машинно-ориентированные языки программирования',
	'Программирование на языках высокого уровня',
	'Моделирование систем',
	'Введение в наноматериалы и нанотехнологии',
	'Базы данных',
	'Основы управления разработкой программных систем',
	'Физическая культура',
	'Деньги, кредит, банки',
	'Финансы предприятия',
	'Организация и технология отрасли',
	'Немецкий язык',
	'Философия',
	'Философские вопросы естествознания и технических наук',
	'Философские проблемы науки и техники',
	'Расчет и конструирование изделий и форм',
	'Химия и технология полимерных покрытий'
);
$shorts = array(
	'Философ.пробл.науки и техн.',
	'Философ.',
	'Ин.яз',
	'бд',
	'Базы данных',
	'Ин. Яз.',
	'Компьютер.графика',
	'Иностр. язык',
	'Кг',
	'Введ.в наноматер.и нанотехнол.',
	'Осн.упр.разраб.програм.систем',
	'физ.культ.',
	'физ-ра',
	'Деньги,кредит,банки',
	'Финансы предпр.',
	'О р г а н и з а ц и я и т е х н о л о г и я о т р а с л и',
	'Н е м е ц к и й я з ы к ',
	'Расчет и констр.изд.и форм',
	'Хим.и техн.полим.покрыт.'
);
$DisciplineMatcher = new DisciplineMatcher();


foreach ($shorts as $short)
{
	echo "Поиск сокращения " . $short . ": ";
	$index = $DisciplineMatcher->GetMatch($subjects, $short);
	if (!is_null($index))
	{
		echo "[" . $subjects[$index] . "]<BR>\n";
	}
	else
	{
		echo "<span style='color: red;'>[NULL]</span><BR>\n";
	}
}
?>