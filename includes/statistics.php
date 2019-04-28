<?php

/*
This file contains code for displaying piecharts on the statistics page.
*/

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
global $wpdb;

	// Active users array used in all queries
	$active_users_query =
		"SELECT user_id
		FROM wp_usermeta
		WHERE wp_usermeta.meta_key = 'wp_user_level'
		AND wp_usermeta.meta_value != 10
		AND user_id IN (
			SELECT id
			FROM wp_users
		)";

	// Q1. GENDER
	$queryGender = sprintf(
		"SELECT value AS 'Key', count(value) AS 'Value'
		FROM wp_bp_xprofile_data
		WHERE field_id = 139 AND user_id IN (%s)
		GROUP BY value", $active_users_query);
	$dataGender = $wpdb->get_results($queryGender);

	// Q2. AGE
	$queryAge = sprintf(
							"SELECT value AS 'Key', COUNT(value) as 'Value'
							FROM wp_bp_xprofile_data
							WHERE field_id = 142 AND user_id IN (%s)
							GROUP BY value",
							$active_users_query);
	$dataAge = $wpdb->get_results($queryAge);

	// Q3. CITY
	$queryCity = sprintf(
		"SELECT value AS 'Key', count(value) AS 'Value'
		FROM wp_bp_xprofile_data
		WHERE field_id = 143 AND user_id IN (%s)
		GROUP BY value", $active_users_query);
	$dataCity = $wpdb->get_results($queryCity);

	// Q4. EDUCATION
	$queryEdu = sprintf(
		"SELECT value AS 'Key', count(value) AS 'Value'
		FROM wp_bp_xprofile_data
		WHERE field_id = 186 AND user_id IN (%s)
		GROUP BY value",
		$active_users_query);
	$dataEdu = $wpdb->get_results($queryEdu);

?>

<html>
<head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<style>
	body {
		font-family: 'Open Sans', sans-serif;
	}
	</style>
	<script type="text/javascript">
	  // Load Charts and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      google.charts.setOnLoadCallback(drawGender);
      google.charts.setOnLoadCallback(drawAge);
      google.charts.setOnLoadCallback(drawCity);
      google.charts.setOnLoadCallback(drawEdu);

	   // GENDER
      function drawGender() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Key');
        data.addColumn('number', 'Value');
        data.addRows([
		<?php
		foreach($dataGender as $row) {
			printf("['%s', %s],", substr($row->Key, 3) , $row->Value);
		}

		?>
        ]);

        var options = {title:'Gender',
                       width:300,
                       height:250,
					   colors: ['#a2ad00', '#6a8012', '#646464', '#c4c4c4', '#808080','#999999','#b3b3b3','#cccccc'],
					   chartArea:{
							left:20,
							top: 40,
							width: '100%',
							height: '200px',
						},
						titleTextStyle: {
							color: '#4a4d4e',
							fontName: 'Roboto',
							fontSize: 16
						}
						};

        var chart = new google.visualization.PieChart(document.getElementById('chartGender'));
        chart.draw(data, options);
      }


      function drawAge() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Key');
        data.addColumn('number', 'Value');
        data.addRows([
		<?php
		foreach($dataAge as $row) {
			printf("['%s', %s],", $row->Key , $row->Value);
		}

		?>
        ]);

        var options = {title:'Age',
                       width:300,
                       height:250,
					   colors: ['#a2ad00', '#6a8012', '#646464', '#c4c4c4', '#808080','#999999','#b3b3b3','#cccccc'],
					   chartArea:{
							left:20,
							top: 40,
							width: '100%',
							height: '200',
						},
						titleTextStyle: {
							color: '#4a4d4e',
							fontName: 'Roboto',
							fontSize: 16
						}};

        var chart = new google.visualization.PieChart(document.getElementById('chartAge'));
        chart.draw(data, options);
      }


      function drawCity() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Key');
        data.addColumn('number', 'Value');
        data.addRows([
        <?php
		foreach($dataCity as $row) {
			printf("['%s', %s],", substr($row->Key,3), $row->Value);
		}
		?>
        ]);

        var options = {title:'City',
                       width:300,
                       height:250,
					   colors: ['#a2ad00', '#6a8012', '#646464', '#c4c4c4', '#808080','#999999','#b3b3b3','#cccccc'],
					   chartArea:{
							left:20,
							top: 40,
							width: '100%',
							height: '200',
						},
						titleTextStyle: {
							color: '#4a4d4e',
							fontName: 'Roboto',
							fontSize: 16
						}};

        var chart = new google.visualization.PieChart(document.getElementById('chartCity'));
        chart.draw(data, options);
      }


      function drawEdu() {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Key');
        data.addColumn('number', 'Value');
        data.addRows([
        <?php
		foreach($dataEdu as $row) {
			printf("['%s', %s],", substr($row->Key,3), $row->Value);
		}
		?>
        ]);

        var options = {title:'Education',
                       width:300,
                       height:250,
					   colors: ['#a2ad00', '#6a8012', '#646464', '#c4c4c4', '#808080','#999999','#b3b3b3','#cccccc'],
					   chartArea:{
							left:20,
							top: 40,
							width: '100%',
							height: '200',
						},
						titleTextStyle: {
							color: '#4a4d4e',
							fontName: 'Roboto',
							fontSize: 16
						}};

        var chart = new google.visualization.PieChart(document.getElementById('chartEdu'));
        chart.draw(data, options);
      }

	</script>
</head>
<body>
<table class="columns">
	<tr>
		<td><div id="chartGender" style="border: 0px solid #ccc"></div></td>
		<td><div id="chartAge" style="border: 0px solid #ccc"></div></td>
	</tr><tr>
		<td><div id="chartCity" style="border: 0px solid #ccc"></div></td>
		<td><div id="chartEdu" style="border: 0px solid #ccc"></div></td>
	</tr>
</table>
</body>
