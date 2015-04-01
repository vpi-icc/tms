<?php

/**
 * Description of newPHPClass
 *
 * @author tema4ka
 */
class TestParser {
    
    private $parser;
    private $data;
    public $results;
    
    public function __construct($parser) {
        $this->parser = $parser;    
    }
    
    public function run() {
        if ( !$this->tableLayoutRecognized() ) {
            return false;
        }
        $allTestsPassed = true;
        $allTestsPassed &= $this->groupsRecognized();
        $allTestsPassed &= $this->genericCellsRecognized();
        $allTestsPassed &= $this->physicalEducationCellsRecognized();
        $allTestsPassed &= $this->customDateCellsRecognized();
        $allTestsPassed &= $this->doubleLineTitleRecognized();
        $allTestsPassed &= $this->parallelDisciplinesRecognized();   
        $allTestsPassed &= $this->complexDisciplinesRecognized();
        
        return $allTestsPassed ? true : false;
    }
    
    private function report($test, $result, $description = null) {
        $this->results[] = array('test' => $test, 'result' => $result, 'description' => $description);
    }
    
    private function tableLayoutRecognized() {
        $title = 'Распознавание контуров таблицы';
        $fileToParse = $_SERVER['DOCUMENT_ROOT'] . '/examples/fei4_140213.xlsx';
        if ( !$this->parser->parsing($fileToParse) ) {
            $code = $this->parser->getStatusCode();
            $descr = $this->parser->getStatusDescription();
            $details = $this->parser->getStatusDetails();
            $this->report($title, false, $code . ': ' . $descr . ' (' . $details . ')');
            return false;
        }

        $this->data = $this->parser->getParseData();
        $this->report($title, true);
        return true;
    }
    
    private function groupsRecognized() {
        $title = 'Распознавание групп';
        $referenceGroupList = array('ВХТ-401', 'ВВТ-406', 'ВЭ-411', 'ВЭМ-413', 'ВЭМ-5', 'ВТПЭ-5');
        $groupList = array();
        foreach ( $this->data as $pair )
        {
            if ( !in_array($pair->group, $groupList) ) $groupList[] = $pair->group;
        }
        $n = count($referenceGroupList);
        $intersection = array_intersect($referenceGroupList, $groupList);
        $areListsIdentical = count($intersection) == $n;
        if ( !$areListsIdentical ) {
            $diffList = array_diff($referenceGroupList, $groupList);
            $diff = implode(', ', $diffList);
            $groups = implode(', ', $groupList);
            $this->report($title, false, "Not recognized: ${diff}. Parser supplied the following groups: ${groups}");
            return false;
        }
        $this->report($title, true);
        return true;
    }
    
    private function findMatch($title, array $items) {
        $isTestPassed = true;
        foreach ( $items as $item ) {
            list ( $id, $pairFromTest ) = $item;
            $pairFromParser = $this->data[$id];
            $isTestPassed &= $pairFromParser == $pairFromTest;
            if ( !$isTestPassed ) {                
                $delim = ' = ';
                $strPairFromParser = implode($delim, get_object_vars($pairFromParser));
                if ( empty($strPairFromParser) ) $strPairFromParser = '(пустая строка)';
                $strPairFromTest = implode($delim, get_object_vars($pairFromTest));
                $this->report($title, false, "<pre>${strPairFromTest} (original)\n${strPairFromParser} (parsed)</pre>");
            }
        }
        return $isTestPassed ? true : false;
    }
    
    private function genericCellsRecognized() {
        $title = 'Распознавание обычных ячеек';
        $items = array();
        $items[] = array(1, new Meeting('17.2,3.3,17.3,31.3,14.4,28.4,12.5,26.5,9.6,', '2', 'А-12', 'АТПП', 'лек.', 'Чичилин', 'ВХТ-401'));
        $items[] = array(111, new Meeting('21.2,7.3,21.3,4.4,18.4,2.5,16.5,30.5,13.6,', '5', 'В-206', 'Экономика недвижимости', 'лек.', 'Иевлева', 'ВЭМ-413'));
        $items[] = array(133, new Meeting('22.2,8.3,22.3,5.4,19.4,3.5,17.5,31.5,14.6,', '2', 'В-111', 'Деловой ин.яз.', 'пр.', 'Хван', 'ВЭМ-5'));
        $items[] = array(66, new Meeting('14.2,28.2,14.3,28.3,11.4,25.4,9.5,23.5,6.6,', '2', 'В-209', 'Теор.планир.эксп.', 'лаб.', 'Короткова', 'ВВТ-406', '1п/г 2п/г'));
        $items[] = array(61, new Meeting('13.2,27.2,13.3,27.3,10.4,24.4,8.5,22.5,5.6,', '1', 'Б-104', 'БЖД', 'лек.', 'Александрина', 'ВВТ-406'));
        $items[] = array(53, new Meeting('11.2,25.2,11.3,25.3,8.4,22.4,6.5,20.5,3.6,', '1', 'В-206', 'Вып.выпуск.раб.', 'лек.', 'Рыбанов', 'ВВТ-406'));
                
        if ( !$this->findMatch($title, $items) ) return false;                
        $this->report($title, true);
        return true;
    }
    
