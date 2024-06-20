<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    if (isset($_FILES["uploadFile"])) {
        $file = $_FILES["uploadFile"]["tmp_name"];

        if (file_exists($file)) {
            $content = file_get_contents($file); // Read the file content
        } else {
            $content = '';
        }
    } else {
        $content = isset($_REQUEST['information']) ? $_REQUEST['information'] : '';
    }

    $text = nl2br(htmlspecialchars($content));
	if(empty($text)){
		$text = isset($_REQUEST['information']) ? $_REQUEST['information'] : '';
	 }
    if (empty($text)) {
        echo "No data submitted";
    } else {
// 		$text= str_replace(array("\n", "\r", "\t", "br"), ' ', $text);
	
//  echo $text."\n";
// // die();
// 		echo "\n";
//   echo $text = "John Doe, the CEO of ExampleCorp, announced a new product launch in New York on April 5, 2024. The event was met with positive feedback. Jane Smith, the CTO, will lead the project, expected to complete by the end of 2024.";

$persons = [];
$events = [];
$projects = [];
$personPattern = '/([A-Za-z\s]+), the ([A-Za-z]+) of ([A-Za-z]+),/';
$eventPattern = '/announced a new (.+?) in ([A-Za-z\s]+) on ([A-Za-z]+\s\d{1,2},\s\d{4})\. The event was met with (\w+) feedback\./';
$projectPattern = '/will lead the project, expected to complete by the end of (\d{4})\./';

if (preg_match_all($personPattern, $text, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $persons[] = [
            "@type" => "Person",
            "name" => $match[1],
            "jobTitle" => $match[2],
            "affiliation" => [
                "@type" => "Organization",
                "name" => $match[3],
            ],
        ];
    }
}

if (preg_match($eventPattern, $text, $matches)) {
    $events[] = [
        "@type" => "Event",
        "name" => "Product Launch",
        "location" => $matches[2],
        "startDate" => date('Y-m-d', strtotime($matches[3])),
        "sentiment" => $matches[4],
    ];
}
if (preg_match($projectPattern, $text, $matches)) {
    $projects[] = [
        "@type" => "Project",
        "name" => "New Product Development",
        "leader" => isset($persons[1]) ? [
            "@type" => "Person",
            "name" => $persons[1]["name"],
        ] : null,
        "expectedCompletionDate" => $matches[1] . '-12-31',
    ];
}
echo '<pre>';

$output = [
    "@context" => [
        "name" => "http://schema.org/name",
        "jobTitle" => "http://schema.org/jobTitle",
        "affiliation" => "http://schema.org/affiliation",
        "location" => "http://schema.org/location",
        "startDate" => "http://schema.org/startDate",
        "sentiment" => "http://schema.org/sentiment",
        "Person" => "http://schema.org/Person",
        "Organization" => "http://schema.org/Organization",
        "Event" => "http://schema.org/Event",
        "Project" => "http://schema.org/Project",
        "leader" => "http://schema.org/leader",
        "expectedCompletionDate" => "http://schema.org/expectedCompletionDate",
    ],
    "@graph" => array_merge($persons, $events, $projects),
];

$jsonld = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

echo $jsonld;
}

}


?>
<!DOCTYPE html>
<html>
   <head>
      <title>data filter</title>
   </head>
   <body>
   <center>
        <h1>Data Filter</h1>
        <form action="/test2.php" method="POST" enctype="multipart/form-data">
            <textarea name="information" id="" cols="30" rows="10"></textarea><br>
            <input type="file" name="uploadFile"><br>
            <input type="submit" value="Submit">
        </form>
    </center>
</body>
</html>