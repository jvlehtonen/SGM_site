<?php
// Start the output buffer.
ob_start();

// Set PHP headers for CSV output.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=syngap_table.csv');

include("../sql/connect.php");

// Prepare the content to write it to CSV file.
$sql = "SELECT * FROM for_csv";
$stmt = $conn->stmt_init();
$stmt->prepare($sql);
$stmt->execute();
if ( $result = $stmt->get_result() ) {
    // Clean up output buffer before writing anything to CSV file.
    ob_end_clean();

    // Create a file pointer with PHP.
    $output = fopen( 'php://output', 'w' );

    // Get field information for all columns
    $header_args = array();
    $finfo = $result->fetch_fields();
    foreach ($finfo as $val) {
             $header_args[] = $val->name;
    }
    // Write headers to CSV file.
    fputcsv( $output, $header_args, ';' );

    // Loop through the prepared data to output it to CSV file.
    while ( $data_item = $result->fetch_assoc() ) {
        fputcsv( $output, $data_item, ';' );
    }

    // Close the file pointer with PHP with the updated output.
    fclose( $output );
}
$stmt->close();
$conn->close();
?>