    private function physicalEducationCellsRecognized() {
        $title = 'Распознавание физической культуры';
        $items = array();
        $items[] = array(4, new Meeting('18.2,4.3,18.3,1.4,15.4,13.5,27.5,10.6,', '1', '', 'Физическая культура', '', 'Хаирова', 'ВХТ-401', '13.00-14.30'));
        $items[] = array(5, new Meeting('18.2,4.3,18.3,1.4,15.4,13.5,27.5,10.6,', '2', '', 'Физическая культура', '', 'Хаирова', 'ВХТ-401', '13.00-14.30'));
                
        if ( !$this->findMatch($title, $items) ) return false;                
        $this->report($title, true);
        return true;
    }
    
    private function customDateCellsRecognized() {
        $title = 'Распознавание ячеек со встроенными датами';
        $items[] = array(134, new Meeting('24.02, 24.03, 21.04, 19.05', '4', 'В-204', 'Теория кризисного управления', 'пр.', 'Гаврилова', 'ВЭМ-5'));
        if ( !$this->findMatch($title, $items) ) return false;
        $this->report($title, true);
        return true;
    }
    
    private function doubleLineTitleRecognized() {
        $title = 'Распознавание двухстрочных дисциплин';
        // с пробелом после буквы "в"
        $items[] = array(147, new Meeting('18.2,4.3,18.3,1.4,15.4,13.5,27.5,10.6,', '2', 'А-29', 'Комп.мет.и инф.сист.в техн.синт. пер.полим.', 'лаб.', 'Александрина', 'ВТПЭ-5'));
        if ( !$this->findMatch($title, $items) ) return false;
        $this->report($title, true);
        return true;
    }
    
    private function parallelDisciplinesRecognized() {
        $title = 'Распознавание дисциплин по подгруппам';
        $items[] = array(13, new Meeting('20.2,6.3,20.3,3.4,17.4,1.5,15.5,29.5,12.6,', '2', 'Б-306', 'Осн.терм.и кин. синтеза ВМС', 'лаб.', 'Пучков', 'ВХТ-401', '1/2 п/г'));
        $items[] = array(15, new Meeting('20.2,6.3,20.3,3.4,17.4,1.5,15.5,29.5,12.6,', '2', 'Б-309', 'БЖД', 'лаб.', 'Шиповский', 'ВХТ-401', '2/1 п/г'));
        if ( !$this->findMatch($title, $items) ) return false;
        $this->report($title, true);
        return true;
    }
    
    private function complexDisciplinesRecognized() {
        $title = 'Распознавание сложных дисциплин (подгруппы, эксплицитные даты, множественные преподаватели и аудитории и т. д.)';
        $items[] = array(158, new Meeting('14.02,14.03,11.04,6.06', '2', 'Б-008', 'Теор. и эксп. мет. иссл. в химии', 'лаб.', 'Новопольцева', 'ВТПЭ-5', ', 009'));
        $items[] = array(161, new Meeting('28.02,28.03,25.04,23.05', '3', 'Б-008', 'Рецептуростр. полим. композ', 'лаб.', 'Новопольцева', 'ВТПЭ-5', ', 009'));
        if ( !$this->findMatch($title, $items) ) return false;
        $this->report($title, true);
        return true;
    }
    
    public function printDump() {
        echo '<h2>Full parser output dump</h2>';
        echo '<table width="100%">';
        $id = -1;
        foreach ( $this->data as $pair ) {
            $id++;            
            $date = $pair->date;
            $type = $pair->type;
            $lecturer = $pair->lecturer;
            $offset = $pair->offset;
            $discipline = $pair->discipline;
            $room = $pair->room;
            $group = $pair->group;
            $comment = $pair->comment;
            echo "<tr><td>${id}</td><td>${date}</td><td>${offset}</td><td>${discipline}</td><td>${type}</td><td>${group}</td><td>${room}</td><td>${lecturer}</td><td>${comment}</td></tr>";
        }
        echo '</table>';
    }
}

?>