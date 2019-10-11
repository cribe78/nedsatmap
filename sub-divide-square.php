<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10/10/19
 * Time: 2:28 PM
 */

$rect_coords_str = "7.799958558522151,53.2922548068703,0 4.9012285735511,53.96810208984763,0 3.225267284864326,51.27873277204284,0 5.955968933932332,50.64047436931616,0";

$r1 = preg_split("/\s/", $rect_coords_str);
echo "after the breakpoint\n";
$rect_coords = [];

foreach ($r1 as $idx => $coord) {
    $r2 = preg_split("/,/", $coord);
    $rect_coords[] = [floatval($r2[0]), floatval($r2[1])];
}

// coords are longitude, latitude
//print_r($rect_coords);

$subboxes = quarter_box($rect_coords);

$file_head =  <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Document>
	<name>subboxes</name>
	<Style id="s_ylw-pushpin_hl">
		<IconStyle>
			<scale>1.3</scale>
			<Icon>
				<href>http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href>
			</Icon>
			<hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>
		</IconStyle>
	</Style>
	<StyleMap id="m_ylw-pushpin">
		<Pair>
			<key>normal</key>
			<styleUrl>#s_ylw-pushpin</styleUrl>
		</Pair>
		<Pair>
			<key>highlight</key>
			<styleUrl>#s_ylw-pushpin_hl</styleUrl>
		</Pair>
	</StyleMap>
	<Style id="s_ylw-pushpin">
		<IconStyle>
			<scale>1.1</scale>
			<Icon>
				<href>http://maps.google.com/mapfiles/kml/pushpin/ylw-pushpin.png</href>
			</Icon>
			<hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>
		</IconStyle>
	</Style>
	<Folder>
		<name>SubPlaces</name>
		<open>1</open>
EOT;

$footer = <<<EOT
	</Folder>
</Document>
</kml>
EOT;

$output = $file_head;

foreach($subboxes as $idx => $box) {
    $output .= box_to_placemark($box, "subbox$idx");
}

$output .= $footer;

file_put_contents("subboxes.kml", $output);


function quarter_box($box) {
    $b_center = line_center($box, 0, 2);
    $edge_centers = [
        line_center($box, 0, 1),
        line_center($box, 1, 2),
        line_center($box, 2, 3),
        line_center($box,3, 0)
    ];

    $subboxes = [
        [
            $box[0],
            $edge_centers[3],
            $b_center,
            $edge_centers[0]
        ],
        [
            $box[1],
            $edge_centers[0],
            $b_center,
            $edge_centers[1]
        ],
        [
            $box[2],
            $edge_centers[1],
            $b_center,
            $edge_centers[2]
        ],
        [
            $box[3],
            $edge_centers[2],
            $b_center,
            $edge_centers[3]
        ]
    ];

    return $subboxes;
}


function line_center($set_of_points, $pidx1, $pidx2) {
    $lon_center = ($set_of_points[$pidx1][0] + $set_of_points[$pidx2][0])/2;
    $lat1 = $set_of_points[$pidx1][1];
    $lat2 = $set_of_points[$pidx2][1];

    $lat_center = rad2deg(asin((sin(deg2rad($lat1)) + sin(deg2rad($lat2)))/2));

    return [$lon_center, $lat_center];
}

function box_to_str($box) {
    $str = "{$box[0][0]},{$box[0][1]},0 {$box[1][0]},{$box[1][1]},0 {$box[2][0]},{$box[2][1]},0 {$box[3][0]},{$box[3][1]},0 {$box[0][0]},{$box[0][1]},0 ";
    return $str;
}

function box_to_placemark($box, $name) {
    $coords = box_to_str($box);

    $placemark = <<<EOT
		<Placemark>
			<name>$name</name>
			<styleUrl>#m_ylw-pushpin</styleUrl>
			<Polygon>
				<tessellate>1</tessellate>
				<outerBoundaryIs>
					<LinearRing>
						<coordinates>
                           $coords 
						</coordinates>
					</LinearRing>
				</outerBoundaryIs>
			</Polygon>
		</Placemark>
EOT;

    return $placemark;
}