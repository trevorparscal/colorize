<!doctype html>

<?php

$start = microtime( true );

?>
<html>
	<head>
		<title>SVG Colorize test</title>
		<style>
			body {
				margin: 0;
				padding: 2em;
				background-color: #ddd;
				font-family: sans-serif;
			}
			th,
			td {
				min-width: 2em;
				text-align: center;
				padding: 0.25em;
				border-bottom: solid 1px #ccc;
			}
			td:first-child {
				text-align: right;
			}
			td {
				color: #333;
			}
		</style>
	</head>
	<body>
		<table cellspacing="0">
			<thead>
				<th></th>
				<th>V</th>
				<th>R</th>
				<th style="color:#ffffff">V</th>
				<th style="color:#ffffff">R</th>
				<th style="color:#347bff">V</th>
				<th style="color:#347bff">R</th>
				<th style="color:#00af89">V </th>
				<th style="color:#00af89">R </th>
				<th style="color:#d11d13">V</th>
				<th style="color:#d11d13">R</th>
			</thead>
			<tbody>
<?php

$sources = glob( 'src/*.svg' );
$colors = array( '#ffffff', '#347bff', '#00af89', '#d11d13' );
$variants = array(
	$sources,
	array( 'src/check.svg' ),
	array( 'src/add.svg' ),
	array( 'src/remove.svg' ),
);

foreach ( $sources as $source ) {
	echo '<tr><td>' . basename( $source, '.svg' ) . '</td>';

	$data = file_get_contents( $source );
	$data = preg_replace_callback( '/d="(.+?)"/', function ( $matches ) {
		$pathdata = $matches[1];
		// Make sure there is at least one space between numbers, and that leading zero is not omitted.
		// rsvg has issues with syntax like "M-1-2" and "M.445.483" and especially "M-.445-.483".
		$pathdata = preg_replace( '/(-?)(\d*\.\d+|\d+)/', ' ${1}0$2 ', $pathdata );
		// Strip unnecessary leading zeroes for prettiness, not strictly necessary
		$pathdata = preg_replace( '/([ -])0(\d)/', '$1$2', $pathdata );
		return "d=\"$pathdata\"";
	}, $data );
	$originalData = $data;

	echo '<td><img src="data:image/svg+xml;base64,' . base64_encode( $data ) . '"></td>';
	echo '<td><img src="data:image/png;base64,' . base64_encode( pngify( $data ) ) . '"></td>';

	foreach ( $colors as $variant => $color ) {
		if ( in_array( $source, $variants[$variant] ) ) {
			$svg = new DomDocument;
			$svg->loadXml( $originalData );
			$svg->getElementsByTagName( 'g' )->item( 0 )->setAttribute( 'fill', $color );
			$data = $svg->saveXml();

			echo '<td><img src="data:image/svg+xml;base64,' . base64_encode( $data ) . '"></td>';
			echo '<td><img src="data:image/png;base64,' . base64_encode( pngify( $data ) ) . '"></td>';
		} else {
			echo '<td></td><td></td>';
		}
	}
	echo '</tr>';
}

function pngify( $svg ) {
	$process = proc_open(
		"/usr/local/bin/rsvg-convert",
		array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ) ),
		$pipes
	);

	if ( is_resource( $process ) ) {
		fwrite( $pipes[0], $svg );
		fclose( $pipes[0] );
		$png = stream_get_contents( $pipes[1] );
		fclose( $pipes[1] );
		proc_close( $process );

		return $png;
	}
	return false;
}

?>
			</tbody>
		</table>
		<h1><?php echo microtime( true ) - $start; ?> seconds</h1>
	</body>
</html>
