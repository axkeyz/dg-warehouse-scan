<?php

include_once('../classes/Query.php');
include_once('../config.php');

if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$location = $_REQUEST['location'];
$item = $_REQUEST['item'] ?? '';

if(isset($_REQUEST['location'])){
    $get_items = new Query("Select OriginalItemCode as 'Scanned Item Code', ItemCode as 'Current Item Code', WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                            From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code
                            Where WarehouseLocation.Name = ? and (StockItem.Status = ? or StockItem.Status = ? or StockItem.Status = ?)", 
                            [$_REQUEST['location'], 'S', 'O', 'E']);
    // $get_items = (new Query())->check_connection();
    $item_headers = $get_items->get_cols();
    $items = $get_items->get_results();
}

if(isset($_REQUEST['item'])){
    if(strlen($_REQUEST['item']) == 9){
        $get_max_dg_item = (new Query("Select ItemCode from StockItem where Counter = (select max(Counter) from StockItem where Owner = ?)", [34]))->get_results();
        $new_dg_item = intval(substr($get_max_dg_item[0]["ItemCode"], 2)) + 1;
        $max_counter = (new Query("Select max(Counter) as Counter from StockItem", NULL))->get_results();
        $new_counter = $max_counter[0]["Counter"] + 1;

        $new_item_details = (new Query("Select ItemCode as 'Scanned Item Code', 'Current Item Code' = ?, WarehouseLocation.Name as Location, Status.Name as 'Shipping Status' 
                                From StockItem left join WarehouseLocation on StockItem.Location = WarehouseLocation.Code left join Status on StockItem.Status = Status.Code Where StockItem.ItemCode = ?", ['DG'.$new_counter, $_REQUEST['item']]))->get_results()[0];
    }
}

?>

Number of Rows: <?php echo $get_items->get_row_count(); ?><br />
<?php if(isset($items) && is_array($items)): ?>
    <table class="table">
        <thead class="thead-dark">
            <tr>
                <?php foreach($item_headers as $header): ?>
                    <th scope="col"><?php echo $header['Name']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($new_item_details) && is_array($new_item_details)): ?>
                <tr>
                    <?php foreach($new_item_details as $new_detail): ?>
                        <td class="table-warning"><?php echo $new_detail; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endif; ?>
            <?php foreach($items as $item): ?>
                <tr>
                    <?php foreach($item as $i): ?>
                        <td><?php echo $i; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    There may be an error in your Warehouse Location as no items were found.
<?php endif; ?>