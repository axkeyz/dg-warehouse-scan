<?php

if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

include_once('../classes/Query.php');
include_once('../config.php');


$location = $_REQUEST["location"];
$item = $_REQUEST["item"] ?? '';

if(isset($_REQUEST['location'])){
    $get_items = new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                            From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code
                            Where WarehouseLocation.Name = ? and (StockItem.Status = ? or StockItem.Status = ? or StockItem.Status = ?)", 
                            [$_REQUEST['location'], 'S', 'O', 'E']);
    // $get_items = (new Query())->check_connection();
    $item_headers = $get_items->get_cols();
    $items = $get_items->get_results();
}

?>

Number of Rows: <?php echo $get_items->get_row_count(); ?>
<table class="table">
    <thead class="thead-dark">
        <tr>
            <?php foreach($item_headers as $header): ?>
                <th scope="col"><?php echo $header['Name']; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($items as $item): ?>
            <tr>
                <?php foreach($item as $i): ?>
                    <td><?php echo $i; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>