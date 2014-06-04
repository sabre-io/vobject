<?php

namespace Sabre\VObject;

$inputFiles = [
    "africa",
    "antarctica",
    "asia",
    "australasia",
    "europe",
    "northamerica",
    "southamerica",
];

$path = 'tzdata/';

require __DIR__ . '/../vendor/autoload.php';


$rules = [];
$zones = [];



function parse() {

    global $inputFiles, $path;

    foreach($inputFiles as $inputFile) {

        parseFile($path . $inputFile);

    }

}

function getline($h) {
    do {
        $line = fgets($h);
        if ($line===false) return;
        if ($line[0]==='#' || trim($line)=="") {
            continue;
        }
        // If we got here.. it's a valid line
      
        // Stripping comments
        if (strpos($line,'#')!==false) {
            $line = rtrim(substr($line, 0, strpos($line,'#')));
        }
        // Is thee anything left?
        if (!trim($line,"\t ")) {
            continue;
        }
        break;
    } while(true);



    $line = preg_split("/[\s]/", $line);
    return $line;

}

function parseFile($file) {

    global $rules, $zones;

    $h = fopen($file, 'r');
    if (!$h) {
        echo "Could not open file: $file\n";
        return;
    }
    while($line = getline($h)) {

        if (is_null($line)) break;

        switch($line[0]) {
            case 'Rule' :
                if (count($line) < 9) {
                    echo "Invalid rule: " . implode("--t--", $line);
                    break; 
                }
                $rules[] = [
                    'name'   => $line[1],
                    'from'   => $line[2],
                    'to'     => $line[3],
                    'in'     => $line[4],
                    'on'     => $line[5],
                    'at'     => $line[6],
                    'save'   => $line[7],
                    'letter' => $line[8],
                ];
                break;
            case 'Zone' :
                $zone['name'] = $line[1];
                $zone['rules'] = [];

                $lastLine = null;
                do {
                    if (count($line) < 5) {
                        echo "Invalid zone: " . implode("|", $line), ". Last line: " . implode("|", $lastLine) . "\n";
                        print_r($line);
                        print_r($zone);
                        die();
                        break; 
                    }
                    $until = null;
                    if (isset($line[5])) {
                        $until = implode(' ', array_slice($line,5));
                    }
                    $zone['rules'][] = [
                        'gmtoff' => $line[2],
                        'rules' => $line[3],
                        'format' => $line[4],
                        'until' => $until,
                    ];
                    if ($until) {

                        // If there was an 'until' clause, it means we need to 
                        // parse the next line as well.
                        $lastLine = $line;
                        $line = getline($h);
                        if (is_null($line)) break;

                        // Sometimes there's 2, sometimes theres 3 empty parts 
                        // before the continuation rules starts. So we're 
                        // removing all empty parts, and addin them again.
                        while($line[0]==="") {
                            array_shift($line);
                        }
                        // Adding two empty parts again.
                        array_unshift($line, "", "");
                        
                    }
                } while($until);
                $zones[] = $zone;
                break;
            case 'Link' :
                break;
            default :
                echo "Unknown line: " . implode("\t", $line), "\n";
                break;

        }

    }

}

parse();

echo "Found " . count($zones) . " zones, and " . count($rules) . " named rules\n";

foreach($zones as $zone) {
    if ($zone['name']!='Europe/Amsterdam') {
        continue;
    }
    generateVTimeZone($zone);
}

function parseUntil($until) { 

    if (!$until) return null;
    if (!preg_match('/^([0-9]{4})(?:\W)?([A-Za-z]{3})?(?:\W)*([0-9]+)?(?:\W)*([0-9]:[0-9]+)?$/', trim($until), $matches)) {
        echo "Unknown until: " . $until . "\n";
        return null;
    }

    $month = 1;
    if (isset($matches[2])) {
        $monthMap = [
            'Apr' => 4,
            'May' => 5,
            'Jul' => 7,
        ];
        if (isset($monthMap[$matches[2]])) {
            $month = $monthMap[$matches[2]];
        } else {
            echo "Unknown month: $matches[2] !\n";
        }
    }

    $time = isset($matches[4]) && $matches[4] ? $matches[4] : '00:00';
    if (strlen($time)<5) $time = '0' . $time;
    $time = str_replace(':','', $time);

    return 
        $matches[1] .
        ($month<10 ? '0' . $month : $month) .
        (isset($matches[3])?$matches[3]:1) . 'T' . 
        $time .
        '00';

}


function generateVTimeZone($zone) {

    $vcal = new Component\VCalendar();
    $vtimezone = $vcal->add('VTIMEZONE', [
        'TZID' => $zone['name'],
    ]);

    $lastEnd = null;
    foreach($zone['rules'] as $rule) {

        $start = parseUntil($rule['until']);
        echo $start, "\n";

    }

}
