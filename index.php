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
	echo '<td><img src="data:image/svg+xml;base64,' . base64_encode( $data ) . '"></td>';
	echo '<td><img src="data:image/png;base64,' . base64_encode( pngify( $data ) ) . '"></td>';

	foreach ( $colors as $variant => $color ) {
		if ( in_array( $source, $variants[$variant] ) ) {
			$svg = new DomDocument;
			$svg->load( $source );
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
	$sampling = 16;
	$dpi = 72;
	$density = $dpi * $sampling;
	$scale = 100 / $sampling;
	$process = proc_open(
		"/opt/ImageMagick/bin/convert -antialias -background transparent -density {$density} " .
			"-resize {$scale}% -unsharp 1.5x1.5+1.0+0.1 -background none svg:- png:-",
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
