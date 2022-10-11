<!DOCTYPE html>
<html lang="en">
<head>
    <title>SVGGraph</title>

    <style>
        body {
            background-color: #1b1e28;
        }
    </style>
</head>
<body>

<?php
require_once '../autoloader.php';

enum GraphType
{
    case LINE;
    case BAR;
    case PIE;
    case DONUT;
}

// Bar Chart ref: https://www.goat1000.com/svggraph-bar.php
function getRenderedGraph(GraphType $graphType, array $values, array $colors, ?array $legendEntries, array $guideline = null, array $extraSettings = []): string
{
    // ref: https://www.goat1000.com/svggraph-settings.php
    $settings = [
        'auto_fit' => false,
        'back_colour' => '#282e39',
        'back_stroke_width' => 0,
        'stroke_width' => 0,
        'stroke_colour' => '#fff',
        'axis_colour' => '#fff',
        'axis_overlap' => 0,
        'grid_colour' => '#fff',
        'label_colour' => '#fff',
        'axis_font' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
        'context_font' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
        'data_label_font' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
        'graph_title_font' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
        'legend_font' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
        'tooltip_font' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"',
        'minimum_grid_spacing' => 50,
        'show_subdivisions' => false,
        'bar_round' => 1,
        'label_h' => "Hosts",
        'label_y' => "Availability in %",
        'legend_entry_height' => 10,
        'legend_title' => 'Legend',
        'tooltip_back_colour' => "#000",
        'tooltip_colour' => "#fff",
        'tooltip_round' => 1,
        'tooltip_stroke_width' => 0,
        'tooltip_shadow_opacity' => 0,
        'legend_position' => 'outer right',
    ];

    if ($guideline)
        $settings['guideline'] = $guideline;

    if (is_array($legendEntries) && sizeof($legendEntries) === 0) {
        if (array_key_exists(0, $values) && is_array($values[0]))
            $iterValues = $values[0];
        else
            $iterValues = $values;

        foreach ($iterValues as $key => $value)
            $legendEntries[] = $key;
    }

    if (is_array($legendEntries) && sizeof($legendEntries) > 0)
        $settings['legend_entries'] = $legendEntries;

    $longestWordCount = 0;
    foreach ($values as $name => $nix) {
        if (strlen($name) > $longestWordCount)
            $longestWordCount = strlen($name);
    }
    $settings['pad_right'] = 20 + ($longestWordCount * 5.3);

    $settings = array_merge($settings, $extraSettings);

    $graph = new Goat1000\SVGGraph\SVGGraph(500 * 1.25, 400 * 1.25, $settings);
    $graph->colours($colors);

    $graph->values($values);
    $graph->links(['Host020' => 'report/view/host020.htm']);

    $output = "";

    if ($graphType === GraphType::BAR)
        $output = $graph->fetch('BarGraph');
    elseif ($graphType === GraphType::LINE)
        $output = $graph->fetch('MultiLineGraph');
    elseif ($graphType === GraphType::PIE)
        $output = $graph->fetch('PieGraph');
    elseif ($graphType === GraphType::DONUT)
        $output = $graph->fetch('DonutGraph');

    // enable for js functionality. (E.g. magnify)
    // $output .= $graph->fetchJavascript();

    return $output;
}

/**
 * Bar Graph with long name
 */
echo getRenderedGraph(
    GraphType::BAR,
    ['Host020' => 30, 'Host030' => 50, 'Host040' => 40, 'Host050' => 25, 'Host060' => 45, 'Host070_der_sehr_lang_ist' => 35],
    ['#44bb77'],
    [],
    [100, "Target Availability", "text_colour" => "#fff"]
);

/**
 * Bar Graph
 */
echo getRenderedGraph(
    GraphType::BAR,
    ['Host020' => 30, 'Host030' => 50, 'Host040' => 40, 'Host050' => 25, 'Host060' => 45, 'Host070' => 35],
    ['#44bb77'],
    null,
    [100, "Target Availability", "text_colour" => "#fff"]
);

/**
 * Colored Bar Graph
 */
echo getRenderedGraph(
    GraphType::BAR,
    ['Icinga' => 30, 'Example1' => 50, 'Icinga Website' => 40, 'Light Switches' => 25, 'Smart Tables' => 45],
    ['#50cfe2', '#4285F4', '#FF9900', '#405DE6', '#E01E5A'],
    [],
    [100, "Target Availability", "text_colour" => "#fff"]
);

/**
 * Line Graph
 */
echo getRenderedGraph(
    GraphType::LINE,
    [
        ['Mon' => 30, 'Tue' => 50, 'Wed' => 43, 'Thur' => 20, 'Fri' => 45],
        ['Mon' => 38, 'Tue' => 52, 'Wed' => 40, 'Thur' => 28, 'Fri' => 41],
        ['Mon' => 36, 'Tue' => 53, 'Wed' => 48, 'Thur' => 25, 'Fri' => 44],
        ['Mon' => 34, 'Tue' => 55, 'Wed' => 45, 'Thur' => 24, 'Fri' => 48],
        ['Mon' => 33, 'Tue' => 57, 'Wed' => 42, 'Thur' => 27, 'Fri' => 42],
    ],
    ['#50cfe2', '#4285F4', '#FF9900', '#405DE6', '#E01E5A'],
    ['Icinga', 'Example1', 'Icinga Website', 'Light Switches', 'Smart Tables'],
);

/**
 * Pie Graph
 */
echo getRenderedGraph(
    GraphType::PIE,
    ['Icinga' => 30, 'Example1' => 50, 'Icinga Website' => 43, 'Light Switches' => 25, 'Smart Tables' => 45],
    ['#50cfe2', '#4285F4', '#FF9900', '#405DE6', '#E01E5A'],
    null,
);

/**
 * Donut Graph
 */
echo getRenderedGraph(
    GraphType::DONUT,
    ['Icinga' => 30, 'Example1' => 50, 'Icinga Website' => 43, 'Light Switches' => 25, 'Smart Tables' => 45],
    ['#50cfe2', '#4285F4', '#FF9900', '#405DE6', '#E01E5A'],
    null,
    null,
    ['inner_text' => 'Namen']
);
?>
</body>
</html>
