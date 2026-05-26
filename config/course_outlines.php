<?php

function normalizeOutlineText($text) {
    $text = preg_replace('/\r\n?/', "\n", $text);
    $text = preg_replace('/[ \t]+/', ' ', $text);
    return trim($text);
}

function getBscsCourseOutlines() {
    static $courses = null;
    if ($courses !== null) {
        return $courses;
    }

    $path = __DIR__ . '/../BSCSoutline.txt';
    if (!file_exists($path)) {
        return [];
    }

    $content = normalizeOutlineText(file_get_contents($path));
    $pattern = '/Course Name:\s*(.+?)(?=\nCourse Structure:|\nCredit Hours:|\nPrerequisites:|\nObjectives:)(.*?)(?=\nCourse Name:|\nSemester-\d+|\z)/is';
    preg_match_all('/\nSemester[-\s]*(\d+)/i', $content, $semesterMatches, PREG_OFFSET_CAPTURE);
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

    $courses = [];
    foreach ($matches as $match) {
        $name = trim(preg_replace('/\s+/', ' ', $match[1][0]));
        $block = trim($match[2][0]);
        $outline = '';
        $semester = findSemesterForOffset($semesterMatches, $match[0][1]);

        if (preg_match('/Course Outline:\s*(.*?)(?=\n(?:Suggested Text Book|Text Books|Reference Material|Recommended Texts|Objectives:|Course Name:|Semester-\d+|\z))/is', $block, $outlineMatch)) {
            $outline = trim(preg_replace('/\s+/', ' ', $outlineMatch[1]));
        }

        if ($name !== '' && $outline !== '') {
            $key = strtolower($name);
            $courses[$key] = [
                'university' => 'GCUF',
                'course' => 'BSCS',
                'semester' => $semester,
                'name' => $name,
                'outline' => $outline
            ];
        }
    }

    uasort($courses, function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    return array_values($courses);
}

function findBscsCourseOutlineBySubject($subject) {
    $subject = strtolower(trim($subject));
    if ($subject === '') {
        return null;
    }

    foreach (getBscsCourseOutlines() as $course) {
        if (strtolower($course['name']) === $subject) {
            return $course;
        }
    }

    foreach (getBscsCourseOutlines() as $course) {
        if (str_contains(strtolower($course['name']), $subject) || str_contains($subject, strtolower($course['name']))) {
            return $course;
        }
    }

    return null;
}

function findSemesterForOffset($semesterMatches, $offset) {
    $semester = null;
    if (!isset($semesterMatches[1])) {
        return $semester;
    }

    foreach ($semesterMatches[1] as $index => $match) {
        $markerOffset = $semesterMatches[0][$index][1];
        if ($markerOffset <= $offset) {
            $semester = intval($match[0]);
        } else {
            break;
        }
    }

    return $semester;
}
?>
